<?php
// app/Controller/PermisosController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\PermisoModel;
use Exception;

class PermisosController {
    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new PermisoModel($db);
    }

    public function index() {
        // 1. Obtener roles y módulos
        $roles = $this->model->obtenerRoles();
        $modulos = $this->model->obtenerModulos();

        // 2. Determinar qué rol está seleccionado (default: el primero)
        $id_rol_seleccionado = (int) ($_GET['id_rol'] ?? ($roles[0]['id_rol'] ?? 0));

        // 3. Obtener los permisos del rol seleccionado
        $permisosActuales = [];
        if ($id_rol_seleccionado > 0) {
            $permisosRaw = $this->model->obtenerPermisosPorRol($id_rol_seleccionado);
            
            // Reorganizar: [id_modulo => [crear => 1, leer => 1, ...]]
            foreach ($permisosRaw as $p) {
                $permisosActuales[$p['id_modulo']] = [
                    'crear' => (int) $p['crear'],
                    'leer' => (int) $p['leer'],
                    'actualizar' => (int) $p['actualizar'],
                    'eliminar' => (int) $p['eliminar'],
                ];
            }
        }

        // 4. Buscar el nombre del rol seleccionado
        $rolSeleccionado = null;
        foreach ($roles as $r) {
            if ((int) $r['id_rol'] === $id_rol_seleccionado) {
                $rolSeleccionado = $r;
                break;
            }
        }

        // 5. Cargar la vista
        require_once __DIR__ . '/../view/configuracion/permisosView.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?url=permisos');
            exit();
        }

        $id_rol = (int) ($_POST['id_rol'] ?? 0);
        $permisosPost = $_POST['permisos'] ?? [];

        // Validación
        if ($id_rol <= 0) {
            $_SESSION['error'] = "Rol inválido";
            header('Location: ?url=permisos');
            exit();
        }

        // No permitir modificar el Administrador (id_rol = 1)
        if ($id_rol === 1) {
            $_SESSION['error'] = "No se pueden modificar los permisos del Administrador";
            header('Location: ?url=permisos&id_rol=1');
            exit();
        }

        // Actualizar permisos
        $resultado = $this->model->actualizarPermisos($id_rol, $permisosPost);

        if ($resultado) {
            $_SESSION['mensaje'] = "✅ Permisos actualizados correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['error'] = "❌ Error al actualizar los permisos";
        }

        header("Location: ?url=permisos&id_rol={$id_rol}");
        exit();
    }
}