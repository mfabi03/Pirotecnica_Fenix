<?php
// app/view/reportes/generar_pdf_salida.php

error_reporting(0);
ini_set('display_errors', 0);
while (ob_get_level() > 0) { ob_end_clean(); }

require_once dirname(__DIR__, 3) . '/fpdf.php';
require_once dirname(__DIR__, 3) . '/app/Config/Connect/ConnectDB.php';

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\notasalidaModel;

if (!isset($_GET['id']) || empty($_GET['id'])) { die("ID de nota no proporcionado."); }
$idNota = (int) $_GET['id'];

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new notasalidaModel($db);
    $nota = $modelo->obtenerNotaPorId($idNota);
    if (!$nota) die("Nota de salida no encontrada.");
} catch (Exception $e) { die("Error: " . $e->getMessage()); }

function pdf_txt($texto) {
    $texto = (string) $texto;
    if ($texto === '') return '';
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);
        if ($conv !== false) return $conv;
    }
    return $texto;
}

$logoPath = dirname(__DIR__, 3) . '/assets/imagenes/logo.png';
$logoDisponible = file_exists($logoPath);
require_once dirname(__DIR__, 3) . '/app/Config/timezone.php';

$fechaSolo = !empty($nota['fecha']) ? date('d/m/Y', strtotime($nota['fecha'])) : 'N/A';
$detalles = $nota['detalles'] ?? [];
$totalUnidades = 0;
foreach ($detalles as $d) { $totalUnidades += (int)($d['cantidad'] ?? 0); }
$estadoAnulada = ((int) ($nota['eliminado'] ?? 0) === 1);

class PDF extends FPDF
{
    public $idNota = ''; public $estadoAnulada = false;
    public $logoPath = ''; public $tieneLogo = false;

    function Header()
    {
        if ($this->tieneLogo) { $this->Image($this->logoPath, 10, 8, 20, 20); }

        $this->SetXY(35, 9);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(26, 26, 46);
        $this->Cell(95, 7, pdf_txt('PIROTECNICA FENIX C.A.'), 0, 0, 'L');

        $this->SetFillColor(243, 156, 18);
        $this->SetTextColor(26, 26, 46);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(60, 7, pdf_txt('NOTA DE SALIDA'), 0, 1, 'C', true);

        $this->SetXY(35, 17);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(95, 6, pdf_txt('Sistema de gestion - Comprobante'), 0, 1, 'L');

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
$pdf->idNota = $idNota;
$pdf->estadoAnulada = $estadoAnulada;
$pdf->logoPath = $logoPath;
$pdf->tieneLogo = $logoDisponible;
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

$yInicio = $pdf->GetY();
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
$pdf->Cell(60, 8, pdf_txt($idNota), 'LR', 1, 'L', true);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 8, pdf_txt('Fecha'), 'LR', 0, 'L', true);
$pdf->Cell(60, 8, pdf_txt($fechaSolo), 'LR', 1, 'L', true);

$pdf->SetX(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 8, pdf_txt('Encargado'), 'LR', 0, 'L', true);
$pdf->Cell(60, 8, pdf_txt($nota['usuario_responsable'] ?? 'N/A'), 'LR', 1, 'L', true);

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
if ($estadoAnulada) {
    $pdf->SetTextColor(220, 53, 69);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(65, 8, pdf_txt('ANULADA'), 'LR', 1, 'L', true);
} else {
    $pdf->SetTextColor(40, 167, 69);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(65, 8, pdf_txt('ACTIVA'), 'LR', 1, 'L', true);
}

$yCierre = $yInicio + 40;
$pdf->SetDrawColor(230, 230, 230);
$pdf->Line(10, $yCierre, 100, $yCierre);
$pdf->Line(105, $yCierre, 200, $yCierre);
$pdf->SetY($yCierre + 8);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(26, 26, 46);
$pdf->Cell(0, 7, pdf_txt('DETALLE DE PRODUCTOS'), 0, 1, 'L');
$pdf->SetDrawColor(243, 156, 18);
$pdf->SetLineWidth(0.6);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->SetLineWidth(0.2);
$pdf->Ln(2);

$pdf->SetDrawColor(220, 220, 220);
$pdf->SetFillColor(245, 245, 245);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 8);

// 🔥 SOLO Producto, Categoría y Cant. (Sin Costo)
$w = [14, 90, 55, 31];
$headers = ['ID', 'Producto', 'Categoría', 'Cant.'];
foreach ($headers as $i => $h) {
    $align = in_array($i, [0, 3]) ? 'C' : 'L';
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
        $prod = trim((string) ($d['nombre_producto'] ?? ''));
        if (strlen($prod) > 45) $prod = substr($prod, 0, 42) . '...';
        $cat = trim((string) ($d['nombre_categoria'] ?? 'Sin categoria'));
        if (strlen($cat) > 30) $cat = substr($cat, 0, 27) . '...';

        $fill = $alt;
        $pdf->SetFillColor(250, 250, 250);
        $pdf->Cell($w[0], 8, (string) ($d['id_producto'] ?? ''), 'B', 0, 'C', $fill);
        $pdf->Cell($w[1], 8, pdf_txt($prod), 'B', 0, 'L', $fill);
        $pdf->Cell($w[2], 8, pdf_txt($cat), 'B', 0, 'L', $fill);
        $pdf->Cell($w[3], 8, (string) ($d['cantidad'] ?? 0), 'B', 1, 'C', $fill);
        $alt = !$alt;
    }
}

$pdf->Ln(2);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell($w[0] + $w[1] + $w[2], 10, pdf_txt('TOTAL UNIDADES'), 'T', 0, 'R', true);
$pdf->SetTextColor(243, 156, 18);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell($w[3], 10, (string) $totalUnidades, 'T', 1, 'C', true);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 6, pdf_txt('Gracias por su preferencia - Pirotecnia Fenix C.A.'), 0, 1, 'C');
$pdf->Cell(0, 6, pdf_txt('Generado el ' . date('d/m/Y H:i:s')), 0, 1, 'R');

$pdf->Output('I', 'Nota_Salida_' . $idNota . '.pdf');
exit;