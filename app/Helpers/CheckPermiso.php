<?php
// app/Helpers/CheckPermiso.php
namespace App\Pirotecnicafenix\Helpers;

class CheckPermiso {
    
    /**
     * Verifica si el usuario tiene permiso para una acción.
     * Si no lo tiene, redirige con mensaje de error.
     * 
     * @param \PDO $db Conexión a la BD
     * @param string $modulo Nombre del módulo (debe coincidir con tabla `modulo`)
     * @param string $accion 'crear', 'leer', 'actualizar', 'eliminar'
     * @param string $urlRedirigir URL a donde redirigir si no tiene permiso
     */
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