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

    // OBTENER TODOS LOS PRODUCTOS

    public function obtenerProductos() {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.descripcion,
                        p.cantidad AS stock,
                        p.costo_unitario,
                        p.id_categoria,
                        c.nombre_categoria
                    FROM producto p
                    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                    ORDER BY p.id_producto DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerProductos: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER PRODUCTO POR ID

    public function obtenerProductoPorId($id) {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.descripcion,
                        p.cantidad AS stock,
                        p.costo_unitario,
                        p.id_categoria,
                        c.nombre_categoria
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
                        costo_unitario,
                        id_categoria
                    ) VALUES (
                        :descripcion,
                        :cantidad,
                        :costo_unitario,
                        :id_categoria
                    )";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                ':descripcion' => $datos['descripcion'],
                ':cantidad' => $datos['cantidad'],
                ':costo_unitario' => $datos['costo_unitario'],
                ':id_categoria' => $datos['id_categoria'] ?? null
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
            // NOTA: El campo 'cantidad' (stock) NO se actualiza aquí. Las existencias
            // deben gestionarse mediante notas de entrada/salida para mantener trazabilidad.
            $sql = "UPDATE producto SET 
                        descripcion = :descripcion,
                        costo_unitario = :costo_unitario,
                        id_categoria = :id_categoria
                    WHERE id_producto = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':descripcion' => $datos['descripcion'],
                ':costo_unitario' => $datos['costo_unitario'],
                ':id_categoria' => $datos['id_categoria'] ?? null,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizarProducto: " . $e->getMessage());
            return false;
        }
    }

    // VERIFICAR SI EL PRODUCTO ESTÁ ASOCIADO A NOTAS

    public function verificarAsociaciones($id) {
        try {
            $stmtEntrada = $this->db->prepare("SELECT COUNT(*) FROM detalle_entrada WHERE id_producto = ?");
            $stmtEntrada->execute([$id]);
            $entradas = (int) $stmtEntrada->fetchColumn();

            $stmtSalida = $this->db->prepare("SELECT COUNT(*) FROM detalle_salida WHERE id_producto = ?");
            $stmtSalida->execute([$id]);
            $salidas = (int) $stmtSalida->fetchColumn();

            return [
                'tiene_asociaciones' => ($entradas > 0 || $salidas > 0),
                'entradas' => $entradas,
                'salidas' => $salidas
            ];
        } catch (PDOException $e) {
            error_log("Error al verificar asociaciones del producto: " . $e->getMessage());
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

            $sql = "DELETE FROM producto WHERE id_producto = ?";
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

    // BUSCAR PRODUCTOS

    public function buscarProductos($termino) {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.descripcion,
                        p.cantidad AS stock,
                        p.costo_unitario,
                        p.id_categoria,
                        c.nombre_categoria
                    FROM producto p
                    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                    WHERE p.descripcion LIKE :termino
                    ORDER BY p.descripcion ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['termino' => "%$termino%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarProductos: " . $e->getMessage());
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
                    FROM producto";
            
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