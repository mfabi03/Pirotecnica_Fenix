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
                    hora_apertura TIME NULL,
                    id_usuario INT NULL,
                    ultimo_id_nota_salida INT NULL DEFAULT NULL,
                    total_documentos INT NOT NULL DEFAULT 0,
                    total_productos_distintos INT NOT NULL DEFAULT 0,
                    total_clientes INT NOT NULL DEFAULT 0,
                    total_unidades INT NOT NULL DEFAULT 0,
                    total_lineas INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_cierre_usuario_fecha (fecha_cierre, id_usuario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        try { $this->db->exec($sql); } catch (Exception $e) {}

        $columnas = [
            'hora_apertura' => "TIME NULL AFTER fecha_cierre",
            'total_productos_distintos' => "INT NOT NULL DEFAULT 0 AFTER total_documentos",
            'total_clientes' => "INT NOT NULL DEFAULT 0 AFTER total_productos_distintos"
        ];

        foreach ($columnas as $col => $definicion) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM cierre_caja LIKE '$col'");
                if ($stmt && $stmt->rowCount() === 0) {
                    $this->db->exec("ALTER TABLE cierre_caja ADD COLUMN $col $definicion");
                }
            } catch (Exception $e) {}
        }
    }

    public function obtenerUltimoIdNotaSalidaPorUsuarioFecha($fecha, $id_usuario = null) {
        if (empty($fecha)) return null;
        $sql = "SELECT MAX(ns.id_nota_salida) AS ultimo_id FROM nota_de_salida ns WHERE DATE(ns.fecha) = :fecha AND ns.eliminado = 0";
        $params = [':fecha' => $fecha];
        if ($id_usuario !== null && $id_usuario !== '') { $sql .= " AND ns.id_usuario = :id_usuario"; $params[':id_usuario'] = (int)$id_usuario; }
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) { $stmt->bindValue($key, $value); }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['ultimo_id']) && $row['ultimo_id'] !== null ? (int)$row['ultimo_id'] : null;
    }

    public function obtenerUltimoCierreCaja($fecha, $id_usuario = null) {
        if (empty($fecha)) return null;
        $this->asegurarTablaCierreCaja();
        $sql = "SELECT * FROM cierre_caja WHERE fecha_cierre = :fecha_cierre";
        $params = [':fecha_cierre' => $fecha];
        if ($id_usuario !== null && $id_usuario !== '') { $sql .= " AND id_usuario = :id_usuario"; $params[':id_usuario'] = (int)$id_usuario; }
        $sql .= " ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) $stmt->bindValue($key, $value);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function actualizarUltimoIdCierreCaja($fecha, $id_usuario = null, $ultimo_id_nota_salida = null) {
        if (empty($fecha)) return false;
        $this->asegurarTablaCierreCaja();
        $sql = "UPDATE cierre_caja SET ultimo_id_nota_salida = :ultimo_id_nota_salida WHERE fecha_cierre = :fecha_cierre AND id_usuario <=> :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_cierre', $fecha);
        $stmt->bindValue(':id_usuario', $id_usuario !== null && $id_usuario !== '' ? (int)$id_usuario : null, PDO::PARAM_INT);
        $stmt->bindValue(':ultimo_id_nota_salida', $ultimo_id_nota_salida !== null ? (int)$ultimo_id_nota_salida : null, PDO::PARAM_INT);
        try { $stmt->execute(); return $stmt->rowCount() > 0; } catch (Exception $e) { return false; }
    }

    public function guardarCierreCaja($fecha, $id_usuario = null, $total_documentos = 0, $total_unidades = 0, $total_lineas = 0, $hora_apertura = null, $total_productos = 0, $total_clientes = 0) {
        if (empty($fecha)) return false;
        $this->asegurarTablaCierreCaja();
        $ultimoIdNota = $this->obtenerUltimoIdNotaSalidaPorUsuarioFecha($fecha, $id_usuario);
        if ($this->existeCierreCaja($fecha, $id_usuario)) { return $this->actualizarUltimoIdCierreCaja($fecha, $id_usuario, $ultimoIdNota); }

        try {
            $sql = "INSERT INTO cierre_caja (fecha_cierre, hora_apertura, id_usuario, ultimo_id_nota_salida, total_documentos, total_productos_distintos, total_clientes, total_unidades, total_lineas)
                    VALUES (:fecha_cierre, :hora_apertura, :id_usuario, :ultimo_id_nota_salida, :total_documentos, :total_productos, :total_clientes, :total_unidades, :total_lineas)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':fecha_cierre', $fecha);
            $stmt->bindValue(':hora_apertura', $hora_apertura);
            $stmt->bindValue(':id_usuario', $id_usuario !== null && $id_usuario !== '' ? (int)$id_usuario : null, PDO::PARAM_INT);
            $stmt->bindValue(':ultimo_id_nota_salida', $ultimoIdNota !== null ? (int)$ultimoIdNota : null, PDO::PARAM_INT);
            $stmt->bindValue(':total_documentos', (int)$total_documentos, PDO::PARAM_INT);
            $stmt->bindValue(':total_productos', (int)$total_productos, PDO::PARAM_INT);
            $stmt->bindValue(':total_clientes', (int)$total_clientes, PDO::PARAM_INT);
            $stmt->bindValue(':total_unidades', (int)$total_unidades, PDO::PARAM_INT);
            $stmt->bindValue(':total_lineas', (int)$total_lineas, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error en guardarCierreCaja: " . $e->getMessage());
            return false;
        }
    }

    public function existeCierreCaja($fecha, $id_usuario = null) {
        $this->asegurarTablaCierreCaja();
        $sql = "SELECT 1 FROM cierre_caja WHERE fecha_cierre = :fecha_cierre";
        $params = [':fecha_cierre' => $fecha];
        if ($id_usuario !== null && $id_usuario !== '') { $sql .= " AND id_usuario = :id_usuario"; $params[':id_usuario'] = (int)$id_usuario; } else { $sql .= " AND id_usuario IS NULL"; }
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    // 🔥 CONSULTA LIMPIA PARA LA VISTA DE CAJA (Cuenta productos y categorías)
    public function obtenerSalidasCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null, $limit = 10, $offset = 0) {
        $where = ["ns.eliminado = 0"];
        $params = [];

        if (!empty($id_usuario)) { $where[] = "ns.id_usuario = :id_usuario"; $params[':id_usuario'] = $id_usuario; }

        if (!empty($fecha)) {
            $cierre = $this->obtenerUltimoCierreCaja($fecha, $id_usuario);
            if ($cierre && isset($cierre['ultimo_id_nota_salida']) && $cierre['ultimo_id_nota_salida'] !== null) {
                $where[] = "ns.id_nota_salida > :ultimo_id_cierre";
                $params[':ultimo_id_cierre'] = (int)$cierre['ultimo_id_nota_salida'];
            } else {
                $cierreAnterior = $this->obtenerUltimoCierreCaja(date('Y-m-d', strtotime($fecha . ' -1 day')), $id_usuario);
                if ($cierreAnterior && isset($cierreAnterior['ultimo_id_nota_salida'])) {
                    $where[] = "(DATE(ns.fecha) = :fecha OR ns.id_nota_salida > :ultimo_id_cierre_ant)";
                    $params[':fecha'] = $fecha;
                    $params[':ultimo_id_cierre_ant'] = (int)$cierreAnterior['ultimo_id_nota_salida'];
                } else {
                    $where[] = "DATE(ns.fecha) = :fecha";
                    $params[':fecha'] = $fecha;
                }
            }
        }

        if (!empty($id_categoria) && $id_categoria > 0) { $where[] = "prod.id_categoria = :id_categoria"; $params[':id_categoria'] = $id_categoria; }
        if (!empty($busqueda)) { $where[] = "prod.descripcion LIKE :busqueda"; $params[':busqueda'] = '%' . $busqueda . '%'; }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT 
                    ns.id_nota_salida AS id_documento,
                    DATE_FORMAT(ns.fecha, '%d/%m/%Y %h:%i %p') AS fecha_movimiento,
                    GROUP_CONCAT(DISTINCT CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') ORDER BY prod.descripcion SEPARATOR ', ') AS nombre_producto,
                    GROUP_CONCAT(DISTINCT c.nombre_categoria ORDER BY c.nombre_categoria SEPARATOR ', ') AS categoria,
                    SUM(ds.cantidad) AS cantidad,
                    COUNT(DISTINCT prod.id_producto) AS total_productos, -- 🔥 Cuenta productos
                    COUNT(DISTINCT c.id_categoria) AS total_categorias, -- 🔥 Cuenta categorías
                    COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
                    CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
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
        return count($this->obtenerSalidasCajero($id_usuario, $fecha, $busqueda, $id_categoria, 99999, 0));
    }

    public function obtenerResumenCierreCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null) {
        $movs = $this->obtenerSalidasCajero($id_usuario, $fecha, $busqueda, $id_categoria, 99999, 0);
        return [
            'total_documentos' => count(array_unique(array_column($movs, 'id_documento'))),
            'total_unidades' => array_sum(array_column($movs, 'cantidad')),
            'total_lineas' => 0,
        ];
    }
}
?>