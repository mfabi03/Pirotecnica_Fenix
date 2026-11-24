<?php
// app/Controller/ReporteCajaController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\ReporteCajaModel;
use App\Pirotecnicafenix\Model\notasalidaModel;
use App\Pirotecnicafenix\Traits\ValidadorTrait;
use Exception;
use Throwable;

class ReporteCajaController
{
    private $model;
    private $modeloNota;
    private $val;

    public function __construct($db = null)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->val = new class { use ValidadorTrait; };
        $this->model = new ReporteCajaModel();
        $this->modeloNota = new notasalidaModel();
    }

    private function esAdmin()
    {
        return (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1)
            || (strtolower($_SESSION['nombre_rol'] ?? '') === 'administrador');
    }

    public function index()
    {
        $fecha = $this->val->normalizarFecha($_GET['fecha'] ?? '', date('Y-m-d'));
        $busqueda = $this->val->limpiar($_GET['busqueda'] ?? '');
        $por_pagina = (int)($_GET['por_pagina'] ?? 10);
        $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset = ($pagina_actual - 1) * $por_pagina;
        $id_usuario_filtro = (int)($_SESSION['id_usuario'] ?? 0);

        $cajaCerrada = $this->model->existeCierreCaja($fecha, $id_usuario_filtro);
        if ($cajaCerrada) {
            $movimientos = []; $totalRegistros = 0; $totalDocumentos = 0; $totalUnidades = 0;
        } else {
            $movimientos = $this->model->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, null, $por_pagina, $offset);
            $totalRegistros = count($movimientos);
            $totalDocumentos = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnidades = array_sum(array_column($movimientos, 'cantidad'));
        }
        $porPaginaActual = $por_pagina;
        $paginaActual = $pagina_actual;
        $totalPaginas = $por_pagina > 0 ? ceil($totalRegistros / $por_pagina) : 1;
        $fechaFiltro = $fecha;

        require_once dirname(__DIR__) . '/view/reporteCaja/reporteCajaView.php';
    }

    public function historial()
    {
        if (!$this->esAdmin()) { header('Location: ?url=dashboard'); exit(); }
        try { $cierres = $this->model->obtenerHistorialCierres(); }
        catch (Exception $e) { $cierres = []; $error = $e->getMessage(); }
        require_once dirname(__DIR__) . '/view/reporteCaja/historialCierresView.php';
    }

    public function obtenerCierreDiaAjax()
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
            $fechaInput = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
            $fecha = $this->val->normalizarFecha($fechaInput, date('Y-m-d'));

            $movimientos = $this->model->obtenerSalidasCajero($id_usuario, $fecha, null, null, 9999, 0);
            
            $totalDocs = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnds = array_sum(array_column($movimientos, 'cantidad'));

            $detalle = [];
            foreach ($movimientos as $m) {
                $detalle[] = [
                    'id_documento'     => $m['id_documento'],
                    'fecha_movimiento' => $m['fecha_movimiento'],
                    'usuario_activo'   => $m['usuario_activo'],
                    'nombre_cliente'   => $m['nombre_cliente'],
                    'tipo_cliente'     => $m['tipo_cliente'] ?? 'Natural',
                    'nombre_producto'  => $m['nombre_producto'],
                    'categoria'        => $m['categoria'],
                    'cantidad'         => $m['cantidad'],
                    'total_productos'  => $m['total_productos'] ?? 0,
                    'total_categorias' => $m['total_categorias'] ?? 0,
                ];
            }
            echo json_encode(['success' => true, 'data' => [
                'total_documentos' => $totalDocs, 
                'total_unidades' => $totalUnds, 
                'detalle' => $detalle
            ]], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    public function confirmarCierreCajaAjax()
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $idSesion = $_SESSION['id_usuario'] ?? null;
            if ($idSesion === null) throw new Exception('Sesión no válida');

            $fechaInput = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
            $fecha_solicitada = $this->val->normalizarFecha($fechaInput, date('Y-m-d'));
            $id_usuario = (int)$idSesion;

            if ($this->model->existeCierreCaja($fecha_solicitada, $id_usuario)) {
                throw new Exception('Esta caja ya fue cerrada hoy.');
            }

            $movimientos = $this->model->obtenerSalidasParaCierre($id_usuario, $fecha_solicitada);
            $total_docs = 0; $total_unds = 0; $total_prods = 0; $total_cats = 0; $total_clientes = 0; $hora_apertura = null;
            $notas_vistas = []; $prods_vistos = []; $cats_vistas = []; $clientes_vistos = [];

            if (!empty($movimientos)) {
                $primeraVenta = end($movimientos);
                $hora_apertura = date('H:i:s', strtotime($primeraVenta['fecha_movimiento']));
                foreach ($movimientos as $m) {
                    if (!in_array($m['id_documento'], $notas_vistas)) { $notas_vistas[] = $m['id_documento']; $total_docs++; }
                    $total_unds += (int)$m['cantidad'];
                    $prod_limpio = preg_replace('/\s*\(x\d+\)/', '', $m['nombre_producto']);
                    if (!in_array($prod_limpio, $prods_vistos)) { $prods_vistos[] = $prod_limpio; $total_prods++; }
                    $cat_actual = $m['categoria'] ?? 'Sin Categoría';
                    if (!in_array($cat_actual, $cats_vistas)) { $cats_vistas[] = $cat_actual; $total_cats++; }
                    if (!in_array($m['nombre_cliente'], $clientes_vistos)) { $clientes_vistos[] = $m['nombre_cliente']; $total_clientes++; }
                }
            }

            $guardado = $this->model->guardarCierreCaja(
                $fecha_solicitada, $id_usuario, $total_docs, $total_unds, $total_cats, $hora_apertura, $total_prods, $total_clientes
            );

            if (!$guardado) throw new Exception('No se pudo guardar el cierre');

            $cierreGuardado = $this->model->obtenerCierrePorFechaYUsuario($fecha_solicitada, $id_usuario);
            $id_cierre_nuevo = $cierreGuardado ? $cierreGuardado['id_cierre'] : 0;

            echo json_encode(['success' => true, 'mensaje' => 'Cierre del día guardado correctamente', 'data' => [
                'id_cierre' => $id_cierre_nuevo,
                'fecha' => $fecha_solicitada,
                'hora_apertura' => $hora_apertura ? date('h:i A', strtotime($hora_apertura)) : '—',
                'total_documentos' => $total_docs, 'total_unidades' => $total_unds,
                'total_productos' => $total_prods, 'total_categorias' => $total_cats,
                'total_clientes' => $total_clientes,
                'encargado' => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')),
                'detalle' => array_map(function($m) {
                    return [
                        'id_documento' => $m['id_documento'], 'fecha_movimiento' => $m['fecha_movimiento'],
                        'nombre_cliente' => $m['nombre_cliente'], 'tipo_cliente' => $m['tipo_cliente'] ?? 'Natural',
                        'nombre_producto' => $m['nombre_producto'], 'categoria' => $m['categoria'] ?? 'Sin categoría',
                        'cantidad' => $m['cantidad'], 'usuario_activo' => $m['usuario_activo']
                    ];
                }, $movimientos)
            ]], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al guardar el cierre: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function verDetalleAjax()
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) throw new Exception('ID inválido');
            
            // 1. Intentar buscar como Cierre de Caja
            $cierre = $this->model->obtenerCierrePorId($id);
            if ($cierre) {
                $detalles = $this->model->obtenerDetalleCierrePorId($id);
                echo json_encode([
                    'success' => true, 
                    'nota' => array_merge($cierre, ['detalles' => $detalles])
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 2. Intentar buscar como Nota de Salida
            $nota = $this->modeloNota->obtenerNotaPorId($id);
            if ($nota) {
                echo json_encode(['success' => true, 'nota' => $nota], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Registro no encontrado');
            }
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    public function exportarPDF()
    {
        if (ob_get_level()) ob_clean();
        try {
            $id_cierre = isset($_GET['id_cierre']) ? (int)$_GET['id_cierre'] : 0;
            $fecha = $this->val->normalizarFecha($_GET['fecha'] ?? '', date('Y-m-d'));
            $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);

            if ($id_cierre <= 0) {
                $cierreGuardado = $this->model->obtenerCierrePorFechaYUsuario($fecha, $id_usuario);
                if ($cierreGuardado) $id_cierre = (int)$cierreGuardado['id_cierre'];
            }

            if ($id_cierre > 0) {
                $cierre = $this->model->obtenerCierrePorId($id_cierre);
                if (!$cierre) die("Error: Cierre no encontrado.");
                
                $movimientos = $this->model->obtenerDetalleCierrePorId($id_cierre);
                
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
            } else {
                $usuario_activo = $this->model->obtenerNombreUsuario($id_usuario);
                $datos_pdf = [
                    'fecha' => $fecha ? date('d/m/Y', strtotime($fecha)) : date('d/m/Y'),
                    'hora_apertura' => 'Sin ventas',
                    'total_documentos' => 0, 
                    'total_unidades' => 0,
                    'total_productos' => 0, 
                    'total_clientes' => 0,
                    'movimientos' => [], 
                    'encargado' => $usuario_activo
                ];
            }
            
            require_once dirname(__DIR__) . '/view/reporteCaja/generar_pdfCajaView.php';
            exit();
        } catch (Throwable $e) {
            die("Error al generar el PDF: " . $e->getMessage());
        }
    }

    public function exportarCSV()
    {
        if (ob_get_level()) ob_clean();
        $id_cierre = isset($_GET['id_cierre']) ? (int)$_GET['id_cierre'] : 0;
        $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
        $fecha = $this->val->normalizarFecha($_GET['fecha'] ?? '', date('Y-m-d'));

        try {
            if ($id_cierre <= 0) {
                $cierreGuardado = $this->model->obtenerCierrePorFechaYUsuario($fecha, $id_usuario);
                if ($cierreGuardado) $id_cierre = (int)$cierreGuardado['id_cierre'];
            }

            if ($id_cierre > 0) {
                $cierre = $this->model->obtenerCierrePorId($id_cierre);
                if (!$cierre) die("Error: Cierre no encontrado.");
                $movimientos = $this->model->obtenerDetalleCierreCSVPorId($id_cierre);
                $fechaArchivo = $cierre['fecha_cierre'];
            } else {
                $movimientos = $this->model->obtenerSalidasCajero($id_usuario, $fecha, null, null, 9999, 0);
                $fechaArchivo = $fecha;
            }

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="Cierre_Caja_' . $fechaArchivo . '.csv"');
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID Nota', 'Fecha/Hora', 'Cliente', 'Tipo Cliente', 'Productos (Cantidad)', 'Categorías', 'Unidades', 'Encargado']);
            $totalUnidades = 0;
            
            foreach ($movimientos as $m) {
                $totalUnidades += (int)$m['cantidad'];
                fputcsv($output, [
                    '#' . $m['id_documento'], 
                    $m['fecha_movimiento'], 
                    $m['nombre_cliente'],
                    $m['tipo_cliente'], 
                    $m['nombre_producto'], 
                    $m['categoria'], 
                    $m['cantidad'], 
                    $m['usuario_activo']
                ]);
            }
            fputcsv($output, ['', '', '', '', '', 'TOTALES', $totalUnidades, '']);
            fclose($output);
            exit();
        } catch (Throwable $e) {
            die("Error al generar el CSV: " . $e->getMessage());
        }
    }
}