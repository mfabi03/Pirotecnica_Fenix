<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\ReporteCajaModel;
use App\Pirotecnicafenix\Model\notasalidaModel;
use Exception;
use PDO;

class ReporteCajaController {
    private $db;
    private $model;
    private $modeloNota;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new ReporteCajaModel($db);
        $this->modeloNota = new notasalidaModel($db);
    }

    // Vista principal
    public function index() {
        $esAdmin = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (strtolower($_SESSION['nombre_rol'] ?? '') === 'administrador');
        
        // Fecha vacía por defecto
        $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : null;
        $busqueda = $_GET['busqueda'] ?? null;
        $id_categoria = $_GET['id_categoria'] ?? null;

        $por_pagina = (int)($_GET['por_pagina'] ?? 10);
        $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset = ($pagina_actual - 1) * $por_pagina;

        // Obtenemos el último cierre global
        $cierreGlobal = $this->model->obtenerUltimoCierreCaja($fecha, null);
        $ultimoIdCierre = ($cierreGlobal && $cierreGlobal['ultimo_id_nota_salida']) ? (int)$cierreGlobal['ultimo_id_nota_salida'] : 0;

        $id_usuario_filtro = $esAdmin ? null : ($_SESSION['id_usuario'] ?? null);
        $movimientos = $this->model->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria, $por_pagina, $offset);
        
        // Filtramos notas ya cobradas
        if ($ultimoIdCierre > 0) {
            $movimientos = array_filter($movimientos, function($m) use ($ultimoIdCierre) {
                return (int)$m['id_documento'] > $ultimoIdCierre;
            });
        }
        $movimientos = array_values($movimientos);

        $totalDocumentos = count(array_unique(array_column($movimientos, 'id_documento')));
        $totalUnidades = array_sum(array_column($movimientos, 'cantidad'));
        
        $totalRegistros = $totalDocumentos;
        $porPaginaActual = $por_pagina;
        $paginaActual = $pagina_actual;
        $totalPaginas = $por_pagina > 0 ? ceil($totalRegistros / $por_pagina) : 1;
        
        // Si no hay movimientos, la caja se considera cerrada/limpia
        $cajaCerrada = ($totalDocumentos === 0);
        $fechaFiltro = $fecha ?? ''; 
        
        $db = $this->db;

        $viewFile = dirname(__DIR__) . '/view/reporteCaja/reporteCajaView.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Error: No se encuentra la vista en $viewFile");
        }
    }

    // AJAX Modal
    public function obtenerCierreDiaAjax() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $esAdmin = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1);
            $id_usuario = $esAdmin ? null : ($_SESSION['id_usuario'] ?? null);
            $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : null;

            $cierreGlobal = $this->model->obtenerUltimoCierreCaja($fecha, null);
            $ultimoIdCierre = ($cierreGlobal && $cierreGlobal['ultimo_id_nota_salida']) ? (int)$cierreGlobal['ultimo_id_nota_salida'] : 0;

            $movimientos = $this->model->obtenerSalidasCajero($id_usuario, $fecha, null, null, 9999, 0);
            
            if ($ultimoIdCierre > 0) {
                $movimientos = array_filter($movimientos, function($m) use ($ultimoIdCierre) {
                    return (int)$m['id_documento'] > $ultimoIdCierre;
                });
            }
            $movimientos = array_values($movimientos);

            $totalDocs = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnds = array_sum(array_column($movimientos, 'cantidad'));

            $detalle = [];
            foreach ($movimientos as $m) {
                $detalle[] = [
                    'id_documento' => $m['id_documento'],
                    'fecha_movimiento' => $m['fecha_movimiento'],
                    'usuario_activo' => $m['usuario_activo'],
                    'nombre_cliente' => $m['nombre_cliente'],
                    'tipo_cliente' => $m['tipo_cliente'],
                    'nombre_producto' => $m['nombre_producto'],
                    'categoria' => $m['categoria'],
                    'cantidad' => $m['cantidad']
                ];
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_documentos' => $totalDocs,
                    'total_unidades' => $totalUnds,
                    'detalle' => $detalle
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
        }
        exit();
    }

    // PDF
    public function exportarPDF() {
        try {
            $esAdmin = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1);
            $id_usuario = $esAdmin ? null : ($_SESSION['id_usuario'] ?? null);
            $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : null;
            $busqueda = $_GET['busqueda'] ?? null;
            $id_categoria = $_GET['id_categoria'] ?? null;

            $cierreGlobal = $this->model->obtenerUltimoCierreCaja($fecha, null);
            $ultimoIdCierre = ($cierreGlobal && $cierreGlobal['ultimo_id_nota_salida']) ? (int)$cierreGlobal['ultimo_id_nota_salida'] : 0;

            $movimientos = $this->model->obtenerSalidasCajero($id_usuario, $fecha, $busqueda, $id_categoria, 9999, 0);
            
            if ($ultimoIdCierre > 0) {
                $movimientos = array_filter($movimientos, function($m) use ($ultimoIdCierre) {
                    return (int)$m['id_documento'] > $ultimoIdCierre;
                });
            }
            $movimientos = array_values($movimientos);

            $totalDocs = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnds = array_sum(array_column($movimientos, 'cantidad'));

            $datos_pdf = [
                'fecha' => $fecha ? date('d/m/Y', strtotime($fecha)) : date('d/m/Y'),
                'total_documentos' => $totalDocs,
                'total_unidades' => $totalUnds,
                'movimientos' => $movimientos
            ];

            $archivoPdf = dirname(__DIR__) . '/view/reporteCaja/generar_pdfCajaView.php';
            if (file_exists($archivoPdf)) {
                require_once $archivoPdf;
                exit();
            } else {
                die("Error: No se encuentra generar_pdfCajaView.php en $archivoPdf");
            }
        } catch (Exception $e) {
            die("Error al generar el PDF: " . $e->getMessage());
        }
    }

    public function verDetalleAjax() {
        header('Content-Type: application/json; charset=utf-8');
        $id = $_GET['id'] ?? 0;
        try {
            $nota = $this->modeloNota->obtenerNotaPorId($id);
            if ($nota) {
                echo json_encode(['success' => true, 'nota' => $nota]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Nota no encontrada']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }

    public function exportarCSV() { exit(); }

    // Confirmar cierre con validación
    public function confirmarCierreCajaAjax() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $fecha = $_POST['fecha'] ?? date('Y-m-d');
            $id_usuario_guardar = $_SESSION['id_usuario'] ?? 1; 
            
            // Verificamos si ya existe un cierre para esta fecha
            $cierreExistente = $this->model->obtenerUltimoCierreCaja($fecha, null);
            $resumen = $this->model->obtenerResumenCierreCajero(null, $fecha);
            
            if ($cierreExistente && $resumen['total_documentos'] == 0) {
                echo json_encode(['success' => false, 'mensaje' => 'La caja de esta fecha ya fue cerrada.']);
                exit();
            }
            
            $guardado = $this->model->guardarCierreCaja(
                $fecha, $id_usuario_guardar, $resumen['total_documentos'], $resumen['total_unidades'], $resumen['total_lineas']
            );

            echo json_encode(['success' => $guardado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
        }
        exit();
    }
}