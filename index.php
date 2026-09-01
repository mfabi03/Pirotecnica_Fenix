<?php
// ==========================================
// 1. ACTIVAR TODOS LOS ERRORES
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================
// 2. CARGAR AUTOLOAD DE COMPOSER
// ==========================================
require __DIR__ . '/vendor/autoload.php';

// ==========================================
// 3. INICIAR SESIÓN
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 4. CARGAR MIDDLEWARE
// ==========================================
use App\Pirotecnicafenix\Middleware\AuthMiddleware;
use App\Pirotecnicafenix\Controller\DashboardController;

// ==========================================
// 5. FUNCIONES DE AYUDA
// ==========================================
function isLoggedIn() {
    return AuthMiddleware::isLoggedIn();
}

function getUserRole() {
    return AuthMiddleware::getUserRole();
}

// ==========================================
// 6. OBTENER LA RUTA SOLICITADA
// ==========================================
$url = isset($_GET['url']) ? $_GET['url'] : 'main';

// ==========================================
// 7. CONEXIÓN A BASE DE DATOS
// ==========================================
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

// ==========================================
// 8. PROCESAR LOGOUT
// ==========================================
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

// ==========================================
// 9. PROCESAR LOGIN (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $url === 'login' && isset($_POST['usuario']) && isset($_POST['clave'])) {
    require_once __DIR__ . '/app/Controller/LoginController.php';
    exit();
}

// ==========================================
// 10. RUTAS DE LOS MÓDULOS
// ==========================================

// NOTA: El middleware se ejecuta DENTRO de cada caso, no antes del switch

switch ($url) {
    
    // ==========================================
    // 🏠 MAIN (BIENVENIDA) - PÁGINA PRINCIPAL
    // ==========================================
case 'main':
case '':
    if (isLoggedIn()) {
        // CORREGIDO: Ambos roles van al dashboard
        header('Location: ?url=dashboard');
        exit();
    }
    require_once __DIR__ . '/app/view/configuracion/main.php';
    break;
    // ==========================================
    // 🔐 LOGIN
    // ==========================================
    case 'login':
        if (isLoggedIn()) {
            if (getUserRole() === 1) {
                header('Location: ?url=usuarios');
            } else {
                header('Location: ?url=dashboard');
            }
            exit();
        }
        require_once __DIR__ . '/app/Controller/LoginController.php';
        break;
    
    // ==========================================
    // 📊 DASHBOARD
    // ==========================================
    case 'dashboard':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/DashboardController.php';
        $dashboardController = new DashboardController($pdo);
        $dashboardController->index();
        break;
    
    // ==========================================
    // 👤 USUARIOS (Solo Admin)
    // ==========================================
    case 'usuarios':
        AuthMiddleware::requireAdmin();
        require_once __DIR__ . '/app/Controller/UsuarioController.php';
        break;
    
    // ==========================================
    // 🔑 ROLES (Solo Admin)
    // ==========================================
    //case 'roles':
    //   AuthMiddleware::requireAdmin();
    //   require_once __DIR__ . '/app/Controller/RolController.php';
    //    break;
    
    // ==========================================
    // 🏷️ CATEGORÍAS (Requiere autenticación)
    // ==========================================
    case 'categorias':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/CategoriaController.php';
        break;
    
    // ==========================================
    // 📦 PRODUCTOS (Requiere autenticación)
    // ==========================================
    case 'productos':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/ProductosController.php';
        break;
    
    // ==========================================
    // 🚚 PROVEEDORES (Requiere autenticación)
    // ==========================================
    case 'proveedores':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/ProveedoresController.php';
        break;
    
    // ==========================================
    // 👥 CLIENTES (Requiere autenticación)
    // ==========================================
    case 'clientes':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/ClientesController.php';
        break;
    
    // ==========================================
    // 📥 NOTA DE ENTRADA (Requiere autenticación)
    // ==========================================
    case 'notaentrada':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/NotaEntradaController.php';
        break;
    
    // ==========================================
    // 📤 NOTA DE SALIDA (Requiere autenticación)
    // ==========================================
    case 'notasalida':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/NotaSalidaController.php';
        break;
    
   // ==========================================
   // 📊 REPORTES
   // ==========================================
    case 'reportes':
    AuthMiddleware::requireAuth();
    require_once __DIR__ . '/app/Controller/ReportesController.php';
    $reporteController = new App\Pirotecnicafenix\Controller\ReportesController($pdo);
    $reporteController->index();
    break;
    
    
    // ==========================================
    // 404 - PÁGINA NO ENCONTRADA
    // ==========================================
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
?>