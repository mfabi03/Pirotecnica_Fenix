<?php
<<<<<<< HEAD
// ==========================================
// 1. ACTIVAR TODOS LOS ERRORES
// ==========================================
=======
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

<<<<<<< HEAD
// ==========================================
// 2. CARGAR AUTOLOAD DE COMPOSER
// ==========================================
require __DIR__ . '/vendor/autoload.php';

// ==========================================
// 3. INICIAR SESIÓN
// ==========================================
=======
require __DIR__ . '/vendor/autoload.php';

>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
// ==========================================
// 4. CARGAR MIDDLEWARE
// ==========================================
use App\Pirotecnicafenix\Middleware\AuthMiddleware;
use App\Pirotecnicafenix\Controller\DashboardController;

// ==========================================
// 5. FUNCIONES DE AYUDA
// ==========================================
=======
use App\Pirotecnicafenix\Middleware\AuthMiddleware;
use App\Pirotecnicafenix\Controller\DashboardController;

>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
function isLoggedIn() {
    return AuthMiddleware::isLoggedIn();
}

function getUserRole() {
    return AuthMiddleware::getUserRole();
}

<<<<<<< HEAD
// ==========================================
// 6. OBTENER LA RUTA SOLICITADA
// ==========================================
$url = isset($_GET['url']) ? $_GET['url'] : 'main';

// ==========================================
// 7. CONEXIÓN A BASE DE DATOS
// ==========================================
=======
$url = isset($_GET['url']) ? $_GET['url'] : 'main';

>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
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

<<<<<<< HEAD
// ==========================================
// 8. PROCESAR LOGOUT
// ==========================================
=======
// LOGOUT
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
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

<<<<<<< HEAD
// ==========================================
// 9. PROCESAR LOGIN (POST)
// ==========================================
=======
// LOGIN POST
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $url === 'login' && isset($_POST['usuario']) && isset($_POST['clave'])) {
    require_once __DIR__ . '/app/Controller/LoginController.php';
    exit();
}

<<<<<<< HEAD
// ==========================================
// 10. RUTAS DE LOS MÓDULOS
// ==========================================

switch ($url) {
    
    // ==========================================
    // 🏠 MAIN (BIENVENIDA)
    // ==========================================
=======
switch ($url) {
    
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
    case 'main':
    case '':
        if (isLoggedIn()) {
            header('Location: ?url=dashboard');
            exit();
        }
        require_once __DIR__ . '/app/view/configuracion/main.php';
        break;

<<<<<<< HEAD
    // ==========================================
    // 🔐 LOGIN
    // ==========================================
=======
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
    case 'login':
        if (isLoggedIn()) {
            header('Location: ?url=dashboard');
            exit();
        }
        require_once __DIR__ . '/app/Controller/LoginController.php';
        break;

<<<<<<< HEAD
    // ==========================================
    // 📊 DASHBOARD
    // ==========================================
=======
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
    case 'dashboard':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/DashboardController.php';
        $dashboardController = new DashboardController($pdo);
        $dashboardController->index();
        break;

<<<<<<< HEAD
    // ==========================================
    // 👥 CLIENTES
    // ==========================================
    case 'clientes':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/clientesController.php';
        break;

    // ==========================================
    // 📤 NOTA DE SALIDA
    // ==========================================
    case 'notasalida':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/notasalidaController.php';
        break;

    // ==========================================
    // 📥 NOTA DE ENTRADA
    // ==========================================
    case 'notaentrada':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/notaentradaController.php';
        break;

    // ==========================================
    // 📦 PRODUCTOS
    // ==========================================
    case 'productos':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/productosController.php';
        break;

    // ==========================================
    // 🚚 PROVEEDORES
    // ==========================================
    case 'proveedores':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/proveedoresController.php';
        break;

    // ==========================================
    // 🏷️ CATEGORÍAS
    // ==========================================
    case 'categorias':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/CategoriaController.php';
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
    case 'roles':
        AuthMiddleware::requireAdmin();
        require_once __DIR__ . '/app/Controller/RolController.php';
        break;

    // ==========================================
    // 📊 REPORTES
    // ==========================================
    case 'reportes':
        AuthMiddleware::requireAuth();
=======
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
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
        require_once __DIR__ . '/app/Controller/ReportesController.php';
        $reportesController = new \App\Pirotecnicafenix\Controller\ReportesController($pdo);
        $reportesController->index();
        break;

<<<<<<< HEAD
    // ==========================================
    // 📊 EXPORTAR CSV (REPORTES)
    // ==========================================
    case 'exportar_csv':
        AuthMiddleware::requireAuth();
        require_once __DIR__ . '/app/Controller/ReportesController.php';
        $reportesController = new \App\Pirotecnicafenix\Controller\ReportesController($pdo);
        $reportesController->exportarCSV();

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
=======
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
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
