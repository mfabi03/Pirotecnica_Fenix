<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;

class ProductoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // OBTENER TODOS LOS PRODUCTOS (Activos e Inactivos)
    public function obtenerProductos() {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.descripcion,
                        p.cantidad AS stock,
                        p.stock_minimo,
                        p.costo_unitario,
                        p.id_categoria,
                        p.especificaciones,
                        p.id_proveedor,
                        p.eliminado,
                        c.nombre_categoria,
                        COALESCE(
                            (SELECT prov.razon_social 
                             FROM detalle_entrada de 
                             INNER JOIN nota_de_entrada ne ON de.id_nota_entrada = ne.id_nota_entrada 
                             INNER JOIN proveedor prov ON ne.id_proveedor = prov.id_proveedor 
                             WHERE de.id_producto = p.id_producto AND ne.eliminado = 0 
                             ORDER BY ne.fecha_ingreso DESC LIMIT 1),
                            (SELECT prov2.razon_social FROM proveedor prov2 WHERE prov2.id_proveedor = p.id_proveedor)
                        ) AS nombre_proveedor
                    FROM producto p
                    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                    ORDER BY p.eliminado ASC, p.id_producto DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerProductos: " . $e->getMessage());
            return [];
        }
    }

    // CONTAR PRODUCTOS
    public function contarProductos($termino = null) {
        try {
            $termino = trim((string) $termino);
            
            if ($termino !== '') {
                $sql = "SELECT COUNT(*) 
                        FROM producto p
                        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                        WHERE p.eliminado = 0
                        AND (p.descripcion LIKE :termino OR c.nombre_categoria LIKE :termino)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['termino' => "%{$termino}%"]);
            } else {
                $sql = "SELECT COUNT(*) FROM producto WHERE eliminado = 0";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en contarProductos: " . $e->getMessage());
            return 0;
        }
    }

    // OBTENER PRODUCTO POR ID (Sin importar el estado)
    public function obtenerProductoPorId($id) {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.descripcion,
                        p.cantidad AS stock,
                        p.stock_minimo,
                        p.costo_unitario,
                        p.id_categoria,
                        p.especificaciones,
                        p.id_proveedor,
                        p.eliminado,
                        c.nombre_categoria,
                        COALESCE(
                            (SELECT prov.razon_social 
                             FROM detalle_entrada de 
                             INNER JOIN nota_de_entrada ne ON de.id_nota_entrada = ne.id_nota_entrada 
                             INNER JOIN proveedor prov ON ne.id_proveedor = prov.id_proveedor 
                             WHERE de.id_producto = p.id_producto AND ne.eliminado = 0 
                             ORDER BY ne.fecha_ingreso DESC LIMIT 1),
                            (SELECT prov2.razon_social FROM proveedor prov2 WHERE prov2.id_proveedor = p.id_proveedor)
                        ) AS nombre_proveedor
                    FROM producto p
                    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                    WHERE p.id_producto = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerProductoPorId: " . $e->getMessage());
            return null;
        }
    }

    // OBTENER CATEGORÍAS
    public function obtenerCategorias() {
        try {
            $sql = "SELECT id_categoria, nombre_categoria 
                    FROM categoria 
                    WHERE eliminado = 0
                    ORDER BY nombre_categoria ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCategorias: " . $e->getMessage());
            return [];
        }
    }

    // REGISTRAR PRODUCTO
    public function registrarProducto($datos) {
        try {
            $sql = "INSERT INTO producto (
                        descripcion,
                        cantidad,
                        stock_minimo,
                        costo_unitario,
                        id_categoria,
                        especificaciones,
                        id_proveedor,
                        eliminado
                    ) VALUES (
                        :descripcion,
                        :cantidad,
                        :stock_minimo,
                        :costo_unitario,
                        :id_categoria,
                        :especificaciones,
                        :id_proveedor,
                        0
                    )";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':descripcion' => $datos['descripcion'],
                ':cantidad' => $datos['cantidad'] ?? 0,
                ':stock_minimo' => $datos['stock_minimo'] ?? 10,
                ':costo_unitario' => $datos['costo_unitario'] ?? 0,
                ':id_categoria' => $datos['id_categoria'] ?? null,
                ':especificaciones' => $datos['especificaciones'] ?? null,
                ':id_proveedor' => $datos['id_proveedor'] ?? null
            ]);

            if ($success) {
                return intval($this->db->lastInsertId());
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en registrarProducto: " . $e->getMessage());
            return false;
        }
    }

    // ACTUALIZAR PRODUCTO
    public function actualizarProducto($id, $datos) {
        try {
            $sql = "UPDATE producto SET 
                        descripcion = :descripcion,
                        stock_minimo = :stock_minimo,
                        especificaciones = :especificaciones
                    WHERE id_producto = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':descripcion' => $datos['descripcion'],
                ':stock_minimo' => $datos['stock_minimo'] ?? 10,
                ':especificaciones' => $datos['especificaciones'] ?? null,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizarProducto: " . $e->getMessage());
            return false;
        }
    }

    // VERIFICAR ASOCIACIONES
    public function verificarAsociaciones($id) {
        try {
            $stmtEntrada = $this->db->prepare("
                SELECT COUNT(*) 
                FROM detalle_entrada d
                INNER JOIN nota_de_entrada n ON d.id_nota_entrada = n.id_nota_entrada
                WHERE d.id_producto = ? 
                AND n.eliminado = 0
            ");
            $stmtEntrada->execute([$id]);
            $entradas = (int) $stmtEntrada->fetchColumn();

            $stmtSalida = $this->db->prepare("
                SELECT COUNT(*) 
                FROM detalle_salida d
                INNER JOIN nota_de_salida n ON d.id_nota_salida = n.id_nota_salida
                WHERE d.id_producto = ? 
                AND n.eliminado = 0
            ");
            $stmtSalida->execute([$id]);
            $salidas = (int) $stmtSalida->fetchColumn();

            return [
                'tiene_asociaciones' => ($entradas > 0 || $salidas > 0),
                'entradas' => $entradas,
                'salidas' => $salidas
            ];
        } catch (PDOException $e) {
            error_log("Error al verificar asociaciones: " . $e->getMessage());
            return ['tiene_asociaciones' => false, 'entradas' => 0, 'salidas' => 0];
        }
    }

    // ELIMINAR PRODUCTO
    public function eliminarProducto($id) {
        try {
            $asociaciones = $this->verificarAsociaciones($id);
            if ($asociaciones['tiene_asociaciones']) {
                throw new Exception('No se pudo eliminar el producto porque está asociado o relacionado con notas de entrada/salida.');
            }

            $sql = "UPDATE producto SET eliminado = 1 WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminarProducto: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // REACTIVAR PRODUCTO
    public function reactivarProducto($id) {
        try {
            $stmtCheck = $this->db->prepare("SELECT id_producto FROM producto WHERE id_producto = :id AND eliminado = 1 LIMIT 1");
            $stmtCheck->execute([':id' => $id]);
            if (!$stmtCheck->fetch()) {
                throw new Exception("El producto no existe o ya está activo.");
            }

            $stmt = $this->db->prepare("UPDATE producto SET eliminado = 0 WHERE id_producto = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // BUSCAR PRODUCTOS
    public function buscarProductos($termino) {
        try {
            $termino = trim((string) $termino);
            $sql = "SELECT 
                        p.id_producto,
                        p.descripcion,
                        p.cantidad AS stock,
                        p.stock_minimo,
                        p.costo_unitario,
                        p.id_categoria,
                        p.especificaciones,
                        p.id_proveedor,
                        p.eliminado,
                        c.nombre_categoria,
                        COALESCE(
                            (SELECT prov.razon_social 
                             FROM detalle_entrada de 
                             INNER JOIN nota_de_entrada ne ON de.id_nota_entrada = ne.id_nota_entrada 
                             INNER JOIN proveedor prov ON ne.id_proveedor = prov.id_proveedor 
                             WHERE de.id_producto = p.id_producto AND ne.eliminado = 0 
                             ORDER BY ne.fecha_ingreso DESC LIMIT 1),
                            (SELECT prov2.razon_social FROM proveedor prov2 WHERE prov2.id_proveedor = p.id_proveedor)
                        ) AS nombre_proveedor
                    FROM producto p
                    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                    WHERE (p.descripcion LIKE :termino
                    OR c.nombre_categoria LIKE :termino
                    OR CAST(p.id_producto AS CHAR) LIKE :termino)
                    ORDER BY p.eliminado ASC, p.descripcion ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['termino' => "%$termino%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarProductos: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER HISTORIAL DE PROVEEDORES
    public function obtenerHistorialProveedores($id_producto) {
        try {
            $sql = "SELECT DISTINCT 
                        prov.razon_social, 
                        prov.rif, 
                        ne.fecha_ingreso,
                        de.cantidad,
                        de.costo_unitario
                    FROM producto prod
                    INNER JOIN detalle_entrada de ON prod.id_producto = de.id_producto
                    INNER JOIN nota_de_entrada ne ON de.id_nota_entrada = ne.id_nota_entrada
                    INNER JOIN proveedor prov ON ne.id_proveedor = prov.id_proveedor
                    WHERE prod.id_producto = :id_producto AND ne.eliminado = 0
                    ORDER BY ne.fecha_ingreso DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_producto' => $id_producto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerHistorialProveedores: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER RESUMEN
    public function getResumen() {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_productos,
                        SUM(cantidad) AS total_stock,
                        COUNT(DISTINCT id_categoria) AS total_categorias
                    FROM producto
                    WHERE eliminado = 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getResumen: " . $e->getMessage());
            return [
                'total_productos' => 0,
                'total_stock' => 0,
                'total_categorias' => 0
            ];
        }
    }
}
?>