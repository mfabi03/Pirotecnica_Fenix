<?php
// app/Helpers/CheckPermiso.php
namespace App\Pirotecnicafenix\Helpers;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;

class CheckPermiso extends ConnectDB
{
    private $conex;

    public function __construct()
    {
        parent::__construct();
        $this->conex = $this->getConnection();
    }

    public function verificar($modulo, $accion, $urlRedirigir)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id_rol = $_SESSION['id_rol'] ?? 0;

        $permisoHelper = new PermisoHelper();

        if (!$permisoHelper->tienePermiso($id_rol, $modulo, $accion)) {
            $_SESSION['error'] = "No tienes permiso para {$accion} en el módulo de {$modulo}.";
            header("Location: {$urlRedirigir}");
            exit();
        }
    }
}