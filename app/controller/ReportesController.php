<?php
// app/Controller/ReportesController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\ReportesModel;
use App\Pirotecnicafenix\Helpers\Validador;
use Exception;

class ReportesController
{
    private $modelo;

    public function __construct($db = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->modelo = new ReportesModel();
    }

    public function index()
    {
        $ajax = (!empty($_GET['ajax']) || !empty($_POST['ajax']));

        $req = function($key) {
            $val = $_POST[$key] ?? $_GET[$key] ?? null;
            return $val !== null && $val !== '' ? Validador::limpiar($val) : null;
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
        $totalCierres = $totalesGlobales['total_cierres'] ?? 0;
        $totalNotas = $totalesGlobales['total_notas'] ?? 0;

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

    public function obtenerCierreAjax()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = Validador::validarId($_GET['id'] ?? null, 'ID de cierre');
            $cierre = $this->modelo->obtenerCierrePorId($id);

            if (!$cierre) {
                echo json_encode(['success' => false, 'mensaje' => 'Cierre no encontrado'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $detalles = $this->modelo->obtenerDetalleCierre($id);

            echo json_encode([
                'success' => true,
                'cierre' => $cierre,
                'detalles' => $detalles
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function exportarCierrePDF()
    {
        try {
            $id_cierre = Validador::validarId($_GET['id_cierre'] ?? null, 'ID de cierre');

            $cierre = $this->modelo->obtenerCierrePorId($id_cierre);
            if (!$cierre) {
                die("Error: Cierre no encontrado.");
            }

            $movimientos = $this->modelo->obtenerDetalleCierrePDF($id_cierre);

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

    public function exportarPDF()
    {
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

            $fecha_inicio = Validador::limpiar($_GET['fecha_inicio'] ?? ($_GET['fecha'] ?? ''));
            if ($fecha_inicio === '') $fecha_inicio = null;
            $fecha_fin = Validador::limpiar($_GET['fecha_fin'] ?? '');
            if ($fecha_fin === '') $fecha_fin = null;
            $busqueda = Validador::limpiar($_GET['busqueda'] ?? '');
            if ($busqueda === '') $busqueda = null;
            $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
            $tipo_movimiento = Validador::limpiar($_GET['tipo_movimiento'] ?? '');
            if ($tipo_movimiento === '') $tipo_movimiento = null;

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

    public function exportarCSV()
    {
        try {
            $fecha_inicio = Validador::limpiar($_GET['fecha_inicio'] ?? ($_GET['fecha'] ?? ''));
            if ($fecha_inicio === '') $fecha_inicio = null;
            $fecha_fin = Validador::limpiar($_GET['fecha_fin'] ?? '');
            if ($fecha_fin === '') $fecha_fin = null;
            $busqueda = Validador::limpiar($_GET['busqueda'] ?? '');
            if ($busqueda === '') $busqueda = null;
            $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
            $tipo_movimiento = Validador::limpiar($_GET['tipo_movimiento'] ?? '');
            if ($tipo_movimiento === '') $tipo_movimiento = null;

            $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="Reporte_Movimientos_' . date('Ymd_His') . '.csv"');

            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['N° NOTA', 'FECHA', 'CLIENTE / PROVEEDOR', 'TIPO', 'PRODUCTOS', 'CATEGORÍAS', 'UNIDADES', 'ENCARGADO']);

            $totalEntradas = 0; $totalSalidas = 0; $totalAnuladas = 0; $totalCierres = 0;
            $cierresVistos = [];

            foreach ($movimientos as $m) {
                $t = $m['tipo_movimiento'] ?? '';
                $esAnulacion = (strpos($t, 'Anulación') === 0 || stripos($t, 'anulad') !== false);
                $esCierre = ($t === 'Cierre de Caja' || ($m['tipo_origen'] ?? '') === 'cierre');

                if ($esCierre) {
                    if (!in_array($m['id_documento'], $cierresVistos)) {
                        $cierresVistos[] = $m['id_documento'];
                        $totalCierres++;

                        fputcsv($output, [
                            '#' . $m['id_documento'],
                            isset($m['fecha_movimiento']) ? date('d/m/Y', strtotime($m['fecha_movimiento'])) : '',
                            'Cierre Global de Turno',
                            'Cierre de Caja',
                            'Global',
                            'Global',
                            (int)($m['total_unidades'] ?? 0),
                            $m['usuario_activo'] ?? 'N/A'
                        ]);
                    }
                } else {
                    if ($esAnulacion) $totalAnuladas++;
                    elseif (stripos($t, 'Entrada') !== false) $totalEntradas++;
                    elseif (stripos($t, 'Salida') !== false) $totalSalidas++;

                    fputcsv($output, [
                        '#' . $m['id_documento'],
                        isset($m['fecha_movimiento']) ? date('d/m/Y', strtotime($m['fecha_movimiento'])) : '',
                        $m['nombre_cliente'] ?? 'N/A',
                        $t,
                        (int)($m['total_productos'] ?? 0) . ' Prod.',
                        (int)($m['total_categorias'] ?? 0) . ' Cat.',
                        (int)($m['total_unidades'] ?? 0),
                        $m['usuario_activo'] ?? 'N/A'
                    ]);
                }
            }

            fputcsv($output, ['', '', '', '', '', '', '', '']);
            fputcsv($output, ['RESUMEN', 'Entradas:', $totalEntradas, 'Salidas:', $totalSalidas, 'Anuladas:', $totalAnuladas, 'Cierres: ' . $totalCierres]);

            fclose($output);
            exit();
        } catch (Exception $e) {
            die("Error al generar el CSV: " . $e->getMessage());
        }
    }
}