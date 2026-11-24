<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Middleware\AuthMiddleware;

class FrontController {

    private $url;

    public function __construct() {
        // 1. Iniciar sesión de forma segura
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            session_start();
        }

        // 2. Obtener la URL
        $this->url = $_GET['url'] ?? 'main';
        
        // 3. Enrutar
        $this->dispatch($this->url);
    }

    private function dispatch($url) {
        
        // --- RUTAS PÚBLICAS (No requieren sesión) ---
        if (in_array($url, ['main', 'login', ''], true)) {
            
            // Si ya está logueado, lo mandamos al dashboard
            if ($url === 'login' && AuthMiddleware::isLoggedIn()) {
                header('Location: ?url=dashboard');
                exit();
            }
            if (($url === 'main' || $url === '') && AuthMiddleware::isLoggedIn()) {
                header('Location: ?url=dashboard');
                exit();
            }

            // Cargar la vista o controlador público
            if ($url === 'login') {
                require_once __DIR__ . '/LoginController.php';
            } else {
                require_once __DIR__ . '/../view/configuracion/main.php';
            }
            return;
        }

        // --- RUTA LOGOUT ---
        if ($url === 'logout') {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            header('Location: ?url=main');
            exit();
        }

        // --- RUTAS PROTEGIDAS (Requieren sesión) ---
        AuthMiddleware::requireAuth();

        // Mapeo de rutas (Archivo y Permisos requeridos)
        // Formato: 'url' => ['archivo' => '...', 'permiso' => ['Modulo', 'accion'] o null]
        $rutas = [
            'dashboard'   => ['archivo' => 'DashboardController.php', 'permiso' => null],
            'clientes'    => ['archivo' => 'clientesController.php', 'permiso' => ['Clientes', 'leer']],
            'productos'   => ['archivo' => 'productosController.php', 'permiso' => ['Productos', 'leer']],
            'proveedores' => ['archivo' => 'proveedoresController.php', 'permiso' => ['Proveedores', 'leer']],
            'categorias'  => ['archivo' => 'categoriaController.php', 'permiso' => ['Categorias', 'leer']],
            'usuarios'    => ['archivo' => 'UsuarioController.php', 'permiso' => ['Usuarios', 'leer']],
            'roles'       => ['archivo' => 'RolController.php', 'permiso' => ['Roles', 'leer']],
            'notaentrada' => ['archivo' => 'notaentradaController.php', 'permiso' => ['Notas de Entrada', 'leer']],
            'notasalida'  => ['archivo' => 'notasalidaController.php', 'permiso' => ['Notas de Salida', 'leer']],
            'permisos'    => ['archivo' => 'PermisosController.php', 'permiso' => ['Roles', 'actualizar']],
            'reportes'    => ['archivo' => 'ReportesController.php', 'permiso' => ['Reportes', 'leer']],
            'reportecaja' => ['archivo' => 'ReporteCajaController.php', 'permiso' => ['ReporteCaja', 'leer']],
            
            // Endpoints AJAX y Exportaciones
            'exportar_csv'         => ['archivo' => 'ReportesController.php', 'permiso' => ['Reportes', 'leer']],
            'exportar_pdf'         => ['archivo' => 'ReportesController.php', 'permiso' => ['Reportes', 'leer']],
            'obtener_cierre_ajax'  => ['archivo' => 'ReportesController.php', 'permiso' => null],
            'exportar_cierre_pdf'  => ['archivo' => 'ReportesController.php', 'permiso' => null],
            'exportar_caja_csv'    => ['archivo' => 'ReporteCajaController.php', 'permiso' => ['ReporteCaja', 'leer']],
            'exportar_caja_pdf'    => ['archivo' => 'ReporteCajaController.php', 'permiso' => ['ReporteCaja', 'leer']],
            'cierre_caja_ajax'     => ['archivo' => 'ReporteCajaController.php', 'permiso' => ['ReporteCaja', 'leer']],
            'confirmar_cierre_caja'=> ['archivo' => 'ReporteCajaController.php', 'permiso' => ['ReporteCaja', 'leer']],
            'detalle_caja_json'    => ['archivo' => 'ReporteCajaController.php', 'permiso' => ['ReporteCaja', 'leer']],
            'obtener_motivo_ajax'  => ['archivo' => 'ReportesController.php', 'permiso' => ['Reportes', 'leer']],
            
            // 🔥 NUEVAS RUTAS PARA EL HISTORIAL (Solucionan el error de JSON)
            'ver_cierre_ajax'       => ['archivo' => 'ReporteCajaController.php', 'permiso' => ['ReporteCaja', 'leer']],
            'exportar_pdf_historial'=> ['archivo' => 'ReporteCajaController.php', 'permiso' => ['ReporteCaja', 'leer']]
        ];

        // Validar si la ruta existe
        if (isset($rutas[$url])) {
            $configRuta = $rutas[$url];
            
            // VALIDAR PERMISO (Si la ruta lo requiere)
            if ($configRuta['permiso'] !== null) {
                AuthMiddleware::requirePermiso($configRuta['permiso'][0], $configRuta['permiso'][1]);
            }

            // Cargar el archivo del controlador
            $rutaArchivo = __DIR__ . '/' . $configRuta['archivo'];
            if (!file_exists($rutaArchivo)) {
                $this->error404("Archivo de controlador no encontrado: " . $configRuta['archivo']);
                return;
            }

            //  DETECTAR SI EL CONTROLADOR ES UNA CLASE O ES PROCEDURAL
            $controladoresPorClase = [
                'dashboard'            => 'DashboardController',
                'permisos'             => 'PermisosController',
                'reportes'             => 'ReportesController',
                'reportecaja'          => 'ReporteCajaController',
                'roles'                => 'RolController',
                'usuarios'             => 'UsuarioController',
                'exportar_csv'         => 'ReportesController',
                'exportar_pdf'         => 'ReportesController',
                'obtener_cierre_ajax'  => 'ReportesController',
                'exportar_cierre_pdf'  => 'ReportesController',
                'exportar_caja_csv'    => 'ReporteCajaController',
                'exportar_caja_pdf'    => 'ReporteCajaController',
                'cierre_caja_ajax'     => 'ReporteCajaController',
                'confirmar_cierre_caja'=> 'ReporteCajaController',
                'detalle_caja_json'    => 'ReporteCajaController',
                'obtener_motivo_ajax'  => 'ReportesController',
                
                // 🔥 MAPEO DE LAS NUEVAS RUTAS
                'ver_cierre_ajax'       => 'ReporteCajaController',
                'exportar_pdf_historial'=> 'ReporteCajaController'
            ];

            if (isset($controladoresPorClase[$url])) {
                // Controlador basado en clase → instanciar y llamar al método correcto
                require_once $rutaArchivo;
                $nombreClase = 'App\\Pirotecnicafenix\\Controller\\' . $controladoresPorClase[$url];

                if (!class_exists($nombreClase)) {
                    $this->error404("No se pudo cargar la clase {$nombreClase}.");
                    return;
                }

                $controller = new $nombreClase();

                // Determinar qué método llamar según la URL
                $metodo = 'index';
                if ($url === 'exportar_csv')          $metodo = 'exportarCSV';
                if ($url === 'exportar_pdf')          $metodo = 'exportarPDF';
                if ($url === 'obtener_cierre_ajax')   $metodo = 'obtenerCierreAjax';
                if ($url === 'exportar_cierre_pdf')   $metodo = 'exportarCierrePDF';
                if ($url === 'exportar_caja_csv')     $metodo = 'exportarCSV';
                if ($url === 'exportar_caja_pdf')     $metodo = 'exportarPDF';
                if ($url === 'cierre_caja_ajax')      $metodo = 'obtenerCierreDiaAjax';
                if ($url === 'confirmar_cierre_caja') $metodo = 'confirmarCierreCajaAjax';
                if ($url === 'detalle_caja_json')     $metodo = 'verDetalleAjax';
                if ($url === 'obtener_motivo_ajax')   $metodo = 'obtenerCierreAjax';
                
                // 🔥 ASIGNACIÓN DE LOS NUEVOS MÉTODOS
                if ($url === 'ver_cierre_ajax')        $metodo = 'verCierreAjax';
                if ($url === 'exportar_pdf_historial') $metodo = 'exportarPDFHistorico';

                if (!method_exists($controller, $metodo)) {
                    $this->error404("El controlador {$nombreClase} no tiene método {$metodo}().");
                    return;
                }

                $controller->$metodo();

            } else {
                // Controlador procedural → solo incluir (ejecuta su código al cargarse)
                require_once $rutaArchivo;
            }

        } else {
            $this->error404("La ruta '{$url}' no existe.");
        }
    }

    private function error404($mensaje) {
        http_response_code(404);
        require_once __DIR__ . '/../view/header.php';
        echo "<div class='container mt-5 text-center'>";
        echo "<h1 class='display-1 text-muted'>404</h1>";
        echo "<h2 class='text-muted'>Página no encontrada</h2>";
        echo "<p class='text-muted'>" . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<a href='?url=main' class='btn btn-primary mt-3'>Volver al inicio</a>";
        echo "</div>";
        require_once __DIR__ . '/../view/footer.php';
        exit();
    }
}