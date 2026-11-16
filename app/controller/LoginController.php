<?php
// app/Controller/LoginController.php
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Model\LoginModel;
use App\Pirotecnicafenix\Helpers\Validador;
use Exception;

// 1. INICIAR SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. MODELO (hereda conexión)
$modelo = new LoginModel();

// 3. PARÁMETROS
$action = $_GET['action'] ?? 'index';
$errorMsg = null;
$bloqueado = false;
$segundos_restantes = 0;

// 4. LOGOUT
if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
    header('Location: ?url=login');
    exit();
}

// 5. VERIFICAR BLOQUEO
$tiempo_actual = time();

if (isset($_SESSION['bloqueo_hasta']) && $_SESSION['bloqueo_hasta'] > $tiempo_actual) {
    $bloqueado = true;
    $segundos_restantes = $_SESSION['bloqueo_hasta'] - $tiempo_actual;
    $errorMsg = "Has superado el límite de 3 intentos. La cuenta está bloqueada por {$segundos_restantes} segundos.";
} elseif (isset($_SESSION['bloqueo_hasta'])) {
    unset($_SESSION['bloqueo_hasta']);
    $_SESSION['intentos_fallidos'] = 0;
}

// 6. PROCESAR LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['clave'])) {

    if (!$bloqueado) {
        try {
            $usuario = Validador::requerido($_POST['usuario'] ?? '', 'usuario');
            $clave   = Validador::requerido($_POST['clave'] ?? '', 'contraseña');

            $usuarioData = $modelo->autenticar($usuario, $clave);

            if ($usuarioData) {
                unset($_SESSION['intentos_fallidos'], $_SESSION['bloqueo_hasta']);

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                session_regenerate_id(true);

                $_SESSION['id_usuario'] = $usuarioData['id_usuario'];
                $_SESSION['usuario']    = $usuarioData['usuario'];
                $_SESSION['id_rol']     = $usuarioData['id_rol'];
                $_SESSION['nombre']     = $usuarioData['nombre'] ?? $usuarioData['usuario'];
                $_SESSION['apellido']   = $usuarioData['apellido'] ?? '';

                header('Location: ?url=dashboard');
                exit();
            }

            // LOGIN FALLIDO
            if (!isset($_SESSION['intentos_fallidos'])) {
                $_SESSION['intentos_fallidos'] = 0;
            }
            $_SESSION['intentos_fallidos']++;

            if ($_SESSION['intentos_fallidos'] >= 3) {
                $_SESSION['bloqueo_hasta'] = time() + 60;
                $bloqueado = true;
                $segundos_restantes = 60;
                $errorMsg = "Has superado el límite de 3 intentos. La cuenta está bloqueada por 60 segundos.";
            } else {
                $intentos_restantes = 3 - $_SESSION['intentos_fallidos'];
                $errorMsg = "Usuario o contraseña incorrectos. Te quedan {$intentos_restantes} intentos.";
            }
        } catch (Exception $e) {
            $errorMsg = "Error: " . $e->getMessage();
        }
    }
}

// 7. SI YA ESTÁ LOGUEADO
if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario']) && !$bloqueado) {
    header('Location: ?url=dashboard');
    exit();
}

// 8. VISTA
$error = $errorMsg;
$basePath = __DIR__ . "/../view/configuracion/";
$viewFile = $basePath . "login.php";

if (file_exists($viewFile)) {
    require_once $viewFile;
} else {
    die("ERROR: No se encuentra la vista de login en: " . $viewFile);
}