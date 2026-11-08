<?php
// app/view/reportes/generar_pdfReportesView.php

error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level() > 0) {
    ob_end_clean();
}

require_once dirname(__DIR__, 3) . '/fpdf.php';

function pdf_txt($texto) {
    $texto = (string) $texto;
    if ($texto === '') return '';
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);
        if ($conv !== false) return $conv;
    }
    return $texto;
}

$datos_pdf = $datos_pdf ?? [];
$fecha_reporte = $datos_pdf['fecha_reporte'] ?? date('d/m/Y h:i A');
$filtros = $datos_pdf['filtros'] ?? 'Todos los movimientos';
$movimientos = $datos_pdf['movimientos'] ?? [];
$totalEntradas = $datos_pdf['totalEntradas'] ?? 0;
$totalSalidas = $datos_pdf['totalSalidas'] ?? 0;
$totalAnuladas = $datos_pdf['totalAnuladas'] ?? 0;
$usuario_activo = $_SESSION['usuario_nombre'] ?? 'Administrador';

$logoPath = dirname(__DIR__, 3) . '/assets/imagenes/logo.png';
$logoDisponible = file_exists($logoPath);

class PDF extends FPDF
{
    public $logoPath = '';
    public $tieneLogo = false;

    function Header()
    {
        if ($this->tieneLogo) {
            $this->Image($this->logoPath, 10, 8, 20, 20);
        }

        $this->SetXY(35, 9);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(26, 26, 46);
        $this->Cell(150, 7, pdf_txt('PIROTECNICA FENIX C.A.'), 0, 0, 'L');

        $this->SetFillColor(243, 156, 18);
        $this->SetTextColor(26, 26, 46);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(100, 7, pdf_txt('REPORTE DE MOVIMIENTOS'), 0, 1, 'C', true);

        $this->SetXY(35, 17);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(150, 6, pdf_txt('Sistema de gestion - Historial de Notas'), 0, 1, 'L');

        $this->SetDrawColor(243, 156, 18);
        $this->SetLineWidth(0.8);
        $this->Line(10, 32, 287, 32);
        $this->SetLineWidth(0.2);

        $this->SetY(36);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, pdf_txt('Pagina ' . $this->PageNo() . ' - Generado el ' . date('d/m/Y H:i')), 0, 0, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4'); // Horizontal
$pdf->logoPath = $logoPath;
$pdf->tieneLogo = $logoDisponible;
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

$yInicio = $pdf->GetY();

// TARJETAS
$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(230, 230, 230);

// ---------------------------------------------------------
// Tarjeta 1: FILTROS APLICADOS
// ---------------------------------------------------------
$pdf->SetXY(10, $yInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(85, 8, pdf_txt('FILTROS APLICADOS'), 1, 1, 'L', true);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(26, 26, 46);
$pdf->MultiCell(85, 6, pdf_txt($filtros), 'LR', 'L', true);
$pdf->SetDrawColor(230, 230, 230);
$pdf->Line(10, $pdf->GetY(), 95, $pdf->GetY());

// ---------------------------------------------------------
// Tarjeta 2: FECHA DEL REPORTE
// ---------------------------------------------------------
$pdf->SetXY(100, $yInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(60, 8, pdf_txt('FECHA DEL REPORTE'), 1, 1, 'L', true);

$pdf->SetXY(100, $yInicio + 8);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(60, 8, pdf_txt($fecha_reporte), 'LR', 1, 'L', true);

$pdf->SetDrawColor(230, 230, 230);
$pdf->Line(100, $yInicio + 24, 160, $yInicio + 24);

// ---------------------------------------------------------
// Tarjeta 3: TOTALES
// ---------------------------------------------------------
$pdf->SetXY(165, $yInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(55, 8, pdf_txt('TOTALES'), 1, 1, 'L', true);

$pdf->SetXY(165, $yInicio + 8);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(25, 8, pdf_txt('Entradas:'), 'LR', 0, 'L', true);
$pdf->SetTextColor(40, 167, 69);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 8, pdf_txt('+ ' . $totalEntradas), 'LR', 1, 'L', true);

$pdf->SetXY(165, $yInicio + 16);
$pdf->SetTextColor(26, 26, 46);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(25, 8, pdf_txt('Salidas:'), 'LR', 0, 'L', true);
$pdf->SetTextColor(220, 53, 69);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 8, pdf_txt('- ' . $totalSalidas), 'LR', 1, 'L', true);

$pdf->SetDrawColor(230, 230, 230);
$pdf->Line(165, $yInicio + 24, 220, $yInicio + 24);

// ---------------------------------------------------------
// Tarjeta 4: INFORMACIÓN (Anuladas y Generado por)
// ---------------------------------------------------------
$pdf->SetXY(225, $yInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(62, 8, pdf_txt('INFORMACIÓN'), 1, 1, 'L', true);

$pdf->SetXY(225, $yInicio + 8);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(30, 8, pdf_txt('Anuladas:'), 'LR', 0, 'L', true);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(32, 8, pdf_txt((string)$totalAnuladas), 'LR', 1, 'L', true);

$pdf->SetXY(225, $yInicio + 16);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 8, pdf_txt('Generado por:'), 'LR', 0, 'L', true);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(32, 8, pdf_txt($usuario_activo), 'LR', 1, 'L', true);

$pdf->SetDrawColor(230, 230, 230);
$pdf->Line(225, $yInicio + 24, 287, $yInicio + 24);
$pdf->SetY($yInicio + 30);

// ============================================
// TABLA DE MOVIMIENTOS
// ============================================
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(0, 7, pdf_txt('DETALLE DE MOVIMIENTOS'), 0, 1, 'L');

$pdf->SetDrawColor(243, 156, 18);
$pdf->SetLineWidth(0.6);
$pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
$pdf->SetLineWidth(0.2);
$pdf->Ln(2);

$pdf->SetDrawColor(220, 220, 220);
$pdf->SetFillColor(245, 245, 245);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 8);

$w = [16, 24, 50, 25, 75, 60, 35];
$headers = ['N° NOTA', 'FECHA', 'CLIENTE / PROVEEDOR', 'TIPO', 'PRODUCTOS', 'ENCARGADO', 'UNIDADES'];

foreach ($headers as $i => $h) {
    $align = in_array($i, [0, 3, 6]) ? 'C' : 'L';
    $pdf->Cell($w[$i], 8, pdf_txt($h), 'TB', 0, $align, true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(26, 26, 46);

$alt = false;
if (empty($movimientos)) {
    $pdf->Cell(array_sum($w), 12, pdf_txt('No hay movimientos registrados con estos filtros'), 1, 1, 'C', true);
} else {
    foreach ($movimientos as $m) {
        $cliente = trim((string) ($m['nombre_cliente'] ?? 'N/A'));
        if (strlen($cliente) > 32) $cliente = substr($cliente, 0, 29) . '...';

        // 🔥 LIMPIEZA DEL (X20)
        $productos = trim((string) ($m['productos_lista'] ?? ''));
        $productos = preg_replace('/\s*\(x\d+\)/i', '', $productos);
        if (strlen($productos) > 46) $productos = substr($productos, 0, 43) . '...';

        $encargado = trim((string) ($m['usuario_activo'] ?? ''));
        if (strlen($encargado) > 25) $encargado = substr($encargado, 0, 22) . '...';

        $fecha_mov = isset($m['fecha_movimiento']) ? date('d/m/Y', strtotime($m['fecha_movimiento'])) : '';
        $tipo = $m['tipo_movimiento'] ?? 'Entrada';

        $esAnulacion = (strpos($tipo, 'Anulación') === 0 || stripos($tipo, 'anulad') !== false);
        
        $fill = $alt;
        $pdf->SetFillColor(250, 250, 250);

        $pdf->Cell($w[0], 8, '#' . $m['id_documento'], 'B', 0, 'C', $fill);
        $pdf->Cell($w[1], 8, pdf_txt($fecha_mov), 'B', 0, 'L', $fill);
        $pdf->Cell($w[2], 8, pdf_txt($cliente), 'B', 0, 'L', $fill);
        
        if ($esAnulacion) {
            $pdf->SetTextColor(108, 117, 125); // Gris
        } elseif (stripos($tipo, 'Entrada') !== false) {
            $pdf->SetTextColor(40, 167, 69); // Verde
        } else {
            $pdf->SetTextColor(220, 53, 69); // Rojo
        }
        $pdf->Cell($w[3], 8, pdf_txt($tipo), 'B', 0, 'C', $fill);
        $pdf->SetTextColor(26, 26, 46);

        $pdf->Cell($w[4], 8, pdf_txt($productos), 'B', 0, 'L', $fill);
        $pdf->Cell($w[5], 8, pdf_txt($encargado), 'B', 0, 'L', $fill);
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w[6], 8, (string) ($m['total_unidades'] ?? 0), 'B', 1, 'C', $fill);
        $pdf->SetFont('Arial', '', 8);

        $alt = !$alt;
    }
}

// PIE
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 6, pdf_txt('Gracias por su preferencia - Pirotecnia Fenix C.A.'), 0, 1, 'C');
$pdf->Cell(0, 6, pdf_txt('Generado el ' . date('d/m/Y H:i:s')), 0, 1, 'R');

$pdf->Output('I', 'Reporte_Movimientos_' . date('Ymd_His') . '.pdf');
exit;