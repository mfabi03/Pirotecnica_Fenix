<?php
// app/Model/ReportesModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

class ReportesModel extends ConnectDB
{
    private $conex;

    public function __construct()
    {
        parent::__construct();
        $this->conex = $this->getConnection();
        try {
            $this->conex->exec("SET SESSION group_concat_max_len = 1000000");
        } catch (\Throwable $e) {
            error_log('No se pudo ajustar group_concat_max_len: ' . $e->getMessage());
        }
    }

    public function obtenerMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $limit = 10, $offset = 0)
    {
        $whereEntrada = [];
        $whereSalida  = [];
        $whereCierre  = [];
        $params       = [];

        $fecha_inicio = $this->normalizarFechaSql($fecha_inicio);
        $fecha_fin    = $this->normalizarFechaSql($fecha_fin);

        // ---------- FILTRO FECHAS ----------
        if (!empty($fecha_inicio) && !empty($fecha_fin) && $fecha_fin !== $fecha_inicio) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) >= :fecha_inicio";
            $whereSalida[]  = "DATE(ns.fecha) >= :fecha_inicio";
            $whereCierre[]  = "DATE(c.created_at) >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;

            $whereEntrada[] = "DATE(ne.fecha_ingreso) <= :fecha_fin";
            $whereSalida[]  = "DATE(ns.fecha) <= :fecha_fin";
            $whereCierre[]  = "DATE(c.created_at) <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        } elseif (!empty($fecha_inicio)) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) >= :fecha_inicio";
            $whereSalida[]  = "DATE(ns.fecha) >= :fecha_inicio";
            $whereCierre[]  = "DATE(c.created_at) >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        } elseif (!empty($fecha_fin)) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) <= :fecha_fin";
            $whereSalida[]  = "DATE(ns.fecha) <= :fecha_fin";
            $whereCierre[]  = "DATE(c.created_at) <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }

        // ---------- FILTRO CATEGORÍA ----------
        if (!empty($id_categoria) && $id_categoria > 0) {
            $whereEntrada[] = "prod.id_categoria = :id_categoria";
            $whereSalida[]  = "prod.id_categoria = :id_categoria";
            $whereCierre[]  = "1 = 0";
            $params[':id_categoria'] = $id_categoria;
        }

        // ---------- FILTRO BÚSQUEDA ----------
        if (!empty($busqueda)) {
            $whereEntrada[] = "(prod.descripcion LIKE :busqueda OR c.nombre_categoria LIKE :busqueda OR pr.razon_social LIKE :busqueda OR pr.rif LIKE :busqueda OR CAST(ne.id_nota_entrada AS CHAR) LIKE :busqueda)";
            $whereSalida[]  = "(prod.descripcion LIKE :busqueda OR c.nombre_categoria LIKE :busqueda OR p_cliente.nombre LIKE :busqueda OR p_cliente.apellido LIKE :busqueda OR p_cliente.cedula LIKE :busqueda OR cj.razon_social LIKE :busqueda OR cj.rif LIKE :busqueda OR CAST(ns.id_nota_salida AS CHAR) LIKE :busqueda)";
            $whereCierre[]  = "(CONCAT(p.nombre, ' ', p.apellido) LIKE :busqueda OR CAST(c.id_cierre AS CHAR) LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $whereClauseEntrada = !empty($whereEntrada) ? ' WHERE ' . implode(' AND ', $whereEntrada) : '';
        $whereClauseSalida  = !empty($whereSalida)  ? ' WHERE ' . implode(' AND ', $whereSalida)  : '';
        $whereClauseCierre  = !empty($whereCierre)  ? ' WHERE ' . implode(' AND ', $whereCierre)  : '';

        // ---------- SQL ENTRADA ----------
        $sqlEntrada = "SELECT
            ne.id_nota_entrada AS id_documento,
            0 AS id_usuario_cierre,
            CASE
                WHEN ne.fecha_ingreso IS NULL
                     OR ne.fecha_ingreso = '0000-00-00'
                     OR ne.fecha_ingreso = '0000-00-00 00:00:00'
                    THEN '0000-00-00 00:00:00'
                ELSE ne.fecha_ingreso
            END AS fecha_movimiento,
            CASE WHEN ne.eliminado = 1 THEN 'Anulación Entrada' ELSE 'Entrada' END AS tipo_movimiento,
            'entrada' AS tipo_origen,
            COALESCE(pr.razon_social, 'Sin proveedor') AS nombre_cliente,
            COALESCE(pr.rif, 'N/A') AS documento_cliente,
            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo,
            COUNT(de.id_detalle_entrada) AS total_productos,
            COUNT(DISTINCT c.id_categoria) AS total_categorias,
            COALESCE(GROUP_CONCAT(CONCAT(prod.descripcion, ' (x', de.cantidad, ')') ORDER BY de.id_detalle_entrada SEPARATOR ', '), 'Sin productos') AS productos_lista,
            COALESCE(GROUP_CONCAT(DISTINCT c.nombre_categoria ORDER BY c.nombre_categoria SEPARATOR ', '), 'Sin categoría') AS categoria,
            COALESCE(SUM(de.cantidad), 0) AS total_unidades,
            ne.eliminado AS estado_anulado,
            ne.motivo_anulacion AS motivo_anulacion
        FROM nota_de_entrada ne
        LEFT JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
        LEFT JOIN producto prod ON de.id_producto = prod.id_producto
        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
        LEFT JOIN proveedor pr ON ne.id_proveedor = pr.id_proveedor
        LEFT JOIN usuario u ON ne.id_usuario = u.id_usuario
        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
        {$whereClauseEntrada}
        GROUP BY ne.id_nota_entrada, ne.fecha_ingreso, ne.eliminado, ne.motivo_anulacion, pr.razon_social, pr.rif, per_u.nombre, per_u.apellido";

        // ---------- SQL SALIDA ----------
        $sqlSalida = "SELECT
            ns.id_nota_salida AS id_documento,
            0 AS id_usuario_cierre,
            CASE
                WHEN ns.fecha IS NULL
                     OR ns.fecha = '0000-00-00'
                     OR ns.fecha = '0000-00-00 00:00:00'
                    THEN '0000-00-00 00:00:00'
                ELSE ns.fecha
            END AS fecha_movimiento,
            CASE WHEN ns.eliminado = 1 THEN 'Anulación Salida' ELSE 'Salida' END AS tipo_movimiento,
            'salida' AS tipo_origen,
            CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN cj.razon_social ELSE CONCAT_WS(' ', p_cliente.nombre, p_cliente.apellido) END AS nombre_cliente,
            COALESCE(cj.rif, p_cliente.cedula, 'N/A') AS documento_cliente,
            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo,
            COUNT(ds.id_detalle_salida) AS total_productos,
            COUNT(DISTINCT c.id_categoria) AS total_categorias,
            COALESCE(GROUP_CONCAT(CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') ORDER BY ds.id_detalle_salida SEPARATOR ', '), 'Sin productos') AS productos_lista,
            COALESCE(GROUP_CONCAT(DISTINCT c.nombre_categoria ORDER BY c.nombre_categoria SEPARATOR ', '), 'Sin categoría') AS categoria,
            COALESCE(SUM(ds.cantidad), 0) AS total_unidades,
            ns.eliminado AS estado_anulado,
            ns.motivo_anulacion AS motivo_anulacion
        FROM nota_de_salida ns
        LEFT JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
        LEFT JOIN producto prod ON ds.id_producto = prod.id_producto
        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
        LEFT JOIN persona p_cliente ON ns.id_persona = p_cliente.id_persona
        LEFT JOIN cliente_juridico cj ON p_cliente.id_persona = cj.id_persona
        LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
        {$whereClauseSalida}
        GROUP BY ns.id_nota_salida, ns.fecha, ns.eliminado, ns.motivo_anulacion, cj.id_cliente_juridico, cj.razon_social, cj.rif, p_cliente.nombre, p_cliente.apellido, p_cliente.cedula, per_u.nombre, per_u.apellido";

        // ---------- SQL CIERRE ----------
        $sqlCierre = "SELECT
            c.id_cierre AS id_documento,
            c.id_usuario AS id_usuario_cierre,
            CASE
                WHEN c.created_at IS NULL
                     OR c.created_at = '0000-00-00'
                     OR c.created_at = '0000-00-00 00:00:00'
                    THEN '0000-00-00 00:00:00'
                ELSE c.created_at
            END AS fecha_movimiento,
            'Cierre de Caja' AS tipo_movimiento,
            'cierre' AS tipo_origen,
            'Cierre Global' AS nombre_cliente,
            'N/A' AS documento_cliente,
            COALESCE(CONCAT(p.nombre, ' ', p.apellido), 'N/A') AS usuario_activo,
            c.total_productos_distintos AS total_productos,
            c.total_categorias AS total_categorias,
            '---' AS productos_lista,
            '---' AS categoria,
            c.total_unidades AS total_unidades,
            0 AS estado_anulado,
            NULL AS motivo_anulacion
        FROM cierre_caja c
        LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
        LEFT JOIN persona p ON u.id_persona = p.id_persona
        {$whereClauseCierre}";

        // ---------- SELECCIÓN SEGÚN TIPO ----------
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
                        SELECT * FROM ({$sqlEntrada}) AS t1 WHERE t1.estado_anulado = 0
                        UNION ALL
                        SELECT * FROM ({$sqlSalida}) AS t2 WHERE t2.estado_anulado = 0
                        UNION ALL
                        SELECT * FROM ({$sqlCierre}) AS t3
                    ) AS t";
        }

        // ORDEN DESCENDENTE: registros más recientes siempre primero
        $sql .= " ORDER BY
                    CASE
                        WHEN fecha_movimiento IS NULL
                             OR fecha_movimiento = ''
                             OR fecha_movimiento = '0000-00-00'
                             OR fecha_movimiento = '0000-00-00 00:00:00'
                            THEN '0000-00-00 00:00:00'
                        WHEN CHAR_LENGTH(fecha_movimiento) = 10
                            THEN CONCAT(fecha_movimiento, ' 23:59:59')
                        ELSE fecha_movimiento
                    END DESC,
                    id_documento DESC
                  LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->conex->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("========== ERROR obtenerMovimientosDiarios ==========");
            error_log("Mensaje: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            error_log("====================================================");
            return [];
        }
    }

    public function contarMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null)
    {
        $movimientos = $this->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
        return count($movimientos);
    }

    public function obtenerTotalesReporte($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null)
    {
        $where = [];
        $params = [];

        $fecha_inicio = $this->normalizarFechaSql($fecha_inicio);
        $fecha_fin    = $this->normalizarFechaSql($fecha_fin);

        if (!empty($fecha_inicio) && !empty($fecha_fin) && $fecha_fin !== $fecha_inicio) {
            $where[] = "fecha_movimiento >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
            $where[] = "fecha_movimiento <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        } elseif (!empty($fecha_inicio)) {
            $where[] = "fecha_movimiento >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        } elseif (!empty($fecha_fin)) {
            $where[] = "fecha_movimiento <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }

        if (!empty($id_categoria) && $id_categoria > 0) {
            $where[] = "id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }
        if (!empty($busqueda)) {
            $where[] = "(nombre_producto LIKE :busqueda OR nombre_cliente LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        if ($tipo_movimiento !== null && $tipo_movimiento !== '') {
            $tipoNormalizado = trim((string)$tipo_movimiento);
            if (in_array($tipoNormalizado, ['Anulación', 'Anulada', 'Anuladas'], true)) {
                $where[] = "tipo_movimiento LIKE 'Anulación%'";
            } elseif (in_array($tipoNormalizado, ['Anulación Entrada', 'Anulaciones Entrada'], true)) {
                $where[] = "tipo_movimiento = 'Anulación Entrada'";
            } elseif (in_array($tipoNormalizado, ['Anulación Salida', 'Anulaciones Salida'], true)) {
                $where[] = "tipo_movimiento = 'Anulación Salida'";
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
            SUM(CASE WHEN tipo_movimiento LIKE 'Anulación%' THEN 1 ELSE 0 END) AS total_anuladas,
            SUM(CASE WHEN tipo_movimiento = 'Cierre de Caja' THEN 1 ELSE 0 END) AS total_cierres,
            COUNT(*) AS total_notas
        FROM (
            SELECT
                CASE WHEN ne.eliminado = 1 THEN 'Anulación Entrada' ELSE 'Entrada' END AS tipo_movimiento,
                'entrada' AS tipo_origen,
                COALESCE(prod.descripcion, 'Sin producto') AS nombre_producto,
                COALESCE(pr.razon_social, 'Sin proveedor') AS nombre_cliente,
                COALESCE(prod.id_categoria, 0) AS id_categoria,
                DATE(COALESCE(ne.fecha_ingreso, NOW())) AS fecha_movimiento
            FROM nota_de_entrada ne
            LEFT JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
            LEFT JOIN producto prod ON de.id_producto = prod.id_producto
            LEFT JOIN proveedor pr ON ne.id_proveedor = pr.id_proveedor
            UNION ALL
            SELECT
                CASE WHEN ns.eliminado = 1 THEN 'Anulación Salida' ELSE 'Salida' END AS tipo_movimiento,
                'salida' AS tipo_origen,
                COALESCE(prod.descripcion, 'Sin producto') AS nombre_producto,
                CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN cj.razon_social ELSE CONCAT_WS(' ', p_cliente.nombre, p_cliente.apellido) END AS nombre_cliente,
                COALESCE(prod.id_categoria, 0) AS id_categoria,
                DATE(COALESCE(ns.fecha, NOW())) AS fecha_movimiento
            FROM nota_de_salida ns
            LEFT JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
            LEFT JOIN producto prod ON ds.id_producto = prod.id_producto
            LEFT JOIN persona p_cliente ON ns.id_persona = p_cliente.id_persona
            LEFT JOIN cliente_juridico cj ON p_cliente.id_persona = cj.id_persona
            UNION ALL
            SELECT
                'Cierre de Caja' AS tipo_movimiento,
                'cierre' AS tipo_origen,
                'Cierre Global' AS nombre_producto,
                'Cierre Global' AS nombre_cliente,
                0 AS id_categoria,
                DATE(created_at) AS fecha_movimiento
            FROM cierre_caja
        ) resumen{$whereSql}";

        try {
            $stmt = $this->conex->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en obtenerTotalesReporte: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            $row = [];
        }

        return [
            'total_entradas' => (int)($row['total_entradas'] ?? 0),
            'total_salidas'  => (int)($row['total_salidas']  ?? 0),
            'total_anuladas' => (int)($row['total_anuladas'] ?? 0),
            'total_cierres'  => (int)($row['total_cierres']  ?? 0),
            'total_notas'    => (int)($row['total_notas']    ?? 0),
        ];
    }

    public function obtenerTotalesGlobales()
    {
        return $this->obtenerTotalesReporte();
    }

    private function normalizarFechaSql($fecha)
    {
        if (empty($fecha)) return null;
        $fecha = trim((string)$fecha);
        if ($fecha === '') return null;

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $fecha, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $fecha, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        $ts = strtotime($fecha);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }
        return null;
    }

    public function obtenerCierrePorId($idCierre) { return null; }
    public function obtenerDetalleCierre($idCierre) { return []; }
    public function obtenerDetalleCierrePDF($idCierre) { return []; }
}