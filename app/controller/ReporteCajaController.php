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
    // AJAX: DETALLE DE NOTA DE SALIDA (MODAL)
    // ============================================================
    public function obtenerDetalleNotaAjax() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
                exit;
            }

            require_once dirname(__DIR__) . '/model/notasalidaModel.php';
            $modeloSalida = new \App\Pirotecnicafenix\Model\notasalidaModel($this->db);

            $nota = $modeloSalida->obtenerNotaPorId($id);
            
            if (!$nota) {
                echo json_encode(['success' => false, 'error' => 'Nota no encontrada']);
                exit;
            }

            echo json_encode(['success' => true, 'nota' => $nota], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }

    // ============================================================
    // VISTA PRINCIPAL — REPORTE DE CAJA
    // ============================================================
    public function index() {
        // ⚠️ INTERCEPTAR PETICIÓN AJAX PARA EL MODAL
        if (isset($_GET['type']) && $_GET['type'] === 'detalle_json' && isset($_GET['id'])) {
            $this->obtenerDetalleNotaAjax();
            return;
        }

        $ajax = (!empty($_GET['ajax']) || !empty($_POST['ajax']));
        $fecha = (isset($_POST['fecha']) && trim($_POST['fecha']) !== '') ? trim($_POST['fecha']) : ((isset($_GET['fecha']) && trim($_GET['fecha']) !== '') ? trim($_GET['fecha']) : date('Y-m-d'));
        $busqueda = (isset($_POST['busqueda']) && trim($_POST['busqueda']) !== '') ? trim($_POST['busqueda']) : ((isset($_GET['busqueda']) && trim($_GET['busqueda']) !== '') ? trim($_GET['busqueda']) : null);
        $id_categoria = (isset($_POST['id_categoria']) && $_POST['id_categoria'] !== '' && $_POST['id_categoria'] > 0) ? (int)$_POST['id_categoria'] : ((isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0) ? (int)$_GET['id_categoria'] : null);
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if (isset($_POST['pagina']) && $_POST['pagina'] !== '') $pagina = (int)$_POST['pagina'];
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
        if (isset($_POST['por_pagina']) && $_POST['por_pagina'] !== '') $porPagina = (int)$_POST['por_pagina'];
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
            require_once __DIR__ . '/../view/reporteCaja/reporteCajaView.php';
            $html = ob_get_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'html' => $html], JSON_UNESCAPED_UNICODE);
            exit();
        }

        require_once __DIR__ . '/../view/reporteCaja/reporteCajaView.php';
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
        fputcsv($output, ['Nota #', 'Producto', 'Categoría', 'Cantidad', 'Fecha', 'Responsable', 'Total', 'Estado', 'Cliente']);

        foreach ($movimientos as $m) {
            $cantidad = (int)($m['cantidad'] ?? 0);
            fputcsv($output, [
                $m['id_documento'] ?? '',
                $m['nombre_producto'] ?? 'Sin producto',
                $m['categoria'] ?? 'Sin categoría',
                $cantidad . ' unidades',
                date('d/m/Y', strtotime($m['fecha_movimiento'] ?? '')),
                $m['usuario_activo'] ?? 'N/A',
                $cantidad . ' unidades',
                'Activa',
                $m['nombre_cliente'] ?? 'Sin cliente'
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

        $notasAgrupadas = [];
        foreach ($movimientos as $m) {
            $idNota = (int)($m['id_documento'] ?? 0);
            if ($idNota <= 0) continue;

            if (!isset($notasAgrupadas[$idNota])) {
                $notasAgrupadas[$idNota] = [
                    'id_documento' => $idNota,
                    'fecha_movimiento' => $m['fecha_movimiento'] ?? $fecha,
                    'usuario_activo' => $m['usuario_activo'] ?? 'N/A',
                    'nombre_cliente' => $m['nombre_cliente'] ?? 'Sin cliente',
                    'tipo_cliente' => $m['tipo_cliente'] ?? 'Natural',
                    'productos' => [],
                    'categorias' => [],
                    'total_unidades' => 0,
                ];
            }

            $producto = preg_replace('/\s*\(x\d+\)/', '', trim((string)($m['nombre_producto'] ?? 'Sin producto')));
            if ($producto !== '' && !in_array($producto, $notasAgrupadas[$idNota]['productos'], true)) {
                $notasAgrupadas[$idNota]['productos'][] = $producto;
            }

            $categoria = trim((string)($m['categoria'] ?? 'Sin categoría'));
            if ($categoria !== '' && !in_array($categoria, $notasAgrupadas[$idNota]['categorias'], true)) {
                $notasAgrupadas[$idNota]['categorias'][] = $categoria;
            }

            $notasAgrupadas[$idNota]['total_unidades'] += (int)($m['cantidad'] ?? 0);
        }

        uasort($notasAgrupadas, function ($a, $b) {
            return ((int)$b['id_documento']) <=> ((int)$a['id_documento']);
        });

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        $pdf->SetFillColor(26, 26, 46);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->pdfAjustado('Reporte Diario de Caja'), 0, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 7, $this->pdfAjustado('Fecha: ' . date('d/m/Y', strtotime($fecha))), 0, 1, 'L');
        $pdf->Cell(0, 7, $this->pdfAjustado('Responsable: ' . $nombreCajero), 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetDrawColor(210, 210, 210);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(95, 10, $this->pdfAjustado('TOTAL DE NOTAS'), 1, 0, 'C', true);
        $pdf->Cell(95, 10, $this->pdfAjustado('TOTAL DE UNIDADES'), 1, 1, 'C', true);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(95, 10, (string)($resumen['total_documentos'] ?? count($notasAgrupadas)), 1, 0, 'C', true);
        $pdf->Cell(95, 10, (string)($resumen['total_unidades'] ?? 0), 1, 1, 'C', true);
        $pdf->Ln(6);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetFillColor(26, 26, 46);
        $pdf->SetTextColor(255, 255, 255);

        $w = [18, 18, 28, 22, 46, 26, 18, 18];
        $headers = ['NOTA #', 'FECHA', 'CLIENTE', 'TIPO', 'PRODUCTOS', 'CATEGORÍAS', 'UNIDADES', 'ENCARGADO'];

        foreach ($headers as $index => $header) {
            $pdf->Cell($w[$index], 7, $this->pdfAjustado($header), 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetTextColor(0, 0, 0);

        if (empty($notasAgrupadas)) {
            $pdf->Cell(0, 12, $this->pdfAjustado('No hay movimientos registrados para este cierre.'), 1, 1, 'C');
        } else {
            foreach ($notasAgrupadas as $nota) {
                $fechaNota = date('d/m/Y', strtotime($nota['fecha_movimiento']));
                $cliente = trim((string)($nota['nombre_cliente'] ?? 'Sin cliente'));
                $tipo = trim((string)($nota['tipo_cliente'] ?? 'Natural'));
                $productos = implode(', ', $nota['productos']);
                $categorias = implode(', ', $nota['categorias']);
                $encargado = trim((string)($nota['usuario_activo'] ?? 'N/A'));

                $cliente = strlen($cliente) > 18 ? substr($cliente, 0, 18) . '...' : $cliente;
                $tipo = strlen($tipo) > 14 ? substr($tipo, 0, 14) . '...' : $tipo;
                $productos = strlen($productos) > 28 ? substr($productos, 0, 28) . '...' : $productos;
                $categorias = strlen($categorias) > 18 ? substr($categorias, 0, 18) . '...' : $categorias;
                $encargado = strlen($encargado) > 16 ? substr($encargado, 0, 16) . '...' : $encargado;

                $pdf->Cell($w[0], 7, '#' . (string)$nota['id_documento'], 1, 0, 'C');
                $pdf->Cell($w[1], 7, $this->pdfAjustado($fechaNota), 1, 0, 'C');
                $pdf->Cell($w[2], 7, $this->pdfAjustado($cliente), 1, 0, 'L');
                $pdf->Cell($w[3], 7, $this->pdfAjustado($tipo), 1, 0, 'L');
                $pdf->Cell($w[4], 7, $this->pdfAjustado($productos), 1, 0, 'L');
                $pdf->Cell($w[5], 7, $this->pdfAjustado($categorias), 1, 0, 'L');
                $pdf->Cell($w[6], 7, (string)$nota['total_unidades'], 1, 0, 'C');
                $pdf->Cell($w[7], 7, $this->pdfAjustado($encargado), 1, 1, 'L');
            }
        }

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 6, $this->pdfAjustado('Generado el ' . date('d/m/Y H:i:s')), 0, 1, 'R');

        $pdf->Output('I', 'reporte_caja_' . $fecha . '.pdf');
        exit();
    }
}