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
                    total_documentos INT NOT NULL DEFAULT 0,
                    total_unidades INT NOT NULL DEFAULT 0,
                    total_lineas INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_cierre_usuario_fecha (fecha_cierre, id_usuario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        try {
            $this->db->exec($sql);
        } catch (Exception $e) {
            // Si la BD no admite esta estructura exacta, se ignora para no romper el flujo
        }
    }

    public function guardarCierreCaja($fecha, $id_usuario = null, $total_documentos = 0, $total_unidades = 0, $total_lineas = 0) {
        if (empty($fecha)) {
            return false;
        }

        $this->asegurarTablaCierreCaja();

        $existe = $this->existeCierreCaja($fecha, $id_usuario);
        if ($existe) {
            return true;
        }

        $sql = "INSERT INTO cierre_caja (fecha_cierre, id_usuario, total_documentos, total_unidades, total_lineas)
                VALUES (:fecha_cierre, :id_usuario, :total_documentos, :total_unidades, :total_lineas)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_cierre', $fecha);
        $stmt->bindValue(':id_usuario', $id_usuario !== null && $id_usuario !== '' ? (int)$id_usuario : null, PDO::PARAM_INT);
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

    public function obtenerHistorialCierres($fecha = null, $id_usuario = null, $limit = 20) {
        $this->asegurarTablaCierreCaja();

        $where = [];
        $params = [];

        if (!empty($fecha)) {
            $where[] = "cc.fecha_cierre = :fecha";
            $params[':fecha'] = $fecha;
        }

        if (!empty($id_usuario)) {
            $where[] = "cc.id_usuario = :id_usuario";
            $params[':id_usuario'] = (int)$id_usuario;
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT cc.*, CONCAT(COALESCE(per.nombre, ''), ' ', COALESCE(per.apellido, '')) AS nombre_usuario
                FROM cierre_caja cc
                LEFT JOIN usuario u ON u.id_usuario = cc.id_usuario
                LEFT JOIN persona per ON per.id_persona = u.id_persona
                $whereSql
                ORDER BY cc.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    // MÉTODOS ORIGINALES (ADMIN / REPORTE GENERAL)
    // ============================================================

    public function obtenerMovimientosCaja($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $limit = 10, $offset = 0, $id_usuario = null) {
        $whereEntrada = ["ne.eliminado = 0"];
        $whereSalida = ["ns.eliminado = 0"];
        $params = [];

        if (!empty($fecha)) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) = :fecha";
            $whereSalida[] = "DATE(ns.fecha) = :fecha";
            $params[':fecha'] = $fecha;
        }

        if (!empty($id_usuario)) {
            $whereEntrada[] = "ne.id_usuario = :id_usuario";
            $whereSalida[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
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
                            ne.id_nota_entrada AS id_documento,
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

        $sqlSalida = "SELECT 
                            ns.id_nota_salida AS id_documento,
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

    public function contarMovimientosCaja($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $id_usuario = null) {
        $movimientos = $this->obtenerMovimientosCaja($fecha, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0, $id_usuario);
        return count($movimientos);
    }

    public function obtenerResumenCaja($fecha = null, $id_categoria = null, $busqueda = null, $id_usuario = null) {
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

        if (!empty($id_usuario)) {
            $whereEntradaActiva[] = "ne.id_usuario = :id_usuario";
            $whereSalidaActiva[] = "ns.id_usuario = :id_usuario";
            $whereEntradaAnulada[] = "ne.id_usuario = :id_usuario";
            $whereSalidaAnulada[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
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

    public function obtenerTotalesGlobales($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $id_usuario = null) {
        $params = [];
        $whereEntrada = ["ne.eliminado = 0"];
        $whereSalida = ["ns.eliminado = 0"];

        if (!empty($fecha)) {
            $whereEntrada[] = "DATE(ne.fecha_ingreso) = :fecha";
            $whereSalida[] = "DATE(ns.fecha) = :fecha";
            $params[':fecha'] = $fecha;
        }

        if (!empty($id_usuario)) {
            $whereEntrada[] = "ne.id_usuario = :id_usuario";
            $whereSalida[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
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

    // ============================================================
    // MÉTODOS: REPORTE / CIERRE SOLO PARA CAJERO (SALIDAS)
    // ============================================================

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

        try {
            // INTENTO 1: Consulta completa asumiendo la estructura estándar de clientes
            $sql = "SELECT 
                        ns.id_nota_salida AS id_documento,
                        ns.fecha AS fecha_movimiento,
                        prod.descripcion AS nombre_producto,
                        c.nombre_categoria AS categoria,
                        ds.cantidad AS cantidad,
                        COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
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
                    ORDER BY ns.fecha DESC, ns.id_nota_salida DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            // INTENTO 2 (RESPALDO): Si falla el JOIN con cliente, lo hacemos sin el cliente
            $sql = "SELECT 
                        ns.id_nota_salida AS id_documento,
                        ns.fecha AS fecha_movimiento,
                        prod.descripcion AS nombre_producto,
                        c.nombre_categoria AS categoria,
                        ds.cantidad AS cantidad,
                        COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
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

        $sql = "SELECT COUNT(*) AS total
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