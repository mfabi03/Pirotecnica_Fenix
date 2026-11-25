<?php
// app/Model/ReporteCajaModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

class ReporteCajaModel extends ConnectDB
{
    private $conex;

    public function __construct()
    {
        parent::__construct();
        $this->conex = $this->getConnection();
        try {
            $this->conex->exec("SET SESSION group_concat_max_len = 1000000");
        } catch (\Throwable $e) {
            error_log('No se pudo ajustar group_concat_max_len en ReporteCajaModel: ' . $e->getMessage());
        }
        $this->asegurarTablaCierreCaja();
    }

    private function normalizarFechaSql($fecha)
    {
        if (empty($fecha)) {
            return null;
        }
        $fecha = trim((string)$fecha);
        if ($fecha === '') {
            return null;
        }
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

    private function asegurarTablaCierreCaja()
    {
        $sql = "CREATE TABLE IF NOT EXISTS cierre_caja (
                    id_cierre INT AUTO_INCREMENT PRIMARY KEY,
                    fecha_cierre DATE NOT NULL,
                    hora_apertura TIME NULL,
                    id_usuario INT NULL,
                    ultimo_id_nota_salida INT NULL DEFAULT NULL,
                    total_documentos INT NOT NULL DEFAULT 0,
                    total_productos_distintos INT NOT NULL DEFAULT 0,
                    total_categorias INT NOT NULL DEFAULT 0,
                    total_clientes INT NOT NULL DEFAULT 0,
                    total_unidades INT NOT NULL DEFAULT 0,
                    total_lineas INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_cierre_usuario_fecha (fecha_cierre, id_usuario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        try {
            $this->conex->exec($sql);
        } catch (Exception $e) {
            error_log("Error creando tabla cierre_caja: " . $e->getMessage());
        }

        $columnas = [
            'hora_apertura'             => "TIME NULL AFTER fecha_cierre",
            'ultimo_id_nota_salida'     => "INT NULL DEFAULT NULL AFTER id_usuario",
            'total_productos_distintos' => "INT NOT NULL DEFAULT 0 AFTER total_documentos",
            'total_categorias'          => "INT NOT NULL DEFAULT 0 AFTER total_productos_distintos",
            'total_clientes'            => "INT NOT NULL DEFAULT 0 AFTER total_categorias",
            'total_lineas'              => "INT NOT NULL DEFAULT 0 AFTER total_unidades"
        ];

        foreach ($columnas as $col => $definicion) {
            try {
                $stmt = $this->conex->query("SHOW COLUMNS FROM cierre_caja LIKE " . $this->conex->quote($col));
                if ($stmt && $stmt->rowCount() === 0) {
                    $this->conex->exec("ALTER TABLE cierre_caja ADD COLUMN $col $definicion");
                }
            } catch (Exception $e) {
                error_log("Error verificando columna $col en cierre_caja: " . $e->getMessage());
            }
        }
    }

    public function obtenerUltimoCierreUsuario($id_usuario)
    {
        if ($id_usuario === null || $id_usuario === '' || (int)$id_usuario <= 0) {
            return null;
        }

        try {
            $sql = "SELECT *
                    FROM cierre_caja
                    WHERE id_usuario = :id_usuario
                    ORDER BY id_cierre DESC
                    LIMIT 1";
            $stmt = $this->conex->prepare($sql);
            $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error en obtenerUltimoCierreUsuario: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerUltimoIdNotaSalidaUsuario($id_usuario)
    {
        if ($id_usuario === null || $id_usuario === '' || (int)$id_usuario <= 0) {
            return null;
        }

        try {
            $sql = "SELECT MAX(ns.id_nota_salida) AS ultimo_id
                    FROM nota_de_salida ns
                    WHERE ns.id_usuario = :id_usuario
                      AND (ns.eliminado = 0 OR ns.eliminado IS NULL)";
            $stmt = $this->conex->prepare($sql);
            $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (isset($row['ultimo_id']) && $row['ultimo_id'] !== null) ? (int)$row['ultimo_id'] : null;
        } catch (Exception $e) {
            error_log("Error en obtenerUltimoIdNotaSalidaUsuario: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerUltimoIdNotaSalidaPorUsuarioFecha($fecha, $id_usuario = null)
    {
        return $this->obtenerUltimoIdNotaSalidaUsuario($id_usuario);
    }

    public function obtenerUltimoCierreCaja($fecha, $id_usuario = null)
    {
        if ($id_usuario !== null && $id_usuario !== '' && (int)$id_usuario > 0) {
            return $this->obtenerUltimoCierreUsuario((int)$id_usuario);
        }

        $fechaSql = $this->normalizarFechaSql($fecha);
        if (empty($fechaSql)) {
            return null;
        }

        try {
            $sql = "SELECT * FROM cierre_caja WHERE fecha_cierre = :fecha_cierre AND id_usuario IS NULL ORDER BY id_cierre DESC LIMIT 1";
            $stmt = $this->conex->prepare($sql);
            $stmt->bindValue(':fecha_cierre', $fechaSql, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error en obtenerUltimoCierreCaja: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerCierreAnterior($fecha, $idCierreActual, $id_usuario = null)
    {
        $sql = "SELECT ultimo_id_nota_salida
                FROM cierre_caja
                WHERE id_cierre < :id_actual";
        $params = [
            ':id_actual' => (int)$idCierreActual
        ];

        if ($id_usuario !== null && $id_usuario !== '' && (int)$id_usuario > 0) {
            $sql .= " AND id_usuario = :id_usuario";
            $params[':id_usuario'] = (int)$id_usuario;
        }

        $sql .= " ORDER BY id_cierre DESC LIMIT 1";

        try {
            $stmt = $this->conex->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $val = $stmt->fetchColumn();
            return ($val !== false && $val !== null) ? (int)$val : 0;
        } catch (Exception $e) {
            error_log("Error en obtenerCierreAnterior: " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerCierrePorFechaYUsuario($fecha, $id_usuario = null)
    {
        return $this->obtenerUltimoCierreCaja($fecha, $id_usuario);
    }

    public function actualizarUltimoIdCierreCaja($fecha, $id_usuario = null,$ultimo_id_nota_salida = null)
    {
        $fechaSql = $this->normalizarFechaSql($fecha);
        if (empty($fechaSql)) {
            return false;
        }

        $sql = "UPDATE cierre_caja
                SET ultimo_id_nota_salida = :ultimo_id_nota_salida
                WHERE fecha_cierre = :fecha_cierre
                  AND id_usuario <=> :id_usuario";

        try {
            $stmt = $this->conex->prepare($sql);
            $stmt->bindValue(':fecha_cierre',$fechaSql, PDO::PARAM_STR);
            if ($id_usuario !== null && $id_usuario !== '' && (int)$id_usuario > 0) {
                $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':id_usuario', null, PDO::PARAM_NULL);
            }

            if ($ultimo_id_nota_salida !== null &&$ultimo_id_nota_salida !== '') {
                $stmt->bindValue(':ultimo_id_nota_salida', (int)$ultimo_id_nota_salida, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':ultimo_id_nota_salida', null, PDO::PARAM_NULL);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en actualizarUltimoIdCierreCaja: " . $e->getMessage());
            return false;
        }
    }

    public function guardarCierreCaja(
        $fecha,$id_usuario = null,
        $total_documentos = 0,$total_unidades = 0,
        $total_categorias = 0,$hora_apertura = null,
        $total_productos = 0,$total_clientes = 0
    ) {
        if ($id_usuario === null || $id_usuario === '' || (int)$id_usuario <= 0) {
            return false;
        }

        $idUsuarioInt = (int)$id_usuario;
        $fechaSql = $this->normalizarFechaSql($fecha) ?? date('Y-m-d');
        $ultimoIdNota = $this->obtenerUltimoIdNotaSalidaUsuario($idUsuarioInt);

        if ($this->existeCierreCaja($fechaSql,$idUsuarioInt)) {
            return $this->actualizarUltimoIdCierreCaja($fechaSql, $idUsuarioInt,$ultimoIdNota);
        }

        try {
            $sql = "INSERT INTO cierre_caja (
                        fecha_cierre,
                        hora_apertura,
                        id_usuario,
                        ultimo_id_nota_salida,
                        total_documentos,
                        total_productos_distintos,
                        total_categorias,
                        total_clientes,
                        total_unidades,
                        total_lineas
                    ) VALUES (
                        :fecha_cierre,
                        :hora_apertura,
                        :id_usuario,
                        :ultimo_id_nota_salida,
                        :total_documentos,
                        :total_productos,
                        :total_categorias,
                        :total_clientes,
                        :total_unidades,
                        :total_lineas
                    )";

            $stmt = $this->conex->prepare($sql);
            $stmt->bindValue(':fecha_cierre',$fechaSql, PDO::PARAM_STR);
            if (!empty($hora_apertura)) {
                $stmt->bindValue(':hora_apertura', (string)$hora_apertura, PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':hora_apertura', null, PDO::PARAM_NULL);
            }

            $stmt->bindValue(':id_usuario',$idUsuarioInt, PDO::PARAM_INT);

            if ($ultimoIdNota !== null) {
                $stmt->bindValue(':ultimo_id_nota_salida', (int)$ultimoIdNota, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':ultimo_id_nota_salida', null, PDO::PARAM_NULL);
            }

            $stmt->bindValue(':total_documentos', (int)$total_documentos, PDO::PARAM_INT);$stmt->bindValue(':total_productos', (int)$total_productos, PDO::PARAM_INT);$stmt->bindValue(':total_categorias', (int)$total_categorias, PDO::PARAM_INT);$stmt->bindValue(':total_clientes', (int)$total_clientes, PDO::PARAM_INT);$stmt->bindValue(':total_unidades', (int)$total_unidades, PDO::PARAM_INT);$stmt->bindValue(':total_lineas', 0, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en guardarCierreCaja: " . $e->getMessage());
            return false;
        }
    }

    public function existeCierreCaja($fecha,$id_usuario = null)
    {
        if ($id_usuario === null || $id_usuario === '' || (int)$id_usuario <= 0) {
            return false;
        }

        $fechaSql = $this->normalizarFechaSql($fecha);
        if (empty($fechaSql)) {
            return false;
        }

        try {
            $sql = "SELECT 1 FROM cierre_caja WHERE fecha_cierre = :fecha_cierre AND id_usuario = :id_usuario LIMIT 1";
            $stmt =$this->conex->prepare($sql);$stmt->bindValue(':fecha_cierre', $fechaSql, PDO::PARAM_STR);$stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);$stmt->execute();
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error en existeCierreCaja: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerSalidasCajero($id_usuario,$fecha = null, $busqueda = null,$id_categoria = null, $limit = 100,$offset = 0)
    {
        if ($id_usuario === null || $id_usuario === '' || (int)$id_usuario <= 0) {
            return [];
        }

        $idUsuarioInt = (int)$id_usuario;
        $where = [
            "ns.id_usuario = :id_usuario",
            "(ns.eliminado = 0 OR ns.eliminado IS NULL)"
        ];
        $params = [
            ':id_usuario' => $idUsuarioInt
        ];

        $ultimoCierre = $this->obtenerUltimoCierreUsuario($idUsuarioInt);
        if ($ultimoCierre && isset($ultimoCierre['ultimo_id_nota_salida']) && $ultimoCierre['ultimo_id_nota_salida'] !== null) {$where[] = "ns.id_nota_salida > :ultimo_id_cerrado";
            $params[':ultimo_id_cerrado'] = (int)$ultimoCierre['ultimo_id_nota_salida'];
        }

        if (!empty($id_categoria) && (int)$id_categoria > 0) {$where[] = "prod.id_categoria = :id_categoria";
            $params[':id_categoria'] = (int)$id_categoria;
        }

        if (!empty($busqueda)) {$where[] = "(prod.descripcion LIKE :busqueda OR c.nombre_categoria LIKE :busqueda OR cj.razon_social LIKE :busqueda OR p_cliente.nombre LIKE :busqueda OR p_cliente.apellido LIKE :busqueda OR CAST(ns.id_nota_salida AS CHAR) LIKE :busqueda)";
            $params[':busqueda'] = '%' . trim((string)$busqueda) . '%';
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $sql = "SELECT
                    ns.id_nota_salida AS id_documento,
                    DATE_FORMAT(COALESCE(ns.fecha, NOW()), '%d/%m/%Y %h:%i %p') AS fecha_movimiento,
                    COALESCE(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(prod.descripcion, 'Sin producto'), ' (x', COALESCE(ds.cantidad, 0), ')') ORDER BY prod.descripcion SEPARATOR ', '), 'Sin productos') AS nombre_producto,
                    COALESCE(GROUP_CONCAT(DISTINCT COALESCE(c.nombre_categoria, 'Sin categoría') ORDER BY c.nombre_categoria SEPARATOR ', '), 'Sin categoría') AS categoria,
                    COALESCE(SUM(COALESCE(ds.cantidad, 0)), 0) AS cantidad,
                    COUNT(ds.id_detalle_salida) AS total_productos,
                    COUNT(DISTINCT COALESCE(prod.id_categoria, 0)) AS total_categorias,
                    COALESCE(NULLIF(CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN cj.razon_social ELSE CONCAT_WS(' ', p_cliente.nombre, p_cliente.apellido) END, ''), 'Sin cliente') AS nombre_cliente,
                    CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
                    COALESCE(NULLIF(CONCAT_WS(' ', per_u.nombre, per_u.apellido), ''), 'N/A') AS usuario_activo
                FROM nota_de_salida ns
                LEFT JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                LEFT JOIN producto prod ON ds.id_producto = prod.id_producto
                LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                LEFT JOIN persona p_cliente ON ns.id_persona = p_cliente.id_persona
                LEFT JOIN cliente_juridico cj ON p_cliente.id_persona = cj.id_persona
                LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                $whereSql
                GROUP BY ns.id_nota_salida, ns.fecha, cj.id_cliente_juridico, cj.razon_social, p_cliente.nombre, p_cliente.apellido, per_u.nombre, per_u.apellido
                ORDER BY
                    CASE
                        WHEN ns.fecha IS NULL OR ns.fecha = '0000-00-00' OR ns.fecha = '0000-00-00 00:00:00'
                            THEN '9999-12-31 23:59:59'
                        ELSE ns.fecha
                    END DESC,
                    ns.id_nota_salida DESC
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->conex->prepare($sql);
            foreach ($params as $k =>$v) {
                $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', max(1, (int)$limit), PDO::PARAM_INT);$stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);$stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerSalidasCajero: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerSalidasParaCierre($id_usuario,$fecha)
    {
        return $this->obtenerSalidasCajero($id_usuario,$fecha, null, null, 9999, 0);
    }

    public function obtenerHistorialCierres()
    {
        try {
            $sql = "SELECT c.*,
                           COALESCE(NULLIF(CONCAT_WS(' ', p.nombre, p.apellido), ''), 'Usuario') AS nombre_usuario,
                           COALESCE(p.cedula, 'N/A') AS cedula_usuario
                    FROM cierre_caja c
                    LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                    LEFT JOIN persona p ON u.id_persona = p.id_persona
                    ORDER BY c.fecha_cierre DESC, c.id_cierre DESC";
            $stmt =$this->conex->prepare($sql);$stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerHistorialCierres: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerCierrePorId($idCierre)
    {
        try {
            $sql = "SELECT c.*,
                           COALESCE(NULLIF(CONCAT_WS(' ', p.nombre, p.apellido), ''), 'Usuario') AS nombre_usuario
                    FROM cierre_caja c
                    LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                    LEFT JOIN persona p ON u.id_persona = p.id_persona
                    WHERE c.id_cierre = :id LIMIT 1";
            $stmt =$this->conex->prepare($sql);$stmt->bindValue(':id', (int)$idCierre, PDO::PARAM_INT);$stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error en obtenerCierrePorId: " . $e->getMessage());
            return null;
        }
    }

    private function consultarDetalleCierre($idCierre,$incluirCategoria = false)
    {
        try {
            $cierre = $this->obtenerCierrePorId($idCierre);
            if (!$cierre) {
                return [];
            }

            $idUsuarioCierre =$cierre['id_usuario'] ?? null;
            $ultimoIdAnterior =$this->obtenerCierreAnterior($cierre['fecha_cierre'],$idCierre, $idUsuarioCierre);$ultimoIdActual = isset($cierre['ultimo_id_nota_salida']) &&$cierre['ultimo_id_nota_salida'] !== null
                ? (int)$cierre['ultimo_id_nota_salida']
                : PHP_INT_MAX;

            $categoriaSelect =$incluirCategoria
                ? "COALESCE(GROUP_CONCAT(DISTINCT COALESCE(c.nombre_categoria, 'Sin Cat') ORDER BY c.nombre_categoria SEPARATOR ', '), 'Sin Cat') AS categoria,"
                : "COUNT(DISTINCT COALESCE(prod.id_categoria, 0)) AS total_categorias,";

            $productosSelect =$incluirCategoria
                ? ""
                : "COUNT(ds.id_detalle_salida) AS total_productos,";

            $sql = "SELECT ns.id_nota_salida AS id_documento,
                           DATE_FORMAT(COALESCE(ns.fecha, NOW()), '%d/%m/%Y %h:%i %p') AS fecha_movimiento,
                           COALESCE(GROUP_CONCAT(DISTINCT CONCAT(COALESCE(prod.descripcion, 'Prod'), ' (x', COALESCE(ds.cantidad, 0), ')') ORDER BY prod.descripcion SEPARATOR ', '), 'Sin productos') AS nombre_producto,
                           COALESCE(SUM(COALESCE(ds.cantidad, 0)), 0) AS cantidad,
                           $productosSelect$categoriaSelect
                           COALESCE(NULLIF(CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN cj.razon_social ELSE CONCAT_WS(' ', p_cliente.nombre, p_cliente.apellido) END, ''), 'Sin cliente') AS nombre_cliente,
                           CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
                           COALESCE(NULLIF(CONCAT_WS(' ', per_u.nombre, per_u.apellido), ''), 'N/A') AS usuario_activo
                    FROM nota_de_salida ns
                    LEFT JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                    LEFT JOIN producto prod ON ds.id_producto = prod.id_producto
                    LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                    LEFT JOIN persona p_cliente ON ns.id_persona = p_cliente.id_persona
                    LEFT JOIN cliente_juridico cj ON p_cliente.id_persona = cj.id_persona
                    LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                    LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                    WHERE ns.id_nota_salida > :ultimo_id_anterior
                      AND ns.id_nota_salida <= :ultimo_id_actual
                      AND (ns.eliminado = 0 OR ns.eliminado IS NULL)";

            $params = [
                ':ultimo_id_anterior' => (int)$ultimoIdAnterior,
                ':ultimo_id_actual'   => (int)$ultimoIdActual
            ];

            if ($idUsuarioCierre !== null &&$idUsuarioCierre !== '' && (int)$idUsuarioCierre > 0) {$sql .= " AND ns.id_usuario = :id_usuario";
                $params[':id_usuario'] = (int)$idUsuarioCierre;
            }

            $sql .= " GROUP BY ns.id_nota_salida, ns.fecha, cj.id_cliente_juridico, cj.razon_social, p_cliente.nombre, p_cliente.apellido, per_u.nombre, per_u.apellido
                      ORDER BY ns.fecha DESC, ns.id_nota_salida DESC";

            $stmt = $this->conex->prepare($sql);
            foreach ($params as$k => $v) {$stmt->bindValue($k,$v, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en consultarDetalleCierre: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetalleCierrePorId($idCierre)
    {
        return $this->consultarDetalleCierre($idCierre, false);
    }

    public function obtenerDetalleCierreCSVPorId($idCierre)
    {
        return $this->consultarDetalleCierre($idCierre, true);
    }

    public function obtenerNombreUsuario($idUsuario)
    {
        if (empty($idUsuario)) {
            return 'Usuario';
        }
        try {
            $stmt =$this->conex->prepare("SELECT p.nombre, p.apellido FROM usuario u INNER JOIN persona p ON u.id_persona = p.id_persona WHERE u.id_usuario = :id");
            $stmt->bindValue(':id', (int)$idUsuario, PDO::PARAM_INT);$stmt->execute();
            $datos =$stmt->fetch(PDO::FETCH_ASSOC);
            if ($datos) {$nombreCompleto = trim(implode(' ', array_filter([$datos['nombre'] ?? '',$datos['apellido'] ?? ''])));
                if ($nombreCompleto !== '') {
                    return $nombreCompleto;
                }
            }
        } catch (Exception $e) {
            error_log("Error en obtenerNombreUsuario: " . $e->getMessage());
        }
        return 'Usuario';
    }
}