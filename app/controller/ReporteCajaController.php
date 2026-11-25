<?php
// app/controller/ReporteCajaController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\ReporteCajaModel;
use App\Pirotecnicafenix\Model\notasalidaModel;
use App\Pirotecnicafenix\Traits\ValidadorTrait;
use DateTime;
use Exception;
use Throwable;

class ReporteCajaController
{
    private $model;
    private $modeloNota;
    private $val;

    public function __construct($db = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rutaTz = dirname(__DIR__) . '/Config/timezone.php';
        if (file_exists($rutaTz)) {
            require_once $rutaTz;
        }

        $this->val = new class {
            use ValidadorTrait;
        };
        $this->model = new ReporteCajaModel();
        $this->modeloNota = new notasalidaModel();
    }

    private function esAdmin(): bool
    {
        return (isset($_SESSION['id_rol']) && (int)$_SESSION['id_rol'] === 1)
            || (strtolower(trim((string)($_SESSION['nombre_rol'] ?? ''))) === 'administrador');
    }

    private function obtenerIdUsuarioSesion(): ?int
    {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_usuario'] === '' || (int)$_SESSION['id_usuario'] <= 0) {
            return null;
        }
        return (int)$_SESSION['id_usuario'];
    }

    private function normalizarFechaFiltro($fechaInput, string $default): string
    {
        $limpia = $this->val->limpiar($fechaInput ?? '');
        if ($limpia === '') {
            return $default;
        }
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $limpia, $m)) {
            $limpia = "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return $this->val->normalizarFecha($limpia, $default) ?? $default;
    }

    private function extraerHora24DesdeMovimiento(array $mov): ?string
    {
        $raw = trim((string)($mov['fecha_movimiento'] ?? ''));
        if ($raw === '') {
            return null;
        }

        foreach (['d/m/Y h:i A', 'd/m/Y H:i:s', 'Y-m-d H:i:s', 'Y-m-d h:i A'] as $formato) {
            $dt = DateTime::createFromFormat($formato, $raw);
            if ($dt instanceof DateTime) {
                return $dt->format('H:i:s');
            }
        }

        $normalizada = preg_replace('#^(\d{2})/(\d{2})/(\d{4})#', '$3-$2-$1', $raw);
        $ts = strtotime($normalizada);
        return $ts !== false ? date('H:i:s', $ts) : null;
    }

    private function formatearHora12(?string $hora24): string
    {
        if (empty($hora24)) {
            return '—';
        }
        $ts = strtotime((string)$hora24);
        return $ts !== false ? date('h:i A', $ts) : '—';
    }

    private function obtenerMovimientosUsuarioPendientes(?int $idUsuario): array
    {
        if ($idUsuario === null || $idUsuario <= 0) {
            return [];
        }

        $movimientos = $this->model->obtenerSalidasCajero($idUsuario, null, null, null, 9999, 0);

        return array_map(function ($m) {
            $m['productos_lista'] = $m['nombre_producto'] ?? 'Sin productos';
            $m['total_unidades']  = (int)($m['cantidad'] ?? 0);
            return $m;
        }, $movimientos);
    }

    private function calcularMetricasMovimientos(array $movimientos): array
    {
        $totalDocs = 0;
        $totalUnds = 0;
        $totalProds = 0;
        $totalCats = 0;
        $totalClientes = 0;
        $horaApertura = null;

        $notasVistas = [];
        $prodsVistos = [];
        $catsVistas = [];
        $clientesVistos = [];

        if (!empty($movimientos)) {
            // Como $movimientos viene ordenado DESC, la última fila corresponde a la primera nota registrada
            $primeraVenta = end($movimientos);
            if (is_array($primeraVenta)) {
                $horaApertura = $this->extraerHora24DesdeMovimiento($primeraVenta);
            }
            reset($movimientos);

            foreach ($movimientos as $m) {
                $idDoc = $m['id_documento'] ?? null;
                if ($idDoc !== null && !in_array($idDoc, $notasVistas, true)) {
                    $notasVistas[] = $idDoc;
                    $totalDocs++;
                }

                $totalUnds += (int)($m['cantidad'] ?? 0);

                $prodRaw = (string)($m['nombre_producto'] ?? '');
                foreach (explode(',', $prodRaw) as $parteProd) {
                    $prodLimpio = trim(preg_replace('/\s*\(x\d+\)\s*$/i', '', $parteProd));
                    if ($prodLimpio !== '' && $prodLimpio !== 'Sin productos' && !in_array($prodLimpio, $prodsVistos, true)) {
                        $prodsVistos[] = $prodLimpio;
                        $totalProds++;
                    }
                }

                $catRaw = (string)($m['categoria'] ?? 'Sin Categoría');
                foreach (explode(',', $catRaw) as $parteCat) {
                    $catLimpia = trim($parteCat);
                    if ($catLimpia !== '' && !in_array($catLimpia, $catsVistas, true)) {
                        $catsVistas[] = $catLimpia;
                        $totalCats++;
                    }
                }

                $cliente = trim((string)($m['nombre_cliente'] ?? 'Sin cliente'));
                if ($cliente !== '' && !in_array($cliente, $clientesVistos, true)) {
                    $clientesVistos[] = $cliente;
                    $totalClientes++;
                }
            }
        }

        return [
            'total_documentos' => $totalDocs,
            'total_unidades'   => $totalUnds,
            'total_productos'  => $totalProds,
            'total_categorias' => $totalCats,
            'total_clientes'   => $totalClientes,
            'hora_apertura'    => $horaApertura,
        ];
    }

    public function index()
    {
        $val = $this->val;
        $fecha = $this->normalizarFechaFiltro($_GET['fecha'] ?? '', date('Y-m-d'));
        $busqueda = $val->limpiar($_GET['busqueda'] ?? '');
        $por_pagina = max(1, (int)($_GET['por_pagina'] ?? 10));
        $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset = ($pagina_actual - 1) * $por_pagina;

        $id_usuario_sesion = $this->obtenerIdUsuarioSesion();
        $todosMovimientos = $this->obtenerMovimientosUsuarioPendientes($id_usuario_sesion);

        $ultimoCierreUsuario = $id_usuario_sesion ? $this->model->obtenerUltimoCierreUsuario($id_usuario_sesion) : null;
        $cajaCerrada = empty($todosMovimientos) && !empty($ultimoCierreUsuario);

        if ($busqueda !== '') {
            $needle = mb_strtolower($busqueda, 'UTF-8');
            $todosMovimientos = array_values(array_filter($todosMovimientos, function ($m) use ($needle) {
                $haystack = mb_strtolower(
                    ($m['nombre_producto'] ?? '') . ' ' .
                    ($m['categoria'] ?? '') . ' ' .
                    ($m['nombre_cliente'] ?? '') . ' ' .
                    ($m['usuario_activo'] ?? '') . ' ' .
                    ($m['id_documento'] ?? ''),
                    'UTF-8'
                );
                return mb_strpos($haystack, $needle) !== false;
            }));
        }

        $totalRegistros  = count($todosMovimientos);
        $totalDocumentos = count(array_unique(array_filter(array_column($todosMovimientos, 'id_documento'))));
        $totalUnidades   = array_sum(array_map(function ($m) {
            return (int)($m['cantidad'] ?? 0);
        }, $todosMovimientos));

        $movimientos = array_slice($todosMovimientos, $offset, $por_pagina);

        $porPaginaActual = $por_pagina;
        $paginaActual = $pagina_actual;
        $totalPaginas = max(1, (int)ceil($totalRegistros / $por_pagina));
        $fechaFiltro = $fecha;

        require_once dirname(__DIR__) . '/view/reporteCaja/reporteCajaView.php';
    }

    public function historial()
    {
        if (!$this->esAdmin()) {
            header('Location: ?url=dashboard');
            exit();
        }
        try {
            $cierres = $this->model->obtenerHistorialCierres();
        } catch (Exception $e) {
            $cierres = [];
            $error = $e->getMessage();
        }
        require_once dirname(__DIR__) . '/view/reporteCaja/historialCierresView.php';
    }

    public function obtenerCierreDiaAjax()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_usuario_sesion = $this->obtenerIdUsuarioSesion();
            $movimientos = $this->obtenerMovimientosUsuarioPendientes($id_usuario_sesion);
            $metricas = $this->calcularMetricasMovimientos($movimientos);

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
                    'cantidad'         => (int)$m['cantidad'],
                    'total_productos'  => (int)($m['total_productos'] ?? 0),
                    'total_categorias' => (int)($m['total_categorias'] ?? 0),
                ];
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'hora_apertura'    => $this->formatearHora12($metricas['hora_apertura']),
                    'hora_cierre'      => date('h:i A'),
                    'total_documentos' => $metricas['total_documentos'],
                    'total_unidades'   => $metricas['total_unidades'],
                    'total_productos'  => $metricas['total_productos'],
                    'total_categorias' => $metricas['total_categorias'],
                    'total_clientes'   => $metricas['total_clientes'],
                    'detalle'          => $detalle
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function confirmarCierreCajaAjax()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_usuario = $this->obtenerIdUsuarioSesion();
            if ($id_usuario === null) {
                throw new Exception('Sesión no válida');
            }

            $fechaInput = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
            $fecha_solicitada = $this->normalizarFechaFiltro($fechaInput, date('Y-m-d'));

            $movimientos = $this->obtenerMovimientosUsuarioPendientes($id_usuario);
            if (empty($movimientos)) {
                throw new Exception('No hay notas de salida pendientes por cerrar para este usuario.');
            }

            $metricas = $this->calcularMetricasMovimientos($movimientos);

            $guardado = $this->model->guardarCierreCaja(
                $fecha_solicitada,
                $id_usuario,
                $metricas['total_documentos'],
                $metricas['total_unidades'],
                $metricas['total_categorias'],
                $metricas['hora_apertura'],
                $metricas['total_productos'],
                $metricas['total_clientes']
            );

            if (!$guardado) {
                throw new Exception('No se pudo guardar el cierre');
            }

            $cierreGuardado = $this->model->obtenerUltimoCierreUsuario($id_usuario);
            $id_cierre_nuevo = $cierreGuardado ? (int)$cierreGuardado['id_cierre'] : 0;

            $encargadoSesion = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
            if ($encargadoSesion === '') {
                $encargadoSesion = $this->model->obtenerNombreUsuario($id_usuario);
            }

            $horaAperturaFmt = $this->formatearHora12($metricas['hora_apertura']);
            $horaCierreFmt = date('h:i A');

            echo json_encode([
                'success' => true,
                'mensaje' => 'Cierre del día guardado correctamente',
                'data' => [
                    'id_cierre'        => $id_cierre_nuevo,
                    'fecha'            => $fecha_solicitada,
                    'hora_apertura'    => $horaAperturaFmt,
                    'hora_cierre'      => $horaCierreFmt,
                    'total_documentos' => $metricas['total_documentos'],
                    'total_unidades'   => $metricas['total_unidades'],
                    'total_productos'  => $metricas['total_productos'],
                    'total_categorias' => $metricas['total_categorias'],
                    'total_clientes'   => $metricas['total_clientes'],
                    'encargado'        => $encargadoSesion,
                    'detalle'          => array_map(function ($m) {
                        return [
                            'id_documento'     => $m['id_documento'],
                            'fecha_movimiento' => $m['fecha_movimiento'],
                            'nombre_cliente'   => $m['nombre_cliente'],
                            'tipo_cliente'     => $m['tipo_cliente'] ?? 'Natural',
                            'nombre_producto'  => $m['nombre_producto'],
                            'categoria'        => $m['categoria'] ?? 'Sin categoría',
                            'cantidad'         => (int)$m['cantidad'],
                            'usuario_activo'   => $m['usuario_activo']
                        ];
                    }, $movimientos)
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar el cierre: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function verDetalleAjax()
    {
        $val = $this->val;
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = $val->validarId($_GET['id'] ?? $_POST['id'] ?? null, 'ID de registro');
            $tipo = strtolower(trim((string)($_GET['tipo'] ?? $_POST['tipo'] ?? 'nota')));

            if ($tipo === 'cierre') {
                $cierre = $this->model->obtenerCierrePorId($id);
                if (!$cierre) {
                    throw new Exception('Cierre no encontrado');
                }
                $detalles = $this->model->obtenerDetalleCierrePorId($id);
                echo json_encode([
                    'success' => true,
                    'nota'    => array_merge($cierre, ['detalles' => $detalles]),
                    'tipo'    => 'Cierre'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $nota = $this->modeloNota->obtenerNotaPorId($id);
            if ($nota) {
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
                    }
                    unset($d);
                }

                echo json_encode([
                    'success' => true,
                    'nota'    => $nota,
                    'tipo'    => 'Salida'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            throw new Exception('Nota de salida no encontrada');
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function exportarPDF()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $rutaTz = dirname(__DIR__) . '/Config/timezone.php';
        if (file_exists($rutaTz)) {
            require_once $rutaTz;
        }

        try {
            $id_cierre = isset($_GET['id_cierre']) ? (int)$_GET['id_cierre'] : 0;
            $fecha = $this->normalizarFechaFiltro($_GET['fecha'] ?? '', date('Y-m-d'));
            $id_usuario = $this->obtenerIdUsuarioSesion();

            if ($id_cierre <= 0 && $id_usuario) {
                $cierreGuardado = $this->model->obtenerUltimoCierreUsuario($id_usuario);
                if ($cierreGuardado) {
                    $id_cierre = (int)$cierreGuardado['id_cierre'];
                }
            }

            if ($id_cierre > 0) {
                $cierre = $this->model->obtenerCierrePorId($id_cierre);
                if (!$cierre) {
                    die("Error: Cierre no encontrado.");
                }

                $movimientos = $this->model->obtenerDetalleCierrePorId($id_cierre);
                $metricasCierre = $this->calcularMetricasMovimientos($movimientos);

                $horaAperturaRaw = !empty($cierre['hora_apertura'])
                    ? $cierre['hora_apertura']
                    : $metricasCierre['hora_apertura'];

                $datos_pdf = [
                    'fecha'            => date('d/m/Y', strtotime($cierre['fecha_cierre'])),
                    'hora_apertura'    => $this->formatearHora12($horaAperturaRaw),
                    'hora_cierre'      => $this->val->hora_es_am_pm($cierre['created_at'] ?? $cierre['fecha_cierre'] ?? null),
                    'total_documentos' => (int)$cierre['total_documentos'],
                    'total_unidades'   => (int)$cierre['total_unidades'],
                    'total_productos'  => (int)$cierre['total_productos_distintos'],
                    'total_clientes'   => (int)$cierre['total_clientes'],
                    'movimientos'      => $movimientos,
                    'encargado'        => $cierre['nombre_usuario']
                ];
            } else {
                $movimientosDia = $this->obtenerMovimientosUsuarioPendientes($id_usuario);
                $metricasDia = $this->calcularMetricasMovimientos($movimientosDia);
                $usuario_activo = $this->model->obtenerNombreUsuario($id_usuario);

                $datos_pdf = [
                    'fecha'            => date('d/m/Y', strtotime($fecha)),
                    'hora_apertura'    => $metricasDia['hora_apertura'] ? $this->formatearHora12($metricasDia['hora_apertura']) : 'Sin ventas',
                    'hora_cierre'      => '—',
                    'total_documentos' => $metricasDia['total_documentos'],
                    'total_unidades'   => $metricasDia['total_unidades'],
                    'total_productos'  => $metricasDia['total_productos'],
                    'total_clientes'   => $metricasDia['total_clientes'],
                    'movimientos'      => $movimientosDia,
                    'encargado'        => $usuario_activo
                ];
            }

            $archivoPdf = dirname(__DIR__) . '/view/reporteCaja/generar_pdfCajaView.php';
            if (file_exists($archivoPdf)) {
                require_once $archivoPdf;
                exit();
            }
            die("Error: No se encuentra generar_pdfCajaView.php en $archivoPdf");
        } catch (Throwable $e) {
            die("Error al generar el PDF: " . $e->getMessage());
        }
    }

    public function exportarCSV()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $rutaTz = dirname(__DIR__) . '/Config/timezone.php';
        if (file_exists($rutaTz)) {
            require_once $rutaTz;
        }

        $id_cierre = isset($_GET['id_cierre']) ? (int)$_GET['id_cierre'] : 0;
        $id_usuario = $this->obtenerIdUsuarioSesion();
        $fecha = $this->normalizarFechaFiltro($_GET['fecha'] ?? '', date('Y-m-d'));

        try {
            if ($id_cierre <= 0 && $id_usuario) {
                $cierreGuardado = $this->model->obtenerUltimoCierreUsuario($id_usuario);
                if ($cierreGuardado) {
                    $id_cierre = (int)$cierreGuardado['id_cierre'];
                }
            }

            if ($id_cierre > 0) {
                $cierre = $this->model->obtenerCierrePorId($id_cierre);
                if (!$cierre) {
                    die("Error: Cierre no encontrado.");
                }
                $movimientos = $this->model->obtenerDetalleCierreCSVPorId($id_cierre);
                $fechaArchivo = $cierre['fecha_cierre'];
            } else {
                $movimientos = $this->obtenerMovimientosUsuarioPendientes($id_usuario);
                $fechaArchivo = $fecha;
            }

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="Cierre_Caja_' . $fechaArchivo . '.csv"');
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID Nota', 'Fecha/Hora', 'Cliente', 'Tipo Cliente', 'Productos (Cantidad)', 'Categorías', 'Unidades', 'Encargado']);
            $totalUnidades = 0;

            foreach ($movimientos as $m) {
                $cant = (int)($m['cantidad'] ?? 0);
                $totalUnidades += $cant;
                fputcsv($output, [
                    '#' . ($m['id_documento'] ?? ''),
                    $m['fecha_movimiento'] ?? '',
                    $m['nombre_cliente'] ?? '',
                    $m['tipo_cliente'] ?? 'Natural',
                    $m['nombre_producto'] ?? '',
                    $m['categoria'] ?? '',
                    $cant,
                    $m['usuario_activo'] ?? ''
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