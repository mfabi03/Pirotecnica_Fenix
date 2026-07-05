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

    /**
     * Obtener todos los proveedores con datos de persona (JOIN)
     */
    public function obtenerProveedores() {
        try {
            $sql = "SELECT 
                        p.id_proveedor, 
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
                    ORDER BY p.id_proveedor DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la consulta SQL: " . $e->getMessage());
        }
    }

    /**
     * Obtener un proveedor por ID con datos de persona (JOIN)
     */
    public function obtenerProveedorPorId($id) {
        try {
            $sql = "SELECT 
                        p.id_proveedor, 
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
                    WHERE p.id_proveedor = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la consulta SQL: " . $e->getMessage());
        }
    }

    /**
     * Registrar un nuevo proveedor (con persona asociada)
     */
    public function registrarProveedor(array $datos) {
        $this->db->beginTransaction();
        try {
            // 1. Verificar si el RIF ya existe
            $checkSql = "SELECT COUNT(*) FROM proveedor WHERE rif = :rif";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['rif' => $datos['rif']]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("El RIF '{$datos['rif']}' ya está registrado.");
            }

            // 2. Insertar en persona (los datos de contacto)
            $sqlPersona = "INSERT INTO persona (
                                nombre, 
                                apellido, 
                                cedula, 
                                telefono, 
                                direccion, 
                                correo_electronico
                            ) VALUES (
                                :nombre, 
                                :apellido, 
                                :cedula, 
                                :telefono, 
                                :direccion, 
                                :correo_electronico
                            )";
            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([
                ':nombre' => $datos['nombre_contacto'] ?? 'Proveedor',
                ':apellido' => $datos['apellido_contacto'] ?? '',
                ':cedula' => $datos['rif'], // Usamos el RIF como cédula
                ':telefono' => $datos['numero_contacto'],
                ':direccion' => $datos['direccion'],
                ':correo_electronico' => $datos['correo_electronico'] ?? null
            ]);
            $idPersona = $this->db->lastInsertId();

            // 3. Insertar en proveedor (con referencia a persona)
            $sqlProveedor = "INSERT INTO proveedor (
                                rif, 
                                razon_social, 
                                numero_contacto, 
                                id_persona
                            ) VALUES (
                                :rif, 
                                :razon_social, 
                                :numero_contacto, 
                                :id_persona
                            )";
            $stmtProveedor = $this->db->prepare($sqlProveedor);
            $stmtProveedor->execute([
                ':rif' => $datos['rif'],
                ':razon_social' => $datos['razon_social'],
                ':numero_contacto' => $datos['numero_contacto'],
                ':id_persona' => $idPersona
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al registrar proveedor: " . $e->getMessage());
        }
    }

    /**
     * Actualizar un proveedor y su persona asociada
     */
    public function actualizarProveedor($id, array $datos) {
        $this->db->beginTransaction();
        try {
            // 1. Verificar si el proveedor existe
            $checkSql = "SELECT id_persona FROM proveedor WHERE id_proveedor = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['id' => $id]);
            $proveedor = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$proveedor) {
                throw new Exception("El proveedor con ID {$id} no existe.");
            }

            // 2. Verificar si el RIF ya existe (excluyendo el actual)
            $checkRifSql = "SELECT COUNT(*) FROM proveedor WHERE rif = :rif AND id_proveedor != :id";
            $checkRifStmt = $this->db->prepare($checkRifSql);
            $checkRifStmt->execute(['rif' => $datos['rif'], 'id' => $id]);
            if ($checkRifStmt->fetchColumn() > 0) {
                throw new Exception("El RIF '{$datos['rif']}' ya está registrado en otro proveedor.");
            }

            // 3. Actualizar persona
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

            // 4. Actualizar proveedor
            $sqlProveedor = "UPDATE proveedor SET 
                                rif = :rif, 
                                razon_social = :razon_social, 
                                numero_contacto = :numero_contacto
                            WHERE id_proveedor = :id";
            $stmtProveedor = $this->db->prepare($sqlProveedor);
            $stmtProveedor->execute([
                ':rif' => $datos['rif'],
                ':razon_social' => $datos['razon_social'],
                ':numero_contacto' => $datos['numero_contacto'],
                ':id' => $id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al actualizar proveedor: " . $e->getMessage());
        }
    }

    /**
     * Eliminar un proveedor (y su persona asociada)
     */
    public function eliminarProveedor($id) {
        $this->db->beginTransaction();
        try {
            // 1. Verificar si tiene notas de entrada asociadas
            $checkSql = "SELECT COUNT(*) FROM nota_de_entrada WHERE id_proveedor = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['id' => $id]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar el proveedor porque tiene notas de entrada asociadas.");
            }

            // 2. Obtener id_persona
            $sqlPersona = "SELECT id_persona FROM proveedor WHERE id_proveedor = :id";
            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute(['id' => $id]);
            $proveedor = $stmtPersona->fetch(PDO::FETCH_ASSOC);

            // 3. Eliminar proveedor
            $sql = "DELETE FROM proveedor WHERE id_proveedor = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);

            // 4. Eliminar persona asociada
            if ($proveedor) {
                $sqlDeletePersona = "DELETE FROM persona WHERE id_persona = :id_persona";
                $stmtDeletePersona = $this->db->prepare($sqlDeletePersona);
                $stmtDeletePersona->execute(['id_persona' => $proveedor['id_persona']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al eliminar proveedor: " . $e->getMessage());
        }
    }

    /**
     * Buscar proveedores por término con datos de persona
     */
    public function buscarProveedores($termino) {
    try {
        $sql = "SELECT 
                    p.id_proveedor, 
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
                WHERE p.rif LIKE :termino 
                   OR p.razon_social LIKE :termino 
                   OR p.numero_contacto LIKE :termino
                   OR pe.direccion LIKE :termino
                   OR pe.correo_electronico LIKE :termino
                ORDER BY p.id_proveedor DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['termino' => "%{$termino}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("Error en la consulta SQL: " . $e->getMessage());
    }
}

    /**
     * Obtener proveedores para select
     */
    public function obtenerProveedoresParaSelect() {
        try {
            $sql = "SELECT 
                        p.id_proveedor, 
                        p.razon_social,
                        p.rif
                    FROM proveedor p
                    ORDER BY p.razon_social ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener proveedores: " . $e->getMessage());
        }
    }
}