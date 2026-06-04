<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\iniciodesecionModel;

class iniciodesecionController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        require_once dirname(__DIR__, 2) . "/app/view/configuracion/iniciodesecion.php";
    }

    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $modelo = new iniciodesecionModel($this->db);
            $usuario = $modelo->verificarUsuario($_POST['usuario'], $_POST['password']);

            if ($usuario) {
                session_start();
                $_SESSION['usuario'] = $usuario;
                header("Location: ?url=configuracion"); // Redirigir al inicio
            } else {
                echo "<script>alert('Credenciales incorrectas'); window.history.back();</script>";
            }
        }
    }
    public function logout() {
    // 1. Iniciar la sesión para poder acceder a ella
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 2. Limpiar todas las variables de sesión
    $_SESSION = [];
    
    // 3. Destruir la sesión en el servidor
    session_destroy();
    
    // 4. Redirigir al login
    header("Location: ?url=auth");
    exit();
   }
   
}
