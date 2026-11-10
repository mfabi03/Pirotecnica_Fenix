<?php
namespace App\Pirotecnicafenix\Model;

use PDO;

class ReportesModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ============================================================
    // OBTENER MOVIMIENTOS AGRUPADOS POR NOTA (Entradas, Salidas y Cierres)
    // ============================================================
    public function obtenerMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $limit = 10, $offset = 0) {
        $whereEntrada = [];
        $whereSalida = [];
        $whereCierre = [];
        $params = [];

        if (!empty($fecha_inicio)) {
            if (!empty($fecha_fin) && $fecha_fin !== $fecha_inicio) {
                $whereEntrada[] = "DATE(ne.fecha_ingreso) >= :fecha_inicio";
                $whereSalida[] = "DATE(ns.fecha) >= :fecha_inicio";
                $whereCierre[] = "DATE(c.created_at) >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;

                $whereEntrada[] = "DATE(ne.fecha_ingreso) <= :fecha_fin";
                $whereSalida[] = "DATE(ns.fecha) <= :fecha_fin";
                $whereCierre[] = "DATE(c.created_at) <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin;
            } else {
                $whereEntrada[] = "DATE(ne.fecha_ingreso) = :fecha_inicio";
                $whereSalida[] = "DATE(ns.fecha) = :fecha_inicio";
                $whereCierre[] = "DATE(c.created_at) = :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            }
        }
        
        if (!empty($id_categoria) && $id_categoria > 0) {
            $whereEntrada[] = "prod.id_categoria = :id_categoria";
            $whereSalida[] = "prod.id_categoria = :id_categoria";
            $whereCierre[] = "1 = 0"; 
            $params[':id_categoria'] = $id_categoria;
        }
        if (!empty($busqueda)) {
            $whereEntrada[] = "prod.descripcion LIKE :busqueda";
            $whereSalida[] = "prod.descripcion LIKE :busqueda";
            $whereCierre[] = "CONCAT(p.nombre, ' ', p.apellido) LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $whereClauseEntrada = !empty($whereEntrada) ? ' WHERE ' . implode(' AND ', $whereEntrada) : '';
        $whereClauseSalida = !empty($whereSalida) ? ' WHERE ' . implode(' AND ', $whereSalida) : '';
        $whereClauseCierre = !empty($whereCierre) ? ' WHERE ' . implode(' AND ', $whereCierre) : '';

        // 1. ENTRADAS
        $sqlEntrada = "SELECT 
            ne.id_nota_entrada AS id_documento,
            ne.fecha_ingreso AS fecha_movimiento,
            CASE WHEN ne.eliminado = 1 THEN 'Anulación' ELSE 'Entrada' END AS tipo_movimiento,
            'entrada' AS tipo_origen,
            COALESCE(pr.razon_social, 'Sin proveedor') AS nombre_cliente,
            pr.rif AS documento_cliente,
            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo,
            COUNT(de.id_detalle_entrada) AS total_productos,
            COUNT(DISTINCT c.id_categoria) AS total_categorias,
            GROUP_CONCAT(CONCAT(prod.descripcion, ' (x', de.cantidad, ')') ORDER BY de.id_detalle_entrada SEPARATOR ', ') AS productos_lista,
            GROUP_CONCAT(DISTINCT c.nombre_categoria ORDER BY c.nombre_categoria SEPARATOR ', ') AS categoria,
            SUM(de.cantidad) AS total_unidades,
            ne.eliminado AS estado_anulado,
            ne.motivo_anulacion AS motivo_anulacion
        FROM nota_de_entrada ne
        INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
        INNER JOIN producto prod ON de.id_producto = prod.id_producto
        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
        LEFT JOIN proveedor pr ON ne.id_proveedor = pr.id_proveedor
        LEFT JOIN usuario u ON ne.id_usuario = u.id_usuario
        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
        {$whereClauseEntrada}
        GROUP BY ne.id_nota_entrada, ne.fecha_ingreso, ne.eliminado, ne.motivo_anulacion, pr.razon_social, pr.rif, per_u.nombre, per_u.apellido";

        // 2. SALIDAS
        $sqlSalida = "SELECT 
            ns.id_nota_salida AS id_documento,
            ns.fecha AS fecha_movimiento,
            CASE WHEN ns.eliminado = 1 THEN 'Anulación' ELSE 'Salida' END AS tipo_movimiento,
            'salida' AS tipo_origen,
            CASE 
                WHEN cj.id_cliente_juridico IS NOT NULL THEN cj.razon_social
                ELSE CONCAT_WS(' ', p_cliente.nombre, p_cliente.apellido)
            END AS nombre_cliente,
            COALESCE(cj.rif, p_cliente.cedula, 'N/A') AS documento_cliente,
            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo,
            COUNT(ds.id_detalle_salida) AS total_productos,
            COUNT(DISTINCT c.id_categoria) AS total_categorias,
            GROUP_CONCAT(CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') ORDER BY ds.id_detalle_salida SEPARATOR ', ') AS productos_lista,
            GROUP_CONCAT(DISTINCT c.nombre_categoria ORDER BY c.nombre_categoria SEPARATOR ', ') AS categoria,
            SUM(ds.cantidad) AS total_unidades,
            ns.eliminado AS estado_anulado,
            ns.motivo_anulacion AS motivo_anulacion
        FROM nota_de_salida ns
        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
        INNER JOIN producto prod ON ds.id_producto = prod.id_producto
        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
        LEFT JOIN persona p_cliente ON ns.id_persona = p_cliente.id_persona
        LEFT JOIN cliente_juridico cj ON p_cliente.id_persona = cj.id_persona
        LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
        {$whereClauseSalida}
        GROUP BY ns.id_nota_salida, ns.fecha, ns.eliminado, ns.motivo_anulacion, cj.id_cliente_juridico, cj.razon_social, cj.rif, p_cliente.nombre, p_cliente.apellido, p_cliente.cedula, per_u.nombre, per_u.apellido";

        // 3. CIERRES DE CAJA (Corregido para evitar error de columna)
        $sqlCierre = "SELECT 
            c.id_cierre AS id_documento,
            c.created_at AS fecha_movimiento,
            'Cierre de Caja' AS tipo_movimiento,
            'cierre' AS tipo_origen,
            'Cierre Global' AS nombre_cliente,
            'N/A' AS documento_cliente,
            COALESCE(CONCAT(p.nombre, ' ', p.apellido), 'N/A') AS usuario_activo,
            c.total_productos_distintos AS total_productos,
            0 AS total_categorias, 
            '---' AS productos_lista,
            '---' AS categoria,
            c.total_unidades AS total_unidades,
            0 AS estado_anulado,
            NULL AS motivo_anulacion
        FROM cierre_caja c
        LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
        LEFT JOIN persona p ON u.id_persona = p.id_persona
        {$whereClauseCierre}";

        $tipoNormalizado = is_string($tipo_movimiento) ? trim($tipo_movimiento) : null;

        if ($tipoNormalizado === 'Entrada' || $tipoNormalizado === 'Entradas') {
            $sql = "SELECT * FROM ({$sqlEntrada}) AS t WHERE t.estado_anulado = 0";
        } elseif ($tipoNormalizado === 'Salida' || $tipoNormalizado === 'Salidas') {
            $sql = "SELECT * FROM ({$sqlSalida}) AS t WHERE t.estado_anulado = 0";
        } elseif ($tipoNormalizado === 'Cierre de Caja' || $tipoNormalizado === 'Cierres') {
            $sql = "SELECT * FROM ({$sqlCierre}) AS t";
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
            $sql = "SELECT * FROM (
                        SELECT * FROM ({$sqlEntrada}) AS t1 
                        UNION ALL
                        SELECT * FROM ({$sqlSalida}) AS t2
                        UNION ALL
                        SELECT * FROM ({$sqlCierre}) AS t3
                    ) AS t";
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

    // ============================================================
    // OBTENER TOTALES (Para las tarjetas de estadísticas)
    // ============================================================
    public function obtenerTotalesReporte($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null) {
        $where = [];
        $params = [];

        if (!empty($fecha_inicio)) {
            if (!empty($fecha_fin) && $fecha_fin !== $fecha_inicio) {
                $where[] = "fecha_movimiento >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
                $where[] = "fecha_movimiento <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin;
            } else {
                $where[] = "fecha_movimiento = :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            }
        }
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
            } elseif ($tipoNormalizado === 'Cierre de Caja' || $tipoNormalizado === 'Cierres') {
                $where[] = "tipo_movimiento = 'Cierre de Caja'";
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
            SUM(CASE WHEN tipo_movimiento = 'Cierre de Caja' THEN 1 ELSE 0 END) AS total_cierres,
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
            UNION ALL
            SELECT
                'Cierre de Caja' AS tipo_movimiento,
                'cierre' AS tipo_origen,
                'Cierre Global' AS nombre_producto,
                0 AS id_categoria,
                DATE(created_at) AS fecha_movimiento
            FROM cierre_caja
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
            'total_cierres' => (int)($row['total_cierres'] ?? 0),
            'total_notas' => (int)($row['total_notas'] ?? 0),
        ];
    }

    public function obtenerTotalesGlobales() {
        return $this->obtenerTotalesReporte();
    }
}
?>