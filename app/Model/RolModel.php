<?php
// app/Model/RolModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use PDOException;
use Exception;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

class RolModel extends ConnectDB
{
    private $conex;

    public function __construct()
    {
        parent::__construct();
        $this->conex = $this->getConnection();
    }

    public function getAllRoles()
    {
        try {
            $sql = "SELECT
                        r.id_rol,
                        r.nombre_rol,
                        r.eliminado,
                        (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol AND u.eliminado = 0) as total_usuarios
                    FROM rol r
                    ORDER BY r.eliminado ASC, r.id_rol ASC";

            $stmt = $this->conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getAllRoles: " . $e->getMessage());
            return [];
        }
    }

    public function contarRoles($busqueda = null)
    {
        try {
            $busqueda = trim((string) $busqueda);

            if ($busqueda !== '') {
                $sql = "SELECT COUNT(*) FROM rol
                        WHERE nombre_rol LIKE :busqueda";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute([':busqueda' => '%' . $busqueda . '%']);
            } else {
                $sql = "SELECT COUNT(*) FROM rol";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute();
            }

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en contarRoles: " . $e->getMessage());
            return 0;
        }
    }

    public function getRolById($id)
    {
        try {
            $sql = "SELECT
                        r.id_rol,
                        r.nombre_rol,
                        r.eliminado,
                        (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol AND u.eliminado = 0) as total_usuarios
                    FROM rol r
                    WHERE r.id_rol = :id
                    LIMIT 1";

            $stmt = $this->conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getRolById: " . $e->getMessage());
            return null;
        }
    }

    public function buscarRoles($busqueda)
    {
        try {
            $sql = "SELECT
                        r.id_rol,
                        r.nombre_rol,
                        r.eliminado,
                        (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol AND u.eliminado = 0) as total_usuarios
                    FROM rol r
                    WHERE r.nombre_rol LIKE :busqueda
                    ORDER BY r.eliminado ASC, r.id_rol ASC";

            $stmt = $this->conex->prepare($sql);
            $stmt->execute([':busqueda' => '%' . $busqueda . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarRoles: " . $e->getMessage());
            return [];
        }
    }

    public function crearRol($nombre)
    {
        try {
            $sqlCheck = "SELECT COUNT(*) as total FROM rol WHERE nombre_rol = :nombre AND eliminado = 0";
            $stmtCheck = $this->conex->prepare($sqlCheck);
            $stmtCheck->execute([':nombre' => $nombre]);
            $existe = $stmtCheck->fetchColumn();

            if ($existe > 0) {
                throw new Exception("Ya existe un rol con el nombre '" . $nombre . "'");
            }

            $sql = "INSERT INTO rol (nombre_rol, eliminado) VALUES (:nombre, 0)";
            $stmt = $this->conex->prepare($sql);
            $stmt->execute([':nombre' => $nombre]);
            return $this->conex->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error PDO en crearRol: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function actualizarRol($id, $nombre)
    {
        try {
            $rol = $this->getRolById($id);
            if (!$rol) {
                throw new Exception("El rol no existe");
            }

            if ($id == 1) {
                throw new Exception("No puedes modificar el rol de Administrador");
            }

            $sqlCheck = "SELECT COUNT(*) as total FROM rol WHERE nombre_rol = :nombre AND id_rol != :id AND eliminado = 0";
            $stmtCheck = $this->conex->prepare($sqlCheck);
            $stmtCheck->execute([':nombre' => $nombre, ':id' => $id]);
            $existe = $stmtCheck->fetchColumn();

            if ($existe > 0) {
                throw new Exception("Ya existe otro rol con el nombre '" . $nombre . "'");
            }

            $sql = "UPDATE rol SET nombre_rol = :nombre WHERE id_rol = :id";
            $stmt = $this->conex->prepare($sql);
            return $stmt->execute([':id' => $id, ':nombre' => $nombre]);
        } catch (PDOException $e) {
            error_log("Error PDO en actualizarRol: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminarRol($id)
    {
        try {
            $sqlCheck = "SELECT id_rol FROM rol WHERE id_rol = :id AND eliminado = 0 LIMIT 1";
            $stmtCheck = $this->conex->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $id]);
            $rol = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$rol) {
                throw new Exception("El rol no existe o ya fue eliminado");
            }

            if ($id == 1) {
                throw new Exception("No puedes eliminar el rol de Administrador");
            }

            $sqlUser = "SELECT COUNT(*) FROM usuario WHERE id_rol = :id AND eliminado = 0";
            $stmtUser = $this->conex->prepare($sqlUser);
            $stmtUser->execute([':id' => $id]);
            $total = $stmtUser->fetchColumn();

            if ($total > 0) {
                throw new Exception("No puedes inactivar este rol porque tiene $total usuarios asignados");
            }

            $sql = "UPDATE rol SET eliminado = 1 WHERE id_rol = :id";
            $stmt = $this->conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error PDO en eliminarRol: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function reactivarRol($id)
    {
        try {
            $sqlCheck = "SELECT id_rol FROM rol WHERE id_rol = :id LIMIT 1";
            $stmtCheck = $this->conex->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $id]);
            $rol = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$rol) {
                throw new Exception("El rol no existe");
            }

            $sql = "UPDATE rol SET eliminado = 0 WHERE id_rol = :id";
            $stmt = $this->conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error PDO en reactivarRol: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function existeRol($nombre, $idExcluir = null)
    {
        try {
            $sql = "SELECT COUNT(*) FROM rol
                    WHERE nombre_rol = :nombre
                    AND eliminado = 0";

            $params = [':nombre' => $nombre];

            if ($idExcluir !== null) {
                $sql .= " AND id_rol != :id";
                $params[':id'] = $idExcluir;
            }

            $stmt = $this->conex->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en existeRol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🔥 NUEVO: cuenta usuarios activos asignados a un rol.
     * Movido del controlador (antes hacía SQL directo en delete_ajax).
     */
    public function contarUsuariosPorRol($idRol)
    {
        try {
            $stmt = $this->conex->prepare("SELECT COUNT(*) FROM usuario WHERE id_rol = :id AND eliminado = 0");
            $stmt->execute([':id' => $idRol]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en contarUsuariosPorRol: " . $e->getMessage());
            return 0;
        }
    }
}