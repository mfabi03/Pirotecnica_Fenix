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

    // OBTENER PRODUCTOS
    public function obtenerProductos() {
        try {
            $sql = "SELECT 
                        id_producto, 
                        descripcion, 
                        costo_unitario, 
                        cantidad 
                    FROM producto 
                    WHERE eliminado = 0
                    ORDER BY descripcion ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener productos: " . $e->getMessage());
        }
    }

    // OBTENER PROVEEDORES
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
                    WHERE p.eliminado = 0 AND pe.eliminado = 0
                    ORDER BY p.razon_social ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener proveedores: " . $e->getMessage());
        }
    }

    // LISTADO DE NOTAS DE ENTRADA
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
                    WHERE n.eliminado = 0
                    GROUP BY n.id_nota_entrada, n.fecha_ingreso, n.descripcion, n.costo_total, p.razon_social, p.rif, pe.nombre, pe.apellido
                    ORDER BY n.id_nota_entrada DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener notas de entrada: " . $e->getMessage());
        }
    }

    //  CONTAR NOTAS DE ENTRADA (para paginación)
    public function contarNotasEntrada() {
        try {
            $sql = "SELECT COUNT(*) FROM nota_de_entrada WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Error al contar notas de entrada: " . $e->getMessage());
        }
    }

    public function buscarNotasEntrada($termino) {
        try {
            $termino = trim((string) $termino);
            if ($termino === '') {
                return $this->obtenerNotasEntrada();
            }

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
                    WHERE n.eliminado = 0
                      AND (
                            CAST(n.id_nota_entrada AS CHAR) LIKE :termino
                         OR p.razon_social LIKE :termino
                         OR CONCAT_WS(' ', pe.nombre, pe.apellido) LIKE :termino
                         OR prod.descripcion LIKE :termino
                         OR n.descripcion LIKE :termino
                      )
                    GROUP BY n.id_nota_entrada, n.fecha_ingreso, n.descripcion, n.costo_total, p.razon_social, p.rif, pe.nombre, pe.apellido
                    ORDER BY n.id_nota_entrada DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':termino' => "%$termino%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al buscar notas de entrada: " . $e->getMessage());
        }
    }

    // OBTENER NOTA POR ID
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
                WHERE n.id_nota_entrada = :id AND n.eliminado = 0
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

    // GUARDAR NOTA COMPLETA
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
                    costo_total,
                    eliminado
                ) VALUES (
                    :fecha_ingreso, 
                    :id_proveedor, 
                    :id_usuario, 
                    :descripcion, 
                    :costo_total,
                    0
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
                    UPDATE producto SET 
                        cantidad = cantidad + :cantidad,
                        costo_unitario = :costo_unitario
                    WHERE id_producto = :id_producto
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
                    ':costo_unitario' => $d['costo_unitario'],
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

    // ANULAR NOTA DE ENTRADA
    public function anularNotaEntrada($id, $motivo, $idUsuario) {
        $this->db->beginTransaction();
        try {
            $stmtCheck = $this->db->prepare("
                SELECT id_nota_entrada FROM nota_de_entrada WHERE id_nota_entrada = :id AND eliminado = 0
            ");
            $stmtCheck->execute([':id' => $id]);
            if ($stmtCheck->rowCount() === 0) {
                throw new Exception("La nota no existe o ya fue anulada");
            }

            $stmtDet = $this->db->prepare("
                SELECT id_producto, cantidad FROM detalle_entrada WHERE id_nota_entrada = :id
            ");
            $stmtDet->execute([':id' => $id]);
            $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception("La nota no tiene productos asociados");
            }

            $stmtStock = $this->db->prepare("
                UPDATE producto SET cantidad = cantidad - :cantidad WHERE id_producto = :id_producto
            ");
            foreach ($detalles as $d) {
                $stmtStock->execute([
                    ':cantidad' => $d['cantidad'],
                    ':id_producto' => $d['id_producto']
                ]);
            }

            $stmtDel = $this->db->prepare("UPDATE nota_de_entrada SET eliminado = 1 WHERE id_nota_entrada = :id");
            $stmtDel->execute([':id' => $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al anular nota de entrada: " . $e->getMessage());
        }
    }

    // OBTENER RESUMEN
    public function getResumen() {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_registros,
                        SUM(CASE WHEN eliminado = 0 THEN 1 ELSE 0 END) AS total_notas,
                        SUM(CASE WHEN eliminado = 1 THEN 1 ELSE 0 END) AS total_anuladas,
                        COALESCE(SUM(CASE WHEN eliminado = 0 THEN costo_total ELSE 0 END), 0) AS total_compras
                    FROM nota_de_entrada";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total_notas' => (int) ($result['total_notas'] ?? 0),
                'total_compras' => (float) ($result['total_compras'] ?? 0),
                'total_anuladas' => (int) ($result['total_anuladas'] ?? 0),
                'total_registros' => (int) ($result['total_registros'] ?? 0)
            ];
        } catch (PDOException $e) {
            return [
                'total_notas' => 0,
                'total_compras' => 0,
                'total_anuladas' => 0,
                'total_registros' => 0
            ];
        }
    }
}