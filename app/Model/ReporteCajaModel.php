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

    public function existeCierreCaja($fecha, $id_usuario = null) {
        $sql = "SELECT 1 FROM cierre_caja WHERE fecha_cierre = :fecha_cierre AND id_usuario <=> :id_usuario LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_cierre', $fecha);
        $stmt->bindValue(':id_usuario', $id_usuario !== null && $id_usuario !== '' ? (int)$id_usuario : null, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function fechaCerradaParaCaja($fecha, $id_usuario = null) {
        if (empty($fecha)) return false;

        $cierre = $this->obtenerUltimoCierreCaja($fecha, $id_usuario);
        if (!$cierre) return false;

        if (!isset($cierre['ultimo_id_nota_salida']) || $cierre['ultimo_id_nota_salida'] === null) {
            return false;
        }

        $ultimoId = (int)$cierre['ultimo_id_nota_salida'];
        $ultimoNotaActual = $this->obtenerUltimoIdNotaSalidaPorUsuarioFecha($fecha, $id_usuario);

        if ($ultimoNotaActual !== null && $ultimoNotaActual > $ultimoId) {
            return false;
        }

        return true;
    }

    private function aplicarFiltroPostCierre(&$where, &$params, $fecha, $id_usuario = null) {
        if (empty($fecha)) return false;

        $cierre = $this->obtenerUltimoCierreCaja($fecha, $id_usuario);
        if (!$cierre) {
            return false; 
        }

        if (!isset($cierre['ultimo_id_nota_salida']) || $cierre['ultimo_id_nota_salida'] === null) {
            return false;
        }

        $where[] = "ns.id_nota_salida > :ultimo_id_nota_salida";
        $params[':ultimo_id_nota_salida'] = (int)$cierre['ultimo_id_nota_salida'];
        return true;
    }

    public function obtenerNotasPostCierre($id_usuario, $fecha) {
        $where = ["ns.eliminado = 0", "DATE(ns.fecha) = :fecha"];
        $params = [':fecha' => $fecha];

        if (!empty($id_usuario)) {
            $where[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }

        $cierre = $this->obtenerUltimoCierreCaja($fecha, $id_usuario);
        if ($cierre && isset($cierre['ultimo_id_nota_salida']) && $cierre['ultimo_id_nota_salida'] !== null) {
            $where[] = "ns.id_nota_salida > :ultimo_id_nota_salida";
            $params[':ultimo_id_nota_salida'] = (int)$cierre['ultimo_id_nota_salida'];
        }

        $sql = "SELECT ns.id_nota_salida, ns.fecha, ns.id_persona, ns.id_usuario
                FROM nota_de_salida ns
                WHERE " . implode(' AND ', $where) . "
                ORDER BY ns.id_nota_salida DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSalidasCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null, $limit = 10, $offset = 0) {
        $where = ["ns.eliminado = 0"];
        $params = [];

        if (!empty($id_usuario)) {
            $where[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }
        if (!empty($fecha)) {
            $where[] = "DATE(ns.fecha) = :fecha";
            $params[':fecha'] = $fecha;
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

        // 🔥 AGREGAMOS EL CAMPO tipo_cliente Y ASEGURAMOS LOS JOINS
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
            // Fallback seguro si algo falla en la consulta principal
            $sql = "SELECT 
                        ns.id_nota_salida AS id_documento,
                        ns.fecha AS fecha_movimiento,
                        GROUP_CONCAT(CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') SEPARATOR ', ') AS nombre_producto,
                        GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categoria,
                        SUM(ds.cantidad) AS cantidad,
                        'Sin cliente' AS nombre_cliente,
                        'Natural' AS tipo_cliente,
                        COALESCE(CONCAT(per.nombre, ' ', per.apellido), 'N/A') AS usuario_activo
                    FROM nota_de_salida ns
                    INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                    INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                    LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                    LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                    LEFT JOIN persona per ON u.id_persona = per.id_persona
                    $whereSql
                    GROUP BY ns.id_nota_salida, ns.fecha, per.nombre, per.apellido
                    ORDER BY ns.fecha DESC, ns.id_nota_salida DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function contarSalidasCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null) {
        $where = ["ns.eliminado = 0"];
        $params = [];

        if (!empty($id_usuario)) {
            $where[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }
        if (!empty($fecha)) {
            $where[] = "DATE(ns.fecha) = :fecha";
            $params[':fecha'] = $fecha;
        }

        if (!empty($id_categoria) && $id_categoria > 0) {
            $where[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }
        if (!empty($busqueda)) {
            $where[] = "prod.descripcion LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql = "SELECT COUNT(DISTINCT ns.id_nota_salida) AS total
                FROM nota_de_salida ns
                INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                WHERE " . implode(' AND ', $where);

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function obtenerResumenCierreCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null) {
        $where = ["ns.eliminado = 0", "DATE(ns.fecha) = :fecha"];
        $params = [':fecha' => $fecha];

        if (!empty($id_usuario)) {
            $where[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }

        if (!empty($id_categoria) && $id_categoria > 0) {
            $where[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = (int)$id_categoria;
        }
        if (!empty($busqueda)) {
            $where[] = "prod.descripcion LIKE :busqueda";
            $params[':busqueda'] = '%' . trim($busqueda) . '%';
        }

        $sql = "SELECT 
                    COUNT(DISTINCT ns.id_nota_salida) AS total_documentos,
                    COALESCE(SUM(ds.cantidad), 0)     AS total_unidades,
                    COUNT(*)                          AS total_lineas
                FROM nota_de_salida ns
                INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                WHERE " . implode(' AND ', $where);

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_documentos' => (int)($row['total_documentos'] ?? 0),
            'total_unidades'   => (int)($row['total_unidades'] ?? 0),
            'total_lineas'     => (int)($row['total_lineas'] ?? 0),
        ];
    }

    public function obtenerResumenCierreDia($id_usuario, $fecha) {
        return $this->obtenerResumenCierreCajero($id_usuario, $fecha);
    }

    public function obtenerNombreCajero($id_usuario) {
        $sql = "SELECT CONCAT(COALESCE(per.nombre,''), ' ', COALESCE(per.apellido,'')) AS nombre
                FROM usuario u
                LEFT JOIN persona per ON u.id_persona = per.id_persona
                WHERE u.id_usuario = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['nombre'] ?? 'N/A';
    }
}