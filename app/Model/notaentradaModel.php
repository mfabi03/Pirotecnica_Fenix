<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;

class NotaentradaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener productos para el selector
     */
    public function obtenerProductos() {
        try {
            $sql = "SELECT 
                        id_producto, 
                        descripcion, 
                        costo_unitario, 
                        cantidad 
                    FROM producto 
                    ORDER BY descripcion ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener productos: " . $e->getMessage());
        }
    }

    /**
     * Obtener proveedores para el selector
     */
    public function obtenerProveedores() {
        try {
            $sql = "SELECT 
                        p.id_proveedor, 
                        p.razon_social, 
                        p.rif,
                        pe.nombre,
                        pe.apellido,
                        pe.telefono
                    FROM proveedor p
                    INNER JOIN persona pe ON p.id_persona = pe.id_persona
                    ORDER BY p.razon_social ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener proveedores: " . $e->getMessage());
        }
    }

    /**
     * Listado de notas de entrada con datos del proveedor
     */
    public function obtenerNotasEntrada() {
        try {
            $sql = "SELECT 
                        n.id_nota_entrada, 
                        n.fecha_ingreso, 
                        n.descripcion,
                        n.costo_total,
                        p.razon_social, 
                        p.rif,
                        CONCAT_WS(' ', pe.nombre, pe.apellido) AS encargado_nombre,
                        GROUP_CONCAT(
                            CONCAT(prod.descripcion, ' (x', d.cantidad, ')')
                            ORDER BY d.id_detalle_entrada
                            SEPARATOR ', '
                        ) AS productos_lista
                    FROM nota_de_entrada n
                    LEFT JOIN proveedor p ON n.id_proveedor = p.id_proveedor
                    LEFT JOIN usuario u ON n.id_usuario = u.id_usuario
                    LEFT JOIN persona pe ON u.id_persona = pe.id_persona
                    LEFT JOIN detalle_entrada d ON n.id_nota_entrada = d.id_nota_entrada
                    LEFT JOIN producto prod ON d.id_producto = prod.id_producto
                    GROUP BY n.id_nota_entrada, n.fecha_ingreso, n.descripcion, n.costo_total, p.razon_social, p.rif, pe.nombre, pe.apellido
                    ORDER BY n.id_nota_entrada DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener notas de entrada: " . $e->getMessage());
        }
    }

    /**
     * Obtener una nota completa con proveedor y detalles
     */
    public function obtenerNotaEntradaPorId($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    n.id_nota_entrada, 
                    n.fecha_ingreso, 
                    n.descripcion,
                    n.costo_total,
                    p.id_proveedor, 
                    p.razon_social, 
                    p.rif,
                    CONCAT_WS(' ', pe.nombre, pe.apellido) AS encargado_nombre
                FROM nota_de_entrada n
                LEFT JOIN proveedor p ON n.id_proveedor = p.id_proveedor
                LEFT JOIN usuario u ON n.id_usuario = u.id_usuario
                LEFT JOIN persona pe ON u.id_persona = pe.id_persona
                WHERE n.id_nota_entrada = :id
            ");
            $stmt->execute([':id' => $id]);
            $nota = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($nota) {
                $stmtDet = $this->db->prepare("
                    SELECT 
                        d.id_detalle_entrada,
                        d.id_producto,
                        d.cantidad,
                        d.costo_unitario,
                        p.descripcion as nombre_producto
                    FROM detalle_entrada d 
                    JOIN producto p ON d.id_producto = p.id_producto 
                    WHERE d.id_nota_entrada = :id
                ");
                $stmtDet->execute([':id' => $id]);
                $nota['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
            }
            return $nota;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener nota de entrada: " . $e->getMessage());
        }
    }

    /**
     * Guardar una nota completa (cabecera + detalles)
     */
    public function guardarNotaEntradaCompleta(array $datos, array $detalles, $idUsuario) {
        $this->db->beginTransaction();
        try {
            $costoTotal = 0;
            foreach ($detalles as $d) {
                $costoTotal += $d['cantidad'] * $d['costo_unitario'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO nota_de_entrada (
                    fecha_ingreso, 
                    id_proveedor, 
                    id_usuario, 
                    descripcion, 
                    costo_total
                ) VALUES (
                    :fecha_ingreso, 
                    :id_proveedor, 
                    :id_usuario, 
                    :descripcion, 
                    :costo_total
                )
            ");
            $stmt->execute([
                ':fecha_ingreso' => $datos['fecha_ingreso'],
                ':id_proveedor' => $datos['id_proveedor'],
                ':id_usuario' => $idUsuario,
                ':descripcion' => $datos['descripcion'] ?? '',
                ':costo_total' => $costoTotal
            ]);
            $idNota = (int) $this->db->lastInsertId();

            $stmtDet = $this->db->prepare("
                INSERT INTO detalle_entrada (
                    id_nota_entrada, 
                    id_producto, 
                    cantidad, 
                    costo_unitario
                ) VALUES (
                    :id_nota_entrada, 
                    :id_producto, 
                    :cantidad, 
                    :costo_unitario
                )
            ");
            
            $stmtStock = $this->db->prepare("
                UPDATE producto SET cantidad = cantidad + :cantidad WHERE id_producto = :id_producto
            ");

            foreach ($detalles as $d) {
                $stmtDet->execute([
                    ':id_nota_entrada' => $idNota,
                    ':id_producto' => $d['id_producto'],
                    ':cantidad' => $d['cantidad'],
                    ':costo_unitario' => $d['costo_unitario']
                ]);
                
                $stmtStock->execute([
                    ':cantidad' => $d['cantidad'],
                    ':id_producto' => $d['id_producto']
                ]);
            }

            $this->db->commit();
            return $idNota;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al guardar nota de entrada: " . $e->getMessage());
        }
    }

    /**
     * ANULAR NOTA DE ENTRADA (elimina y revierte stock)
     * ==================================================
     */
    public function anularNotaEntrada($id, $motivo, $idUsuario) {
        $this->db->beginTransaction();
        try {
            // 1. Obtener detalles para revertir stock
            $stmtDet = $this->db->prepare("
                SELECT id_producto, cantidad FROM detalle_entrada WHERE id_nota_entrada = :id
            ");
            $stmtDet->execute([':id' => $id]);
            $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception("La nota no tiene productos asociados");
            }

            // 2. Revertir stock (restar)
            $stmtStock = $this->db->prepare("
                UPDATE producto SET cantidad = cantidad - :cantidad WHERE id_producto = :id_producto
            ");
            foreach ($detalles as $d) {
                $stmtStock->execute([
                    ':cantidad' => $d['cantidad'],
                    ':id_producto' => $d['id_producto']
                ]);
            }

            // 3. Eliminar detalles
            $stmtDelDet = $this->db->prepare("DELETE FROM detalle_entrada WHERE id_nota_entrada = :id");
            $stmtDelDet->execute([':id' => $id]);

            // 4. Eliminar nota
            $stmtDel = $this->db->prepare("DELETE FROM nota_de_entrada WHERE id_nota_entrada = :id");
            $stmtDel->execute([':id' => $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al anular nota de entrada: " . $e->getMessage());
        }
    }

    /**
     * Obtener resumen de notas de entrada
     * ====================================
     */
    public function getResumen() {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_notas,
                        COALESCE(SUM(costo_total), 0) AS total_compras
                    FROM nota_de_entrada";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 🔥 CONTAR ANULADAS DESDE SESSION (igual que nota de salida)
            $totalAnuladas = $_SESSION['contador_anulaciones_notaentrada'] ?? 0;
            
            return [
                'total_notas' => $result['total_notas'] ?? 0,
                'total_compras' => $result['total_compras'] ?? 0,
                'total_anuladas' => $totalAnuladas
            ];
        } catch (PDOException $e) {
            return [
                'total_notas' => 0,
                'total_compras' => 0,
                'total_anuladas' => 0
            ];
        }
    }
}
?>