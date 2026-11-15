<?php
namespace App\Pirotecnicafenix\Helpers;

// 🔥 Ahora hereda de ConnectDB
class CheckPermiso extends ConnectDB {
    
    private $conex;

    public function __construct() {
        parent::__construct();
        $this->conex = $this->getConnection();
    }

    // 🔥 Quitamos el parámetro $db
    public function verificar($modulo, $accion, $urlRedirigir) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $id_rol = $_SESSION['id_rol'] ?? 0;
        
        // Instanciamos el PermisoHelper para usar su método
        $permisoHelper = new PermisoHelper();
        
        if (!$permisoHelper->tienePermiso($id_rol, $modulo, $accion)) {
            $_SESSION['error'] = "No tienes permiso para {$accion} en el módulo de {$modulo}.";
            header("Location: {$urlRedirigir}");
            exit();
        }
    }
}