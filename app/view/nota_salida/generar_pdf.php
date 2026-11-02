<?php
// app/view/nota_salida/generar_pdf.php

error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level() > 0) {
    ob_end_clean();
}

require_once dirname(__DIR__, 3) . '/fpdf.php';
require_once dirname(__DIR__, 3) . '/app/Config/Connect/ConnectDB.php';

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\notasalidaModel;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de nota no proporcionado.");
}

$idNota = (int) $_GET['id'];

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new notasalidaModel($db);
    $nota = $modelo->obtenerNotaPorId($idNota);

    if (!$nota) {
        die("Nota de salida no encontrada.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

function pdf_txt($texto) {
    $texto = (string) $texto;
    if ($texto === '') return '';
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);
        if ($conv !== false) return $conv;
    }
    return $texto;
}

// Ruta del logo
$logoPath = dirname(__DIR__, 3) . '/assets/imagenes/logo.png';
$logoDisponible = file_exists($logoPath);

require_once dirname(__DIR__, 3) . '/app/Config/timezone.php';

$fechaSolo = 'N/A';


if (!empty($nota['fecha'])) {
    $ts = strtotime($nota['fecha']);
    if ($ts !== false) {
        $fechaSolo = date('d/m/Y', $ts);
    }
}

// Totales
$detalles = $nota['detalles'] ?? [];
$totalUnidades = 0;
$totalCosto    = 0;

foreach ($detalles as $d) {
    $cantidad = (int) ($d['cantidad'] ?? 0);
    $costo    = (float) ($d['costo_unitario'] ?? 0);
    $totalUnidades += $cantidad;
    $totalCosto    += $cantidad * $costo;
}

$estadoAnulada = ((int) ($nota['eliminado'] ?? 0) === 1);

class PDF extends FPDF
{
    public $idNota = '';
    public $estadoAnulada = false;
    public $logoPath = '';
    public $tieneLogo = false;

    function Header()
    {
        // --- Cabecera: logo + empresa + título ---
        if ($this->tieneLogo) {
            $this->Image($this->logoPath, 10, 8, 20, 20);
        }

        // Nombre empresa
        $this->SetXY(35, 9);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(26, 26, 46);
        $this->Cell(95, 7, pdf_txt('PIROTECNICA FENIX C.A.'), 0, 0, 'L');

        // Etiqueta NOTA DE SALIDA a la derecha
        $this->SetFillColor(243, 156, 18);
        $this->SetTextColor(26, 26, 46);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(60, 7, pdf_txt('NOTA DE SALIDA'), 0, 1, 'C', true);

        // Subtítulo bajo el nombre de la empresa
        $this->SetXY(35, 17);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(95, 6, pdf_txt('Sistema de gestion - Comprobante'), 0, 1, 'L');

        // Línea separadora dorada
        $this->SetDrawColor(243, 156, 18);
        $this->SetLineWidth(0.8);
        $this->Line(10, 32, 200, 32);
        $this->SetLineWidth(0.2);

        // Ajustar Y después de la cabecera
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
$pdf->idNota = $idNota;
$pdf->estadoAnulada = $estadoAnulada;
$pdf->logoPath = $logoPath;
$pdf->tieneLogo = $logoDisponible;
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// ============================================
// TARJETA DE DATOS (dos columnas)
// ============================================
$yInicio = $pdf->GetY();

// -- Columna izquierda: DATOS DE LA NOTA
$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(230, 230, 230);

$pdf->SetXY(10, $yInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(90, 8, pdf_txt('DATOS DE LA NOTA'), 1, 1, 'L', true);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(30, 8, pdf_txt('Nota #'), 'LR', 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 8, pdf_txt($pdf->idNota), 'LR', 1, 'L', true);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 8, pdf_txt('Fecha'), 'LR', 0, 'L', true);
$pdf->Cell(60, 8, pdf_txt($fechaSolo), 'LR', 1, 'L', true);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 10);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 8, pdf_txt('Encargado'), 'LR', 0, 'L', true);
$pdf->Cell(60, 8, pdf_txt($nota['usuario_responsable'] ?? 'N/A'), 'LR', 1, 'L', true);

// -- Columna derecha: DATOS DEL CLIENTE
$pdf->SetXY(105, $yInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(95, 8, pdf_txt('DATOS DEL CLIENTE'), 1, 1, 'L', true);

$pdf->SetXY(105, $yInicio + 8);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(30, 8, pdf_txt('Cliente'), 'LR', 0, 'L', true);
$pdf->Cell(65, 8, pdf_txt($nota['cliente'] ?? 'N/A'), 'LR', 1, 'L', true);

$pdf->SetXY(105, $yInicio + 16);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 8, pdf_txt('Documento'), 'LR', 0, 'L', true);
$pdf->Cell(65, 8, pdf_txt($nota['documento'] ?? 'N/A'), 'LR', 1, 'L', true);

