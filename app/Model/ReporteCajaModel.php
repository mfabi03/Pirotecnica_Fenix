<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class ReporteCajaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private function asegurarTablaCierreCaja() {
        $sql = "CREATE TABLE IF NOT EXISTS cierre_caja (
                    id_cierre INT AUTO_INCREMENT PRIMARY KEY,
                    fecha_cierre DATE NOT NULL,
                    id_usuario INT NULL,
                    ultimo_id_nota_salida INT NULL DEFAULT NULL,
                    total_documentos INT NOT NULL DEFAULT 0,
                    total_unidades INT NOT NULL DEFAULT 0,
                    total_lineas INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_cierre_usuario_fecha (fecha_cierre, id_usuario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        try {
            $this->db->exec($sql);
        } catch (Exception $e) {}

        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM cierre_caja LIKE 'ultimo_id_nota_salida'");
            if ($stmt && $stmt->rowCount() === 0) {
                $this->db->exec("ALTER TABLE cierre_caja ADD COLUMN ultimo_id_nota_salida INT NULL DEFAULT NULL AFTER id_usuario");
            }
        } catch (Exception $e) {}
    }

    public function obtenerUltimoIdNotaSalidaPorUsuarioFecha($fecha, $id_usuario = null) {
        if (empty($fecha)) return null;

        $sql = "SELECT MAX(ns.id_nota_salida) AS ultimo_id
                FROM nota_de_salida ns
                WHERE DATE(ns.fecha) = :fecha AND ns.eliminado = 0";
        $params = [':fecha' => $fecha];

        if ($id_usuario !== null && $id_usuario !== '') {
            $sql .= " AND ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = (int)$id_usuario;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['ultimo_id']) && $row['ultimo_id'] !== null ? (int)$row['ultimo_id'] : null;
    }

    public function obtenerUltimoCierreCaja($fecha, $id_usuario = null) {
        if (empty($fecha)) return null;
        $this->asegurarTablaCierreCaja();

        $sql = "SELECT * FROM cierre_caja WHERE fecha_cierre = :fecha_cierre";
        $params = [':fecha_cierre' => $fecha];

        if ($id_usuario !== null && $id_usuario !== '') {
            $sql .= " AND id_usuario = :id_usuario";
            $params[':id_usuario'] = (int)$id_usuario;
        }
        $sql .= " ORDER BY created_at DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) $stmt->bindValue($key, $value);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function actualizarUltimoIdCierreCaja($fecha, $id_usuario = null, $ultimo_id_nota_salida = null) {
        if (empty($fecha)) return false;
        $this->asegurarTablaCierreCaja();

        $sql = "UPDATE cierre_caja
                SET ultimo_id_nota_salida = :ultimo_id_nota_salida
                WHERE fecha_cierre = :fecha_cierre
                  AND id_usuario <=> :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_cierre', $fecha);
        $stmt->bindValue(':id_usuario', $id_usuario !== null && $id_usuario !== '' ? (int)$id_usuario : null, PDO::PARAM_INT);
        $stmt->bindValue(':ultimo_id_nota_salida', $ultimo_id_nota_salida !== null ? (int)$ultimo_id_nota_salida : null, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function guardarCierreCaja($fecha, $id_usuario = null, $total_documentos = 0, $total_unidades = 0, $total_lineas = 0) {
        if (empty($fecha)) return false;
        $this->asegurarTablaCierreCaja();

        $ultimoIdNota = $this->obtenerUltimoIdNotaSalidaPorUsuarioFecha($fecha, $id_usuario);

        if ($this->existeCierreCaja($fecha, $id_usuario)) {
            return $this->actualizarUltimoIdCierreCaja($fecha, $id_usuario, $ultimoIdNota);
        }

        $sql = "INSERT INTO cierre_caja (fecha_cierre, id_usuario, ultimo_id_nota_salida, total_documentos, total_unidades, total_lineas)
                VALUES (:fecha_cierre, :id_usuario, :ultimo_id_nota_salida, :total_documentos, :total_unidades, :total_lineas)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_cierre', $fecha);
        $stmt->bindValue(':id_usuario', $id_usuario !== null && $id_usuario !== '' ? (int)$id_usuario : null, PDO::PARAM_INT);
        $stmt->bindValue(':ultimo_id_nota_salida', $ultimoIdNota !== null ? (int)$ultimoIdNota : null, PDO::PARAM_INT);
        $stmt->bindValue(':total_documentos', (int)$total_documentos, PDO::PARAM_INT);
        $stmt->bindValue(':total_unidades', (int)$total_unidades, PDO::PARAM_INT);
        $stmt->bindValue(':total_lineas', (int)$total_lineas, PDO::PARAM_INT);
        
        try {
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // 🔥 CONSULTA POR USUARIO: Si no hay ID de usuario, asumimos que no hay cierre
    public function existeCierreCaja($fecha, $id_usuario = null) {
        $this->asegurarTablaCierreCaja();
        
        if (empty($id_usuario)) {
            return false; 
        }

        $sql = "SELECT 1 FROM cierre_caja WHERE fecha_cierre = :fecha_cierre AND id_usuario = :id_usuario LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_cierre', $fecha);
        $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        
        return (bool)$stmt->fetchColumn();
    }

    // ============================================================
    // CONSULTAS CON ARRASTRE (Filtrado por el usuario actual)
    // ============================================================
    public function obtenerSalidasCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null, $limit = 10, $offset = 0) {
        $where = ["ns.eliminado = 0"];
        $params = [];

        if (!empty($id_usuario)) {
            $where[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }

        if (!empty($fecha)) {
            // Buscamos el último cierre de ESTE usuario (en la fecha anterior)
            $cierreAnterior = $this->obtenerUltimoCierreCaja(date('Y-m-d', strtotime($fecha . ' -1 day')), $id_usuario);
            
            if ($cierreAnterior && isset($cierreAnterior['ultimo_id_nota_salida'])) {
                $where[] = "(DATE(ns.fecha) = :fecha OR ns.id_nota_salida > :ultimo_id_cierre)";
                $params[':fecha'] = $fecha;
                $params[':ultimo_id_cierre'] = (int)$cierreAnterior['ultimo_id_nota_salida'];
            } else {
                $where[] = "DATE(ns.fecha) = :fecha";
                $params[':fecha'] = $fecha;
            }
        }

        if (!empty($id_categoria) && $id_categoria > 0) {
            $where[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }
        if (!empty($busqueda)) {
            $where[] = "prod.descripcion LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT 
                    ns.id_nota_salida AS id_documento,
                    ns.fecha AS fecha_movimiento,
                    GROUP_CONCAT(DISTINCT CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') ORDER BY prod.descripcion SEPARATOR ', ') AS nombre_producto,
                    GROUP_CONCAT(DISTINCT c.nombre_categoria ORDER BY c.nombre_categoria SEPARATOR ', ') AS categoria,
                    SUM(ds.cantidad) AS cantidad,
                    COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
                    CASE 
                        WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico'
                        ELSE 'Natural'
                    END AS tipo_cliente,
                    COALESCE(CONCAT(per.nombre, ' ', per.apellido), 'N/A') AS usuario_activo
                FROM nota_de_salida ns
                INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                LEFT JOIN persona per ON u.id_persona = per.id_persona
                LEFT JOIN persona per_cliente ON ns.id_persona = per_cliente.id_persona
                LEFT JOIN cliente_juridico cj ON per_cliente.id_persona = cj.id_persona
                LEFT JOIN cliente_natural cn ON per_cliente.id_persona = cn.id_persona
                $whereSql
                GROUP BY ns.id_nota_salida, ns.fecha, cj.razon_social, per_cliente.nombre, per_cliente.apellido, cj.id_cliente_juridico, per.nombre, per.apellido
                ORDER BY ns.fecha DESC, ns.id_nota_salida DESC
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerSalidasCajero: " . $e->getMessage());
            return [];
        }
    }

    public function contarSalidasCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null) {
        $movimientos = $this->obtenerSalidasCajero($id_usuario, $fecha, $busqueda, $id_categoria, 99999, 0);
        return count($movimientos);
    }

    public function obtenerResumenCierreCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null) {
        $movimientos = $this->obtenerSalidasCajero($id_usuario, $fecha, $busqueda, $id_categoria, 99999, 0);
        
        $totalDocumentos = count(array_unique(array_column($movimientos, 'id_documento')));
        $totalUnidades = array_sum(array_column($movimientos, 'cantidad'));
        $totalLineas = 0; 

        return [
            'total_documentos' => $totalDocumentos,
            'total_unidades'   => $totalUnidades,
            'total_lineas'     => $totalLineas,
        ];
    }

    public function obtenerResumenCierreDia($id_usuario, $fecha) {
        return $this->obtenerResumenCierreCajero($id_usuario, $fecha);
    }
}