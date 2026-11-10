<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\ReportesModel;
use Exception;
use PDO;

class ReportesController {
    private $db;
    private $modelo;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = $db;
        $this->modelo = new ReportesModel($this->db);
    }

    public function index() {
        $ajax = (!empty($_GET['ajax']) || !empty($_POST['ajax']));
        
        $req = function($key) {
            if (isset($_POST[$key]) && $_POST[$key] !== '') return trim($_POST[$key]);
            if (isset($_GET[$key]) && $_GET[$key] !== '') return trim($_GET[$key]);
            return null;
        };

        $fecha_inicio = $req('fecha_inicio') ?? $req('fecha');
        $fecha_fin = $req('fecha_fin') ?? $req('fecha_hasta');
        $busqueda = $req('busqueda');
        $id_categoria = $req('id_categoria') !== null ? (int)$req('id_categoria') : null;
        $tipo_movimiento = $req('tipo_movimiento');
        
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
        if (isset($_POST['por_pagina'])) $porPagina = (int)$_POST['por_pagina'];

        $offset = ($pagina - 1) * $porPagina;

        $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, $porPagina, $offset);
        $totalRegistros = $this->modelo->contarMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento);
        $totalesGlobales = $this->modelo->obtenerTotalesReporte($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento);

        $totalPaginas = $porPagina > 0 ? (int)ceil($totalRegistros / $porPagina) : 1;
        $paginaActual = $pagina;
        $porPaginaActual = $porPagina;
        
        $totalEntradas = $totalesGlobales['total_entradas'] ?? 0;
        $totalSalidas = $totalesGlobales['total_salidas'] ?? 0;
        $totalAnuladas = $totalesGlobales['total_anuladas'] ?? 0;
        $totalNotas = $totalesGlobales['total_notas'] ?? 0;

        $db = $this->db;

        if ($ajax) {
            ob_start();
            require_once dirname(__DIR__) . '/view/reportes/reportesView.php';
            $html = ob_get_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'html' => $html], JSON_UNESCAPED_UNICODE);
            exit();
        }

        require_once dirname(__DIR__) . '/view/reportes/reportesView.php';
    }

    public function obtenerCierreAjax() {
        header('Content-Type: application/json; charset=utf-8');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID inválido']);
            exit();
        }

        try {
            $sql = "SELECT c.*, CONCAT(p.nombre, ' ', p.apellido) AS nombre_usuario
                    FROM cierre_caja c
                    LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                    LEFT JOIN persona p ON u.id_persona = p.id_persona
                    WHERE c.id_cierre = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $cierre = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cierre) {
                echo json_encode(['success' => false, 'mensaje' => 'Cierre no encontrado']);
                exit();
            }

            $sqlDet = "SELECT ns.id_nota_salida AS id_documento, ns.fecha, 
                              COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido)) AS nombre_cliente,
                              CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
                              GROUP_CONCAT(DISTINCT prod.descripcion SEPARATOR ', ') AS productos,
                              GROUP_CONCAT(DISTINCT cat.nombre_categoria SEPARATOR ', ') AS categorias,
                              SUM(ds.cantidad) AS total_unidades,
                              COALESCE(CONCAT(per_u.nombre, ' ', per_u.apellido), 'N/A') AS usuario_activo
                       FROM nota_de_salida ns
                       INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                       INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                       LEFT JOIN categoria cat ON prod.id_categoria = cat.id_categoria
                       LEFT JOIN persona per_cliente ON ns.id_persona = per_cliente.id_persona
                       LEFT JOIN cliente_juridico cj ON per_cliente.id_persona = cj.id_persona
                       LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                       LEFT JOIN persona per_u ON u.id_persona = per_u.id_persona
                       WHERE ns.id_nota_salida <= :ultimo_id 
                         AND ns.id_usuario = :id_usuario
                         AND DATE(ns.fecha) = :fecha_cierre
                         AND ns.eliminado = 0
                       GROUP BY ns.id_nota_salida, ns.fecha, cj.razon_social, per_cliente.nombre, per_cliente.apellido, cj.id_cliente_juridico, per_u.nombre, per_u.apellido
                       ORDER BY ns.id_nota_salida DESC";

            $stmtDet = $this->db->prepare($sqlDet);
            $stmtDet->execute([
                ':ultimo_id' => $cierre['ultimo_id_nota_salida'],
                ':id_usuario' => $cierre['id_usuario'],
                ':fecha_cierre' => $cierre['fecha_cierre']
            ]);
            $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'cierre' => $cierre,
                'detalles' => $detalles
            ]);
            exit();

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
            exit();
        }
    }

    public function exportarCierrePDF() {
        $id_cierre = isset($_GET['id_cierre']) ? (int)$_GET['id_cierre'] : 0;
        if ($id_cierre <= 0) { die("Error: ID de cierre inválido."); }

        try {
            $sql = "SELECT c.*, CONCAT(p.nombre, ' ', p.apellido) AS nombre_usuario
                    FROM cierre_caja c
                    LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                    LEFT JOIN persona p ON u.id_persona = p.id_persona
                    WHERE c.id_cierre = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id_cierre]);
            $cierre = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cierre) { die("Error: Cierre no encontrado."); }

            $sqlDet = "SELECT ns.id_nota_salida AS id_documento, DATE_FORMAT(ns.fecha, '%d/%m/%Y %h:%i %p') AS fecha_movimiento,
                              GROUP_CONCAT(DISTINCT CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') SEPARATOR ', ') AS nombre_producto,
                              SUM(ds.cantidad) AS cantidad,
                              COUNT(DISTINCT prod.id_producto) AS total_productos,
                              COUNT(DISTINCT c.id_categoria) AS total_categorias,
                              COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
                              CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
                              COALESCE(CONCAT(per.nombre, ' ', per.apellido), 'N/A') AS usuario_activo
                       FROM nota_de_salida ns
                       INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                       INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                       LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                       LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                       LEFT JOIN persona per ON u.id_persona = per.id_persona
                       LEFT JOIN persona per_cliente ON ns.id_persona = per_cliente.id_persona
                       LEFT JOIN cliente_juridico cj ON per_cliente.id_persona = cj.id_persona
                       WHERE ns.id_nota_salida <= :ultimo_id 
                         AND ns.id_usuario = :id_usuario
                         AND DATE(ns.fecha) = :fecha_cierre
                         AND ns.eliminado = 0
                       GROUP BY ns.id_nota_salida, ns.fecha, cj.razon_social, per_cliente.nombre, per_cliente.apellido, cj.id_cliente_juridico, per.nombre, per.apellido
                       ORDER BY ns.fecha DESC";
            
            $stmtDet = $this->db->prepare($sqlDet);
            $stmtDet->execute([
                ':ultimo_id' => $cierre['ultimo_id_nota_salida'],
                ':id_usuario' => $cierre['id_usuario'],
                ':fecha_cierre' => $cierre['fecha_cierre']
            ]);
            $movimientos = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            $datos_pdf = [
                'fecha' => date('d/m/Y', strtotime($cierre['fecha_cierre'])),
                'hora_apertura' => $cierre['hora_apertura'] ? date('h:i A', strtotime($cierre['hora_apertura'])) : '—',
                'total_documentos' => $cierre['total_documentos'],
                'total_unidades' => $cierre['total_unidades'],
                'total_productos' => $cierre['total_productos_distintos'],
                'total_clientes' => $cierre['total_clientes'],
                'movimientos' => $movimientos,
                'encargado' => $cierre['nombre_usuario']
            ];

            $archivoPdf = dirname(__DIR__) . '/view/reporteCaja/generar_pdfCajaView.php';
            if (file_exists($archivoPdf)) {
                require_once $archivoPdf;
                exit();
            } else {
                die("Error: No se encuentra generar_pdfCajaView.php");
            }
        } catch (Exception $e) {
            die("Error al generar el PDF del cierre: " . $e->getMessage());
        }
    }

    // 🔥 EXPORTAR PDF GENERAL DE MOVIMIENTOS (AGRUPANDO CIERRES)
    public function exportarPDF() {
        try {
            $tipo = $_GET['tipo'] ?? 'reporte';

            if ($tipo === 'salida') {
                $archivoPdf = dirname(__DIR__) . '/view/reportes/generar_pdf_salida.php';
                if (file_exists($archivoPdf)) { require_once $archivoPdf; exit(); }
            }
            
            if ($tipo === 'entrada') {
                $archivoPdf = dirname(__DIR__) . '/view/reportes/generar_pdf_entrada.php';
                if (file_exists($archivoPdf)) { require_once $archivoPdf; exit(); }
            }

            $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : (isset($_GET['fecha']) && $_GET['fecha'] !== '' ? trim($_GET['fecha']) : null);
            $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
            $busqueda = isset($_GET['busqueda']) && $_GET['busqueda'] !== '' ? trim($_GET['busqueda']) : null;
            $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
            $tipo_movimiento = isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] !== '' ? trim($_GET['tipo_movimiento']) : null;

            $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
            
            $totalEntradas = 0; $totalSalidas = 0; $totalAnuladas = 0; $totalCierres = 0;
            
            $movimientosPDF = [];
            $cierresVistos = [];

            foreach ($movimientos as $m) {
                $t = $m['tipo_movimiento'] ?? '';
                $esAnulacion = (strpos($t, 'Anulación') === 0 || stripos($t, 'anulad') !== false);
                $esCierre = ($t === 'Cierre de Caja' || ($m['tipo_origen'] ?? '') === 'cierre');

                if ($esCierre) {
                    if (!in_array($m['id_documento'], $cierresVistos)) {
                        $cierresVistos[] = $m['id_documento'];
                        $totalCierres++;
                        
                        $m['nombre_cliente'] = 'Cierre Global de Turno';
                        $m['productos_lista'] = 'Global';
                        $m['total_productos'] = 'Global';
                        $m['total_categorias'] = 'Global';
                        $m['total_unidades'] = (int)($m['total_unidades'] ?? 0); 
                        
                        $movimientosPDF[] = $m;
                    }
                } else {
                    if ($esAnulacion) $totalAnuladas++;
                    elseif (stripos($t, 'Entrada') !== false) $totalEntradas++;
                    elseif (stripos($t, 'Salida') !== false) $totalSalidas++;
                    
                    $movimientosPDF[] = $m;
                }
            }

            $filtrosStr = [];
            if (!empty($fecha_inicio)) $filtrosStr[] = "Desde: " . date('d/m/Y', strtotime($fecha_inicio));
            if (!empty($fecha_fin)) $filtrosStr[] = "Hasta: " . date('d/m/Y', strtotime($fecha_fin));
            
            $datos_pdf = [
                'fecha_reporte' => date('d/m/Y h:i A'),
                'filtros' => empty($filtrosStr) ? 'Todos los movimientos' : implode(' | ', $filtrosStr),
                'movimientos' => $movimientosPDF,
                'totalEntradas' => $totalEntradas,
                'totalSalidas' => $totalSalidas,
                'totalAnuladas' => $totalAnuladas,
                'totalCierres' => $totalCierres
            ];

            $archivoPdf = dirname(__DIR__) . '/view/reportes/generar_pdfReportesView.php';
            if (file_exists($archivoPdf)) {
                require_once $archivoPdf;
                exit();
            } else {
                die("Error: No se encuentra generar_pdfReportesView.php en $archivoPdf");
            }
        } catch (Exception $e) {
            die("Error al generar el PDF: " . $e->getMessage());
        }
    }

    public function exportarCSV() {
        // ... (tu código de exportarCSV se queda igual)
    }
}
?>