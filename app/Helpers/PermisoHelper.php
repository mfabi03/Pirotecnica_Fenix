<?php
// app/Helpers/PermisoHelper.php
namespace App\Pirotecnicafenix\Helpers;

use PDO;
use Exception;

class PermisoHelper {
    
    /**
     * Verifica si un rol tiene permiso para una acción en un módulo
     * 
     * @param PDO $db Conexión a la BD
     * @param int $id_rol ID del rol
     * @param string $nombre_modulo Nombre del módulo (ej: 'Productos')
     * @param string $accion 'crear', 'leer', 'actualizar', 'eliminar'
     * @return bool
     */
    public static function tienePermiso($db, $id_rol, $nombre_modulo, $accion) {
        // Validación de seguridad: solo acciones permitidas
        $accionesValidas = ['crear', 'leer', 'actualizar', 'eliminar'];
        if (!in_array($accion, $accionesValidas)) {
            error_log("Acción no válida: $accion");
            return false;
        }
        
        try {
            $sql = "SELECT p.$accion 
                    FROM permisos p
                    JOIN modulo m ON p.id_modulo = m.id_modulo
                    WHERE p.id_rol = :id_rol 
                      AND m.nombre_modulo = :modulo
                    LIMIT 1";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_rol' => $id_rol,
                'modulo' => $nombre_modulo
            ]);
            
            $resultado = $stmt->fetchColumn();
            return (bool) $resultado;
            
        } catch (Exception $e) {
            error_log("Error en PermisoHelper::tienePermiso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todos los permisos de un rol
     * 
     * @param PDO $db
     * @param int $id_rol
     * @return array
     */
    public static function obtenerPermisos($db, $id_rol) {
        try {
            $sql = "SELECT 
                        m.id_modulo,
                        m.nombre_modulo,
                        p.crear, p.leer, p.actualizar, p.eliminar
                    FROM permisos p
                    JOIN modulo m ON p.id_modulo = m.id_modulo
                    WHERE p.id_rol = :id_rol
                    ORDER BY m.id_modulo";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['id_rol' => $id_rol]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en PermisoHelper::obtenerPermisos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los nombres de los módulos que un rol puede LEER
     * 
     * @param PDO $db
     * @param int $id_rol
     * @return array
     */
    public static function modulosVisibles($db, $id_rol) {
        try {
            $sql = "SELECT m.nombre_modulo
                    FROM permisos p
                    JOIN modulo m ON p.id_modulo = m.id_modulo
                    WHERE p.id_rol = :id_rol 
                      AND p.leer = 1
                    ORDER BY m.id_modulo";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['id_rol' => $id_rol]);
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'nombre_modulo');
            
        } catch (Exception $e) {
            error_log("Error en PermisoHelper::modulosVisibles: " . $e->getMessage());
            return [];
        }
    }
}