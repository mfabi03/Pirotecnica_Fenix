<?php
// app/Model/RolModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class RolModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ==========================================
    // 1. OBTENER TODOS LOS ROLES
    // ==========================================
    public function getAllRoles() {
        try {
            // ✅ CORREGIDO: tabla 'usuario' (singular) y columna 'id_rol'
            $sql = "SELECT 
                        r.id_rol, 
                        r.nombre_rol,
                        (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol) as total_usuarios
                    FROM rol r 
                    ORDER BY r.id_rol ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getAllRoles: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 2. OBTENER ROL POR ID
    // ==========================================
    public function getRolById($id) {
        try {
            // ✅ CORREGIDO: tabla 'usuario' (singular) y columna 'id_rol'
            $sql = "SELECT 
                        r.id_rol, 
                        r.nombre_rol,
                        (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol) as total_usuarios
                    FROM rol r 
                    WHERE r.id_rol = :id 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getRolById: " . $e->getMessage());
            return null;
        }
    }

    // ==========================================
    // 3. BUSCAR ROLES POR NOMBRE
    // ==========================================
    public function buscarRoles($busqueda) {
        try {
            // ✅ CORREGIDO: tabla 'usuario' (singular) y columna 'id_rol'
            $sql = "SELECT 
                        r.id_rol, 
                        r.nombre_rol,
                        (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol) as total_usuarios
                    FROM rol r 
                    WHERE r.nombre_rol LIKE :busqueda
                    ORDER BY r.id_rol ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['busqueda' => '%' . $busqueda . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en buscarRoles: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 4. CREAR NUEVO ROL
    // ==========================================
    public function crearRol($nombre) {
        try {
            // Verificar si ya existe un rol con ese nombre
            $sqlCheck = "SELECT COUNT(*) as total FROM rol WHERE nombre_rol = :nombre";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute(['nombre' => $nombre]);
            $existe = $stmtCheck->fetchColumn();
            
            if ($existe > 0) {
                throw new Exception("Ya existe un rol con el nombre '" . $nombre . "'");
            }
            
            $sql = "INSERT INTO rol (nombre_rol) VALUES (:nombre)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['nombre' => $nombre]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error en crearRol: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // 5. ACTUALIZAR ROL
    // ==========================================
    public function actualizarRol($id, $nombre) {
        try {
            // Verificar si el rol existe
            $rol = $this->getRolById($id);
            if (!$rol) {
                throw new Exception("El rol no existe");
            }
            
            // No permitir modificar el rol de Administrador (id = 1)
            if ($id == 1) {
                throw new Exception("No puedes modificar el rol de Administrador");
            }
            
            // Verificar si ya existe otro rol con ese nombre
            $sqlCheck = "SELECT COUNT(*) as total FROM rol WHERE nombre_rol = :nombre AND id_rol != :id";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute(['nombre' => $nombre, 'id' => $id]);
            $existe = $stmtCheck->fetchColumn();
            
            if ($existe > 0) {
                throw new Exception("Ya existe otro rol con el nombre '" . $nombre . "'");
            }
            
            $sql = "UPDATE rol SET nombre_rol = :nombre WHERE id_rol = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id, 'nombre' => $nombre]);
        } catch (Exception $e) {
            error_log("Error en actualizarRol: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // 6. ELIMINAR ROL
    // ==========================================
    public function eliminarRol($id) {
        try {
            // Verificar si el rol existe
            $rol = $this->getRolById($id);
            if (!$rol) {
                throw new Exception("El rol no existe");
            }
            
            // No permitir eliminar el rol de Administrador (id = 1)
            if ($id == 1) {
                throw new Exception("No puedes eliminar el rol de Administrador");
            }
            
            // ✅ CORREGIDO: tabla 'usuario' (singular) y columna 'id_rol'
            $sqlCheck = "SELECT COUNT(*) as total FROM usuario WHERE id_rol = :id";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute(['id' => $id]);
            $total = $stmtCheck->fetchColumn();
            
            if ($total > 0) {
                throw new Exception("No puedes eliminar este rol porque tiene $total usuarios asignados");
            }
            
            $sql = "DELETE FROM rol WHERE id_rol = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            error_log("Error en eliminarRol: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // 7. OBTENER ROLES CON CONTEO DE USUARIOS
    // ==========================================
    public function getRolesConConteo() {
        try {
            // ✅ CORREGIDO: tabla 'usuario' (singular) y columna 'id_rol'
            $sql = "SELECT 
                        r.id_rol, 
                        r.nombre_rol,
                        COUNT(u.id_usuario) as total_usuarios
                    FROM rol r
                    LEFT JOIN usuario u ON u.id_rol = r.id_rol
                    GROUP BY r.id_rol, r.nombre_rol
                    ORDER BY r.id_rol ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getRolesConConteo: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 8. OBTENER ROL POR NOMBRE
    // ==========================================
    public function getRolByNombre($nombre) {
        try {
            $sql = "SELECT id_rol, nombre_rol FROM rol WHERE nombre_rol = :nombre LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['nombre' => $nombre]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getRolByNombre: " . $e->getMessage());
            return null;
        }
    }

    // ==========================================
    // 9. VERIFICAR SI UN ROL EXISTE
    // ==========================================
    public function rolExiste($id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM rol WHERE id_rol = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en rolExiste: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // 10. OBTENER ROLES DISPONIBLES (Sin Administrador)
    // ==========================================
    public function getRolesDisponibles() {
        try {
            $sql = "SELECT 
                        id_rol, 
                        nombre_rol
                    FROM rol 
                    WHERE id_rol != 1
                    ORDER BY nombre_rol ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getRolesDisponibles: " . $e->getMessage());
            return [];
        }
    }
}
?>