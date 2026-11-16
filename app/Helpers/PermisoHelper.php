<?php
namespace App\Pirotecnicafenix\Helpers;

use PDO;
use Exception;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

// 🔥 Ahora hereda de ConnectDB
class PermisoHelper extends ConnectDB {
    
    private $conex;

    public function __construct() {
        parent::__construct();
        $this->conex = $this->getConnection();
    }

    // 🔥 Quitamos el parámetro $db de los métodos, ahora usan $this->conex
    public function tienePermiso($id_rol, $nombre_modulo, $accion) {
        if ((int)$id_rol === 1) {
            return true;
        }
        
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
            
            $stmt = $this->conex->prepare($sql);
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
    
    public function modulosVisibles($id_rol) {
        if ((int)$id_rol === 1) {
            try {
                $sql = "SELECT nombre_modulo FROM modulo ORDER BY id_modulo";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute();
                return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'nombre_modulo');
            } catch (Exception $e) {
                error_log("Error en modulosVisibles (admin): " . $e->getMessage());
                return [];
            }
        }
        
        try {
            $sql = "SELECT m.nombre_modulo
                    FROM permisos p
                    JOIN modulo m ON p.id_modulo = m.id_modulo
                    WHERE p.id_rol = :id_rol 
                      AND p.leer = 1
                    ORDER BY m.id_modulo";
            $stmt = $this->conex->prepare($sql);
            $stmt->execute(['id_rol' => $id_rol]);
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'nombre_modulo');
        } catch (Exception $e) {
            error_log("Error en PermisoHelper::modulosVisibles: " . $e->getMessage());
            return [];
        }
    }
}