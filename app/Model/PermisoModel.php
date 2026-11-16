<?php
// app/Model/PermisoModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

class PermisoModel extends ConnectDB
{
    private $conex;

    public function __construct()
    {
        parent::__construct();
        $this->conex = $this->getConnection();
    }

    /**
     * Obtiene la lista de roles (NUEVO - faltaba).
     */
    public function obtenerRoles()
    {
        try {
            $stmt = $this->conex->prepare("SELECT id_rol, nombre_rol FROM rol ORDER BY id_rol ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerRoles: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerModulos()
    {
        $stmt = $this->conex->prepare("SELECT id_modulo, nombre_modulo FROM modulo ORDER BY id_modulo ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los permisos de un rol en formato PLANO:
     * [['id_modulo' => 1, 'crear' => 1, ...], ...]
     */
    public function obtenerPermisosPorRol($id_rol)
    {
        $stmt = $this->conex->prepare(
            "SELECT id_modulo, crear, leer, actualizar, eliminar
             FROM permisos
             WHERE id_rol = :id_rol
             ORDER BY id_modulo ASC"
        );
        $stmt->execute([':id_rol' => $id_rol]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarPermisos($id_rol, $permisosPost)
    {
        try {
            $this->conex->beginTransaction();

            $sqlDelete = "DELETE FROM permisos WHERE id_rol = :id_rol";
            $stmtDelete = $this->conex->prepare($sqlDelete);
            $stmtDelete->execute([':id_rol' => $id_rol]);

            $modulos = $this->obtenerModulos();

            foreach ($modulos as $mod) {
                $id_modulo = (int) $mod['id_modulo'];
                $accionesModulo = $permisosPost[$id_modulo] ?? [];

                $crear = array_key_exists('crear', $accionesModulo) ? 1 : 0;
                $leer = array_key_exists('leer', $accionesModulo) ? 1 : 0;
                $actualizar = array_key_exists('actualizar', $accionesModulo) ? 1 : 0;
                $eliminar = array_key_exists('eliminar', $accionesModulo) ? 1 : 0;

                $sqlInsert = "INSERT INTO permisos
                                (id_rol, id_modulo, crear, leer, actualizar, eliminar)
                              VALUES
                                (:id_rol, :id_modulo, :crear, :leer, :actualizar, :eliminar)";
                $stmtInsert = $this->conex->prepare($sqlInsert);

                $stmtInsert->bindValue(':id_rol', $id_rol, PDO::PARAM_INT);
                $stmtInsert->bindValue(':id_modulo', $id_modulo, PDO::PARAM_INT);
                $stmtInsert->bindValue(':crear', $crear, PDO::PARAM_INT);
                $stmtInsert->bindValue(':leer', $leer, PDO::PARAM_INT);
                $stmtInsert->bindValue(':actualizar', $actualizar, PDO::PARAM_INT);
                $stmtInsert->bindValue(':eliminar', $eliminar, PDO::PARAM_INT);

                $stmtInsert->execute();
            }

            $this->conex->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conex->inTransaction()) {
                $this->conex->rollBack();
            }
            error_log("Error en actualizarPermisos: " . $e->getMessage());
            return false;
        }
    }

    public function inicializarPermisosVacios($id_rol)
    {
        $modulos = $this->obtenerModulos();
        foreach ($modulos as $m) {
            $stmt = $this->conex->prepare("INSERT INTO permisos
                (id_rol, id_modulo, crear, leer, actualizar, eliminar)
                VALUES (:id_rol, :id_modulo, 0, 0, 0, 0)");

            $stmt->bindValue(':id_rol', $id_rol, PDO::PARAM_INT);
            $stmt->bindValue(':id_modulo', $m['id_modulo'], PDO::PARAM_INT);

            $stmt->execute();
        }
        return true;
    }
}