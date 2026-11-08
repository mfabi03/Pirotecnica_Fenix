<?php
// app/Controller/LoginController.php
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\LoginModel;
use Exception;

// 1. INICIAR SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. CARGA DEL MODELO
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'LoginModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR CRÍTICO: No se encuentra el archivo: " . $pathModel);
}

// 3. INICIALIZACIÓN DE CONEXIÓN Y MODELO
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new LoginModel($db);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 4. OBTENER PARÁMETROS
$action = $_GET['action'] ?? 'index';
$errorMsg = null; // Variable para el mensaje de error
$bloqueado = false;
$segundos_restantes = 0;

// 5. PROCESAR LOGOUT
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

// 6. VERIFICAR BLOQUEO ANTES DE PROCESAR NADA
$tiempo_actual = time();

if (isset($_SESSION['bloqueo_hasta']) && $_SESSION['bloqueo_hasta'] > $tiempo_actual) {
    // Está bloqueado y el tiempo no ha pasado
    $bloqueado = true;
    $segundos_restantes = $_SESSION['bloqueo_hasta'] - $tiempo_actual;
    $errorMsg = "Has superado el límite de 3 intentos. La cuenta está bloqueada por {$segundos_restantes} segundos.";
} elseif (isset($_SESSION['bloqueo_hasta'])) {
    // El tiempo ya pasó, limpiamos el bloqueo
    unset($_SESSION['bloqueo_hasta']);
    $_SESSION['intentos_fallidos'] = 0;
}

// 7. PROCESAR LOGIN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario']) && isset($_POST['clave'])) {
    
    if (!$bloqueado) {
        try {
            $usuario = trim($_POST['usuario']);
            $clave = trim($_POST['clave']);
            
            if (empty($usuario) || empty($clave)) {
                throw new Exception("Usuario y contraseña son obligatorios");
            }
            
            $usuarioData = $modelo->autenticar($usuario, $clave);
            
            if ($usuarioData) {
                // ✅ LOGIN EXITOSO: Limpiamos intentos y bloqueos
                unset($_SESSION['intentos_fallidos']);
                unset($_SESSION['bloqueo_hasta']);
                
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                session_regenerate_id(true);
                
                $_SESSION['id_usuario'] = $usuarioData['id_usuario'];
                $_SESSION['usuario'] = $usuarioData['usuario'];
                $_SESSION['id_rol'] = $usuarioData['id_rol'];
                $_SESSION['nombre'] = $usuarioData['nombre'] ?? $usuarioData['usuario'];
                $_SESSION['apellido'] = $usuarioData['apellido'] ?? '';
                
                header('Location: ?url=dashboard');
                exit();
            } else {
                // ❌ LOGIN FALLIDO
                if (!isset($_SESSION['intentos_fallidos'])) {
                    $_SESSION['intentos_fallidos'] = 0;
                }
                $_SESSION['intentos_fallidos']++;
                
                // Si llega al 3er intento, se bloquea por 60 segundos
                if ($_SESSION['intentos_fallidos'] >= 3) {
                    $_SESSION['bloqueo_hasta'] = time() + 60;
                    $bloqueado = true;
                    $segundos_restantes = 60;
                    $errorMsg = "Has superado el límite de 3 intentos. La cuenta está bloqueada por 60 segundos.";
                } else {
                    $intentos_restantes = 3 - $_SESSION['intentos_fallidos'];
                    $errorMsg = "Usuario o contraseña incorrectos. Te quedan {$intentos_restantes} intentos.";
                }
            }
            
        } catch (Exception $e) {
            $errorMsg = "Error: " . $e->getMessage();
        }
    }
}

// 8. VERIFICAR SI YA ESTÁ LOGUEADO (Para no mostrar el login si ya entró)
if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario']) && !$bloqueado) {
    header('Location: ?url=dashboard');
    exit();
}

// 9. CARGAR VISTA DE LOGIN
// Pasamos las variables a la vista
$error = $errorMsg;

$basePath = __DIR__ . "/../view/configuracion/";
$viewFile = $basePath . "login.php";

if (file_exists($viewFile)) {
    require_once $viewFile;
} else {
    die("ERROR: No se encuentra la vista de login en: " . $viewFile);
}
?>