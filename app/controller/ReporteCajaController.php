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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = $db;
        $this->model = new ReporteCajaModel($db);
        $this->modeloNota = new notasalidaModel($db);
    }

    private function esAdmin() {
        return (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (strtolower($_SESSION['nombre_rol'] ?? '') === 'administrador');
    }

    // ============================================================
    // VISTA PRINCIPAL
    // ============================================================
    public function index() {
        $esAdmin = $this->esAdmin();
        
        $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : date('Y-m-d');
        $busqueda = $_GET['busqueda'] ?? null;
        $id_categoria = $_GET['id_categoria'] ?? null;

        $por_pagina = (int)($_GET['por_pagina'] ?? 10);
        $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset = ($pagina_actual - 1) * $por_pagina;

        // 🔥 OBTENEMOS EL ID DEL USUARIO REAL LOGUEADO (Sea Admin o no)
        $id_usuario_filtro = (int)($_SESSION['id_usuario'] ?? 0);

        // Evaluamos si ESTE usuario ya hizo cierre en ESTA fecha
        $cajaCerrada = $this->model->existeCierreCaja($fecha, $id_usuario_filtro);

        if ($cajaCerrada) {
            $movimientos = [];
            $totalRegistros = 0;
            $totalDocumentos = 0;
            $totalUnidades = 0;
        } else {
            $movimientos = $this->model->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria, $por_pagina, $offset);
            $totalRegistros = count($movimientos);
            $totalDocumentos = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnidades = array_sum(array_column($movimientos, 'cantidad'));
        }
        
        $porPaginaActual = $por_pagina;
        $paginaActual = $pagina_actual;
        $totalPaginas = $por_pagina > 0 ? ceil($totalRegistros / $por_pagina) : 1;
        
        $fechaFiltro = $fecha; 
        
        $db = $this->db;

        $viewFile = dirname(__DIR__) . '/view/reporteCaja/reporteCajaView.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Error: No se encuentra la vista en $viewFile");
        }
    }

    // ============================================================
    // AJAX Modal de Cierre
    // ============================================================
    public function obtenerCierreDiaAjax() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
            $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : date('Y-m-d');

            $movimientos = $this->model->obtenerSalidasCajero($id_usuario, $fecha, null, null, 9999, 0);
            $totalDocs = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnds = array_sum(array_column($movimientos, 'cantidad'));

            $detalle = [];
            foreach ($movimientos as $m) {
                $detalle[] = [
                    'id_documento' => $m['id_documento'],
                    'fecha_movimiento' => $m['fecha_movimiento'],
                    'usuario_activo' => $m['usuario_activo'],
                    'nombre_cliente' => $m['nombre_cliente'],
                    'tipo_cliente' => $m['tipo_cliente'] ?? 'Natural',
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

    // ============================================================
    // CONFIRMAR CIERRE DE CAJA (CON SEGURIDAD DE FECHAS Y USUARIO)
    // ============================================================
    public function confirmarCierreCajaAjax() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idSesion = $_SESSION['id_usuario'] ?? null;
            if ($idSesion === null) {
                echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
                exit();
            }

            $fecha_solicitada = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
            $fecha_servidor = date('Y-m-d'); // La fecha real del servidor

            // 🔥 VALIDACIÓN DE SEGURIDAD: No permitir fechas futuras (comentada para pruebas)
            /*
            if ($fecha_solicitada > $fecha_servidor) {
                echo json_encode([
                    'success' => false, 
                    'mensaje' => 'Error de seguridad: No puedes cerrar una caja en una fecha futura. La fecha del servidor es ' . date('d/m/Y', strtotime($fecha_servidor))
                ]);
                exit();
            }
            */

            // 🔥 FORZAMOS EL ID DEL USUARIO (Sin importar si es Admin)
            $id_usuario = (int)$idSesion;

            // Validamos el cierre solo para ESTE usuario
            if ($this->model->existeCierreCaja($fecha_solicitada, $id_usuario)) {
                echo json_encode([
                    'success' => false, 
                    'mensaje' => 'Ya has cerrado tu caja para esta fecha. Las ventas nuevas se arrastrarán a tu siguiente turno.'
                ]);
                exit();
            }

            $resumen = $this->model->obtenerResumenCierreCajero($id_usuario, $fecha_solicitada);

            $guardado = $this->model->guardarCierreCaja(
                $fecha_solicitada,
                $id_usuario, 
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
                    'fecha' => $fecha_solicitada,
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
    // AJAX: VER DETALLE DE NOTA (EL OJITO)
    // ============================================================
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

    // ============================================================
    // PDF
    // ============================================================
       public function exportarPDF() {
        try {
            $esAdmin = $this->esAdmin();
            $id_usuario = $esAdmin ? null : ($_SESSION['id_usuario'] ?? null);
            $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : date('Y-m-d');
            $busqueda = $_GET['busqueda'] ?? null;
            $id_categoria = $_GET['id_categoria'] ?? null;

            // 1. Buscamos los movimientos
            $movimientos = $this->model->obtenerSalidasCajero($id_usuario, $fecha, $busqueda, $id_categoria, 9999, 0);
            $totalDocs = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnds = array_sum(array_column($movimientos, 'cantidad'));

            // 2. Calculamos la hora de apertura
            $hora_apertura = 'Sin ventas';
            if (!empty($movimientos)) {
                $primeraVenta = end($movimientos);
                if ($primeraVenta && !empty($primeraVenta['fecha_movimiento'])) {
                    $hora_apertura = date('h:i A', strtotime($primeraVenta['fecha_movimiento']));
                }
            }

            
            
            $id_usuario_sesion = (int)($_SESSION['id_usuario'] ?? 0);
            
            if ($id_usuario_sesion > 0) {
                $sqlUsuario = "SELECT p.nombre, p.apellido 
                               FROM usuario u 
                               INNER JOIN persona p ON u.id_persona = p.id_persona 
                               WHERE u.id_usuario = :id";
                $stmtUsuario = $this->db->prepare($sqlUsuario);
                $stmtUsuario->execute([':id' => $id_usuario_sesion]);
                $datosPersona = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
                
                if ($datosPersona) {
                    $usuario_activo = trim($datosPersona['nombre'] . ' ' . $datosPersona['apellido']);
                }
            }

            // 4. Armamos los datos para el PDF
            $datos_pdf = [
                'fecha' => $fecha ? date('d/m/Y', strtotime($fecha)) : date('d/m/Y'),
                'hora_apertura' => $hora_apertura,
                'total_documentos' => $totalDocs,
                'total_unidades' => $totalUnds,
                'movimientos' => $movimientos,
                'encargado' => $usuario_activo // 🔥 Le pasamos el nombre real al PDF
            ];

            // 5. Cargamos la vista del PDF
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

    public function exportarCSV() { exit(); }
}