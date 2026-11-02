<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class ReporteCajaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

<<<<<<< HEAD
    public function obtenerMovimientosCaja($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $limit = 10, $offset = 0, $id_usuario_filtro = null) {
        $whereEntrada = ["ne.eliminado = 0"];
        $whereSalida = ["ns.eliminado = 0"];
        $params = [];
=======
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
>>>>>>> e156b5461029137184039cef0bc3c69d7089591e

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

<<<<<<< HEAD
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

        // Filtro por usuario si se proporciona
        if (!empty($id_usuario_filtro)) {
            $whereEntrada[] = "ne.id_usuario = :id_usuario_filtro";
            $whereSalida[] = "ns.id_usuario = :id_usuario_filtro";
            $params[':id_usuario_filtro'] = $id_usuario_filtro;
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

=======
>>>>>>> e156b5461029137184039cef0bc3c69d7089591e
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

    // 🔥 LA CLAVE: Si existe cierre, solo trae IDs mayores al guardado en el cierre
    private function aplicarFiltroPostCierre(&$where, &$params, $fecha, $id_usuario = null) {
        if (empty($fecha)) return false;

        $cierre = $this->obtenerUltimoCierreCaja($fecha, $id_usuario);
        if (!$cierre) {
            return false; // Sin cierre, se muestra todo
        }

        if (!isset($cierre['ultimo_id_nota_salida']) || $cierre['ultimo_id_nota_salida'] === null) {
            return false; // Si no hay ID guardado, no filtramos
        }

        // Solo muestra notas con ID estrictamente mayor al del cierre
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

<<<<<<< HEAD
    public function contarMovimientosCaja($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $id_usuario_filtro = null) {
        $movimientos = $this->obtenerMovimientosCaja($fecha, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0, $id_usuario_filtro);
        return count($movimientos);
    }

    public function obtenerResumenCaja($fecha = null, $id_categoria = null, $busqueda = null, $id_usuario_filtro = null) {
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

        // Filtro por usuario si se proporciona
        if (!empty($id_usuario_filtro)) {
            $whereEntradaActiva[] = "ne.id_usuario = :id_usuario_filtro";
            $whereSalidaActiva[] = "ns.id_usuario = :id_usuario_filtro";
            $whereEntradaAnulada[] = "ne.id_usuario = :id_usuario_filtro";
            $whereSalidaAnulada[] = "ns.id_usuario = :id_usuario_filtro";
            $params[':id_usuario_filtro'] = $id_usuario_filtro;
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

    public function obtenerTotalesGlobales($fecha = null, $id_categoria = null, $busqueda = null, $tipo_movimiento = null, $id_usuario_filtro = null) {
=======
    public function obtenerSalidasCajero($id_usuario, $fecha, $busqueda = null, $id_categoria = null, $limit = 10, $offset = 0) {
        $where = ["ns.eliminado = 0"];
>>>>>>> e156b5461029137184039cef0bc3c69d7089591e
        $params = [];

        if (!empty($id_usuario)) {
            $where[] = "ns.id_usuario = :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }
        if (!empty($fecha)) {
            $where[] = "DATE(ns.fecha) = :fecha";
            $params[':fecha'] = $fecha;
        }

        $this->aplicarFiltroPostCierre($where, $params, $fecha, $id_usuario);

        if (!empty($id_categoria) && $id_categoria > 0) {
            $where[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }
        if (!empty($busqueda)) {
            $where[] = "prod.descripcion LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

<<<<<<< HEAD
        // Filtro por usuario si se proporciona
        if (!empty($id_usuario_filtro)) {
            $whereEntrada[] = "ne.id_usuario = :id_usuario_filtro";
            $whereSalida[] = "ns.id_usuario = :id_usuario_filtro";
            $params[':id_usuario_filtro'] = $id_usuario_filtro;
        }

        $whereEntradaSql = !empty($whereEntrada) ? ' WHERE ' . implode(' AND ', $whereEntrada) : '';
        $whereSalidaSql = !empty($whereSalida) ? ' WHERE ' . implode(' AND ', $whereSalida) : '';
=======
        $whereSql = ' WHERE ' . implode(' AND ', $where);
>>>>>>> e156b5461029137184039cef0bc3c69d7089591e

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

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $sql = "SELECT 
                        ns.id_nota_salida AS id_documento, ns.fecha AS fecha_movimiento,
                        prod.descripcion AS nombre_producto, c.nombre_categoria AS categoria,
                        ds.cantidad AS cantidad, 'Sin cliente' AS nombre_cliente,
                        COALESCE(CONCAT(per.nombre, ' ', per.apellido), 'N/A') AS usuario_activo
                    FROM nota_de_salida ns
                    INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                    INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                    LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                    LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                    LEFT JOIN persona per ON u.id_persona = per.id_persona
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

        $this->aplicarFiltroPostCierre($where, $params, $fecha, $id_usuario);

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

        $this->aplicarFiltroPostCierre($where, $params, $fecha, $id_usuario);

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