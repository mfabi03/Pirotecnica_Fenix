<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\ReporteCajaModel;
use Exception;

class ReporteCajaController {
    private $db;
    private $modelo;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = $db;
        $this->modelo = new ReporteCajaModel($this->db);
    }

    // ============================================================
    // HELPERS DE SESIÓN / PERMISOS
    // ============================================================
    private function usuarioActual() {
        return $_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? null;
    }

    private function rolActual() {
        return $_SESSION['rol'] ?? $_SESSION['nombre_rol'] ?? $_SESSION['id_rol'] ?? null;
    }

    private function esAdmin() {
        $rol = $this->rolActual();
        if (is_numeric($rol)) {
            return (int)$rol === 1; 
        }
        return in_array(strtolower((string)$rol), ['administrador', 'admin'], true);
    }

    private function resolverIdUsuarioFiltro($idSolicitado = null) {
        $idSesion = $this->usuarioActual();
        if ($this->esAdmin()) {
            return ($idSolicitado !== null && $idSolicitado !== '') ? (int)$idSolicitado : null;
        }
        return $idSesion !== null ? (int)$idSesion : -1;
    }

    // ============================================================
    // VISTA PRINCIPAL — REPORTE DE CAJA
    // ============================================================
    public function index() {
        $ajax = (!empty($_GET['ajax']) || !empty($_POST['ajax']));
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null;
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
        $offset = ($pagina - 1) * $porPagina;

        $id_usuario_filtro = $this->resolverIdUsuarioFiltro($_GET['id_usuario'] ?? null);

        $cajaCerrada = $this->modelo->fechaCerradaParaCaja($fecha, $id_usuario_filtro === -1 ? null : $id_usuario_filtro);

        $movimientos    = $this->modelo->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria, $porPagina, $offset);
        $totalRegistros = $this->modelo->contarSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria);
        $resumen        = $this->modelo->obtenerResumenCierreCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria);

        $totalPaginas    = $porPagina > 0 ? (int)ceil($totalRegistros / $porPagina) : 1;
        $paginaActual    = $pagina;
        $porPaginaActual = $porPagina;

        $idsNotas = [];
        $totalUnidades = 0;
        foreach ($movimientos as $mov) {
            $idNota = (int)($mov['id_documento'] ?? 0);
            if ($idNota > 0) {
                $idsNotas[$idNota] = true;
            }
            $totalUnidades += (int)($mov['cantidad'] ?? 0);
        }

        $totalEntradas   = 0;
        $totalSalidas    = $resumen['total_unidades'];
        $stockReal       = 0;
        $totalDocumentos = count($idsNotas);

        $db = $this->db;

        if ($ajax) {
            ob_start();
            require_once __DIR__ . '/../view/reportes/reporteCajaView.php';
            $html = ob_get_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'html' => $html], JSON_UNESCAPED_UNICODE);
            exit();
        }

        require_once __DIR__ . '/../view/reportes/reporteCajaView.php';
    }

    // ============================================================
    // AJAX: resumen del cierre del día + DETALLE DE NOTAS
    // ============================================================
    public function obtenerCierreDiaAjax() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idSesion = $this->usuarioActual();
            if ($idSesion === null) {
                echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
                exit();
            }

            $fecha = $_GET['fecha'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                echo json_encode(['success' => false, 'mensaje' => 'Fecha inválida']);
                exit();
            }

            $id_usuario = $this->esAdmin() ? null : (int)$idSesion;

            $resumen = $this->modelo->obtenerResumenCierreDia($id_usuario, $fecha, $_GET['busqueda'] ?? null, isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null);
            $detalle = $this->modelo->obtenerSalidasCajero($id_usuario, $fecha, null, null, 999999, 0);

            $nombreCajero = $this->esAdmin() ? 'Administrador (Global)' : $this->modelo->obtenerNombreCajero((int)$idSesion);

            echo json_encode([
                'success' => true,
                'data' => [
                    'fecha'            => $fecha,
                    'usuario'          => $nombreCajero,
                    'total_documentos' => $resumen['total_documentos'],
                    'total_unidades'   => $resumen['total_unidades'],
                    'total_lineas'     => $resumen['total_lineas'],
                    'detalle'          => $detalle
                ]
            ]);
            exit();

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensaje' => 'Error al calcular el cierre']);
            exit();
        }
    }

    public function confirmarCierreCajaAjax() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idSesion = $this->usuarioActual();
            if ($idSesion === null) {
                echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
                exit();
            }

            $fecha = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                echo json_encode(['success' => false, 'mensaje' => 'Fecha inválida']);
                exit();
            }

            $id_usuario = $this->esAdmin() ? null : (int)$idSesion;
            $resumen = $this->modelo->obtenerResumenCierreDia($id_usuario, $fecha);

            $guardado = $this->modelo->guardarCierreCaja(
                $fecha,
                $this->esAdmin() ? null : (int)$idSesion,
                (int)($resumen['total_documentos'] ?? 0),
                (int)($resumen['total_unidades'] ?? 0),
                (int)($resumen['total_lineas'] ?? 0)
            );

            if (!$guardado) {
                echo json_encode(['success' => false, 'mensaje' => 'No se pudo guardar el cierre del día']);
                exit();
            }

            echo json_encode([
                'success' => true,
                'mensaje' => 'Cierre del día guardado correctamente',
                'data' => [
                    'fecha' => $fecha,
                    'total_documentos' => (int)($resumen['total_documentos'] ?? 0),
                    'total_unidades' => (int)($resumen['total_unidades'] ?? 0),
                ]
            ]);
            exit();

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensaje' => 'Error al guardar el cierre']);
            exit();
        }
    }

    // ============================================================
    // PDF DEL REPORTE DE CAJA (FPDF)
    // ============================================================
    public function exportarPDF() {
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null;

        $id_usuario_filtro = $this->resolverIdUsuarioFiltro($_GET['id_usuario'] ?? null);

        $movimientos = $this->modelo->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria, 999999, 0);
        $resumen     = $this->modelo->obtenerResumenCierreCajero($id_usuario_filtro, $fecha);
        
        $nombreCajero = $this->esAdmin() ? 'Administrador (Global)' : $this->modelo->obtenerNombreCajero((int)$id_usuario_filtro);

        $this->generarPdfFPDF($fecha, $movimientos, $resumen, $nombreCajero);
    }

    // ============================================================
    // CSV
    // ============================================================
    public function exportarCSV() {
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null;

        $id_usuario_filtro = $this->resolverIdUsuarioFiltro($_GET['id_usuario'] ?? null);

        $movimientos = $this->modelo->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria, 999999, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_caja_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Nota #', 'Cliente', 'Producto', 'Categoría', 'Cantidad', 'Fecha', 'Responsable']);

        foreach ($movimientos as $m) {
            fputcsv($output, [
                $m['id_documento'] ?? '',
                $m['nombre_cliente'] ?? 'Sin cliente',
                $m['nombre_producto'] ?? '',
                $m['categoria'] ?? 'Sin categoría',
                $m['cantidad'] ?? 0,
                date('d/m/Y', strtotime($m['fecha_movimiento'] ?? '')),
                $m['usuario_activo'] ?? 'N/A'
            ]);
        }

        fclose($output);
        exit();
    }

    // ============================================================
    // GENERADOR DE PDF CON FPDF
    // ============================================================
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

    private function generarPdfFPDF($fecha, $movimientos, $resumen, $nombreCajero) {
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
        $pdf->Cell(0, 10, $this->pdfAjustado('Reporte de Cierre de Caja'), 0, 1, 'C', true);

        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(26, 26, 46);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 6, $this->pdfAjustado('Sistema Pirotecnia Fénix'), 0, 1, 'L', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 7, $this->pdfAjustado('Fecha: ' . date('d/m/Y', strtotime($fecha))), 0, 1, 'L');
        $pdf->Cell(0, 7, $this->pdfAjustado('Cajero: ' . $nombreCajero), 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetDrawColor(210, 210, 210);
        $pdf->SetFont('Arial', 'B', 11);

        $pdf->Cell(95, 12, $this->pdfAjustado('TOTAL NOTAS'), 1, 0, 'C', true);
        $pdf->Cell(95, 12, $this->pdfAjustado('TOTAL UNIDADES'), 1, 1, 'C', true);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(95, 12, (string)($resumen['total_documentos'] ?? 0), 1, 0, 'C', true);
        $pdf->Cell(95, 12, (string)($resumen['total_unidades'] ?? 0), 1, 1, 'C', true);
        $pdf->Ln(6);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(26, 26, 46);
        $pdf->SetTextColor(255, 255, 255);

        $w = [18, 36, 46, 18, 26, 42];
        $headers = ['Nota', 'Cliente', 'Producto', 'Cant.', 'Fecha', 'Responsable'];

        foreach ($headers as $index => $header) {
            $pdf->Cell($w[$index], 7, $this->pdfAjustado($header), 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(0, 0, 0);

        if (empty($movimientos)) {
            $pdf->Cell(0, 12, $this->pdfAjustado('No hay movimientos registrados para este cierre.'), 1, 1, 'C', false);
        } else {
            foreach ($movimientos as $m) {
                $cliente = trim((string)($m['nombre_cliente'] ?? 'Sin cliente'));
                $producto = trim((string)($m['nombre_producto'] ?? 'N/A'));
                $responsable = trim((string)($m['usuario_activo'] ?? 'N/A'));

                if (strlen($cliente) > 22) {
                    $cliente = substr($cliente, 0, 22) . '...';
                }
                if (strlen($producto) > 24) {
                    $producto = substr($producto, 0, 24) . '...';
                }
                if (strlen($responsable) > 18) {
                    $responsable = substr($responsable, 0, 18) . '...';
                }

                $pdf->Cell($w[0], 7, (string)($m['id_documento'] ?? 'N/A'), 1, 0, 'C');
                $pdf->Cell($w[1], 7, $this->pdfAjustado($cliente), 1, 0, 'L');
                $pdf->Cell($w[2], 7, $this->pdfAjustado($producto), 1, 0, 'L');
                $pdf->Cell($w[3], 7, (string)($m['cantidad'] ?? 0), 1, 0, 'C');
                $pdf->Cell($w[4], 7, date('d/m/Y', strtotime($m['fecha_movimiento'] ?? '')), 1, 0, 'C');
                $pdf->Cell($w[5], 7, $this->pdfAjustado($responsable), 1, 1, 'L');
            }
        }

        $pdf->Ln(6);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 6, $this->pdfAjustado('Generado el ' . date('d/m/Y H:i:s')), 0, 1, 'R');

        $pdf->Output('I', 'cierre_caja_' . $fecha . '.pdf');
        exit();
    }
}