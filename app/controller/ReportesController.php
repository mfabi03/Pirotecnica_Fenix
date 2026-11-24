<?php
// app/Controller/ReportesController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\ReportesModel;
use App\Pirotecnicafenix\Model\NotaentradaModel;
use App\Pirotecnicafenix\Model\notasalidaModel;
use App\Pirotecnicafenix\Model\ReporteCajaModel; // 🔥 IMPORTAMOS EL MODELO DE CAJA
use App\Pirotecnicafenix\Traits\ValidadorTrait;
use Exception;

class ReportesController
{
    private $modelo;
    private $val;

    public function __construct($db = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->val = new class { use ValidadorTrait; };
        $this->modelo = new ReportesModel();
    }

    /* ============================================================
     *  INDEX — con DISPATCH MANUAL de action
     * ============================================================ */

    public function index()
    {
        // 🔥 DISPATCH MANUAL: si viene ?action=X y el método existe, ejecutarlo.
        $action = $_GET['action'] ?? null;
        if ($action && $action !== 'index' && method_exists($this, $action)) {
            return $this->$action();
        }

        $val = $this->val;
        $ajax = (!empty($_GET['ajax']) || !empty($_POST['ajax']));

        $req = function($key) use ($val) {
            $raw = $_POST[$key] ?? $_GET[$key] ?? null;
            if ($raw === null || $raw === '') return null;
            return $val->limpiar($raw);
        };

        $fecha_inicio = $val->normalizarFecha($req('fecha_inicio') ?? $req('fecha'), null);
        $fecha_fin    = $val->normalizarFecha($req('fecha_fin') ?? $req('fecha_hasta'), null);

        $busqueda        = $req('busqueda');
        $id_categoria    = $req('id_categoria') !== null ? (int)$req('id_categoria') : null;
        $tipo_movimiento = $req('tipo_movimiento');

        $pagina    = isset($_GET['pagina'])     ? max(1, (int)$_GET['pagina'])     : 1;
        $porPagina = isset($_GET['por_pagina']) ? max(1, (int)$_GET['por_pagina']) : 10;
        if (isset($_POST['por_pagina'])) $porPagina = max(1, (int)$_POST['por_pagina']);
        $offset = ($pagina - 1) * $porPagina;

        $movimientos     = [];
        $totalRegistros  = 0;
        $totalesGlobales = [
            'total_entradas' => 0,
            'total_salidas'  => 0,
            'total_anuladas' => 0,
            'total_cierres'  => 0,
            'total_notas'    => 0,
        ];
        $error_reporte = null;

        try {
            $movimientos     = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, $porPagina, $offset);
            $totalRegistros  = $this->modelo->contarMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento);
            $totalesGlobales = $this->modelo->obtenerTotalesReporte($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento);
        } catch (\Throwable $e) {
            error_log('Reporte error: ' . $e->getMessage());
            $error_reporte = $e->getMessage();
        }

        if (!is_array($movimientos)) $movimientos = [];

        $totalPaginas    = $porPagina > 0 ? (int)ceil($totalRegistros / $porPagina) : 1;
        $paginaActual    = $pagina;
        $porPaginaActual = $porPagina;

        $totalEntradas = (int)($totalesGlobales['total_entradas'] ?? 0);
        $totalSalidas  = (int)($totalesGlobales['total_salidas']  ?? 0);
        $totalAnuladas = (int)($totalesGlobales['total_anuladas'] ?? 0);
        $totalCierres  = (int)($totalesGlobales['total_cierres']  ?? 0);
        $totalNotas    = (int)($totalesGlobales['total_notas']    ?? 0);

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

    /* ============================================================
     *  DETALLE NOTA — AJAX unificado (Entrada / Salida / Anulación / Cierre)
     *  URL: ?url=reportes&action=verDetalleAjax&id=123&tipo=Salida
     * ============================================================ */

    public function verDetalleAjax()
    {
        $val = $this->val;

        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id   = $val->validarId($_GET['id'] ?? null, 'ID de nota');
            $tipo = $val->limpiar($_GET['tipo'] ?? '');

            $tipoBase = $tipo;
            if (stripos($tipoBase, 'Anulación') === 0) {
                $tipoBase = trim(str_replace('Anulación', '', $tipoBase));
            }

            // 1. Intentar buscar como Cierre de Caja
            if (stripos($tipo, 'Cierre') !== false) {
                $modeloCaja = new ReporteCajaModel();
                $cierre = $modeloCaja->obtenerCierrePorId($id);
                if ($cierre) {
                    $detalles = $modeloCaja->obtenerDetalleCierrePorId($id);
                    echo json_encode([
                        'success' => true, 
                        'nota' => array_merge($cierre, ['detalles' => $detalles]),
                        'tipo' => 'Cierre'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            if ($tipoBase === 'Entrada') {
                $modelo = new NotaentradaModel();
                $nota = $modelo->obtenerNotaEntradaPorId($id);
                if (!$nota) throw new Exception('Nota de entrada no encontrada.');

                $nota['cliente']   = $nota['razon_social'] ?? 'N/A';
                $nota['documento'] = $nota['rif'] ?? 'N/A';
                $nota['fecha']     = $nota['fecha_ingreso'] ?? ($nota['fecha'] ?? '');
                $nota['encargado'] = $nota['encargado_nombre'] ?? 'N/A';
                $nota['tipo_base'] = 'Entrada';

                if (!empty($nota['detalles']) && is_array($nota['detalles'])) {
                    foreach ($nota['detalles'] as &$d) {
                        $d['nombre_producto'] = $d['nombre_producto'] ?? 'N/A';
                        $d['categoria']       = $d['nombre_categoria'] ?? 'N/A';
                        $d['cantidad']        = (int)($d['cantidad'] ?? 0);
                        $d['costo_unitario']  = (float)($d['costo_unitario'] ?? 0);

                        $esp = trim((string)($d['especificaciones'] ?? ''));
                        $d['especificaciones'] = $esp !== '' ? $esp : ($nota['descripcion'] ?? '');
                        $d['descripcion']      = $d['especificaciones'];
                    }
                    unset($d);
                }
            } elseif ($tipoBase === 'Salida') {
                $modelo = new notasalidaModel();
                $nota = $modelo->obtenerNotaPorId($id);
                if (!$nota) throw new Exception('Nota de salida no encontrada.');

                $nota['cliente']   = $nota['cliente'] ?? 'N/A';
                $nota['documento'] = $nota['documento'] ?? ($nota['cedula'] ?? ($nota['rif'] ?? 'N/A'));
                $nota['fecha']     = $nota['fecha'] ?? '';
                $nota['encargado'] = $nota['usuario_responsable'] ?? 'N/A';
                $nota['tipo_base'] = 'Salida';

                if (!empty($nota['detalles']) && is_array($nota['detalles'])) {
                    foreach ($nota['detalles'] as &$d) {
                        $d['nombre_producto'] = $d['nombre_producto'] ?? 'N/A';
                        $d['categoria']       = $d['nombre_categoria'] ?? ($d['categoria'] ?? 'N/A');
                        $d['cantidad']        = (int)($d['cantidad'] ?? 0);
                        $d['costo_unitario']  = (float)($d['costo_unitario'] ?? 0);
                    }
                    unset($d);
                }
            } else {
                throw new Exception('Tipo de nota no válido: ' . $tipo);
            }

            echo json_encode([
                'success' => true,
                'nota'    => $nota,
                'tipo'    => $tipoBase
            ], JSON_UNESCAPED_UNICODE);
            exit();

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function verDetalleNotaAjax()
    {
        return $this->verDetalleAjax();
    }

    /* ============================================================
     *  PDF INDIVIDUAL — NOTA DE SALIDA
     * ============================================================ */

    public function exportarNotaSalidaPDF()
    {
        $val = $this->val;
        while (ob_get_level()) { ob_end_clean(); }

        try {
            $id = $val->validarId($_GET['id'] ?? null, 'ID de nota');
            $_GET['id'] = $id;

            $archivoPdf = dirname(__DIR__) . '/view/reportes/generar_pdf_salida.php';
            if (file_exists($archivoPdf)) {
                require_once $archivoPdf;
                exit();
            }
            die("Error: No se encuentra generar_pdf_salida.php");
        } catch (Exception $e) {
            die("Error al generar el PDF: " . $e->getMessage());
        }
    }

    /* ============================================================
     *  PDF INDIVIDUAL — NOTA DE ENTRADA
     * ============================================================ */

    public function exportarNotaEntradaPDF()
    {
        $val = $this->val;
        while (ob_get_level()) { ob_end_clean(); }

        try {
            $id = $val->validarId($_GET['id'] ?? null, 'ID de nota');
            $_GET['id'] = $id;

            $archivoPdf = dirname(__DIR__) . '/view/reportes/generar_pdf_entrada.php';
            if (file_exists($archivoPdf)) {
                require_once $archivoPdf;
                exit();
            }
            die("Error: No se encuentra generar_pdf_entrada.php");
        } catch (Exception $e) {
            die("Error al generar el PDF: " . $e->getMessage());
        }
    }

    /* ============================================================
     *  CIERRE — AJAX (Para el historial del modal en Reportes)
     * ============================================================ */

    public function obtenerCierreAjax()
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) throw new Exception('ID de cierre inválido');

            // 🔥 Usamos el modelo de Caja para traer los datos exactos con los conteos
            $modeloCaja = new ReporteCajaModel();
            
            $cierre = $modeloCaja->obtenerCierrePorId($id);
            if (!$cierre) throw new Exception('Cierre no encontrado');

            $detalles = $modeloCaja->obtenerDetalleCierrePorId($id);

            echo json_encode([
                'success'  => true, 
                'cierre'   => $cierre, 
                'detalles' => $detalles
            ], JSON_UNESCAPED_UNICODE);
            exit;

        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /* ============================================================
     *  EXPORTAR CIERRE PDF (Para el botón del modal en Reportes)
     * ============================================================ */

    public function exportarCierrePDF()
    {
        if (ob_get_level()) ob_clean();
        try {
            $id_cierre = (int)($_GET['id_cierre'] ?? 0);
            if ($id_cierre <= 0) die("Error: ID de cierre no proporcionado.");

            $modeloCaja = new ReporteCajaModel();
            
            $cierre = $modeloCaja->obtenerCierrePorId($id_cierre);
            if (!$cierre) die("Error: Cierre no encontrado.");

            $movimientos = $modeloCaja->obtenerDetalleCierrePDF($id_cierre);
            
            $datos_pdf = [
                'fecha' => date('d/m/Y', strtotime($cierre['fecha_cierre'])),
                'hora_apertura' => $cierre['hora_apertura'] ? date('h:i A', strtotime($cierre['hora_apertura'])) : '—',
                'hora_cierre' => $this->val->hora_es_am_pm($cierre['fecha_cierre'] ?? $cierre['created_at'] ?? null),
                'total_documentos' => $cierre['total_documentos'] ?? 0,
                'total_unidades'   => $cierre['total_unidades'] ?? 0,
                'total_productos'  => $cierre['total_productos_distintos'] ?? 0,
                'total_clientes'   => $cierre['total_clientes'] ?? 0,
                'movimientos'      => $movimientos,
                'encargado'        => $cierre['nombre_usuario'] ?? 'N/A'
            ];

            $archivoPdf = dirname(__DIR__) . '/view/reporteCaja/generar_pdfCajaView.php';
            if (file_exists($archivoPdf)) { require_once $archivoPdf; exit(); }
            die("Error: No se encuentra generar_pdfCajaView.php");

        } catch (Throwable $e) {
            die("Error al generar el PDF del cierre: " . $e->getMessage());
        }
    }

    /* ============================================================
     *  EXPORTAR PDF MOVIMIENTOS (reporte completo)
     * ============================================================ */

    public function exportarPDF()
    {
        $val = $this->val;
        try {
            $tipo = $val->limpiar($_GET['tipo'] ?? 'reporte');

            if ($tipo === 'salida') {
                $archivoPdf = dirname(__DIR__) . '/view/reportes/generar_pdf_salida.php';
                if (file_exists($archivoPdf)) { require_once $archivoPdf; exit(); }
            }
            if ($tipo === 'entrada') {
                $archivoPdf = dirname(__DIR__) . '/view/reportes/generar_pdf_entrada.php';
                if (file_exists($archivoPdf)) { require_once $archivoPdf; exit(); }
            }

            $fecha_inicio = $val->normalizarFecha($val->limpiar($_GET['fecha_inicio'] ?? ($_GET['fecha'] ?? '')), null);
            $fecha_fin    = $val->normalizarFecha($val->limpiar($_GET['fecha_fin'] ?? ''), null);
            $busqueda     = $val->limpiar($_GET['busqueda'] ?? '') ?: null;
            $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
            $tipo_movimiento = $val->limpiar($_GET['tipo_movimiento'] ?? '') ?: null;

            $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
            if (!is_array($movimientos)) $movimientos = [];

            $totalEntradas = 0; $totalSalidas = 0; $totalAnuladas = 0; $totalCierres = 0;
            $movimientosPDF = []; $cierresVistos = []; $hora_cierre_pdf = null;

            foreach ($movimientos as $m) {
                $t = $m['tipo_movimiento'] ?? '';
                $esAnulacion = (strpos($t, 'Anulación') === 0 || stripos($t, 'anulad') !== false);
                $esCierre = ($t === 'Cierre de Caja' || ($m['tipo_origen'] ?? '') === 'cierre');

                if ($esCierre) {
                    if (!in_array($m['id_documento'], $cierresVistos)) {
                        $cierresVistos[] = $m['id_documento'];
                        $totalCierres++;
                        if (!$hora_cierre_pdf && !empty($m['hora_cierre'])) $hora_cierre_pdf = $m['hora_cierre'];
                        $m['nombre_cliente']   = 'Cierre Global de Turno';
                        $m['productos_lista']  = 'Global';
                        $m['total_productos']  = 'Global';
                        $m['total_categorias'] = 'Global';
                        $m['total_unidades']   = (int)($m['total_unidades'] ?? 0);
                        $movimientosPDF[] = $m;
                    }
                } else {
                    if ($esAnulacion) $totalAnuladas++;
                    elseif (stripos($t, 'Entrada') !== false) $totalEntradas++;
                    elseif (stripos($t, 'Salida') !== false)  $totalSalidas++;
                    $movimientosPDF[] = $m;
                }
            }

            $filtrosStr = [];
            if (!empty($fecha_inicio)) $filtrosStr[] = "Desde: " . date('d/m/Y', strtotime($fecha_inicio));
            if (!empty($fecha_fin))    $filtrosStr[] = "Hasta: " . date('d/m/Y', strtotime($fecha_fin));

            $datos_pdf = [
                'fecha_reporte' => date('d/m/Y h:i A'),
                'filtros'       => empty($filtrosStr) ? 'Todos los movimientos' : implode(' | ', $filtrosStr),
                'movimientos'   => $movimientosPDF,
                'totalEntradas' => $totalEntradas,
                'totalSalidas'  => $totalSalidas,
                'totalAnuladas' => $totalAnuladas,
                'totalCierres'  => $totalCierres,
                'hora_cierre'   => $val->hora_es_am_pm($hora_cierre_pdf),
            ];

            $archivoPdf = dirname(__DIR__) . '/view/reportes/generar_pdfReportesView.php';
            if (file_exists($archivoPdf)) { require_once $archivoPdf; exit(); }
            die("Error: No se encuentra generar_pdfReportesView.php en $archivoPdf");

        } catch (Exception $e) {
            die("Error al generar el PDF: " . $e->getMessage());
        }
    }

    /* ============================================================
     *  EXPORTAR CSV
     * ============================================================ */

    public function exportarCSV()
    {
        $val = $this->val;
        try {
            $fecha_inicio = $val->normalizarFecha($val->limpiar($_GET['fecha_inicio'] ?? ($_GET['fecha'] ?? '')), null);
            $fecha_fin    = $val->normalizarFecha($val->limpiar($_GET['fecha_fin'] ?? ''), null);
            $busqueda     = $val->limpiar($_GET['busqueda'] ?? '') ?: null;
            $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
            $tipo_movimiento = $val->limpiar($_GET['tipo_movimiento'] ?? '') ?: null;

            $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);
            if (!is_array($movimientos)) $movimientos = [];

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="Reporte_Movimientos_' . date('Ymd_His') . '.csv"');

            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['N° NOTA', 'FECHA', 'HORA', 'CLIENTE / PROVEEDOR', 'TIPO', 'PRODUCTOS', 'CATEGORÍAS', 'UNIDADES', 'ENCARGADO']);

            $totalEntradas = 0; $totalSalidas = 0; $totalAnuladas = 0; $totalCierres = 0;
            $cierresVistos = [];

            foreach ($movimientos as $m) {
                $t = $m['tipo_movimiento'] ?? '';
                $esAnulacion = (strpos($t, 'Anulación') === 0 || stripos($t, 'anulad') !== false);
                $esCierre = ($t === 'Cierre de Caja' || ($m['tipo_origen'] ?? '') === 'cierre');

                $fechaMov = isset($m['fecha_movimiento']) && $m['fecha_movimiento'] ? date('d/m/Y', strtotime($m['fecha_movimiento'])) : '';
                $horaMov  = isset($m['fecha_movimiento']) && $m['fecha_movimiento'] ? date('h:i:s A', strtotime($m['fecha_movimiento'])) : '—';

                if ($esCierre) {
                    if (!in_array($m['id_documento'], $cierresVistos)) {
                        $cierresVistos[] = $m['id_documento'];
                        $totalCierres++;
                        fputcsv($output, [
                            '#' . $m['id_documento'], $fechaMov, $horaMov,
                            'Cierre Global de Turno', 'Cierre de Caja',
                            'Global', 'Global',
                            (int)($m['total_unidades'] ?? 0),
                            $m['usuario_activo'] ?? 'N/A'
                        ]);
                    }
                } else {
                    if ($esAnulacion) $totalAnuladas++;
                    elseif (stripos($t, 'Entrada') !== false) $totalEntradas++;
                    elseif (stripos($t, 'Salida') !== false)  $totalSalidas++;
                    fputcsv($output, [
                        '#' . $m['id_documento'], $fechaMov, $horaMov,
                        $m['nombre_cliente'] ?? 'N/A', $t,
                        (int)($m['total_productos'] ?? 0) . ' Prod.',
                        (int)($m['total_categorias'] ?? 0) . ' Cat.',
                        (int)($m['total_unidades'] ?? 0),
                        $m['usuario_activo'] ?? 'N/A'
                    ]);
                }
            }

            fputcsv($output, ['', '', '', '', '', '', '', '', '']);
            fputcsv($output, ['RESUMEN', 'Entradas:', $totalEntradas, 'Salidas:', $totalSalidas, 'Anuladas:', $totalAnuladas, 'Cierres: ' . $totalCierres]);
            fclose($output);
            exit();

        } catch (Exception $e) {
            die("Error al generar el CSV: " . $e->getMessage());
        }
    }
}