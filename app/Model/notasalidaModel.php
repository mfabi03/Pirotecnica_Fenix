<?php
// app/Model/notasalidaModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Interfaces\InterfacesCrudModel;

class notasalidaModel extends ConnectDB implements InterfacesCrudModel
{
    private $conex;

    public function __construct()
    {
        parent::__construct();
        $this->conex = $this->getConnection();
    }

    /* ============================================================
     *  MÉTODOS DE LA INTERFAZ (puente)
     * ============================================================ */

    public function obtenerTodos()
    {
        return $this->listarNotasSalida();
    }

    public function obtenerPorId($id)
    {
        return $this->obtenerNotaPorId($id);
    }

    public function crear($datos)
    {
        throw new Exception("Use registrarSalida() para crear notas de salida.");
    }

    public function actualizar($id, $datos)
    {
        throw new Exception("Las notas de salida no se actualizan, solo se anulan.");
    }

    public function eliminar($id)
    {
        return $this->eliminarNota($id);
    }

    /* ============================================================
     *  MÉTODOS ORIGINALES
     * ============================================================ */

    public function obtenerProductosConCategoria()
    {
        try {
            $sql = "SELECT p.id_producto, p.descripcion, p.cantidad AS stock, c.nombre_categoria, c.id_categoria
                    FROM producto p
                    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                    WHERE p.cantidad > 0 AND p.eliminado = 0
                    ORDER BY p.descripcion ASC";
            $stmt = $this->conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerProductosConCategoria: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerClientes()
    {
        try {
            $sql = "SELECT p.id_persona AS id_cliente, p.nombre, p.apellido, cj.razon_social,
                        CASE
                            WHEN cj.id_cliente_juridico IS NOT NULL THEN cj.razon_social
                            WHEN cn.id_cliente_natural IS NOT NULL THEN CONCAT(p.nombre, ' ', p.apellido)
                            ELSE CONCAT(p.nombre, ' ', p.apellido)
                        END AS nombre_cliente,
                        p.cedula,
                        CASE
                            WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico'
                            WHEN cn.id_cliente_natural IS NOT NULL THEN 'Natural'
                            ELSE 'Natural'
                        END AS tipo_cliente
                    FROM persona p
                    LEFT JOIN cliente_natural cn ON p.id_persona = cn.id_persona
                    LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                    WHERE (cj.id_cliente_juridico IS NOT NULL OR cn.id_cliente_natural IS NOT NULL)
                    AND p.eliminado = 0
                    ORDER BY nombre_cliente ASC";
            $stmt = $this->conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerClientes: " . $e->getMessage());
            return [];
        }
    }

    // 🔥 NUEVO: valida que el usuario exista y esté activo (movido del controlador)
    public function obtenerIdUsuarioValido($idUsuario)
    {
        if (!$idUsuario) {
            throw new Exception("No hay usuario en sesión.");
        }
        $stmt = $this->conex->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? AND eliminado = 0");
        $stmt->execute([$idUsuario]);
        if (!$stmt->fetch()) {
            throw new Exception("El usuario no existe o está inactivo.");
        }
        return (int) $idUsuario;
    }

    // 🔥 NUEVO: verifica si la caja ya fue cerrada hoy (movido del controlador)
    public function cajaCerradaHoy($idUsuario, $fecha = null)
    {
        $fecha = $fecha ?? date('Y-m-d');
        $stmt = $this->conex->prepare("SELECT COUNT(*) FROM cierre_caja WHERE fecha_cierre = :fecha AND id_usuario = :id_usuario");
        $stmt->execute([':fecha' => $fecha, ':id_usuario' => $idUsuario]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function registrarSalida($datos, $detalles, $idUsuario)
    {
        try {
            $stmtCheck = $this->conex->prepare("SELECT id_persona FROM persona WHERE id_persona = ? AND eliminado = 0");
            $stmtCheck->execute([$datos['id_cliente']]);
            if (!$stmtCheck->fetch()) throw new Exception("El cliente seleccionado no existe o se encuentra inactivo.");

            $stmtCheckUser = $this->conex->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? AND eliminado = 0");
            $stmtCheckUser->execute([$idUsuario]);
            if (!$stmtCheckUser->fetch()) throw new Exception("El usuario con ID $idUsuario no existe en la base de datos.");

            foreach ($detalles as $d) {
                $stmtStock = $this->conex->prepare("SELECT cantidad, eliminado, descripcion FROM producto WHERE id_producto = ?");
                $stmtStock->execute([$d['id_producto']]);
                $producto = $stmtStock->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception("El producto ID {$d['id_producto']} no existe.");
                }
                if ($producto['eliminado'] == 1) {
                    throw new Exception("El producto '{$producto['descripcion']}' está eliminado y no se puede vender.");
                }
                if ($producto['cantidad'] < $d['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto ID {$d['id_producto']}. Disponible: {$producto['cantidad']}");
                }
            }

            $this->conex->beginTransaction();
            try {
                $stmt = $this->conex->prepare("INSERT INTO nota_de_salida (fecha, id_persona, id_usuario, eliminado) VALUES (NOW(), ?, ?, 0)");
                $stmt->execute([$datos['id_cliente'], $idUsuario]);

                $idNota = $this->conex->lastInsertId();

                $stmtDetalle = $this->conex->prepare("INSERT INTO detalle_salida (id_nota_salida, id_producto, cantidad) VALUES (?, ?, ?)");
                $stmtUpdate = $this->conex->prepare("UPDATE producto SET cantidad = cantidad - ? WHERE id_producto = ?");

                foreach ($detalles as $d) {
                    $stmtDetalle->execute([$idNota, $d['id_producto'], $d['cantidad']]);
                    $stmtUpdate->execute([$d['cantidad'], $d['id_producto']]);
                }

                $this->conex->commit();
                return $idNota;
            } catch (Exception $e) {
                $this->conex->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            error_log("Error en registrarSalida: " . $e->getMessage());
            throw $e;
        }
    }

    public function listarNotasSalida($id_usuario = null)
    {
        try {
            $sql = "SELECT id_nota_salida, fecha, id_persona, id_usuario, eliminado, motivo_anulacion
                    FROM nota_de_salida";

            if ($id_usuario !== null) {
                $sql .= " WHERE id_usuario = :id_usuario";
            }

            $sql .= " ORDER BY id_nota_salida DESC";

            $stmt = $this->conex->prepare($sql);
            if ($id_usuario !== null) {
                $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            }
            $stmt->execute();
            $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($notas as &$n) {
                $stmtCliente = $this->conex->prepare("SELECT p.nombre, p.apellido, cj.razon_social,
                                    CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente
                                FROM persona p
                                LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                                WHERE p.id_persona = ?");
                $stmtCliente->execute([$n['id_persona']]);
                $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

                if ($cliente) {
                    $n['cliente_nombre'] = $cliente['nombre'] ?? '';
                    $n['cliente_apellido'] = $cliente['apellido'] ?? '';
                    $n['cliente_razon_social'] = $cliente['razon_social'] ?? '';
                    $n['tipo_cliente'] = $cliente['tipo_cliente'] ?? 'Natural';
                } else {
                    $n['cliente_nombre'] = 'N/A';
                    $n['cliente_apellido'] = '';
                    $n['cliente_razon_social'] = '';
                    $n['tipo_cliente'] = 'Natural';
                }

                $stmtUsuario = $this->conex->prepare("SELECT p.nombre, p.apellido FROM usuario u LEFT JOIN persona p ON u.id_persona = p.id_persona WHERE u.id_usuario = ?");
                $stmtUsuario->execute([$n['id_usuario']]);
                $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    $n['usuario_nombre'] = $usuario['nombre'] ?? '';
                    $n['usuario_apellido'] = $usuario['apellido'] ?? '';
                } else {
                    $n['usuario_nombre'] = 'N/A';
                    $n['usuario_apellido'] = '';
                }

                $stmtProductos = $this->conex->prepare("SELECT p.descripcion AS producto, c.nombre_categoria AS categoria, ds.cantidad
                                FROM detalle_salida ds
                                JOIN producto p ON ds.id_producto = p.id_producto
                                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                                WHERE ds.id_nota_salida = ?");
                $stmtProductos->execute([$n['id_nota_salida']]);
                $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

                $listaProductos = [];
                $listaCategorias = [];
                foreach ($productos as $p) {
                    $listaProductos[] = $p;
                    if (!empty($p['categoria']) && !in_array($p['categoria'], $listaCategorias)) {
                        $listaCategorias[] = $p['categoria'];
                    }
                }
                $n['productos_lista'] = implode(', ', array_map(function($p) { return $p['producto'] . ' (x' . $p['cantidad'] . ')'; }, $listaProductos));
                $n['total_productos'] = count($listaProductos);
                $n['categorias_lista'] = implode(', ', $listaCategorias);
                $n['total_categorias'] = count($listaCategorias);
                $n['productos_detalle'] = $productos;

                $stmtUnidades = $this->conex->prepare("SELECT COALESCE(SUM(cantidad), 0) as total FROM detalle_salida WHERE id_nota_salida = ?");
                $stmtUnidades->execute([$n['id_nota_salida']]);
                $unidades = $stmtUnidades->fetch(PDO::FETCH_ASSOC);
                $n['total_unidades'] = $unidades['total'] ?? 0;
            }
            return $notas;
        } catch (PDOException $e) {
            error_log("Error en listarNotasSalida: " . $e->getMessage());
            return [];
        }
    }

    public function buscarNotasSalida($termino, $id_usuario = null)
    {
        try {
            $termino = trim((string) $termino);
            if ($termino === '') return $this->listarNotasSalida($id_usuario);

            $sql = "SELECT ns.id_nota_salida, ns.fecha, ns.id_persona, ns.id_usuario, ns.eliminado, ns.motivo_anulacion
                    FROM nota_de_salida ns
                    LEFT JOIN persona p ON ns.id_persona = p.id_persona
                    LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                    LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                    LEFT JOIN persona up ON u.id_persona = up.id_persona
                    WHERE (CAST(ns.id_nota_salida AS CHAR) LIKE :termino OR p.nombre LIKE :termino OR p.apellido LIKE :termino
                           OR CONCAT(p.nombre, ' ', p.apellido) LIKE :termino OR up.nombre LIKE :termino OR up.apellido LIKE :termino
                           OR CONCAT(up.nombre, ' ', up.apellido) LIKE :termino OR COALESCE(cj.razon_social, '') LIKE :termino)";

            if ($id_usuario !== null) {
                $sql .= " AND ns.id_usuario = :id_usuario";
            }

            $sql .= " GROUP BY ns.id_nota_salida, ns.fecha, ns.id_persona, ns.id_usuario, ns.eliminado, ns.motivo_anulacion
                    ORDER BY ns.id_nota_salida DESC";

            $stmt = $this->conex->prepare($sql);
            $stmt->bindValue(':termino', "%$termino%");
            if ($id_usuario !== null) {
                $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            }
            $stmt->execute();
            $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($notas as &$n) {
                $stmtCliente = $this->conex->prepare("SELECT p.nombre, p.apellido, cj.razon_social,
                                    CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente
                                FROM persona p
                                LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                                WHERE p.id_persona = ?");
                $stmtCliente->execute([$n['id_persona']]);
                $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

                if ($cliente) {
                    $n['cliente_nombre'] = $cliente['nombre'] ?? '';
                    $n['cliente_apellido'] = $cliente['apellido'] ?? '';
                    $n['cliente_razon_social'] = $cliente['razon_social'] ?? '';
                    $n['tipo_cliente'] = $cliente['tipo_cliente'] ?? 'Natural';
                } else {
                    $n['cliente_nombre'] = 'N/A';
                    $n['cliente_apellido'] = '';
                    $n['cliente_razon_social'] = '';
                    $n['tipo_cliente'] = 'Natural';
                }

                $stmtUsuario = $this->conex->prepare("SELECT p.nombre, p.apellido FROM usuario u LEFT JOIN persona p ON u.id_persona = p.id_persona WHERE u.id_usuario = ?");
                $stmtUsuario->execute([$n['id_usuario']]);
                $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    $n['usuario_nombre'] = $usuario['nombre'] ?? '';
                    $n['usuario_apellido'] = $usuario['apellido'] ?? '';
                } else {
                    $n['usuario_nombre'] = 'N/A';
                    $n['usuario_apellido'] = '';
                }

                $stmtProductos = $this->conex->prepare("SELECT p.descripcion AS producto, c.nombre_categoria AS categoria, ds.cantidad
                                FROM detalle_salida ds
                                JOIN producto p ON ds.id_producto = p.id_producto
                                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                                WHERE ds.id_nota_salida = ?");
                $stmtProductos->execute([$n['id_nota_salida']]);
                $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

                $listaProductos = [];
                $listaCategorias = [];
                foreach ($productos as $p) {
                    $listaProductos[] = $p;
                    if (!empty($p['categoria']) && !in_array($p['categoria'], $listaCategorias)) {
                        $listaCategorias[] = $p['categoria'];
                    }
                }
                $n['productos_lista'] = implode(', ', array_map(function($p) { return $p['producto'] . ' (x' . $p['cantidad'] . ')'; }, $listaProductos));
                $n['total_productos'] = count($listaProductos);
                $n['categorias_lista'] = implode(', ', $listaCategorias);
                $n['total_categorias'] = count($listaCategorias);
                $n['productos_detalle'] = $productos;

                $stmtUnidades = $this->conex->prepare("SELECT COALESCE(SUM(cantidad), 0) as total FROM detalle_salida WHERE id_nota_salida = ?");
                $stmtUnidades->execute([$n['id_nota_salida']]);
                $unidades = $stmtUnidades->fetch(PDO::FETCH_ASSOC);
                $n['total_unidades'] = $unidades['total'] ?? 0;
            }

            return $notas;
        } catch (PDOException $e) {
            error_log("Error en buscarNotasSalida: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerNotaPorId($id)
    {
        try {
            $sql = "SELECT id_nota_salida, fecha, id_persona, id_usuario, eliminado, motivo_anulacion
                    FROM nota_de_salida
                    WHERE id_nota_salida = ?";
            $stmt = $this->conex->prepare($sql);
            $stmt->execute([$id]);
            $nota = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$nota) return null;

            $sqlCliente = "SELECT p.nombre AS cliente_nombre, p.apellido AS cliente_apellido, p.cedula AS cliente_cedula,
                                cj.rif AS cliente_rif, cj.razon_social AS cliente_razon_social,
                                up.nombre AS usuario_nombre, up.apellido AS usuario_apellido
                            FROM nota_de_salida ns
                            LEFT JOIN persona p ON ns.id_persona = p.id_persona
                            LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                            LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                            LEFT JOIN persona up ON u.id_persona = up.id_persona
                            WHERE ns.id_nota_salida = ?";
            $stmtCliente = $this->conex->prepare($sqlCliente);
            $stmtCliente->execute([$id]);
            $info = $stmtCliente->fetch(PDO::FETCH_ASSOC);

            if ($info) {
                $nota['cliente'] = !empty($info['cliente_razon_social']) ? $info['cliente_razon_social'] : trim(($info['cliente_nombre'] ?? '') . ' ' . ($info['cliente_apellido'] ?? ''));
                $nota['cedula'] = $info['cliente_cedula'] ?? '';
                $nota['rif'] = $info['cliente_rif'] ?? '';
                $nota['documento'] = !empty($info['cliente_rif']) ? $info['cliente_rif'] : ($info['cliente_cedula'] ?? 'N/A');
                $nota['usuario_responsable'] = trim(($info['usuario_nombre'] ?? '') . ' ' . ($info['usuario_apellido'] ?? ''));
                if ($nota['usuario_responsable'] === '') $nota['usuario_responsable'] = 'N/A';
            } else {
                $nota['cliente'] = 'N/A';
                $nota['cedula'] = 'N/A';
                $nota['rif'] = 'N/A';
                $nota['documento'] = 'N/A';
                $nota['usuario_responsable'] = 'N/A';
            }

            $detSql = "SELECT
                            ds.id_detalle_salida,
                            ds.id_producto,
                            ds.cantidad,
                            p.descripcion AS nombre_producto,
                            c.nombre_categoria
                        FROM detalle_salida ds
                        JOIN producto p ON ds.id_producto = p.id_producto
                        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                        WHERE ds.id_nota_salida = ?";
            $detStmt = $this->conex->prepare($detSql);
            $detStmt->execute([$id]);
            $nota['detalles'] = $detStmt->fetchAll(PDO::FETCH_ASSOC);

            return $nota;
        } catch (PDOException $e) {
            error_log("Error en obtenerNotaPorId: " . $e->getMessage());
            return null;
        }
    }

    public function eliminarNota($id, $motivo = null)
    {
        try {
            $stmtCheck = $this->conex->prepare("SELECT id_nota_salida FROM nota_de_salida WHERE id_nota_salida = ? AND eliminado = 0");
            $stmtCheck->execute([$id]);
            if (!$stmtCheck->fetch()) throw new Exception("La nota no existe");

            $this->conex->beginTransaction();
            try {
                $stmtDet = $this->conex->prepare("SELECT id_producto, cantidad FROM detalle_salida WHERE id_nota_salida = ?");
                $stmtDet->execute([$id]);
                $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

                $stmtRestaurar = $this->conex->prepare("UPDATE producto SET cantidad = cantidad + ? WHERE id_producto = ?");
                foreach ($detalles as $d) {
                    $stmtRestaurar->execute([$d['cantidad'], $d['id_producto']]);
                }

                $stmtUpd = $this->conex->prepare("UPDATE nota_de_salida SET eliminado = 1, motivo_anulacion = :motivo WHERE id_nota_salida = :id");
                $stmtUpd->execute([':motivo' => $motivo, ':id' => $id]);

                $this->conex->commit();
                return true;
            } catch (Exception $e) {
                $this->conex->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            error_log("Error en eliminarNota: " . $e->getMessage());
            throw $e;
        }
    }

    public function getResumen($id_usuario = null)
    {
        try {
            $sql = "SELECT COUNT(*) AS total_registros,
                        SUM(CASE WHEN eliminado = 0 THEN 1 ELSE 0 END) AS total_notas,
                        SUM(CASE WHEN eliminado = 1 THEN 1 ELSE 0 END) AS total_anuladas
                    FROM nota_de_salida
                    WHERE 1=1";

            if ($id_usuario !== null) {
                $sql .= " AND id_usuario = :id_usuario";
            }

            $stmt = $this->conex->prepare($sql);
            if ($id_usuario !== null) {
                $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total_notas' => (int) ($result['total_notas'] ?? 0),
                'total_anuladas' => (int) ($result['total_anuladas'] ?? 0),
                'total_registros' => (int) ($result['total_registros'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error en getResumen: " . $e->getMessage());
            return ['total_notas' => 0, 'total_anuladas' => 0, 'total_registros' => 0];
        }
    }
}