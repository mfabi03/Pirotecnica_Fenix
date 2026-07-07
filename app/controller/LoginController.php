<?php
// app/Controller/LoginController.php
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\LoginModel;
use Exception;

// ==========================================
// 1. INICIAR SESIÓN
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 2. CARGA DEL MODELO
// ==========================================
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'LoginModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR CRÍTICO: No se encuentra el archivo: " . $pathModel);
}

// ==========================================
// 3. INICIALIZACIÓN DE CONEXIÓN Y MODELO
// ==========================================
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new LoginModel($db);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ==========================================
// 4. OBTENER PARÁMETROS DE LA URL
// ==========================================
$action = $_GET['action'] ?? 'index';
$error = $_GET['error'] ?? null;

// ==========================================
// 5. PROCESAR LOGOUT
// ==========================================
if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: ?url=login');
    exit();
}

// ==========================================
// 6. PROCESAR LOGIN (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario']) && isset($_POST['clave'])) {
    try {
        $usuario = trim($_POST['usuario']);
        $clave = trim($_POST['clave']);
        
        error_log("=== LOGIN CONTROLLER ===");
        error_log("Usuario POST: " . $usuario);
        
        if (empty($usuario) || empty($clave)) {
            throw new Exception("Usuario y contraseña son obligatorios");
        }
        
        // Autenticar usuario
        $usuarioData = $modelo->autenticar($usuario, $clave);
        
        error_log("Resultado autenticación: " . ($usuarioData ? '✅ EXITOSO' : '❌ FALLIDO'));
        
        if ($usuarioData) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            session_regenerate_id(true);
            
            $_SESSION['id_usuario'] = $usuarioData['id_usuario'];
            $_SESSION['usuario'] = $usuarioData['usuario'];
            $_SESSION['id_rol'] = $usuarioData['id_rol'];
            $_SESSION['nombre'] = $usuarioData['nombre'] ?? $usuarioData['usuario'];
            $_SESSION['apellido'] = $usuarioData['apellido'] ?? '';
            
            error_log("Sesión iniciada: " . print_r($_SESSION, true));
            
            // ✅ CORREGIDO: Ambos roles van al dashboard
            header('Location: ?url=dashboard');
            exit();
        } else {
            error_log("❌ Login fallido - Redirigiendo a login con error");
            header('Location: ?url=login&error=1');
            exit();
        }
        
    } catch (Exception $e) {
        error_log("❌ Error en login: " . $e->getMessage());
        header('Location: ?url=login&error=1');
        exit();
    }
}

// ==========================================
// 7. VERIFICAR SI YA ESTÁ LOGUEADO
// ==========================================
if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])) {
    // ✅ CORREGIDO: Ambos roles van al dashboard
    header('Location: ?url=dashboard');
    exit();
}

// ==========================================
// 8. CARGAR VISTA DE LOGIN
// ==========================================
$basePath = __DIR__ . "/../view/configuracion/";
$viewFile = $basePath . "login.php";

if (file_exists($viewFile)) {
    require_once $viewFile;
} else {
    die("ERROR: No se encuentra la vista de login en: " . $viewFile);
}
?>