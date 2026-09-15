<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;

class notasalidaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // OBTENER PRODUCTOS CON CATEGORIA
    public function obtenerProductosConCategoria() {
        try {
            $sql = "SELECT 
                        p.id_producto, 
                        p.descripcion, 
                        p.cantidad AS stock,
                        c.nombre_categoria,
                        c.id_categoria
                    FROM producto p
                    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                    WHERE p.cantidad > 0 AND p.eliminado = 0
                    ORDER BY p.descripcion ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerProductosConCategoria: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER CLIENTES
    public function obtenerClientes() {
        try {
            $sql = "SELECT 
                        p.id_persona AS id_cliente,
                        p.nombre,
                        p.apellido,
                        cj.razon_social,
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
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerClientes: " . $e->getMessage());
            return [];
        }
    }

    // REGISTRAR SALIDA
    public function registrarSalida($datos, $detalles, $idUsuario) {
        try {
            $sqlCheckCliente = "SELECT id_persona FROM persona WHERE id_persona = ? AND eliminado = 0";
            $stmtCheck = $this->db->prepare($sqlCheckCliente);
            $stmtCheck->execute([$datos['id_cliente']]);
            $clienteExiste = $stmtCheck->fetch();
            
            if (!$clienteExiste) {
                throw new Exception("El cliente seleccionado no existe en la base de datos.");
            }
            
            $sqlCheckUsuario = "SELECT id_usuario FROM usuario WHERE id_usuario = ? AND eliminado = 0";
            $stmtCheckUser = $this->db->prepare($sqlCheckUsuario);
            $stmtCheckUser->execute([$idUsuario]);
            $usuarioExiste = $stmtCheckUser->fetch();
            
            if (!$usuarioExiste) {
                throw new Exception("El usuario con ID $idUsuario no existe en la base de datos.");
            }
            
            foreach ($detalles as $d) {
                $sqlStock = "SELECT cantidad FROM producto WHERE id_producto = ? AND eliminado = 0";
                $stmtStock = $this->db->prepare($sqlStock);
                $stmtStock->execute([$d['id_producto']]);
                $stockActual = $stmtStock->fetchColumn();
                
                if ($stockActual < $d['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto ID {$d['id_producto']}. Disponible: $stockActual");
                }
            }
            
            $this->db->beginTransaction();
            
            try {
                $sql = "INSERT INTO nota_de_salida (fecha, id_persona, id_usuario, eliminado) 
                        VALUES (CURDATE(), ?, ?, 0)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $datos['id_cliente'], 
                    $idUsuario
                ]);
                
                $idNota = $this->db->lastInsertId();
                
                $sqlDetalle = "INSERT INTO detalle_salida (id_nota_salida, id_producto, cantidad) 
                               VALUES (?, ?, ?)";
                $stmtDetalle = $this->db->prepare($sqlDetalle);
                
                $sqlUpdateStock = "UPDATE producto SET cantidad = cantidad - ? WHERE id_producto = ?";
                $stmtUpdate = $this->db->prepare($sqlUpdateStock);
                
                foreach ($detalles as $d) {
                    $stmtDetalle->execute([
                        $idNota,
                        $d['id_producto'],
                        $d['cantidad']
                    ]);
                    
                    $stmtUpdate->execute([
                        $d['cantidad'],
                        $d['id_producto']
                    ]);
                }
                
                $this->db->commit();
                return $idNota;
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error en registrarSalida: " . $e->getMessage());
            throw $e;
        }
    }

    // LISTAR NOTAS DE SALIDA
    public function listarNotasSalida() {
        try {
            $sql = "SELECT id_nota_salida, fecha, id_persona, id_usuario 
                    FROM nota_de_salida 
                    WHERE eliminado = 0
                    ORDER BY id_nota_salida DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($notas as &$n) {
                $sqlCliente = "SELECT 
                                    p.nombre, 
                                    p.apellido, 
                                    cj.razon_social,
                                    CASE 
                                        WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico'
                                        ELSE 'Natural'
                                    END AS tipo_cliente
                                FROM persona p
                                LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                                WHERE p.id_persona = ?";
                $stmtCliente = $this->db->prepare($sqlCliente);
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
                
                $sqlUsuario = "SELECT p.nombre, p.apellido
                               FROM usuario u
                               LEFT JOIN persona p ON u.id_persona = p.id_persona
                               WHERE u.id_usuario = ?";
                $stmtUsuario = $this->db->prepare($sqlUsuario);
                $stmtUsuario->execute([$n['id_usuario']]);
                $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
                
                if ($usuario) {
                    $n['usuario_nombre'] = $usuario['nombre'] ?? '';
                    $n['usuario_apellido'] = $usuario['apellido'] ?? '';
                } else {
                    $n['usuario_nombre'] = 'N/A';
                    $n['usuario_apellido'] = '';
                }
                
                $sqlProductos = "SELECT 
                                    p.descripcion AS producto,
                                    c.nombre_categoria AS categoria,
                                    ds.cantidad
                                FROM detalle_salida ds
                                JOIN producto p ON ds.id_producto = p.id_producto
                                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                                WHERE ds.id_nota_salida = ?";
                $stmtProductos = $this->db->prepare($sqlProductos);
                $stmtProductos->execute([$n['id_nota_salida']]);
                $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
                
                $listaProductos = [];
                $listaCategorias = [];
                foreach ($productos as $p) {
                    $listaProductos[] = $p['producto'] . ' (x' . $p['cantidad'] . ')';
                    if (!empty($p['categoria']) && !in_array($p['categoria'], $listaCategorias)) {
                        $listaCategorias[] = $p['categoria'];
                    }
                }
                $n['productos_lista'] = implode(', ', $listaProductos);
                $n['categorias_lista'] = implode(', ', $listaCategorias);
                $n['productos_detalle'] = $productos;
                
                $sqlUnidades = "SELECT COALESCE(SUM(cantidad), 0) as total FROM detalle_salida WHERE id_nota_salida = ?";
                $stmtUnidades = $this->db->prepare($sqlUnidades);
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

    // ✅ CONTAR NOTAS DE SALIDA (para paginación)
    public function contarNotasSalida() {
        try {
            $sql = "SELECT COUNT(*) FROM nota_de_salida WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en contarNotasSalida: " . $e->getMessage());
            return 0;
        }
    }

    public function buscarNotasSalida($termino) {
        try {
            $termino = trim((string) $termino);
            if ($termino === '') {
                return $this->listarNotasSalida();
            }

            $sql = "SELECT ns.id_nota_salida, ns.fecha, ns.id_persona, ns.id_usuario 
                    FROM nota_de_salida ns
                    LEFT JOIN persona p ON ns.id_persona = p.id_persona
                    LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                    LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                    LEFT JOIN persona up ON u.id_persona = up.id_persona
                    WHERE ns.eliminado = 0
                      AND (
                            CAST(ns.id_nota_salida AS CHAR) LIKE :termino
                         OR p.nombre LIKE :termino
                         OR p.apellido LIKE :termino
                         OR CONCAT(p.nombre, ' ', p.apellido) LIKE :termino
                         OR up.nombre LIKE :termino
                         OR up.apellido LIKE :termino
                         OR CONCAT(up.nombre, ' ', up.apellido) LIKE :termino
                         OR COALESCE(cj.razon_social, '') LIKE :termino
                      )
                    GROUP BY ns.id_nota_salida, ns.fecha, ns.id_persona, ns.id_usuario
                    ORDER BY ns.id_nota_salida DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':termino' => "%$termino%"]);
            $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($notas as &$n) {
                $sqlCliente = "SELECT 
                                    p.nombre, 
                                    p.apellido, 
                                    cj.razon_social,
                                    CASE 
                                        WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico'
                                        ELSE 'Natural'
                                    END AS tipo_cliente
                                FROM persona p
                                LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                                WHERE p.id_persona = ?";
                $stmtCliente = $this->db->prepare($sqlCliente);
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

                $sqlUsuario = "SELECT p.nombre, p.apellido
                               FROM usuario u
                               LEFT JOIN persona p ON u.id_persona = p.id_persona
                               WHERE u.id_usuario = ?";
                $stmtUsuario = $this->db->prepare($sqlUsuario);
                $stmtUsuario->execute([$n['id_usuario']]);
                $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    $n['usuario_nombre'] = $usuario['nombre'] ?? '';
                    $n['usuario_apellido'] = $usuario['apellido'] ?? '';
                } else {
                    $n['usuario_nombre'] = 'N/A';
                    $n['usuario_apellido'] = '';
                }

                $sqlProductos = "SELECT 
                                    p.descripcion AS producto,
                                    c.nombre_categoria AS categoria,
                                    ds.cantidad
                                FROM detalle_salida ds
                                JOIN producto p ON ds.id_producto = p.id_producto
                                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                                WHERE ds.id_nota_salida = ?";
                $stmtProductos = $this->db->prepare($sqlProductos);
                $stmtProductos->execute([$n['id_nota_salida']]);
                $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

                $listaProductos = [];
                $listaCategorias = [];
                foreach ($productos as $p) {
                    $listaProductos[] = $p['producto'] . ' (x' . $p['cantidad'] . ')';
                    if (!empty($p['categoria']) && !in_array($p['categoria'], $listaCategorias)) {
                        $listaCategorias[] = $p['categoria'];
                    }
                }
                $n['productos_lista'] = implode(', ', $listaProductos);
                $n['categorias_lista'] = implode(', ', $listaCategorias);
                $n['productos_detalle'] = $productos;

                $sqlUnidades = "SELECT COALESCE(SUM(cantidad), 0) as total FROM detalle_salida WHERE id_nota_salida = ?";
                $stmtUnidades = $this->db->prepare($sqlUnidades);
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

    // OBTENER NOTA POR ID
    public function obtenerNotaPorId($id) {
        try {
            $sql = "SELECT id_nota_salida, fecha, id_persona, id_usuario
                    FROM nota_de_salida 
                    WHERE id_nota_salida = ? AND eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $nota = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$nota) {
                return null;
            }
            
            $sqlCliente = "SELECT 
                                p.nombre AS cliente_nombre,
                                p.apellido AS cliente_apellido,
                                cj.razon_social AS cliente_razon_social,
                                up.nombre AS usuario_nombre,
                                up.apellido AS usuario_apellido
                            FROM nota_de_salida ns
                            LEFT JOIN persona p ON ns.id_persona = p.id_persona
                            LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                            LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                            LEFT JOIN persona up ON u.id_persona = up.id_persona
                            WHERE ns.id_nota_salida = ?";
            $stmtCliente = $this->db->prepare($sqlCliente);
            $stmtCliente->execute([$id]);
            $info = $stmtCliente->fetch(PDO::FETCH_ASSOC);
            
            if ($info) {
                $nota['cliente'] = !empty($info['cliente_razon_social'])
                    ? $info['cliente_razon_social']
                    : trim(($info['cliente_nombre'] ?? '') . ' ' . ($info['cliente_apellido'] ?? ''));
                $nota['usuario_responsable'] = trim(($info['usuario_nombre'] ?? '') . ' ' . ($info['usuario_apellido'] ?? ''));
                if ($nota['usuario_responsable'] === '') {
                    $nota['usuario_responsable'] = 'N/A';
                }
            } else {
                $nota['cliente'] = 'N/A';
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
            $detStmt = $this->db->prepare($detSql);
            $detStmt->execute([$id]);
            $nota['detalles'] = $detStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $nota;
        } catch (PDOException $e) {
            error_log("Error en obtenerNotaPorId: " . $e->getMessage());
            return null;
        }
    }

    // ACTUALIZAR NOTA
    public function actualizarNota($id, $datos, $detalles, $idUsuario) {
        try {
            $sqlCheck = "SELECT id_nota_salida FROM nota_de_salida WHERE id_nota_salida = ? AND eliminado = 0";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$id]);
            $nota = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$nota) {
                throw new Exception("La nota no existe");
            }
            
            $sqlCheckUsuario = "SELECT id_usuario FROM usuario WHERE id_usuario = ? AND eliminado = 0";
            $stmtCheckUser = $this->db->prepare($sqlCheckUsuario);
            $stmtCheckUser->execute([$idUsuario]);
            $usuarioExiste = $stmtCheckUser->fetch();
            
            if (!$usuarioExiste) {
                throw new Exception("El usuario con ID $idUsuario no existe en la base de datos.");
            }
            
            $this->db->beginTransaction();
            
            try {
                $sqlOld = "SELECT id_producto, cantidad FROM detalle_salida WHERE id_nota_salida = ?";
                $stmtOld = $this->db->prepare($sqlOld);
                $stmtOld->execute([$id]);
                $oldDetalles = $stmtOld->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($oldDetalles as $old) {
                    $sqlRestaurar = "UPDATE producto SET cantidad = cantidad + ? WHERE id_producto = ?";
                    $stmtRestaurar = $this->db->prepare($sqlRestaurar);
                    $stmtRestaurar->execute([$old['cantidad'], $old['id_producto']]);
                }
                
                $sqlDelete = "DELETE FROM detalle_salida WHERE id_nota_salida = ?";
                $stmtDelete = $this->db->prepare($sqlDelete);
                $stmtDelete->execute([$id]);
                
                foreach ($detalles as $d) {
                    $sqlStock = "SELECT cantidad FROM producto WHERE id_producto = ? AND eliminado = 0";
                    $stmtStock = $this->db->prepare($sqlStock);
                    $stmtStock->execute([$d['id_producto']]);
                    $stockActual = $stmtStock->fetchColumn();
                    
                    if ($stockActual < $d['cantidad']) {
                        throw new Exception("Stock insuficiente para el producto ID {$d['id_producto']}. Disponible: $stockActual");
                    }
                }
                
                $sql = "UPDATE nota_de_salida SET id_persona = ?, id_usuario = ?
                        WHERE id_nota_salida = ? AND eliminado = 0";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $datos['id_cliente'],
                    $idUsuario,
                    $id
                ]);
                
                $sqlDetalle = "INSERT INTO detalle_salida (id_nota_salida, id_producto, cantidad) 
                               VALUES (?, ?, ?)";
                $stmtDetalle = $this->db->prepare($sqlDetalle);
                
                $sqlUpdateStock = "UPDATE producto SET cantidad = cantidad - ? WHERE id_producto = ?";
                $stmtUpdate = $this->db->prepare($sqlUpdateStock);
                
                foreach ($detalles as $d) {
                    $stmtDetalle->execute([
                        $id,
                        $d['id_producto'],
                        $d['cantidad']
                    ]);
                    
                    $stmtUpdate->execute([
                        $d['cantidad'],
                        $d['id_producto']
                    ]);
                }
                
                $this->db->commit();
                return true;
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error en actualizarNota: " . $e->getMessage());
            throw $e;
        }
    }

    // ELIMINAR NOTA
    public function eliminarNota($id) {
        try {
            $sqlCheck = "SELECT id_nota_salida FROM nota_de_salida WHERE id_nota_salida = ? AND eliminado = 0";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$id]);
            $nota = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$nota) {
                throw new Exception("La nota no existe");
            }
            
            $this->db->beginTransaction();
            
            try {
                $sqlDet = "SELECT id_producto, cantidad FROM detalle_salida WHERE id_nota_salida = ?";
                $stmtDet = $this->db->prepare($sqlDet);
                $stmtDet->execute([$id]);
                $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

                $sqlRestaurar = "UPDATE producto SET cantidad = cantidad + ? WHERE id_producto = ?";
                $stmtRestaurar = $this->db->prepare($sqlRestaurar);
                foreach ($detalles as $d) {
                    $stmtRestaurar->execute([$d['cantidad'], $d['id_producto']]);
                }

                $sqlUpd = "UPDATE nota_de_salida SET eliminado = 1 WHERE id_nota_salida = :id";
                $stmtUpd = $this->db->prepare($sqlUpd);
                $stmtUpd->execute([':id' => $id]);
                
                $this->db->commit();
                return true;
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error en eliminarNota: " . $e->getMessage());
            throw $e;
        }
    }

    // OBTENER RESUMEN
    public function getResumen() {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_registros,
                        SUM(CASE WHEN eliminado = 0 THEN 1 ELSE 0 END) AS total_notas,
                        SUM(CASE WHEN eliminado = 1 THEN 1 ELSE 0 END) AS total_anuladas
                    FROM nota_de_salida";
            $stmt = $this->db->prepare($sql);
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