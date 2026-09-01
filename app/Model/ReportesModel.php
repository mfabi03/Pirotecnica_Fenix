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
     * Obtiene movimientos (entradas y salidas) con TODOS los filtros funcionando
     */
    public function obtenerMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $limit = 10, $offset = 0) {
        $whereEntrada = [];
        $whereSalida = [];
        $params = [];

        // 🔥 FILTRO POR RANGO DE FECHAS
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

        // 🔥 FILTRO POR CATEGORÍA
        if (!empty($id_categoria) && $id_categoria > 0) {
            $whereEntrada[] = "prod.id_categoria = :id_categoria";
            $whereSalida[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }

        // 🔥 FILTRO POR PRODUCTO (búsqueda por nombre)
        if (!empty($busqueda)) {
            $whereEntrada[] = "prod.descripcion LIKE :busqueda";
            $whereSalida[] = "prod.descripcion LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $whereClauseEntrada = !empty($whereEntrada) ? ' WHERE ' . implode(' AND ', $whereEntrada) : '';
        $whereClauseSalida = !empty($whereSalida) ? ' WHERE ' . implode(' AND ', $whereSalida) : '';

        // 🔥 CONSULTA DE ENTRADAS
        $sqlEntrada = "SELECT 
                            prod.descripcion AS nombre_producto,
                            'Entrada' AS tipo_movimiento,
                            de.cantidad AS cantidad,
                            de.costo_unitario AS costo_proveedor,
                            ne.Fecha_ingreso AS fecha_movimiento,
                            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo
                        FROM nota_de_entrada ne
                        INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                        INNER JOIN producto prod ON de.id_producto = prod.id_producto
                        LEFT JOIN usuario u ON ne.id_usuario = u.id_usuario
                        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                        $whereClauseEntrada";

        // 🔥 CONSULTA DE SALIDAS
        $sqlSalida = "SELECT 
                            prod.descripcion AS nombre_producto,
                            'Salida' AS tipo_movimiento,
                            ds.cantidad AS cantidad,
                            prod.costo_unitario AS costo_proveedor,
                            ns.fecha AS fecha_movimiento,
                            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo
                        FROM nota_de_salida ns
                        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                        INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                        LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                        $whereClauseSalida";

        // 🔥 APLICAR FILTRO POR TIPO DE MOVIMIENTO (ANTES DEL UNION)
        if ($tipo_movimiento === 'Entrada') {
            $sql = $sqlEntrada;
        } elseif ($tipo_movimiento === 'Salida') {
            $sql = $sqlSalida;
        } else {
            $sql = "($sqlEntrada) UNION ALL ($sqlSalida)";
        }

        // 🔥 ORDENAR Y PAGINAR
        $sql .= " ORDER BY fecha_movimiento DESC LIMIT :limit OFFSET :offset";

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
     * Cuenta el total de registros para paginación
     */
    public function contarMovimientosDiarios($fecha_inicio = null, $fecha_fin = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null) {
        $movimientos = $this->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
        return count($movimientos);
    }

    /**
     * Obtiene los totales globales
     */
    public function obtenerTotalesGlobales() {
        $sql = "SELECT 
                    SUM(CASE WHEN tipo_movimiento = 'Entrada' THEN cantidad ELSE 0 END) AS total_entradas,
                    SUM(CASE WHEN tipo_movimiento = 'Salida' THEN cantidad ELSE 0 END) AS total_salidas
                FROM (
                    SELECT 'Entrada' AS tipo_movimiento, de.cantidad AS cantidad
                    FROM nota_de_entrada ne
                    INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                    
                    UNION ALL
                    
                    SELECT 'Salida' AS tipo_movimiento, ds.cantidad AS cantidad
                    FROM nota_de_salida ns
                    INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
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