$pdf->SetXY(105, $yInicio + 24);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 8, pdf_txt('Estatus'), 'LR', 0, 'L', true);

if ($pdf->estadoAnulada) {
    $pdf->SetTextColor(220, 53, 69);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(65, 8, pdf_txt('ANULADA'), 'LR', 1, 'L', true);
} else {
    $pdf->SetTextColor(40, 167, 69);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(65, 8, pdf_txt('ACTIVA'), 'LR', 1, 'L', true);
}

// Cierre inferior de las tarjetas (4 filas de 8mm)
$yCierre = $yInicio + 40;
$pdf->SetDrawColor(230, 230, 230);
$pdf->Line(10, $yCierre, 100, $yCierre);
$pdf->Line(105, $yCierre, 200, $yCierre);
$pdf->SetY($yCierre + 8);

// ============================================
// TABLA DE PRODUCTOS
// ============================================
// Título de sección
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(0, 7, pdf_txt('DETALLE DE PRODUCTOS'), 0, 1, 'L');

// Línea de acento dorada
$pdf->SetDrawColor(243, 156, 18);
$pdf->SetLineWidth(0.6);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->SetLineWidth(0.2);
$pdf->Ln(2);

// Encabezado de tabla
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetFillColor(245, 245, 245);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 8);

$w = [14, 62, 44, 16, 24, 30];
$headers = ['ID', 'Producto', 'Categoria', 'Cant.', 'Costo', 'Subtotal'];

foreach ($headers as $i => $h) {
    $align = in_array($i, [3]) ? 'C' : ($i >= 4 ? 'R' : 'L');
    $pdf->Cell($w[$i], 8, pdf_txt($h), 'TB', 0, $align, true);
}
$pdf->Ln();

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(26, 26, 46);

$alt = false;
if (empty($detalles)) {
    $pdf->Cell(array_sum($w), 12, pdf_txt('No hay productos en esta nota'), 1, 1, 'C', true);
} else {
    foreach ($detalles as $d) {
        $cantidad = (int) ($d['cantidad'] ?? 0);
        $costo    = (float) ($d['costo_unitario'] ?? 0);
        $subtotal = $cantidad * $costo;

        $producto = trim((string) ($d['nombre_producto'] ?? ''));
        if (strlen($producto) > 34) $producto = substr($producto, 0, 31) . '...';

        $categoria = trim((string) ($d['nombre_categoria'] ?? 'Sin categoria'));
        if (strlen($categoria) > 24) $categoria = substr($categoria, 0, 21) . '...';

        // Fila alterna suave
        $fill = $alt;
        $pdf->SetFillColor(250, 250, 250);

        $pdf->Cell($w[0], 8, (string) ($d['id_producto'] ?? ''), 'B', 0, 'L', $fill);
        $pdf->Cell($w[1], 8, pdf_txt($producto), 'B', 0, 'L', $fill);
        $pdf->Cell($w[2], 8, pdf_txt($categoria), 'B', 0, 'L', $fill);
        $pdf->Cell($w[3], 8, (string) $cantidad, 'B', 0, 'C', $fill);
        $pdf->Cell($w[4], 8, '$' . number_format($costo, 2), 'B', 0, 'R', $fill);

        $pdf->SetTextColor(243, 156, 18);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($w[5], 8, '$' . number_format($subtotal, 2), 'B', 1, 'R', $fill);
        $pdf->SetTextColor(26, 26, 46);
        $pdf->SetFont('Arial', '', 9);

        $alt = !$alt;
    }
}

// ============================================
// FILA DE TOTALES
// ============================================
$pdf->Ln(2);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell($w[0] + $w[1] + $w[2], 10, pdf_txt('TOTALES'), 'T', 0, 'R', true);

$pdf->SetTextColor(243, 156, 18);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell($w[3], 10, (string) $totalUnidades, 'T', 0, 'C', true);

$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($w[4], 10, '', 'T', 0, 'R', true);

$pdf->SetTextColor(40, 167, 69);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell($w[5], 10, '$' . number_format($totalCosto, 2), 'T', 1, 'R', true);

// ============================================
// PIE
// ============================================
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 6, pdf_txt('Gracias por su preferencia - Pirotecnia Fenix C.A.'), 0, 1, 'C');
$pdf->Cell(0, 6, pdf_txt('Generado el ' . date('d/m/Y H:i:s')), 0, 1, 'R');

// Vista previa
$pdf->Output('I', 'Nota_Salida_' . $idNota . '.pdf');
exit;