<?php

namespace App\Pirotecnicafenix\Helpers;

class CheckPermiso {
    
   
    public static function verificar($db, $modulo, $accion, $urlRedirigir) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $id_rol = $_SESSION['id_rol'] ?? 0;
        
        if (!PermisoHelper::tienePermiso($db, $id_rol, $modulo, $accion)) {
            $_SESSION['error'] = "No tienes permiso para {$accion} en el módulo de {$modulo}.";
            header("Location: {$urlRedirigir}");
            exit();
        }
    }
}