<?php
namespace App\Pirotecnicafenix\Model;

use PDO;

class ReportesModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $limit = 10, $offset = 0) {
        $whereEntrada = [];
        $whereSalida = [];
        $params = [];

        if (!empty($fecha_inicio)) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) >= :fecha_inicio";
            $whereSalida[] = "DATE(ns.fecha) >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) <= :fecha_fin";
            $whereSalida[] = "DATE(ns.fecha) <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        if (!empty($id_categoria) && $id_categoria > 0) {
            $whereEntrada[] = "prod.id_categoria = :id_categoria";
            $whereSalida[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }
        if (!empty($busqueda)) {
            $whereEntrada[] = "prod.descripcion LIKE :busqueda";
            $whereSalida[] = "prod.descripcion LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $whereClauseEntrada = !empty($whereEntrada) ? ' WHERE ' . implode(' AND ', $whereEntrada) : '';
        $whereClauseSalida = !empty($whereSalida) ? ' WHERE ' . implode(' AND ', $whereSalida) : '';

        $sqlEntrada = "SELECT 
            prod.descripcion AS nombre_producto,
            c.nombre_categoria AS categoria,
            CASE WHEN ne.eliminado = 1 THEN 'Anulación' ELSE 'Entrada' END AS tipo_movimiento,
            de.cantidad AS cantidad,
            de.costo_unitario AS costo_proveedor,
            ne.fecha_ingreso AS fecha_movimiento,
            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo,
            ne.id_nota_entrada AS id_documento,
            IFNULL(ne.motivo_anulacion, 'No registrado') AS motivo_anulacion,
            ne.eliminado AS estado_anulado
        FROM nota_de_entrada ne
        INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
        INNER JOIN producto prod ON de.id_producto = prod.id_producto
        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
        LEFT JOIN usuario u ON ne.id_usuario = u.id_usuario
        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
        {$whereClauseEntrada}";

        $sqlSalida = "SELECT 
            prod.descripcion AS nombre_producto,
            c.nombre_categoria AS categoria,
            CASE WHEN ns.eliminado = 1 THEN 'Anulación' ELSE 'Salida' END AS tipo_movimiento,
            ds.cantidad AS cantidad,
            prod.costo_unitario AS costo_proveedor,
            ns.fecha AS fecha_movimiento,
            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo,
            ns.id_nota_salida AS id_documento,
            IFNULL(ns.motivo_anulacion, 'No registrado') AS motivo_anulacion,
            ns.eliminado AS estado_anulado
        FROM nota_de_salida ns
        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
        INNER JOIN producto prod ON ds.id_producto = prod.id_producto
        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
        LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
        {$whereClauseSalida}";

        $tipoNormalizado = $tipo_movimiento;
        if (is_string($tipoNormalizado)) {
            $tipoNormalizado = trim($tipoNormalizado);
        }

        if ($tipoNormalizado === 'Entrada' || $tipoNormalizado === 'Entradas') {
            $sql = "SELECT * FROM ({$sqlEntrada}) AS t WHERE t.estado_anulado = 0";
        } elseif ($tipoNormalizado === 'Salida' || $tipoNormalizado === 'Salidas') {
            $sql = "SELECT * FROM ({$sqlSalida}) AS t WHERE t.estado_anulado = 0";
        } elseif ($tipoNormalizado === 'Anulación Entrada' || $tipoNormalizado === 'Anulaciones Entrada') {
            $sql = "SELECT * FROM ({$sqlEntrada}) AS t WHERE t.estado_anulado = 1";
        } elseif ($tipoNormalizado === 'Anulación Salida' || $tipoNormalizado === 'Anulaciones Salida') {
            $sql = "SELECT * FROM ({$sqlSalida}) AS t WHERE t.estado_anulado = 1";
        } elseif (in_array($tipoNormalizado, ['Anulación', 'Anulada', 'Anuladas'], true)) {
            $sql = "SELECT * FROM (
                        SELECT * FROM ({$sqlEntrada}) AS e WHERE e.estado_anulado = 1
                        UNION ALL
                        SELECT * FROM ({$sqlSalida}) AS s WHERE s.estado_anulado = 1
                    ) AS t";
        } else {
            $sql = "SELECT * FROM ({$sqlEntrada}) AS t1 UNION ALL SELECT * FROM ({$sqlSalida}) AS t2";
        }

        $sql .= " ORDER BY fecha_movimiento DESC, id_documento DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null) {
        $movimientos = $this->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
        return count($movimientos);
    }

    public function obtenerTotalesReporte($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null) {
        $where = [];
        $params = [];

        if (!empty($fecha_inicio)) { $where[] = "fecha_movimiento >= :fecha_inicio"; $params[':fecha_inicio'] = $fecha_inicio; }
        if (!empty($fecha_fin)) { $where[] = "fecha_movimiento <= :fecha_fin"; $params[':fecha_fin'] = $fecha_fin; }
        if (!empty($id_categoria) && $id_categoria > 0) { $where[] = "id_categoria = :id_categoria"; $params[':id_categoria'] = $id_categoria; }
        if (!empty($busqueda)) { $where[] = "nombre_producto LIKE :busqueda"; $params[':busqueda'] = '%' . $busqueda . '%'; }
        if ($tipo_movimiento !== null && $tipo_movimiento !== '') {
            $tipoNormalizado = trim((string)$tipo_movimiento);
            if (in_array($tipoNormalizado, ['Anulación', 'Anulada', 'Anuladas'], true)) {
                $where[] = "tipo_movimiento = 'Anulación'";
            } elseif (in_array($tipoNormalizado, ['Anulación Entrada', 'Anulaciones Entrada'], true)) {
                $where[] = "tipo_movimiento = 'Anulación' AND tipo_origen = 'entrada'";
            } elseif (in_array($tipoNormalizado, ['Anulación Salida', 'Anulaciones Salida'], true)) {
                $where[] = "tipo_movimiento = 'Anulación' AND tipo_origen = 'salida'";
            } elseif (in_array($tipoNormalizado, ['Entrada', 'Entradas'], true)) {
                $where[] = "tipo_movimiento = 'Entrada'";
            } elseif (in_array($tipoNormalizado, ['Salida', 'Salidas'], true)) {
                $where[] = "tipo_movimiento = 'Salida'";
            } else {
                $where[] = "tipo_movimiento = :tipo_movimiento";
                $params[':tipo_movimiento'] = $tipoNormalizado;
            }
        }

        $whereSql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT
            SUM(CASE WHEN tipo_movimiento = 'Entrada' THEN 1 ELSE 0 END) AS total_entradas,
            SUM(CASE WHEN tipo_movimiento = 'Salida' THEN 1 ELSE 0 END) AS total_salidas,
            SUM(CASE WHEN tipo_movimiento = 'Anulación' THEN 1 ELSE 0 END) AS total_anuladas,
            COUNT(*) AS total_notas
        FROM (
            SELECT
                CASE WHEN ne.eliminado = 1 THEN 'Anulación' ELSE 'Entrada' END AS tipo_movimiento,
                'entrada' AS tipo_origen,
                prod.descripcion AS nombre_producto,
                prod.id_categoria AS id_categoria,
                DATE(ne.fecha_ingreso) AS fecha_movimiento
            FROM nota_de_entrada ne
            INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
            INNER JOIN producto prod ON de.id_producto = prod.id_producto
            UNION ALL
            SELECT
                CASE WHEN ns.eliminado = 1 THEN 'Anulación' ELSE 'Salida' END AS tipo_movimiento,
                'salida' AS tipo_origen,
                prod.descripcion AS nombre_producto,
                prod.id_categoria AS id_categoria,
                DATE(ns.fecha) AS fecha_movimiento
            FROM nota_de_salida ns
            INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
            INNER JOIN producto prod ON ds.id_producto = prod.id_producto
        ) resumen{$whereSql}";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_entradas' => (int)($row['total_entradas'] ?? 0),
            'total_salidas' => (int)($row['total_salidas'] ?? 0),
            'total_anuladas' => (int)($row['total_anuladas'] ?? 0),
            'total_notas' => (int)($row['total_notas'] ?? 0),
        ];
    }

    public function obtenerTotalesGlobales() {
        return $this->obtenerTotalesReporte();
    }
}