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
        
        // 🔥 LEER TANTO DE $_GET COMO DE $_POST
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

        // 🔥 AQUÍ ESTABA EL ERROR. AHORA USA dirname(__DIR__) PARA IR A app/view/reportes/
        if ($ajax) {
            ob_start();
            require_once dirname(__DIR__) . '/view/reportes/reportesView.php';
            $html = ob_get_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'html' => $html], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // 🔥 MISMA CORRECCIÓN AQUÍ
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
        $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : (isset($_GET['fecha']) && $_GET['fecha'] !== '' ? trim($_GET['fecha']) : null);
        $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $busqueda = isset($_GET['busqueda']) && $_GET['busqueda'] !== '' ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
        $tipo_movimiento = isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] !== '' ? trim($_GET['tipo_movimiento']) : null;

        $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
        $titulo = 'Reporte de Movimientos';
        $subtitulo = $fecha_inicio ? ($fecha_fin ? 'Rango: ' . $fecha_inicio . ' - ' . $fecha_fin : 'Desde: ' . $fecha_inicio) : 'Historial completo';

        $this->generarPdfFPDF($titulo, $subtitulo, $movimientos);
    }

    private function pdfAjustado($texto) {
        $texto = (string)$texto;
        if ($texto === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $convertido = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $texto);
            if ($convertido !== false) {
                return $convertido;
            }
        }

        return $texto;
    }

    private function generarPdfFPDF($titulo, $subtitulo, $movimientos) {
        $fpdfPath = dirname(__DIR__, 2) . '/fpdf.php';
        if (!file_exists($fpdfPath)) {
            http_response_code(500);
            die('No se encontró la librería FPDF en: ' . htmlspecialchars($fpdfPath));
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        require_once $fpdfPath;

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetFillColor(26, 26, 46);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 10, $this->pdfAjustado($titulo), 0, 1, 'C', true);

        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(26, 26, 46);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 6, $this->pdfAjustado('Sistema Pirotecnia Fénix'), 0, 1, 'L', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 7, $this->pdfAjustado($subtitulo), 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetDrawColor(210, 210, 210);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(26, 26, 46);
        $pdf->SetTextColor(255, 255, 255);

        $w = [20, 52, 30, 18, 28, 42];
        $headers = ['Doc.', 'Producto', 'Tipo', 'Cant.', 'Fecha', 'Responsable'];

        foreach ($headers as $index => $header) {
            $pdf->Cell($w[$index], 7, $this->pdfAjustado($header), 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(0, 0, 0);

        if (empty($movimientos)) {
            $pdf->Cell(0, 12, $this->pdfAjustado('No hay movimientos registrados para este rango.'), 1, 1, 'C', false);
        } else {
            foreach ($movimientos as $m) {
                $producto = trim((string)($m['nombre_producto'] ?? 'N/A'));
                $tipo = trim((string)($m['tipo_movimiento'] ?? 'N/A'));
                $responsable = trim((string)($m['usuario_activo'] ?? 'N/A'));

                if (strlen($producto) > 24) {
                    $producto = substr($producto, 0, 24) . '...';
                }
                if (strlen($tipo) > 12) {
                    $tipo = substr($tipo, 0, 12) . '...';
                }
                if (strlen($responsable) > 18) {
                    $responsable = substr($responsable, 0, 18) . '...';
                }

                $pdf->Cell($w[0], 7, (string)($m['id_documento'] ?? 'N/A'), 1, 0, 'C');
                $pdf->Cell($w[1], 7, $this->pdfAjustado($producto), 1, 0, 'L');
                $pdf->Cell($w[2], 7, $this->pdfAjustado($tipo), 1, 0, 'L');
                $pdf->Cell($w[3], 7, (string)($m['cantidad'] ?? 0), 1, 0, 'C');
                $pdf->Cell($w[4], 7, date('d/m/Y', strtotime($m['fecha_movimiento'] ?? '')), 1, 0, 'C');
                $pdf->Cell($w[5], 7, $this->pdfAjustado($responsable), 1, 1, 'L');
            }
        }

        $pdf->Ln(6);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 6, $this->pdfAjustado('Generado el ' . date('d/m/Y H:i:s')), 0, 1, 'R');

        $pdf->Output('I', 'reporte_movimientos_' . date('Y-m-d') . '.pdf');
        exit();
    }
}