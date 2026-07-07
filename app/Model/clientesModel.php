<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;

class clientesModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ==========================================
    // VALIDAR EDAD
    // ==========================================
    public function validarEdad($fechaNacimiento, $edadMinima = 18) {
        try {
            $fechaActual = new \DateTime();
            $fechaNac = new \DateTime($fechaNacimiento);

            if ($fechaNac > $fechaActual) {
                return ['valido' => false, 'mensaje' => 'La fecha de nacimiento no puede ser futura'];
            }

            $edad = $fechaActual->diff($fechaNac)->y;

            if ($edad < $edadMinima) {
                return ['valido' => false, 'mensaje' => "Edad mínima requerida: $edadMinima años. Edad detectada: $edad años"];
            }

            return ['valido' => true, 'edad' => $edad];

        } catch (\Exception $e) {
            return ['valido' => false, 'mensaje' => 'Error al validar la fecha de nacimiento'];
        }
    }

    // ==========================================
    // REGISTRAR CLIENTE NATURAL
    // ==========================================
    public function registrarClienteNatural($datos) {
        if (!$this->db) {
            throw new \Exception("No hay conexión a la base de datos");
        }

        if (empty($datos['cedula'])) {
            throw new \Exception("La cédula es obligatoria.");
        }

        $validacionEdad = $this->validarEdad($datos['fecha_de_nacimiento'], 18);
        if (!$validacionEdad['valido']) {
            throw new \Exception($validacionEdad['mensaje']);
        }

        $this->db->beginTransaction();
        try {
            if ($this->existeCedula($datos['cedula'])) {
                throw new \Exception("Ya existe un cliente con la cédula: " . $datos['cedula']);
            }

            $sql = "INSERT INTO persona (
                        cedula,
                        nombre,
                        apellido,
                        telefono,
                        correo_electronico,
                        direccion
                    ) VALUES (
                        :cedula,
                        :nombre,
                        :apellido,
                        :telefono,
                        :correo_electronico,
                        :direccion
                    )";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                ':cedula' => $datos['cedula'],
                ':nombre' => $datos['nombre'],
                ':apellido' => $datos['apellido'],
                ':telefono' => $datos['telefono'],
                ':correo_electronico' => $datos['correo_electronico'] ?? null,
                ':direccion' => $datos['direccion'] ?? null
            ]);

            if (!$resultado) {
                throw new \Exception("Error al insertar en la tabla persona");
            }

            $idPersona = $this->db->lastInsertId();

            $sql2 = "INSERT INTO cliente_natural (id_persona, fecha_de_nacimiento) 
                     VALUES (:id_persona, :fecha_de_nacimiento)";
            $stmt2 = $this->db->prepare($sql2);
            $resultado2 = $stmt2->execute([
                ':id_persona' => $idPersona,
                ':fecha_de_nacimiento' => $datos['fecha_de_nacimiento']
            ]);

            if (!$resultado2) {
                throw new \Exception("Error al insertar en la tabla cliente_natural");
            }

            $this->db->commit();
            return $idPersona;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ==========================================
    // REGISTRAR CLIENTE JURIDICO
    // ==========================================
    public function registrarClienteJuridico($datos) {
        if (!$this->db) {
            throw new \Exception("No hay conexión a la base de datos");
        }

        if (empty($datos['rif'])) {
            throw new \Exception("El RIF es obligatorio.");
        }

        $this->db->beginTransaction();
        try {
            if ($this->existeRif($datos['rif'])) {
                throw new \Exception("Ya existe un cliente con el RIF: " . $datos['rif']);
            }

            $sql = "INSERT INTO persona (
                        cedula,
                        nombre,
                        apellido,
                        telefono,
                        correo_electronico,
                        direccion
                    ) VALUES (
                        :cedula,
                        :nombre,
                        :apellido,
                        :telefono,
                        :correo_electronico,
                        :direccion
                    )";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                ':cedula' => $datos['cedula'],
                ':nombre' => $datos['razon_social'],
                ':apellido' => '',
                ':telefono' => $datos['telefono'],
                ':correo_electronico' => $datos['correo_electronico'] ?? null,
                ':direccion' => $datos['direccion'] ?? null
            ]);

            if (!$resultado) {
                throw new \Exception("Error al insertar en la tabla persona");
            }

            $idPersona = $this->db->lastInsertId();

            $sql2 = "INSERT INTO cliente_juridico (id_persona, rif, razon_social) 
                     VALUES (:id_persona, :rif, :razon_social)";
            $stmt2 = $this->db->prepare($sql2);
            $resultado2 = $stmt2->execute([
                ':id_persona' => $idPersona,
                ':rif' => $datos['rif'],
                ':razon_social' => $datos['razon_social']
            ]);

            if (!$resultado2) {
                throw new \Exception("Error al insertar en la tabla cliente_juridico");
            }

            $this->db->commit();
            return $idPersona;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ==========================================
    // ACTUALIZAR CLIENTE NATURAL
    // ==========================================
    public function actualizarClienteNatural($id, $datos) {
        try {
            if (empty($datos['cedula'])) {
                throw new \Exception("La cédula es obligatoria.");
            }

            $sql = "UPDATE persona SET 
                        cedula = :cedula,
                        nombre = :nombre,
                        apellido = :apellido,
                        telefono = :telefono,
                        correo_electronico = :correo_electronico,
                        direccion = :direccion
                    WHERE id_persona = :id";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                ':cedula' => $datos['cedula'],
                ':nombre' => $datos['nombre'],
                ':apellido' => $datos['apellido'],
                ':telefono' => $datos['telefono'],
                ':correo_electronico' => $datos['correo_electronico'] ?? null,
                ':direccion' => $datos['direccion'] ?? null,
                ':id' => $id
            ]);

            if (!$resultado) {
                throw new \Exception("Error al actualizar persona");
            }

            $sql2 = "UPDATE cliente_natural SET fecha_de_nacimiento = :fecha_de_nacimiento 
                     WHERE id_persona = :id";
            $stmt2 = $this->db->prepare($sql2);
            return $stmt2->execute([
                ':fecha_de_nacimiento' => $datos['fecha_de_nacimiento'],
                ':id' => $id
            ]);

        } catch (\PDOException $e) {
            error_log("Error en actualizarClienteNatural: " . $e->getMessage());
            throw $e;
        }
    }

    // ==========================================
    // ACTUALIZAR CLIENTE JURIDICO
    // ==========================================
    public function actualizarClienteJuridico($id, $datos) {
        try {
            if (empty($datos['rif'])) {
                throw new \Exception("El RIF es obligatorio.");
            }

            $sql = "UPDATE persona SET 
                        cedula = :cedula,
                        nombre = :nombre,
                        telefono = :telefono,
                        correo_electronico = :correo_electronico,
                        direccion = :direccion
                    WHERE id_persona = :id";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                ':cedula' => $datos['cedula'],
                ':nombre' => $datos['razon_social'],
                ':telefono' => $datos['telefono'],
                ':correo_electronico' => $datos['correo_electronico'] ?? null,
                ':direccion' => $datos['direccion'] ?? null,
                ':id' => $id
            ]);

            if (!$resultado) {
                throw new \Exception("Error al actualizar persona");
            }

            $sql2 = "UPDATE cliente_juridico SET 
                        rif = :rif,
                        razon_social = :razon_social
                    WHERE id_persona = :id";
            $stmt2 = $this->db->prepare($sql2);
            return $stmt2->execute([
                ':rif' => $datos['rif'],
                ':razon_social' => $datos['razon_social'],
                ':id' => $id
            ]);

        } catch (\PDOException $e) {
            error_log("Error en actualizarClienteJuridico: " . $e->getMessage());
            throw $e;
        }
    }

    // ==========================================
    //  BUSCAR CLIENTES (AUTOCOMPLETADO)
    // ==========================================
    public function buscarClientes($termino) {
        try {
            $sql = "SELECT 
                        p.id_persona AS id_cliente,
                        p.nombre,
                        p.apellido,
                        cj.razon_social,
                        CASE 
                            WHEN cj.id_cliente_juridico IS NOT NULL THEN cj.razon_social
                            WHEN cn.id_cliente_natural IS NOT NULL THEN CONCAT(p.nombre, ' ', p.apellido)
                            ELSE CONCAT(p.nombre, ' ', p.apellido)
                        END AS nombre_cliente,
                        p.cedula,
                        cj.rif
                    FROM persona p
                    LEFT JOIN cliente_natural cn ON p.id_persona = cn.id_persona
                    LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                    WHERE (cj.id_cliente_juridico IS NOT NULL 
                        OR cn.id_cliente_natural IS NOT NULL)
                    AND (p.nombre LIKE :termino 
                        OR p.apellido LIKE :termino 
                        OR p.cedula LIKE :termino
                        OR cj.razon_social LIKE :termino
                        OR cj.rif LIKE :termino)
                    ORDER BY nombre_cliente ASC
                    LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['termino' => "%$termino%"]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en buscarClientes: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // BUSCAR CLIENTES CON FILTRO (BÚSQUEDA GENERAL Y TIPO)
    // ==========================================
    public function buscarClientesFiltrados($termino = null, $tipo = 'todos') {
        try {
            $params = [];
            $where = [];

            $sql = "SELECT
                        p.id_persona AS id_cliente,
                        p.cedula,
                        p.nombre,
                        p.apellido,
                        p.telefono,
                        p.correo_electronico,
                        p.direccion,
                        cj.rif,
                        cj.razon_social,
                        cn.fecha_de_nacimiento,
                        CASE
                            WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico'
                            WHEN cn.id_cliente_natural IS NOT NULL THEN 'Natural'
                            ELSE 'Natural'
                        END AS tipo_cliente
                    FROM persona p
                    LEFT JOIN cliente_natural cn ON p.id_persona = cn.id_persona
                    LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                    WHERE (cj.id_cliente_juridico IS NOT NULL OR cn.id_cliente_natural IS NOT NULL)";

            $termino = trim((string) $termino);
            if ($termino !== '') {
                $where[] = "(
                    LOWER(CONCAT(COALESCE(p.nombre, ''), ' ', COALESCE(p.apellido, ''))) LIKE :termino
                    OR LOWER(p.nombre) LIKE :termino
                    OR LOWER(p.apellido) LIKE :termino
                    OR LOWER(p.cedula) LIKE :termino
                    OR LOWER(p.telefono) LIKE :termino
                    OR LOWER(COALESCE(p.correo_electronico, '')) LIKE :termino
                    OR LOWER(COALESCE(cj.razon_social, '')) LIKE :termino
                    OR LOWER(COALESCE(cj.rif, '')) LIKE :termino
                )";
                $params[':termino'] = '%' . mb_strtolower($termino, 'UTF-8') . '%';
            }

            if ($tipo === 'Natural') {
                $where[] = "cn.id_cliente_natural IS NOT NULL";
            } elseif ($tipo === 'Jurídico' || $tipo === 'Juridico' || strtolower($tipo) === 'juridico') {
                $where[] = "cj.id_cliente_juridico IS NOT NULL";
            }

            if (!empty($where)) {
                $sql .= ' AND ' . implode(' AND ', $where);
            }

            $sql .= ' ORDER BY p.nombre ASC';

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarClientesFiltrados: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // OBTENER TODOS LOS CLIENTES
    // ==========================================
    // ==========================================
    // OBTENER TODOS LOS CLIENTES (CON TODOS LOS CAMPOS)
    // ==========================================
    public function obtenerClientes() {
        try {
            $sql = "SELECT 
                    p.id_persona AS id_cliente,
                    p.cedula,
                    p.nombre,
                    p.apellido,
                    p.telefono,
                    p.correo_electronico,
                    p.direccion,
                    cj.rif,
                    cj.razon_social,
                    cn.fecha_de_nacimiento,
                    CASE 
                        WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico'
                        WHEN cn.id_cliente_natural IS NOT NULL THEN 'Natural'
                        ELSE 'Natural'
                    END AS tipo_cliente
                FROM persona p
                LEFT JOIN cliente_natural cn ON p.id_persona = cn.id_persona
                LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                WHERE cj.id_cliente_juridico IS NOT NULL 
                   OR cn.id_cliente_natural IS NOT NULL
                ORDER BY p.nombre ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerClientes: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // OBTENER CLIENTE POR ID
    // ==========================================
    public function obtenerClientePorId($id) {
        try {
            $sql = "SELECT 
                        p.id_persona AS id_cliente,
                        p.cedula,
                        p.nombre,
                        p.apellido,
                        p.telefono,
                        p.correo_electronico,
                        p.direccion,
                        cn.fecha_de_nacimiento,
                        cj.rif,
                        cj.razon_social,
                        CASE 
                            WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' 
                            ELSE 'Natural' 
                        END AS tipo_cliente
                    FROM persona p
                    LEFT JOIN cliente_natural cn ON p.id_persona = cn.id_persona
                    LEFT JOIN cliente_juridico cj ON p.id_persona = cj.id_persona
                    WHERE p.id_persona = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Error en obtenerClientePorId: " . $e->getMessage());
            return null;
        }
    }

    // ==========================================
    // ELIMINAR CLIENTE
    // ==========================================
    public function eliminarCliente($id) {
        if (!$id || !is_numeric($id)) {
            throw new \Exception("ID de cliente inválido para eliminar");
        }

        $this->db->beginTransaction();
        try {
            $sqlCheck = "SELECT id_persona FROM persona WHERE id_persona = :id";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $id]);

            if ($stmtCheck->rowCount() === 0) {
                throw new \Exception("El cliente no existe en la base de datos");
            }

            $sqlNotaSalida = "SELECT 1 FROM nota_de_salida WHERE id_persona = :id LIMIT 1";
            $stmtNotaSalida = $this->db->prepare($sqlNotaSalida);
            $stmtNotaSalida->execute([':id' => $id]);

            if ($stmtNotaSalida->rowCount() > 0) {
                throw new \Exception("No se puede eliminar este cliente porque tiene notas de salida asociadas.");
            }

            $sqlTipo = "SELECT 
                            CASE 
                                WHEN EXISTS (SELECT 1 FROM cliente_natural WHERE id_persona = :id) THEN 'Natural'
                                WHEN EXISTS (SELECT 1 FROM cliente_juridico WHERE id_persona = :id) THEN 'Jurídico'
                                ELSE 'Desconocido'
                            END AS tipo";
            $stmtTipo = $this->db->prepare($sqlTipo);
            $stmtTipo->execute([':id' => $id]);
            $tipoCliente = $stmtTipo->fetchColumn();

            if ($tipoCliente === 'Natural' || $tipoCliente === 'Desconocido') {
                $sql1 = "DELETE FROM cliente_natural WHERE id_persona = :id";
                $stmt1 = $this->db->prepare($sql1);
                $stmt1->execute([':id' => $id]);
            }

            if ($tipoCliente === 'Jurídico' || $tipoCliente === 'Desconocido') {
                $sql2 = "DELETE FROM cliente_juridico WHERE id_persona = :id";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([':id' => $id]);
            }

            $sql3 = "DELETE FROM persona WHERE id_persona = :id";
            $stmt3 = $this->db->prepare($sql3);
            $resultado = $stmt3->execute([':id' => $id]);

            if (!$resultado || $stmt3->rowCount() === 0) {
                throw new \Exception("No se pudo eliminar el registro principal");
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("ERROR en eliminarCliente: " . $e->getMessage());
            throw $e;
        }
    }

    // ==========================================
    // VALIDACIONES
    // ==========================================
    public function existeCedula($cedula) {
        try {
            $sql = "SELECT COUNT(*) FROM persona WHERE cedula = :cedula";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':cedula' => $cedula]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error en existeCedula: " . $e->getMessage());
            return false;
        }
    }

    public function existeRif($rif) {
        try {
            $sql = "SELECT COUNT(*) FROM cliente_juridico WHERE rif = :rif";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':rif' => $rif]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error en existeRif: " . $e->getMessage());
            return false;
        }
    }
}
