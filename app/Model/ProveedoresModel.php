<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;

class ProveedoresModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // OBTENER TODOS LOS PROVEEDORES
    public function obtenerProveedores($incluirInactivos = false) {
        try {
            $sql = "SELECT 
                        p.id_proveedor, 
                        p.eliminado,
                        p.rif,
                        p.razon_social, 
                        p.numero_contacto,
                        p.id_persona,
                        pe.nombre, 
                        pe.apellido, 
                        pe.cedula, 
                        pe.telefono, 
                        pe.direccion, 
                        pe.correo_electronico
                    FROM proveedor p
                    LEFT JOIN persona pe ON p.id_persona = pe.id_persona
                    WHERE (:incluir_inactivos = 1 OR p.eliminado = 0)
                    ORDER BY p.id_proveedor DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':incluir_inactivos' => $incluirInactivos ? 1 : 0]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la consulta SQL: " . $e->getMessage());
        }
    }

    // CONTAR PROVEEDORES (para paginación)
    public function contarProveedores() {
        try {
            $sql = "SELECT COUNT(*) FROM proveedor WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Error al contar proveedores: " . $e->getMessage());
        }
    }

    // OBTENER PROVEEDOR POR ID
    public function obtenerProveedorPorId($id, $incluirInactivos = false) {
        try {
            $sql = "SELECT 
                        p.id_proveedor, 
                        p.eliminado,
                        p.rif,
                        p.razon_social, 
                        p.numero_contacto,
                        p.id_persona,
                        pe.nombre, 
                        pe.apellido, 
                        pe.cedula, 
                        pe.telefono, 
                        pe.direccion, 
                        pe.correo_electronico
                    FROM proveedor p
                    LEFT JOIN persona pe ON p.id_persona = pe.id_persona
                    WHERE p.id_proveedor = :id AND (:incluir_inactivos = 1 OR p.eliminado = 0)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                ':incluir_inactivos' => $incluirInactivos ? 1 : 0
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la consulta SQL: " . $e->getMessage());
        }
    }

    // REGISTRAR PROVEEDOR
    public function registrarProveedor(array $datos) {
        $this->db->beginTransaction();
        try {
            $checkSql = "SELECT COUNT(*) FROM proveedor WHERE rif = :rif AND eliminado = 0";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['rif' => $datos['rif']]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("El RIF '{$datos['rif']}' ya está registrado.");
            }

            $sqlPersona = "INSERT INTO persona (nombre, apellido, cedula, telefono, direccion, correo_electronico, eliminado) 
                           VALUES (:nombre, :apellido, :cedula, :telefono, :direccion, :correo_electronico, 0)";
            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([
                ':nombre' => $datos['nombre_contacto'] ?? 'Proveedor',
                ':apellido' => $datos['apellido_contacto'] ?? '',
                ':cedula' => $datos['rif'],
                ':telefono' => $datos['numero_contacto'],
                ':direccion' => $datos['direccion'],
                ':correo_electronico' => $datos['correo_electronico'] ?? null
            ]);
            $idPersona = $this->db->lastInsertId();

            $sqlProveedor = "INSERT INTO proveedor (rif, razon_social, numero_contacto, id_persona, eliminado)
                             VALUES (:rif, :razon_social, :numero_contacto, :id_persona, 0)";
            $stmtProveedor = $this->db->prepare($sqlProveedor);
            $stmtProveedor->execute([
                ':rif' => $datos['rif'],
                ':razon_social' => $datos['razon_social'],
                ':numero_contacto' => $datos['numero_contacto'],
                ':id_persona' => $idPersona
            ]);

            $idProveedor = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $idProveedor > 0 ? $idProveedor : true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al registrar proveedor: " . $e->getMessage());
        }
    }

    // ACTUALIZAR PROVEEDOR
    public function actualizarProveedor($id, array $datos) {
        $this->db->beginTransaction();
        try {
            $checkSql = "SELECT id_persona FROM proveedor WHERE id_proveedor = :id AND eliminado = 0";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['id' => $id]);
            $proveedor = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$proveedor) {
                throw new Exception("El proveedor con ID {$id} no existe.");
            }
            if (!in_array((string) ($datos['eliminado'] ?? ''), ['0', '1'], true)) {
                throw new Exception("El estado del proveedor no es válido.");
            }

            $checkRifSql = "SELECT COUNT(*) FROM proveedor WHERE rif = :rif AND id_proveedor != :id AND eliminado = 0";
            $checkRifStmt = $this->db->prepare($checkRifSql);
            $checkRifStmt->execute(['rif' => $datos['rif'], 'id' => $id]);
            if ($checkRifStmt->fetchColumn() > 0) {
                throw new Exception("El RIF '{$datos['rif']}' ya está registrado en otro proveedor.");
            }

            $sqlPersona = "UPDATE persona SET 
                                nombre = :nombre, 
                                apellido = :apellido, 
                                cedula = :cedula, 
                                telefono = :telefono, 
                                direccion = :direccion, 
                                correo_electronico = :correo_electronico
                            WHERE id_persona = :id_persona";
            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([
                ':nombre' => $datos['nombre_contacto'] ?? 'Proveedor',
                ':apellido' => $datos['apellido_contacto'] ?? '',
                ':cedula' => $datos['rif'],
                ':telefono' => $datos['numero_contacto'],
                ':direccion' => $datos['direccion'],
                ':correo_electronico' => $datos['correo_electronico'] ?? null,
                ':id_persona' => $proveedor['id_persona']
            ]);

            $sqlProveedor = "UPDATE proveedor SET 
                                rif = :rif, 
                                razon_social = :razon_social, 
                                numero_contacto = :numero_contacto,
                                eliminado = :eliminado
                            WHERE id_proveedor = :id AND eliminado = 0";
            $stmtProveedor = $this->db->prepare($sqlProveedor);
            $stmtProveedor->execute([
                ':rif' => $datos['rif'],
                ':razon_social' => $datos['razon_social'],
                ':numero_contacto' => $datos['numero_contacto'],
                ':eliminado' => (int) $datos['eliminado'],
                ':id' => $id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al actualizar proveedor: " . $e->getMessage());
        }
    }

    // ELIMINAR PROVEEDOR
    public function eliminarProveedor($id) {
        $this->db->beginTransaction();
        try {
            $checkSql = "SELECT COUNT(*) FROM nota_de_entrada WHERE id_proveedor = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['id' => $id]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar el proveedor porque tiene notas de entrada asociadas. Primero anule o reasigne esas notas.");
            }

            $checkProductos = $this->db->prepare("SELECT COUNT(*) FROM producto WHERE id_proveedor = :id");
            $checkProductos->execute(['id' => $id]);
            if ($checkProductos->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar el proveedor porque tiene productos asociados.");
            }

            $sqlPersona = "SELECT id_persona FROM proveedor WHERE id_proveedor = :id";
            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute(['id' => $id]);
            $proveedor = $stmtPersona->fetch(PDO::FETCH_ASSOC);

            $sql = "UPDATE proveedor SET eliminado = 1 WHERE id_proveedor = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);

            if ($proveedor) {
                $sqlDeletePersona = "UPDATE persona SET eliminado = 1 WHERE id_persona = :id_persona";
                $stmtDeletePersona = $this->db->prepare($sqlDeletePersona);
                $stmtDeletePersona->execute(['id_persona' => $proveedor['id_persona']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            if (in_array($e->getMessage(), [
                "No se puede eliminar el proveedor porque tiene notas de entrada asociadas. Primero anule o reasigne esas notas.",
                "No se puede eliminar el proveedor porque tiene productos asociados."
            ], true)) {
                throw $e;
            }
            throw new Exception("Error al eliminar proveedor: " . $e->getMessage());
        }
    }

    public function reactivarProveedor($id) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                SELECT id_persona
                FROM proveedor
                WHERE id_proveedor = :id AND eliminado = 1
                FOR UPDATE
            ");
            $stmt->execute([':id' => $id]);
            $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$proveedor) {
                throw new Exception("El proveedor no existe o ya está activo.");
            }

            $stmt = $this->db->prepare("UPDATE proveedor SET eliminado = 0 WHERE id_proveedor = :id");
            $stmt->execute([':id' => $id]);

            if (!empty($proveedor['id_persona'])) {
                $stmt = $this->db->prepare("UPDATE persona SET eliminado = 0 WHERE id_persona = :id_persona");
                $stmt->execute([':id_persona' => $proveedor['id_persona']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception("Error al reactivar proveedor: " . $e->getMessage());
        }
    }

    // BUSCAR PROVEEDORES
    public function buscarProveedores($termino, $incluirInactivos = false) {
        try {
            $sql = "SELECT 
                        p.id_proveedor, 
                        p.eliminado,
                        p.rif,
                        p.razon_social, 
                        p.numero_contacto,
                        p.id_persona,
                        pe.nombre, 
                        pe.apellido, 
                        pe.cedula, 
                        pe.telefono, 
                        pe.direccion, 
                        pe.correo_electronico
                    FROM proveedor p
                    LEFT JOIN persona pe ON p.id_persona = pe.id_persona
                    WHERE (:incluir_inactivos = 1 OR p.eliminado = 0)
                    AND (p.rif LIKE :termino 
                       OR p.razon_social LIKE :termino 
                       OR p.numero_contacto LIKE :termino
                       OR pe.direccion LIKE :termino
                       OR pe.correo_electronico LIKE :termino)
                    ORDER BY p.id_proveedor DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'termino' => "%{$termino}%",
                ':incluir_inactivos' => $incluirInactivos ? 1 : 0
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la consulta SQL: " . $e->getMessage());
        }
    }

    // OBTENER PROVEEDORES PARA SELECT
    public function obtenerProveedoresParaSelect() {
        try {
            $sql = "SELECT 
                        p.id_proveedor, 
                        p.eliminado,
                        p.razon_social,
                        p.rif
                    FROM proveedor p
                    WHERE p.eliminado = 0
                    ORDER BY p.razon_social ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener proveedores: " . $e->getMessage());
        }
    }

    public function proveedorActivoExiste($id) {
        try {
            $sql = "SELECT 1 FROM proveedor WHERE id_proveedor = :id AND eliminado = 0 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Error al validar proveedor: " . $e->getMessage());
        }
    }
}