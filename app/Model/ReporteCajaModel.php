<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class ReporteCajaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerMovimientosCaja($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $limit = 10, $offset = 0) {
        $whereEntrada = ["ne.eliminado = 0"];
        $whereSalida = ["ns.eliminado = 0"];
        $params = [];

        if (!empty($fecha)) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) = :fecha";
            $whereSalida[] = "DATE(ns.fecha) = :fecha";
            $params[':fecha'] = $fecha;
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

        // ENTRADAS
        $sqlEntrada = "SELECT 
                            prod.descripcion AS nombre_producto,
                            c.nombre_categoria AS categoria,
                            'Entrada' AS tipo_movimiento,
                            de.cantidad AS cantidad,
                            de.costo_unitario AS costo_proveedor,
                            ne.fecha_ingreso AS fecha_movimiento,
                            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo
                        FROM nota_de_entrada ne
                        INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                        INNER JOIN producto prod ON de.id_producto = prod.id_producto
                        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                        LEFT JOIN usuario u ON ne.id_usuario = u.id_usuario
                        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                        $whereClauseEntrada";

        // SALIDAS
        $sqlSalida = "SELECT 
                            prod.descripcion AS nombre_producto,
                            c.nombre_categoria AS categoria,
                            'Salida' AS tipo_movimiento,
                            ds.cantidad AS cantidad,
                            prod.costo_unitario AS costo_proveedor,
                            ns.fecha AS fecha_movimiento,
                            COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo
                        FROM nota_de_salida ns
                        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                        INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                        LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                        LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                        $whereClauseSalida";

        if ($tipo_movimiento === 'Entrada') {
            $sql = $sqlEntrada;
        } elseif ($tipo_movimiento === 'Salida') {
            $sql = $sqlSalida;
        } else {
            $sql = "($sqlEntrada) UNION ALL ($sqlSalida)";
        }

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

    public function contarMovimientosCaja($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null) {
        $movimientos = $this->obtenerMovimientosCaja($fecha, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
        return count($movimientos);
    }

    public function obtenerResumenCaja($fecha = null, $id_categoria = null, $busqueda = null) {
        $params = [':estado_activo' => 0, ':estado_anulado' => 1];
        $whereEntradaActiva = ["ne.eliminado = :estado_activo"];
        $whereSalidaActiva = ["ns.eliminado = :estado_activo"];
        $whereEntradaAnulada = ["ne.eliminado = :estado_anulado"];
        $whereSalidaAnulada = ["ns.eliminado = :estado_anulado"];

        if (!empty($fecha)) {
            $whereEntradaActiva[] = "DATE(ne.fecha_ingreso) = :fecha";
            $whereSalidaActiva[] = "DATE(ns.fecha) = :fecha";
            $whereEntradaAnulada[] = "DATE(ne.fecha_ingreso) = :fecha";
            $whereSalidaAnulada[] = "DATE(ns.fecha) = :fecha";
            $params[':fecha'] = $fecha;
        }

        if (!empty($id_categoria) && $id_categoria > 0) {
            $whereEntradaActiva[] = "prod.id_categoria = :id_categoria";
            $whereSalidaActiva[] = "prod.id_categoria = :id_categoria";
            $whereEntradaAnulada[] = "prod.id_categoria = :id_categoria";
            $whereSalidaAnulada[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }

        if (!empty($busqueda)) {
            $whereEntradaActiva[] = "prod.descripcion LIKE :busqueda";
            $whereSalidaActiva[] = "prod.descripcion LIKE :busqueda";
            $whereEntradaAnulada[] = "prod.descripcion LIKE :busqueda";
            $whereSalidaAnulada[] = "prod.descripcion LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql = "SELECT (
                    (SELECT COUNT(DISTINCT ne.id_nota_entrada)
                     FROM nota_de_entrada ne
                     INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                     INNER JOIN producto prod ON de.id_producto = prod.id_producto
                     WHERE " . implode(' AND ', $whereEntradaActiva) . ")
                    +
                    (SELECT COUNT(DISTINCT ns.id_nota_salida)
                     FROM nota_de_salida ns
                     INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                     INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                     WHERE " . implode(' AND ', $whereSalidaActiva) . ")
                ) AS total_notas,
                (
                    (SELECT COUNT(DISTINCT ne.id_nota_entrada)
                     FROM nota_de_entrada ne
                     INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                     INNER JOIN producto prod ON de.id_producto = prod.id_producto
                     WHERE " . implode(' AND ', $whereEntradaAnulada) . ")
                    +
                    (SELECT COUNT(DISTINCT ns.id_nota_salida)
                     FROM nota_de_salida ns
                     INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                     INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                     WHERE " . implode(' AND ', $whereSalidaAnulada) . ")
                ) AS total_anuladas";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_notas' => (int)($row['total_notas'] ?? 0),
            'total_anuladas' => (int)($row['total_anuladas'] ?? 0)
        ];
    }

    public function obtenerTotalesGlobales($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null) {
        $params = [];
        $whereEntrada = ["ne.eliminado = 0"];
        $whereSalida = ["ns.eliminado = 0"];

        if (!empty($fecha)) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) = :fecha";
            $whereSalida[] = "DATE(ns.fecha) = :fecha";
            $params[':fecha'] = $fecha;
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

        $whereEntradaSql = !empty($whereEntrada) ? ' WHERE ' . implode(' AND ', $whereEntrada) : '';
        $whereSalidaSql = !empty($whereSalida) ? ' WHERE ' . implode(' AND ', $whereSalida) : '';

        if ($tipo_movimiento === 'Entrada') {
            $sql = "SELECT 
                        COALESCE(SUM(de.cantidad), 0) AS total_entradas,
                        0 AS total_salidas
                    FROM nota_de_entrada ne
                    INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                    INNER JOIN producto prod ON de.id_producto = prod.id_producto
                    $whereEntradaSql";
        } elseif ($tipo_movimiento === 'Salida') {
            $sql = "SELECT 
                        0 AS total_entradas,
                        COALESCE(SUM(ds.cantidad), 0) AS total_salidas
                    FROM nota_de_salida ns
                    INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                    INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                    $whereSalidaSql";
        } else {
            $sql = "SELECT 
                        COALESCE(SUM(total_entradas), 0) AS total_entradas,
                        COALESCE(SUM(total_salidas), 0) AS total_salidas
                    FROM (
                        SELECT de.cantidad AS total_entradas, 0 AS total_salidas
                        FROM nota_de_entrada ne
                        INNER JOIN detalle_entrada de ON ne.id_nota_entrada = de.id_nota_entrada
                        INNER JOIN producto prod ON de.id_producto = prod.id_producto
                        $whereEntradaSql
                        UNION ALL
                        SELECT 0 AS total_entradas, ds.cantidad AS total_salidas
                        FROM nota_de_salida ns
                        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                        INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                        $whereSalidaSql
                    ) t";
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_entradas' => (int)($row['total_entradas'] ?? 0),
            'total_salidas' => (int)($row['total_salidas'] ?? 0)
        ];
    }
}