<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class ReportesModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Comprueba si una tabla tiene una columna específica en la DB actual
     */
    private function hasColumn($table, $column) {
        try {
            $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':table' => $table, ':column' => $column]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtiene los movimientos (entradas y salidas)
     */
    public function obtenerMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $limit = 10, $offset = 0) {
        $whereEntrada = [];
        $whereSalida = [];
        $params = [];

        // detectar si existe columna 'anulado' en tablas para mostrar estado
        $hasAnuladoEntrada = $this->hasColumn('nota_de_entrada', 'anulado');
        $hasAnuladoSalida = $this->hasColumn('nota_de_salida', 'anulado');

        // Filtro por rango de fechas
        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $whereEntrada[] = "DATE(ne.Fecha_ingreso) BETWEEN :fecha_inicio AND :fecha_fin";
            $whereSalida[] = "DATE(ns.fecha) BETWEEN :fecha_inicio AND :fecha_fin";
            $params[':fecha_inicio'] = $fecha_inicio;
            $params[':fecha_fin'] = $fecha_fin;
        } elseif (!empty($fecha_inicio)) {
            $whereEntrada[] = "DATE(ne.Fecha_ingreso) >= :fecha_inicio";
            $whereSalida[] = "DATE(ns.fecha) >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        } elseif (!empty($fecha_fin)) {
            $whereEntrada[] = "DATE(ne.Fecha_ingreso) <= :fecha_fin";
            $whereSalida[] = "DATE(ns.fecha) <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }

        // Filtro por categoría
        if (!empty($id_categoria) && $id_categoria > 0) {
            $whereEntrada[] = "prod.id_categoria = :id_categoria";
            $whereSalida[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }

        // Filtro por producto (búsqueda por nombre)
        if (!empty($busqueda)) {
            $whereEntrada[] = "prod.descripcion LIKE :busqueda_entrada";
            $whereSalida[] = "prod.descripcion LIKE :busqueda_salida";
            $params[':busqueda_entrada'] = '%' . $busqueda . '%';
            $params[':busqueda_salida'] = '%' . $busqueda . '%';
        }

        $whereClauseEntrada = !empty($whereEntrada) ? ' WHERE ' . implode(' AND ', $whereEntrada) : '';
        $whereClauseSalida = !empty($whereSalida) ? ' WHERE ' . implode(' AND ', $whereSalida) : '';

        // Si el usuario filtra por tipo_movimiento, ajustar condiciones para incluir/excluir anulados
        if (!empty($tipo_movimiento)) {
            if ($tipo_movimiento === 'Entrada') {
                if ($hasAnuladoEntrada) {
                    $whereEntrada[] = "COALESCE(ne.anulado,0) = 0";
                }
            } elseif ($tipo_movimiento === 'Salida') {
                if ($hasAnuladoSalida) {
                    $whereSalida[] = "COALESCE(ns.anulado,0) = 0";
                }
            } elseif ($tipo_movimiento === 'Anulacion') {
                // Mostrar únicamente movimientos anulados (entradas y salidas)
                if ($hasAnuladoEntrada) {
                    $whereEntrada[] = "COALESCE(ne.anulado,0) = 1";
                } else {
                    // Si la tabla no soporta 'anulado', evitar coincidencias en la parte de entradas
                    $whereEntrada[] = "1 = 0";
                }

                if ($hasAnuladoSalida) {
                    $whereSalida[] = "COALESCE(ns.anulado,0) = 1";
                } else {
                    // Si la tabla no soporta 'anulado', evitar coincidencias en la parte de salidas
                    $whereSalida[] = "1 = 0";
                }
            }

            // reconstruir cláusulas after adding tipo filter
            $whereClauseEntrada = !empty($whereEntrada) ? ' WHERE ' . implode(' AND ', $whereEntrada) : '';
            $whereClauseSalida = !empty($whereSalida) ? ' WHERE ' . implode(' AND ', $whereSalida) : '';
        }

        // 🔥 CONSULTA DE ENTRADAS
        $anuladoEntradaExpr = $hasAnuladoEntrada ? "COALESCE(ne.anulado,0) AS anulado" : "0 AS anulado";
        $tipoEntradaExpr = $hasAnuladoEntrada ? "CASE WHEN COALESCE(ne.anulado,0) = 1 THEN 'Anulacion' ELSE 'Entrada' END AS tipo_movimiento" : "'Entrada' AS tipo_movimiento";
        $sqlEntrada = "SELECT 
                    ne.id_nota_entrada AS origen_id,
                prod.descripcion AS nombre_producto,
                prod.id_categoria AS id_categoria,
                COALESCE(cat.nombre_categoria, '') AS categoria_nombre,
                            " . $tipoEntradaExpr . ",
                    de.cantidad AS cantidad,
                    prod.costo_unitario AS costo_prev,
                    ne.Fecha_ingreso AS fecha_movimiento,
                    COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo,
                    COALESCE(r.nombre_rol, 'N/A') AS rol_usuario,
                    " . $anuladoEntradaExpr . "
                FROM nota_de_entrada ne
                        INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                        INNER JOIN producto prod ON de.id_producto = prod.id_producto
                        LEFT JOIN usuario u ON ne.id_usuario = u.id_usuario
                        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                        LEFT JOIN rol r ON u.id_rol = r.id_rol
                        LEFT JOIN proveedor pp ON ne.id_proveedor = pp.id_proveedor
                        LEFT JOIN persona ppn ON pp.id_persona = ppn.id_persona
                        LEFT JOIN categoria cat ON prod.id_categoria = cat.id_categoria
                        $whereClauseEntrada";

        // 🔥 CONSULTA DE SALIDAS
        $anuladoSalidaExpr = $hasAnuladoSalida ? "COALESCE(ns.anulado,0) AS anulado" : "0 AS anulado";
        $tipoSalidaExpr = $hasAnuladoSalida ? "CASE WHEN COALESCE(ns.anulado,0) = 1 THEN 'Anulacion' ELSE 'Salida' END AS tipo_movimiento" : "'Salida' AS tipo_movimiento";
        $sqlSalida = "SELECT 
                    ns.id_nota_salida AS origen_id,
                prod.descripcion AS nombre_producto,
                prod.id_categoria AS id_categoria,
                COALESCE(cat.nombre_categoria, '') AS categoria_nombre,
                            " . $tipoSalidaExpr . ",
                    ds.cantidad AS cantidad,
                    prod.costo_unitario AS costo_prev,
                    ns.fecha AS fecha_movimiento,
                    COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo,
                    COALESCE(r.nombre_rol, 'N/A') AS rol_usuario,
                    " . $anuladoSalidaExpr . "
                FROM nota_de_salida ns
                        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                        INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                        LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                        LEFT JOIN rol r ON u.id_rol = r.id_rol
                        LEFT JOIN persona pcli ON ns.id_persona = pcli.id_persona
                        LEFT JOIN cliente_juridico pcli_j ON ns.id_persona = pcli_j.id_persona
                        LEFT JOIN categoria cat ON prod.id_categoria = cat.id_categoria
                        $whereClauseSalida";

        // 🔥 CONSULTA DE PRODUCTOS (estado actual)
        // 🔥 CONSULTA DE PRODUCTOS (estado actual)
        $sqlProductos = "SELECT 
                            p.id_producto AS origen_id,
                            p.descripcion AS nombre_producto,
                            p.id_categoria AS id_categoria,
                            COALESCE(cat.nombre_categoria, '') AS categoria_nombre,
                            'Producto' AS tipo_movimiento,
                            p.cantidad AS cantidad,
                            p.costo_unitario AS costo_prev,
                            NULL AS fecha_movimiento,
                            'N/A' AS usuario_activo,
                            'N/A' AS rol_usuario,
                            0 AS anulado
                        FROM producto p
                        LEFT JOIN categoria cat ON p.id_categoria = cat.id_categoria";

        // Aplicar filtros específicos para productos (categoría y búsqueda)
        $whereProducto = [];
        if (!empty($id_categoria) && $id_categoria > 0) {
            $whereProducto[] = "p.id_categoria = :id_categoria";
        }
        if (!empty($busqueda)) {
            $whereProducto[] = "p.descripcion LIKE :busqueda_producto";
            $params[':busqueda_producto'] = '%' . $busqueda . '%';
        }
        $whereClauseProducto = !empty($whereProducto) ? ' WHERE ' . implode(' AND ', $whereProducto) : '';
        $sqlProductos .= $whereClauseProducto;

        // 🔥 APLICAR FILTRO POR TIPO DE MOVIMIENTO
        if ($tipo_movimiento === 'Entrada') {
            $sql = $sqlEntrada . " ORDER BY fecha_movimiento DESC LIMIT :limit OFFSET :offset";
        } elseif ($tipo_movimiento === 'Salida') {
            $sql = $sqlSalida . " ORDER BY fecha_movimiento DESC LIMIT :limit OFFSET :offset";
        } elseif ($tipo_movimiento === 'Producto') {
            $sql = $sqlProductos . " ORDER BY nombre_producto ASC LIMIT :limit OFFSET :offset";
        } elseif ($tipo_movimiento === 'Anulacion') {
            // Mostrar únicamente movimientos anulados (entradas + salidas)
            $sql = "($sqlEntrada) UNION ALL ($sqlSalida) ORDER BY fecha_movimiento DESC LIMIT :limit OFFSET :offset";
        } else {
            $sql = "($sqlEntrada) UNION ALL ($sqlSalida) UNION ALL ($sqlProductos) ORDER BY fecha_movimiento DESC LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de registros con filtros
     */
    public function contarMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null) {
        $movimientos = $this->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
        return count($movimientos);
    }

    /**
     * Obtiene los totales globales (entradas y salidas)
     */
    public function obtenerTotalesGlobales() {
        // Excluir movimientos anulados si la columna 'anulado' existe en las tablas
        $hasAnuladoEntrada = $this->hasColumn('nota_de_entrada', 'anulado');
        $hasAnuladoSalida = $this->hasColumn('nota_de_salida', 'anulado');

        $condEntrada = $hasAnuladoEntrada ? "WHERE COALESCE(ne.anulado,0) = 0" : "";
        $condSalida = $hasAnuladoSalida ? "WHERE COALESCE(ns.anulado,0) = 0" : "";

        $sql = "SELECT 
                    SUM(CASE WHEN tipo_movimiento = 'Entrada' THEN cantidad ELSE 0 END) AS total_entradas,
                    SUM(CASE WHEN tipo_movimiento = 'Salida' THEN cantidad ELSE 0 END) AS total_salidas
                FROM (
                    SELECT 'Entrada' AS tipo_movimiento, de.cantidad AS cantidad
                    FROM nota_de_entrada ne
                    INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                    $condEntrada
                    UNION ALL
                    SELECT 'Salida' AS tipo_movimiento, ds.cantidad AS cantidad
                    FROM nota_de_salida ns
                    INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                    $condSalida
                ) as movimientos_totales";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_entradas' => (int)($row['total_entradas'] ?? 0),
            'total_salidas' => (int)($row['total_salidas'] ?? 0)
        ];
    }
}