<?php
// app/Model/UsuarioModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class UsuarioModel {
    private $db;
    private $lastError = null;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener todos los usuarios con su persona y rol
     */
    public function obtenerUsuariosConPersona() {
        try {
            $sql = "SELECT 
                        u.id_usuario,
                        u.usuario,
                        u.id_rol,
                        p.id_persona,
                        p.nombre,
                        p.apellido,
                        p.direccion,
                        p.cedula,
                        p.telefono,
                        p.correo_electronico,
                        r.nombre_rol AS rol_nombre
                    FROM usuario u
                    INNER JOIN persona p ON u.id_persona = p.id_persona
                    LEFT JOIN rol r ON u.id_rol = r.id_rol
                    ORDER BY u.id_usuario DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerUsuariosConPersona: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un usuario con su persona asociada por ID
     */
    public function obtenerUsuarioPorId($id) {
        try {
            $sql = "SELECT 
                        u.id_usuario,
                        u.usuario,
                        u.id_rol,
                        p.id_persona,
                        p.nombre,
                        p.apellido,
                        p.direccion,
                        p.cedula,
                        p.telefono,
                        p.correo_electronico,
                        r.nombre_rol AS rol_nombre
                    FROM usuario u
                    INNER JOIN persona p ON u.id_persona = p.id_persona
                    LEFT JOIN rol r ON u.id_rol = r.id_rol
                    WHERE u.id_usuario = :id
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerUsuarioPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar usuarios por nombre, usuario o cédula
     */
    public function buscarUsuarios($busqueda) {
        try {
            $sql = "SELECT 
                        u.id_usuario,
                        u.usuario,
                        u.id_rol,
                        p.id_persona,
                        p.nombre,
                        p.apellido,
                        p.direccion,
                        p.cedula,
                        p.telefono,
                        p.correo_electronico,
                        r.nombre_rol AS rol_nombre
                    FROM usuario u
                    INNER JOIN persona p ON u.id_persona = p.id_persona
                    LEFT JOIN rol r ON u.id_rol = r.id_rol
                    WHERE u.usuario LIKE :busqueda
                       OR p.nombre LIKE :busqueda 
                       OR p.apellido LIKE :busqueda
                       OR p.cedula LIKE :busqueda
                    ORDER BY u.id_usuario DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':busqueda' => '%' . $busqueda . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en buscarUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar un nuevo usuario con su persona asociada
     */
    public function registrarUsuarioCompleto($datosPersona, $datosUsuario) {
        $this->db->beginTransaction();
        try {
            // 1. Verificar si el nombre de usuario ya existe
            $checkUserSql = "SELECT COUNT(*) FROM usuario WHERE usuario = :usuario";
            $checkUserStmt = $this->db->prepare($checkUserSql);
            $checkUserStmt->execute([':usuario' => $datosUsuario['usuario']]);
            if ($checkUserStmt->fetchColumn() > 0) {
                throw new Exception("El nombre de usuario '{$datosUsuario['usuario']}' ya está registrado.");
            }

            // 2. Insertar PERSONA
            $sqlPersona = "INSERT INTO persona 
                                (nombre, apellido, direccion, cedula, telefono, correo_electronico) 
                            VALUES 
                                (:nombre, :apellido, :direccion, :cedula, :telefono, :correo_electronico)";
            
            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([
                ':nombre' => $datosPersona['nombre'],
                ':apellido' => $datosPersona['apellido'] ?? '',
                ':direccion' => $datosPersona['direccion'] ?? null,
                ':cedula' => $datosPersona['cedula'],
                ':telefono' => $datosPersona['telefono'] ?? null,
                ':correo_electronico' => $datosPersona['correo'] ?? null
            ]);

            $idPersona = $this->db->lastInsertId();
            if ($idPersona <= 0) {
                throw new Exception("No se pudo insertar la persona.");
            }

            // 3. Insertar USUARIO
            $sqlUsuario = "INSERT INTO usuario 
                                (id_persona, usuario, id_rol, clave) 
                            VALUES 
                                (:id_persona, :usuario, :id_rol, :clave)";
            
            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':id_persona' => $idPersona,
                ':usuario' => $datosUsuario['usuario'],
                ':id_rol' => $datosUsuario['id_rol'] ?? 2,
                ':clave' => $datosUsuario['clave']
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = $e->getMessage();
            error_log("Error en registrarUsuarioCompleto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear usuario (método simplificado)
     */
    public function crearUsuario($datos) {
        try {
            $datosPersona = [
                'nombre' => $datos['nombre_completo'] ?? $datos['nombre'] ?? 'Usuario',
                'apellido' => $datos['apellido'] ?? '',
                'direccion' => $datos['direccion'] ?? null,
                'cedula' => $datos['cedula'] ?? '00000000',
                'telefono' => $datos['telefono'] ?? null,
                'correo' => $datos['email'] ?? null
            ];

            $datosUsuario = [
                'usuario' => $datos['usuario'],
                'id_rol' => $datos['id_rol'] ?? 2,
                'clave' => $datos['clave']
            ];

            return $this->registrarUsuarioCompleto($datosPersona, $datosUsuario);
        } catch (Exception $e) {
            error_log("Error en crearUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un usuario y su persona asociada
     */
    public function actualizarUsuario($id, array $datos) {
        $this->db->beginTransaction();
        try {
            // 1. Verificar si el usuario existe
            $checkSql = "SELECT id_persona FROM usuario WHERE id_usuario = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            $usuario = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$usuario) {
                throw new Exception("El usuario con ID {$id} no existe.");
            }

            // 2. Verificar si el nombre de usuario ya existe (excluyendo el actual)
            $checkUserSql = "SELECT COUNT(*) FROM usuario WHERE usuario = :usuario AND id_usuario != :id";
            $checkUserStmt = $this->db->prepare($checkUserSql);
            $checkUserStmt->execute([
                ':usuario' => $datos['usuario'],
                ':id' => $id
            ]);
            if ($checkUserStmt->fetchColumn() > 0) {
                throw new Exception("El nombre de usuario '{$datos['usuario']}' ya está registrado en otro usuario.");
            }

            // 3. Actualizar PERSONA
            $sqlPersona = "UPDATE persona SET 
                                nombre = :nombre,
                                apellido = :apellido,
                                direccion = :direccion,
                                cedula = :cedula,
                                telefono = :telefono,
                                correo_electronico = :correo_electronico
                            WHERE id_persona = :id_persona";
            
            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([
                ':nombre' => $datos['nombre'] ?? $datos['nombre_completo'] ?? 'Usuario',
                ':apellido' => $datos['apellido'] ?? '',
                ':direccion' => $datos['direccion'] ?? null,
                ':cedula' => $datos['cedula'] ?? '00000000',
                ':telefono' => $datos['telefono'] ?? null,
                ':correo_electronico' => $datos['correo_electronico'] ?? $datos['email'] ?? null,
                ':id_persona' => $usuario['id_persona']
            ]);

            // 4. Actualizar USUARIO
            $sqlUsuario = "UPDATE usuario SET 
                                usuario = :usuario,
                                id_rol = :id_rol";
            
            if (isset($datos['clave']) && !empty($datos['clave'])) {
                $sqlUsuario .= ", clave = :clave";
            }
            
            $sqlUsuario .= " WHERE id_usuario = :id";
            
            $params = [
                ':usuario' => $datos['usuario'],
                ':id_rol' => $datos['id_rol'] ?? 2,
                ':id' => $id
            ];
            
            if (isset($datos['clave']) && !empty($datos['clave'])) {
                $params[':clave'] = password_hash($datos['clave'], PASSWORD_DEFAULT);
            }
            
            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute($params);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = $e->getMessage();
            error_log("Error en actualizarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un usuario y su persona asociada
     */
    public function eliminarUsuario($id) {
        $this->db->beginTransaction();
        try {
            // 1. Verificar si el usuario existe
            $checkSql = "SELECT id_persona FROM usuario WHERE id_usuario = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            $usuario = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$usuario) {
                throw new Exception("El usuario con ID {$id} no existe.");
            }

            $idPersona = $usuario['id_persona'];

            // 2. Eliminar USUARIO
            $sqlUsuario = "DELETE FROM usuario WHERE id_usuario = :id";
            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([':id' => $id]);

            // 3. Eliminar PERSONA
            $sqlPersona = "DELETE FROM persona WHERE id_persona = :id_persona";
            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([':id_persona' => $idPersona]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = $e->getMessage();
            error_log("Error en eliminarUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function getLastError(): ?string {
        return $this->lastError;
    }
}
?>