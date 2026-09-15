<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

// ==========================================
// CONFIGURAR ZONA HORARIA DE VENEZUELA
// Venezuela usa America/Caracas (UTC-4)
// ==========================================
require __DIR__ . '/app/Config/timezone.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Pirotecnicafenix\Middleware\AuthMiddleware;
use App\Pirotecnicafenix\Controller\DashboardController;

function isLoggedIn() {
    return AuthMiddleware::isLoggedIn();
}

function getUserRole() {
    return AuthMiddleware::getUserRole();
}

$url = isset($_GET['url']) ? $_GET['url'] : 'main';

use App\Pirotecnicafenix\Config\Connect\ConnectDB;

$dbError = null;
try {
    $conexion = new ConnectDB();
    $pdo = $conexion->getConnection();
} catch (Exception $e) {
    $dbError = $e->getMessage();
    error_log('DB_ERROR: ' . $dbError);
    $pdo = null;
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "<!-- DEBUG: DB connection failed: " . htmlspecialchars($dbError) . " -->\n";
    }
}

// LOGOUT
if ($url === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: ?url=main');
    exit();
}

// LOGIN POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $url === 'login' && isset($_POST['usuario']) && isset($_POST['clave'])) {
    require_once __DIR__ . '/app/Controller/LoginController.php';
    exit();
}

switch ($url) {
    
    case 'main':
    case '':
        if (isLoggedIn()) {
            header('Location: ?url=dashboard');
            exit();
        }
        require_once __DIR__ . '/app/view/configuracion/main.php';
        break;

    case 'login':
        if (isLoggedIn()) {
            header('Location: ?url=dashboard');
            exit();
        }
        require_once __DIR__ . '/app/Controller/LoginController.php';
        break;

    case 'dashboard':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/DashboardController.php';
        $dashboardController = new DashboardController($pdo);
        $dashboardController->index();
        break;

    // ============================================
    // CLIENTES
    // ============================================
    case 'clientes':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Clientes', 'leer');
        require_once __DIR__ . '/app/Controller/clientesController.php';
        break;

    // ============================================
    // NOTA DE SALIDA
    // ============================================
    case 'notasalida':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Notas de Salida', 'leer');
        require_once __DIR__ . '/app/Controller/notasalidaController.php';
        break;

    // ============================================
    // NOTA DE ENTRADA
    // ============================================
    case 'notaentrada':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Notas de Entrada', 'leer');
        require_once __DIR__ . '/app/Controller/notaentradaController.php';
        break;

    // ============================================
    // PRODUCTOS
    // ============================================
    case 'productos':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Productos', 'leer');
        require_once __DIR__ . '/app/Controller/productosController.php';
        break;

    // ============================================
    // PROVEEDORES
    // ============================================
    case 'proveedores':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Proveedores', 'leer');
        require_once __DIR__ . '/app/Controller/proveedoresController.php';
        break;

    // ============================================
    // CATEGORÍAS
    // ============================================
    case 'categorias':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Categorias', 'leer');
        require_once __DIR__ . '/app/Controller/CategoriaController.php';
        break;

    // ============================================
    // USUARIOS
    // ============================================
    case 'usuarios':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Usuarios', 'leer');
        require_once __DIR__ . '/app/Controller/UsuarioController.php';
        break;

    // ============================================
    // ROLES
    // ============================================
    case 'roles':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Roles', 'leer');
        require_once __DIR__ . '/app/Controller/RolController.php';
        break;

    // ============================================
    // PERMISOS (nueva ruta)
    // ============================================
    case 'permisos':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Roles', 'actualizar');
        require_once __DIR__ . '/app/Controller/PermisosController.php';
        break;

    // ============================================
    // REPORTES
    // ============================================
    case 'reportes':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Reportes', 'leer');
        require_once __DIR__ . '/app/Controller/ReportesController.php';
        $reportesController = new \App\Pirotecnicafenix\Controller\ReportesController($pdo);
        $reportesController->index();
        break;

    // ============================================
    // EXPORTAR CSV
    // ============================================
    case 'exportar_csv':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Reportes', 'leer');
        require_once __DIR__ . '/app/Controller/ReportesController.php';
        $reportesController = new \App\Pirotecnicafenix\Controller\ReportesController($pdo);
        $reportesController->exportarCSV();
        break;   

    case 'permisos':
        AuthMiddleware::requireAuth();
        AuthMiddleware::requirePermiso($pdo, 'Roles', 'actualizar');
        require_once __DIR__ . '/app/Controller/PermisosController.php';
        $permisosController = new \App\Pirotecnicafenix\Controller\PermisosController($pdo);
        
        // Verificar si es POST (guardar) o GET (ver)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $permisosController->guardar();
        } else {
            $permisosController->index();
        }
        break;

    default:
            http_response_code(404);
            require_once __DIR__ . '/app/view/header.php';
            ?>
            <div class="container-fluid px-4">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center py-5">
                            <h1 class="display-1 text-muted">404</h1>
                            <h2 class="text-muted">Página no encontrada</h2>
                            <p class="text-muted">La página que buscas no existe.</p>
                            <a href="?url=main" class="btn btn-primary">Volver al inicio</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            require_once __DIR__ . '/app/view/footer.php';
            break;
}