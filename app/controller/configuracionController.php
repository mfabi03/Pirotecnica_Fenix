<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\UsuarioModel;
use App\Pirotecnicafenix\Model\RolModel;

class ConfiguracionController {
    
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index(){ 
        echo "<h1>Estamos en el método index</h1>";
       require_once dirname(__DIR__, 2) . "/app/view/configuracion/main.php";
    }

    public function usuarios() {
        // 1. OBTENEMOS LA ACCIÓN
        $action = $_GET['action'] ?? 'lista';

        // 2. INSTANCIAMOS EL MODELO AQUÍ (Una sola vez para todo el método)
        $modelo = new UsuarioModel($this->db);

        // 3. MANEJAMOS LAS ACCIONES
        if ($action == 'registrar') {
            require_once dirname(__DIR__, 2) . "/app/view/configuracion/registro.php";
        } elseif ($action == 'guardar') {
            $this->guardar();
        } else {
            // Caso por defecto: Cargamos la lista
            $result = $modelo->obtenerUsuariosConPersona();
            require_once dirname(__DIR__, 2) . "/app/view/configuracion/usuarios_lista.php";
        }
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datosPersona = [
                'nombre' => $_POST['nombre'],
                'apellido' => $_POST['apellido'],
                'cedula' => $_POST['cedula'],
                'telefono' => $_POST['telefono'],
                'correo' => $_POST['correo']
            ];
            
            $datosUsuario = [
                'rol' => $_POST['rol'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
            ];

            $modelo = new UsuarioModel($this->db);
            
            if ($modelo->registrarUsuarioCompleto($datosPersona, $datosUsuario)) {
                header("Location: ?url=configuracion&action=usuarios");
                exit();
            } else {
                echo "Error: No se pudo guardar el registro.";
            }
        }
    }

    public function roles() {
        require_once dirname(__DIR__, 2) . "/app/view/configuracion/roles_permisos.php";
    }
}