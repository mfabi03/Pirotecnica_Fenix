<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class PermisoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerModulos() {
        $stmt = $this->db->prepare("SELECT id_modulo, nombre_modulo FROM modulo ORDER BY id_modulo ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPermisosPorRol($id_rol) {
        $stmt = $this->db->prepare("SELECT id_modulo, crear, leer, actualizar, eliminar FROM permisos WHERE id_rol = :id_rol ORDER BY id_modulo ASC");
        $stmt->execute(['id_rol' => $id_rol]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $permisos = [];
        foreach ($resultados as $row) {
            $permisos[$row['id_modulo']] = [
                'crear'      => (int) $row['crear'],
                'leer'       => (int) $row['leer'],
                'actualizar' => (int) $row['actualizar'],
                'eliminar'   => (int) $row['eliminar'],
            ];
        }
        return $permisos;
    }
public function actualizarPermisos($id_rol, $permisosPost) {
    try {
        $this->db->beginTransaction();

        // 1. ELIMINAR todos los permisos actuales del rol
        $sqlDelete = "DELETE FROM permisos WHERE id_rol = :id_rol";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->execute(['id_rol' => $id_rol]);

        // 2. OBTENER todos los módulos
        $modulos = $this->obtenerModulos();

        // 3. RECORRER cada módulo e INSERTAR sus permisos
        foreach ($modulos as $mod) {
            $id_modulo = (int) $mod['id_modulo'];

            // 3.1 Verificar si este módulo viene en el POST
            $accionesModulo = $permisosPost[$id_modulo] ?? [];

            // 3.2 Calcular cada acción individualmente
            //     Si la clave existe en el array del POST, es 1. Si no, es 0.
            $crear = array_key_exists('crear', $accionesModulo) ? 1 : 0;
            $leer = array_key_exists('leer', $accionesModulo) ? 1 : 0;
            $actualizar = array_key_exists('actualizar', $accionesModulo) ? 1 : 0;
            $eliminar = array_key_exists('eliminar', $accionesModulo) ? 1 : 0;

            // 3.3 INSERTAR la fila usando bindValue para manejar correctamente campos bit(1)
            $sqlInsert = "INSERT INTO permisos 
                            (id_rol, id_modulo, crear, leer, actualizar, eliminar) 
                          VALUES 
                            (:id_rol, :id_modulo, :crear, :leer, :actualizar, :eliminar)";
            $stmtInsert = $this->db->prepare($sqlInsert);
            
            // Usar bindValue con PDO::PARAM_INT para campos bit(1)
            $stmtInsert->bindValue(':id_rol', $id_rol, PDO::PARAM_INT);
            $stmtInsert->bindValue(':id_modulo', $id_modulo, PDO::PARAM_INT);
            $stmtInsert->bindValue(':crear', $crear, PDO::PARAM_INT);
            $stmtInsert->bindValue(':leer', $leer, PDO::PARAM_INT);
            $stmtInsert->bindValue(':actualizar', $actualizar, PDO::PARAM_INT);
            $stmtInsert->bindValue(':eliminar', $eliminar, PDO::PARAM_INT);
            
            $stmtInsert->execute();
        }

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log("Error en actualizarPermisos: " . $e->getMessage());
        return false;
    }
}

    public function inicializarPermisosVacios($id_rol) {
        $modulos = $this->obtenerModulos();
        foreach ($modulos as $m) {
            $stmt = $this->db->prepare("INSERT INTO permisos 
                (id_rol, id_modulo, crear, leer, actualizar, eliminar) 
                VALUES (:id_rol, :id_modulo, 0, 0, 0, 0)");
            
            // Usar bindValue con PDO::PARAM_INT para campos bit(1)
            $stmt->bindValue(':id_rol', $id_rol, PDO::PARAM_INT);
            $stmt->bindValue(':id_modulo', $m['id_modulo'], PDO::PARAM_INT);
            
            $stmt->execute();
        }
        return true;
    }
}