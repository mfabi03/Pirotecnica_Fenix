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
        $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : (isset($_GET['fecha']) && $_GET['fecha'] !== '' ? trim($_GET['fecha']) : null);
        $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $busqueda = isset($_GET['busqueda']) && $_GET['busqueda'] !== '' ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
        $tipo_movimiento = isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] !== '' ? trim($_GET['tipo_movimiento']) : null;
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;

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
            require_once __DIR__ . '/../view/reportes/reportesView.php';
            $html = ob_get_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'html' => $html], JSON_UNESCAPED_UNICODE);
            exit();
        }

        require_once __DIR__ . '/../view/reportes/reportesView.php';
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

        $pdf = $this->generarPdfSimple($titulo, $subtitulo, $movimientos);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="reporte_movimientos_' . date('Y-m-d') . '.pdf"');
        echo $pdf;
        exit();
    }

    private function generarPdfSimple($titulo, $subtitulo, $movimientos) {
        $normalizar = function ($texto) {
            $texto = trim((string)$texto);
            $texto = str_replace(['á', 'à', 'ä', 'â'], 'a', $texto);
            $texto = str_replace(['é', 'è', 'ë', 'ê'], 'e', $texto);
            $texto = str_replace(['í', 'ì', 'ï', 'î'], 'i', $texto);
            $texto = str_replace(['ó', 'ò', 'ö', 'ô'], 'o', $texto);
            $texto = str_replace(['ú', 'ù', 'ü', 'û'], 'u', $texto);
            $texto = str_replace(['ñ'], 'n', $texto);
            $texto = str_replace(['Á', 'À', 'Ä', 'Â'], 'A', $texto);
            $texto = str_replace(['É', 'È', 'Ë', 'Ê'], 'E', $texto);
            $texto = str_replace(['Í', 'Ì', 'Ï', 'Î'], 'I', $texto);
            $texto = str_replace(['Ó', 'Ò', 'Ö', 'Ô'], 'O', $texto);
            $texto = str_replace(['Ú', 'Ù', 'Ü', 'Û'], 'U', $texto);
            $texto = str_replace(['Ñ'], 'N', $texto);
            $texto = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
            return preg_replace('/[^A-Za-z0-9 .,:_\-\/]+/', '', $texto);
        };

        $stream = "BT\n/F1 18 Tf\n50 780 Td\n(" . $normalizar($titulo) . ") Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n50 760 Td\n(" . $normalizar($subtitulo) . ") Tj\nET\n";

        $lineY = 730;
        $stream .= "BT\n/F1 8 Tf\n50 " . $lineY . " Td\n(Documento)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n120 " . $lineY . " Td\n(Producto)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n240 " . $lineY . " Td\n(Tipo)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n310 " . $lineY . " Td\n(Cantidad)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n380 " . $lineY . " Td\n(Fecha)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n480 " . $lineY . " Td\n(Responsable)Tj\nET\n";

        $lineY -= 18;
        foreach ($movimientos as $i => $m) {
            if ($lineY < 80) break; 
            
            $doc = $normalizar($m['id_documento'] ?? 'N/A');
            $nombre = $normalizar($m['nombre_producto'] ?? 'N/A');
            $tipo = $normalizar($m['tipo_movimiento'] ?? 'N/A');
            $cantidad = $normalizar((string)($m['cantidad'] ?? 0));
            $fecha = $normalizar($m['fecha_movimiento'] ?? '');
            $usuario = $normalizar($m['usuario_activo'] ?? 'N/A');

            $stream .= "BT\n/F1 8 Tf\n50 " . $lineY . " Td\n(" . $doc . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n120 " . $lineY . " Td\n(" . $nombre . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n240 " . $lineY . " Td\n(" . $tipo . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n310 " . $lineY . " Td\n(" . $cantidad . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n380 " . $lineY . " Td\n(" . $fecha . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n480 " . $lineY . " Td\n(" . $usuario . ") Tj\nET\n";
            $lineY -= 16;
        }

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj;
        }
        $xrefStart = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string)$offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . $xrefStart . "\n%%EOF\n";

        return $pdf;
    }
}