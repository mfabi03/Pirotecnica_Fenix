<?php
// app/view/reportes/generar_pdfCajaView.php

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
$fecha_reporte = $datos_pdf['fecha'] ?? date('d/m/Y');
$total_documentos = $datos_pdf['total_documentos'] ?? 0;
$total_unidades = $datos_pdf['total_unidades'] ?? 0;
$movimientos = $datos_pdf['movimientos'] ?? [];
$usuario_activo = $_SESSION['usuario_nombre'] ?? 'Administrador';

$logoPath = dirname(__DIR__, 3) . '/assets/imagenes/logo.png';
$logoDisponible = file_exists($logoPath);

class PDF extends FPDF
{
    public $logoPath = '';
    public $tieneLogo = false;

    function Header()
    {
        // Logo
        if ($this->tieneLogo) {
            $this->Image($this->logoPath, 10, 8, 20, 20);
        }

        // Nombre empresa
        $this->SetXY(35, 9);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(26, 26, 46);
        $this->Cell(95, 7, pdf_txt('PIROTECNICA FENIX C.A.'), 0, 0, 'L');

        // Etiqueta CIERRE DE CAJA a la derecha
        $this->SetFillColor(243, 156, 18);
        $this->SetTextColor(26, 26, 46);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(60, 7, pdf_txt('CIERRE DE CAJA'), 0, 1, 'C', true);

        // Subtítulo
        $this->SetXY(35, 17);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(95, 6, pdf_txt('Sistema de gestion - Reporte Diario'), 0, 1, 'L');

        // Línea separadora dorada
        $this->SetDrawColor(243, 156, 18);
        $this->SetLineWidth(0.8);
        $this->Line(10, 32, 200, 32);
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

$pdf = new PDF('P', 'mm', 'A4');
$pdf->logoPath = $logoPath;
$pdf->tieneLogo = $logoDisponible;
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// TARJETAS DE DATOS
$yInicio = $pdf->GetY();

$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(230, 230, 230);

$pdf->SetXY(10, $yInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(90, 8, pdf_txt('DATOS DEL CIERRE'), 1, 1, 'L', true);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(30, 8, pdf_txt('Fecha'), 'LR', 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 8, pdf_txt($fecha_reporte), 'LR', 1, 'L', true);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 8, pdf_txt('Encargado'), 'LR', 0, 'L', true);
$pdf->Cell(60, 8, pdf_txt($usuario_activo), 'LR', 1, 'L', true);

$pdf->SetXY(105, $yInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(95, 8, pdf_txt('RESUMEN DEL DÍA'), 1, 1, 'L', true);

$pdf->SetXY(105, $yInicio + 8);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(30, 8, pdf_txt('Total Notas'), 'LR', 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 8, pdf_txt($total_documentos), 'LR', 1, 'L', true);

$pdf->SetXY(105, $yInicio + 16);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 8, pdf_txt('Total Unidades'), 'LR', 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 8, pdf_txt($total_unidades), 'LR', 1, 'L', true);

$yCierre = $yInicio + 24;
$pdf->SetDrawColor(230, 230, 230);
$pdf->Line(10, $yCierre, 100, $yCierre);
$pdf->Line(105, $yCierre, 200, $yCierre);
$pdf->SetY($yCierre + 8);

// TABLA DE MOVIMIENTOS
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(0, 7, pdf_txt('DETALLE DE MOVIMIENTOS'), 0, 1, 'L');

$pdf->SetDrawColor(243, 156, 18);
$pdf->SetLineWidth(0.6);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->SetLineWidth(0.2);
$pdf->Ln(2);

$pdf->SetDrawColor(220, 220, 220);
$pdf->SetFillColor(245, 245, 245);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 7);

$w = [12, 18, 35, 15, 38, 28, 12, 32];
$headers = ['ID', 'Fecha', 'Cliente', 'Tipo', 'Productos', 'Categorías', 'Cant.', 'Encargado'];

foreach ($headers as $i => $h) {
    $align = in_array($i, [0, 3, 6]) ? 'C' : 'L';
    $pdf->Cell($w[$i], 8, pdf_txt($h), 'TB', 0, $align, true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(26, 26, 46);

$alt = false;
if (empty($movimientos)) {
    $pdf->Cell(array_sum($w), 12, pdf_txt('No hay movimientos en esta fecha'), 1, 1, 'C', true);
} else {
    foreach ($movimientos as $m) {
        $cliente = trim((string) ($m['nombre_cliente'] ?? 'N/A'));
        if (strlen($cliente) > 20) $cliente = substr($cliente, 0, 17) . '...';

        $productos = trim((string) ($m['nombre_producto'] ?? ''));
        if (strlen($productos) > 22) $productos = substr($productos, 0, 20) . '...';

        $categorias = trim((string) ($m['categoria'] ?? ''));
        if (strlen($categorias) > 16) $categorias = substr($categorias, 0, 14) . '...';

        $encargado = trim((string) ($m['usuario_activo'] ?? ''));
        if (strlen($encargado) > 18) $encargado = substr($encargado, 0, 16) . '...';

        $fecha_mov = isset($m['fecha_movimiento']) ? date('d/m/Y', strtotime($m['fecha_movimiento'])) : '';

        $fill = $alt;
        $pdf->SetFillColor(250, 250, 250);

        $pdf->Cell($w[0], 8, '#' . $m['id_documento'], 'B', 0, 'C', $fill);
        $pdf->Cell($w[1], 8, pdf_txt($fecha_mov), 'B', 0, 'L', $fill);
        $pdf->Cell($w[2], 8, pdf_txt($cliente), 'B', 0, 'L', $fill);
        $pdf->Cell($w[3], 8, pdf_txt($m['tipo_cliente'] ?? 'Natural'), 'B', 0, 'C', $fill);
        $pdf->Cell($w[4], 8, pdf_txt($productos), 'B', 0, 'L', $fill);
        $pdf->Cell($w[5], 8, pdf_txt($categorias), 'B', 0, 'L', $fill);
        
        $pdf->SetTextColor(243, 156, 18);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($w[6], 8, (string) $m['cantidad'], 'B', 0, 'C', $fill);
        $pdf->SetTextColor(26, 26, 46);
        $pdf->SetFont('Arial', '', 7);
        
        $pdf->Cell($w[7], 8, pdf_txt($encargado), 'B', 1, 'L', $fill);

        $alt = !$alt;
    }
}

// FILA DE TOTALES
$pdf->Ln(2);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 8);

$pdf->Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5], 10, pdf_txt('TOTALES'), 'T', 0, 'R', true);

$pdf->SetTextColor(243, 156, 18);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($w[6], 10, (string) $total_unidades, 'T', 0, 'C', true);

$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($w[7], 10, '', 'T', 1, 'R', true);

// PIE
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 6, pdf_txt('Gracias por su preferencia - Pirotecnia Fenix C.A.'), 0, 1, 'C');
$pdf->Cell(0, 6, pdf_txt('Generado el ' . date('d/m/Y H:i:s')), 0, 1, 'R');

// Salida
$pdf->Output('I', 'Cierre_Caja_' . date('Ymd_His') . '.pdf');
exit;