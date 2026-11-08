<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\ReportesModel;
use Exception;

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
        $totalUnidades = 0;
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

    public function exportarCSV() {
        $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : (isset($_GET['fecha']) && $_GET['fecha'] !== '' ? trim($_GET['fecha']) : null);
        $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $busqueda = isset($_GET['busqueda']) && $_GET['busqueda'] !== '' ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
        $tipo_movimiento = isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] !== '' ? trim($_GET['tipo_movimiento']) : null;

        $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_movimientos_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Documento', 'Producto', 'Tipo de Movimiento', 'Cantidad', 'Costo Unitario', 'Fecha', 'Responsable']);

        foreach ($movimientos as $m) {
            $cantidad = (float)($m['cantidad'] ?? 0);
            if (($m['tipo_movimiento'] ?? '') === 'Salida') {
                $cantidad = -abs($cantidad);
            }

            fputcsv($output, [
                $m['id_documento'] ?? 'N/A',
                $m['nombre_producto'] ?? 'N/A',
                $m['tipo_movimiento'] ?? 'N/A',
                $cantidad,
                $m['costo_proveedor'] ?? 0,
                $m['fecha_movimiento'] ?? '',
                $m['usuario_activo'] ?? 'N/A'
            ]);
        }

        fclose($output);
        exit();
    }

    public function exportarPDF() {
        try {
            $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : (isset($_GET['fecha']) && $_GET['fecha'] !== '' ? trim($_GET['fecha']) : null);
            $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
            $busqueda = isset($_GET['busqueda']) && $_GET['busqueda'] !== '' ? trim($_GET['busqueda']) : null;
            $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
            $tipo_movimiento = isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] !== '' ? trim($_GET['tipo_movimiento']) : null;

            $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
            
            $totalEntradas = 0;
            $totalSalidas = 0;
            $totalAnuladas = 0;

            foreach ($movimientos as $m) {
                $tipo = $m['tipo_movimiento'] ?? '';
                $esAnulacion = (strpos($tipo, 'Anulación') === 0 || stripos($tipo, 'anulad') !== false);
                
                if ($esAnulacion) {
                    $totalAnuladas++;
                } elseif ($tipo === 'Entrada') {
                    $totalEntradas++;
                } elseif ($tipo === 'Salida') {
                    $totalSalidas++;
                }
            }

            $filtrosStr = [];
            if (!empty($fecha_inicio)) $filtrosStr[] = "Desde: " . date('d/m/Y', strtotime($fecha_inicio));
            if (!empty($fecha_fin)) $filtrosStr[] = "Hasta: " . date('d/m/Y', strtotime($fecha_fin));
            if (!empty($tipo_movimiento)) $filtrosStr[] = "Tipo: " . $tipo_movimiento;
            
            $datos_pdf = [
                'fecha_reporte' => date('d/m/Y h:i A'),
                'filtros' => empty($filtrosStr) ? 'Todos los movimientos' : implode(' | ', $filtrosStr),
                'movimientos' => $movimientos,
                'totalEntradas' => $totalEntradas,
                'totalSalidas' => $totalSalidas,
                'totalAnuladas' => $totalAnuladas
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

    public function obtenerMotivoAjax() {
        // Lógica de motivo
    }
}