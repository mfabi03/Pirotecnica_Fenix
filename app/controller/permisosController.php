<?php
// app/Controller/PermisosController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\PermisoModel;
use App\Pirotecnicafenix\Helpers\Validador;
use Exception;

class PermisosController
{
    private $model;

    public function __construct($db = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new PermisoModel();
    }

    public function index()
    {
        $roles = $this->model->obtenerRoles();
        $modulos = $this->model->obtenerModulos();

        $id_rol_seleccionado = (int) ($_GET['id_rol'] ?? ($roles[0]['id_rol'] ?? 0));

        $permisosActuales = [];
        if ($id_rol_seleccionado > 0) {
            $permisosRaw = $this->model->obtenerPermisosPorRol($id_rol_seleccionado);

            foreach ($permisosRaw as $p) {
                $permisosActuales[$p['id_modulo']] = [
                    'crear'      => (int)$p['crear'],
                    'leer'       => (int)$p['leer'],
                    'actualizar' => (int)$p['actualizar'],
                    'eliminar'   => (int)$p['eliminar'],
                ];
            }
        }

        $rolSeleccionado = null;
        foreach ($roles as $r) {
            if ((int)$r['id_rol'] === $id_rol_seleccionado) {
                $rolSeleccionado = $r;
                break;
            }
        }

        require_once __DIR__ . '/../view/configuracion/permisosView.php';
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?url=permisos');
            exit();
        }

        try {
            $id_rol = Validador::validarId($_POST['id_rol'] ?? null, 'Rol');

            if ($id_rol === 1) {
                $_SESSION['error'] = "No se pueden modificar los permisos del Administrador";
                header('Location: ?url=permisos&id_rol=1');
                exit();
            }

            $permisosPost = $_POST['permisos'] ?? [];
            if (!is_array($permisosPost)) $permisosPost = [];

            $resultado = $this->model->actualizarPermisos($id_rol, $permisosPost);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Permisos actualizados correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['error'] = "❌ Error al actualizar los permisos";
            }

            header("Location: ?url=permisos&id_rol={$id_rol}");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ?url=permisos');
            exit();
        }
    }
}