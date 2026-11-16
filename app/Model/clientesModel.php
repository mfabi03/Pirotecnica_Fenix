<?php
// app/Model/clientesModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Interfaces\InterfacesCrudModel;

class clientesModel extends ConnectDB implements InterfacesCrudModel
{
    private $conex;

    public function __construct()
    {
        parent::__construct();
        $this->conex = $this->getConnection();
    }

    /* ============================================================
     *  MÉTODOS DE LA INTERFAZ (puente)
     * ============================================================ */

    public function obtenerTodos()
    {
        return $this->obtenerClientes();
    }

    public function obtenerPorId($id)
    {
        return $this->obtenerClientePorId($id);
    }

    public function crear($datos)
    {
        return $this->registrarClienteNatural($datos);
    }

    public function actualizar($id, $datos)
    {
        return $this->actualizarClienteNatural($id, $datos);
    }

    public function eliminar($id)
    {
        return $this->eliminarCliente($id);
    }

    /* ============================================================
     *  MÉTODOS ORIGINALES
     * ============================================================ */

    // VALIDAR EDAD
    public function validarEdad($fechaNacimiento, $edadMinima = 18)
    {
        try {
            if (empty($fechaNacimiento)) {
                return ['valido' => false, 'mensaje' => 'La fecha de nacimiento es obligatoria'];
            }
            $fechaActual = new \DateTime();
            $fechaNac = new \DateTime($fechaNacimiento);

            if ($fechaNac > $fechaActual) {
                return ['valido' => false, 'mensaje' => 'La fecha de nacimiento no puede ser futura'];
            }

            $edad = $fechaActual->diff($fechaNac)->y;

            if ($edad < $edadMinima) {
                return ['valido' => false, 'mensaje' => "El cliente debe ser mayor de edad (mínimo $edadMinima años). Edad detectada: $edad años"];
            }

            return ['valido' => true, 'edad' => $edad];
        } catch (\Exception $e) {
            error_log("Error en validarEdad: " . $e->getMessage());
            return ['valido' => false, 'mensaje' => 'Error al validar la fecha de nacimiento'];
        }
    }

    // REGISTRAR CLIENTE NATURAL
    public function registrarClienteNatural($datos)
    {
        if (!$this->conex) {
            throw new \Exception("No hay conexión a la base de datos");
        }

        if (empty($datos['cedula'])) {
            throw new \Exception("La cédula es obligatoria.");
        }

        $validacionEdad = $this->validarEdad($datos['fecha_de_nacimiento'], 18);
        if (!$validacionEdad['valido']) {
            throw new \Exception($validacionEdad['mensaje']);
        }

        if ($this->existeCedula($datos['cedula'])) {
            throw new \Exception("DUPLICADO_CEDULA:" . $datos['cedula']);
        }

        $this->conex->beginTransaction();
        try {
            $sql = "INSERT INTO persona (cedula, nombre, apellido, telefono, correo_electronico, direccion, eliminado)
                    VALUES (:cedula, :nombre, :apellido, :telefono, :correo_electronico, :direccion, 0)";
            $stmt = $this->conex->prepare($sql);
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

            $idPersona = $this->conex->lastInsertId();

            $sql2 = "INSERT INTO cliente_natural (id_persona, fecha_de_nacimiento, eliminado)
                     VALUES (:id_persona, :fecha_de_nacimiento, 0)";
            $stmt2 = $this->conex->prepare($sql2);
            $resultado2 = $stmt2->execute([
                ':id_persona' => $idPersona,
                ':fecha_de_nacimiento' => $datos['fecha_de_nacimiento']
            ]);

            if (!$resultado2) {
                throw new \Exception("Error al insertar en la tabla cliente_natural");
            }

            $this->conex->commit();
            return $idPersona;
        } catch (\PDOException $e) {
            $this->conex->rollBack();
            error_log("Error PDO en registrarClienteNatural: " . $e->getMessage());
            throw new \Exception("No se pudo completar el registro. Intente nuevamente.");
        } catch (\Exception $e) {
            $this->conex->rollBack();
            throw $e;
        }
    }

    // REGISTRAR CLIENTE JURIDICO
    public function registrarClienteJuridico($datos)
    {
        if (!$this->conex) {
            throw new \Exception("No hay conexión a la base de datos");
        }

        if (empty($datos['rif'])) {
            throw new \Exception("El RIF es obligatorio.");
        }

        if ($this->existeRif($datos['rif'])) {
            throw new \Exception("DUPLICADO_RIF:" . $datos['rif']);
        }

        $this->conex->beginTransaction();
        try {
            $sql = "INSERT INTO persona (cedula, nombre, apellido, telefono, correo_electronico, direccion, eliminado)
                    VALUES (:cedula, :nombre, :apellido, :telefono, :correo_electronico, :direccion, 0)";
            $stmt = $this->conex->prepare($sql);
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

            $idPersona = $this->conex->lastInsertId();

            $sql2 = "INSERT INTO cliente_juridico (id_persona, rif, razon_social, eliminado)
                     VALUES (:id_persona, :rif, :razon_social, 0)";
            $stmt2 = $this->conex->prepare($sql2);
            $resultado2 = $stmt2->execute([
                ':id_persona' => $idPersona,
                ':rif' => $datos['rif'],
                ':razon_social' => $datos['razon_social']
            ]);

            if (!$resultado2) {
                throw new \Exception("Error al insertar en la tabla cliente_juridico");
            }

            $this->conex->commit();
            return $idPersona;
        } catch (\PDOException $e) {
            $this->conex->rollBack();
            error_log("Error PDO en registrarClienteJuridico: " . $e->getMessage());
            throw new \Exception("No se pudo completar el registro. Intente nuevamente.");
        } catch (\Exception $e) {
            $this->conex->rollBack();
            throw $e;
        }
    }

    // ACTUALIZAR CLIENTE NATURAL
    public function actualizarClienteNatural($id, $datos)
    {
        try {
            if (empty($datos['cedula'])) {
                throw new \Exception("La cédula es obligatoria.");
            }

            if (!empty($datos['fecha_de_nacimiento'])) {
                $validacionEdad = $this->validarEdad($datos['fecha_de_nacimiento'], 18);
                if (!$validacionEdad['valido']) {
                    throw new \Exception($validacionEdad['mensaje']);
                }
            }

            if ($this->existeCedula($datos['cedula'], $id)) {
                throw new \Exception("DUPLICADO_CEDULA:" . $datos['cedula']);
            }

            $sql = "UPDATE persona SET
                        cedula = :cedula,
                        nombre = :nombre,
                        apellido = :apellido,
                        telefono = :telefono,
                        correo_electronico = :correo_electronico,
                        direccion = :direccion
                    WHERE id_persona = :id AND eliminado = 0";
            $stmt = $this->conex->prepare($sql);
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
                     WHERE id_persona = :id AND eliminado = 0";
            $stmt2 = $this->conex->prepare($sql2);
            return $stmt2->execute([
                ':fecha_de_nacimiento' => $datos['fecha_de_nacimiento'],
                ':id' => $id
            ]);
        } catch (\PDOException $e) {
            error_log("Error PDO en actualizarClienteNatural: " . $e->getMessage());
            throw new \Exception("No se pudo actualizar el cliente. Intente nuevamente.");
        }
    }

    // ACTUALIZAR CLIENTE JURIDICO
    public function actualizarClienteJuridico($id, $datos)
    {
        try {
            if (empty($datos['rif'])) {
                throw new \Exception("El RIF es obligatorio.");
            }

            if ($this->existeRif($datos['rif'], $id)) {
                throw new \Exception("DUPLICADO_RIF:" . $datos['rif']);
            }

            $sql = "UPDATE persona SET
                        cedula = :cedula,
                        nombre = :nombre,
                        telefono = :telefono,
                        correo_electronico = :correo_electronico,
                        direccion = :direccion
                    WHERE id_persona = :id AND eliminado = 0";
            $stmt = $this->conex->prepare($sql);
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
                    WHERE id_persona = :id AND eliminado = 0";
            $stmt2 = $this->conex->prepare($sql2);
            return $stmt2->execute([
                ':rif' => $datos['rif'],
                ':razon_social' => $datos['razon_social'],
                ':id' => $id
            ]);
        } catch (\PDOException $e) {
            error_log("Error PDO en actualizarClienteJuridico: " . $e->getMessage());
            throw new \Exception("No se pudo actualizar el cliente. Intente nuevamente.");
        }
    }

    // BUSCAR CLIENTES
    public function buscarClientes($termino)
    {
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
                    WHERE (cj.id_cliente_juridico IS NOT NULL OR cn.id_cliente_natural IS NOT NULL)
                    AND p.eliminado = 0
                    AND (p.nombre LIKE :termino
                        OR p.apellido LIKE :termino
                        OR p.cedula LIKE :termino
                        OR cj.razon_social LIKE :termino
                        OR cj.rif LIKE :termino)
                    ORDER BY nombre_cliente ASC
                    LIMIT 10";

            $stmt = $this->conex->prepare($sql);
            $stmt->execute(['termino' => "%$termino%"]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en buscarClientes: " . $e->getMessage());
            return [];
        }
    }

    // BUSCAR CLIENTES CON FILTRO
    public function buscarClientesFiltrados($termino = null, $tipo = 'todos', $soloActivos = false)
    {
        try {
            $params = [];
            $where = [];
            if ($soloActivos) {
                $where[] = "p.eliminado = 0";
            }

            $sql = "SELECT
                        p.id_persona AS id_cliente,
                        p.cedula,
                        p.nombre,
                        p.apellido,
                        p.telefono,
                        p.correo_electronico,
                        p.direccion,
                        CASE WHEN p.eliminado = 1 THEN 'inactivo' ELSE 'activo' END AS status,
                        p.eliminado,
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

            $sql .= ' ORDER BY p.eliminado ASC, p.id_persona DESC, p.nombre ASC';

            $stmt = $this->conex->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarClientesFiltrados: " . $e->getMessage());
            return [];
        }
    }

    // CONTAR CLIENTES
    public function contarClientes($termino = null, $tipo = 'todos', $soloActivos = false)
    {
        try {
            $params = [];
            $where = [];
            if ($soloActivos) {
                $where[] = "p.eliminado = 0";
            }
            $sql = "SELECT COUNT(*)
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

            $stmt = $this->conex->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en contarClientes: " . $e->getMessage());
            return 0;
        }
    }

    // OBTENER CLIENTES
    public function obtenerClientes($soloActivos = false)
    {
        try {
            $where = ["(cj.id_cliente_juridico IS NOT NULL OR cn.id_cliente_natural IS NOT NULL)"];
            if ($soloActivos) {
                $where[] = "p.eliminado = 0";
            }

            $sql = "SELECT
                    p.id_persona AS id_cliente,
                    p.cedula,
                    p.nombre,
                    p.apellido,
                    p.telefono,
                    p.correo_electronico,
                    p.direccion,
                    CASE WHEN p.eliminado = 1 THEN 'inactivo' ELSE 'activo' END AS status,
                    p.eliminado,
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
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.eliminado ASC, p.id_persona DESC, p.nombre ASC";

            $stmt = $this->conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerClientes: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER SOLO CLIENTES ACTIVOS
    public function obtenerClientesActivos()
    {
        return $this->obtenerClientes(true);
    }

    // OBTENER CLIENTE POR ID
    public function obtenerClientePorId($id)
    {
        try {
            $sql = "SELECT
                        p.id_persona AS id_cliente,
                        p.cedula,
                        p.nombre,
                        p.apellido,
                        p.telefono,
                        p.correo_electronico,
                        p.direccion,
                        CASE WHEN p.eliminado = 1 THEN 'inactivo' ELSE 'activo' END AS status,
                        p.eliminado,
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

            $stmt = $this->conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en obtenerClientePorId: " . $e->getMessage());
            return null;
        }
    }

    // ELIMINAR CLIENTE (LÓGICO)
    public function eliminarCliente($id)
    {
        if (!$id || !is_numeric($id)) {
            throw new \Exception("ID de cliente inválido para eliminar");
        }

        $this->conex->beginTransaction();
        try {
            $sqlCheck = "SELECT id_persona, eliminado FROM persona WHERE id_persona = :id";
            $stmtCheck = $this->conex->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $id]);
            $clienteCheck = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

            if (!$clienteCheck) {
                throw new \Exception("El cliente no existe en la base de datos");
            }

            if (($clienteCheck['eliminado'] ?? 0) == 1) {
                throw new \Exception("El cliente ya se encuentra inactivo");
            }

            $sqlNotaSalida = "SELECT 1 FROM nota_de_salida WHERE id_persona = :id AND eliminado = 0 LIMIT 1";
            $stmtNotaSalida = $this->conex->prepare($sqlNotaSalida);
            $stmtNotaSalida->execute([':id' => $id]);

            if ($stmtNotaSalida->rowCount() > 0) {
                throw new \Exception("No se puede eliminar porque tiene registros asociados");
            }

            $sqlNotaEntrada = "SELECT 1 FROM nota_de_entrada ne
                               INNER JOIN proveedor prov ON ne.id_proveedor = prov.id_proveedor
                               WHERE prov.id_persona = :id AND ne.eliminado = 0 LIMIT 1";
            $stmtNotaEntrada = $this->conex->prepare($sqlNotaEntrada);
            $stmtNotaEntrada->execute([':id' => $id]);

            if ($stmtNotaEntrada->rowCount() > 0) {
                throw new \Exception("No se puede eliminar porque tiene registros asociados");
            }

            $sqlTipo = "SELECT
                            CASE
                                WHEN EXISTS (SELECT 1 FROM cliente_natural WHERE id_persona = :id) THEN 'Natural'
                                WHEN EXISTS (SELECT 1 FROM cliente_juridico WHERE id_persona = :id) THEN 'Jurídico'
                                ELSE 'Desconocido'
                            END AS tipo";
            $stmtTipo = $this->conex->prepare($sqlTipo);
            $stmtTipo->execute([':id' => $id]);
            $tipoCliente = $stmtTipo->fetchColumn();

            if ($tipoCliente === 'Natural' || $tipoCliente === 'Desconocido') {
                $sql1 = "UPDATE cliente_natural SET eliminado = 1 WHERE id_persona = :id";
                $stmt1 = $this->conex->prepare($sql1);
                $stmt1->execute([':id' => $id]);
            }

            if ($tipoCliente === 'Jurídico' || $tipoCliente === 'Desconocido') {
                $sql2 = "UPDATE cliente_juridico SET eliminado = 1 WHERE id_persona = :id";
                $stmt2 = $this->conex->prepare($sql2);
                $stmt2->execute([':id' => $id]);
            }

            $sql3 = "UPDATE persona SET eliminado = 1 WHERE id_persona = :id";
            $stmt3 = $this->conex->prepare($sql3);
            $resultado = $stmt3->execute([':id' => $id]);

            if (!$resultado || $stmt3->rowCount() === 0) {
                throw new \Exception("No se pudo inactivar el registro principal");
            }

            $this->conex->commit();
            return true;
        } catch (\Exception $e) {
            $this->conex->rollBack();
            error_log("ERROR en eliminarCliente: " . $e->getMessage());
            throw $e;
        }
    }

    // REACTIVAR CLIENTE
    public function reactivarCliente($id)
    {
        if (!$id || !is_numeric($id)) {
            throw new \Exception("ID de cliente inválido");
        }

        $this->conex->beginTransaction();
        try {
            $sqlCheck = "SELECT id_persona FROM persona WHERE id_persona = :id";
            $stmtCheck = $this->conex->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $id]);

            if ($stmtCheck->rowCount() === 0) {
                throw new \Exception("El cliente no existe en la base de datos");
            }

            $this->conex->prepare("UPDATE persona SET eliminado = 0 WHERE id_persona = :id")->execute([':id' => $id]);
            $this->conex->prepare("UPDATE cliente_natural SET eliminado = 0 WHERE id_persona = :id")->execute([':id' => $id]);
            $this->conex->prepare("UPDATE cliente_juridico SET eliminado = 0 WHERE id_persona = :id")->execute([':id' => $id]);

            $this->conex->commit();
            return true;
        } catch (\Exception $e) {
            $this->conex->rollBack();
            error_log("ERROR en reactivarCliente: " . $e->getMessage());
            throw $e;
        }
    }

    // EXISTE CÉDULA
    public function existeCedula($cedula, $excluirId = null)
    {
        try {
            if ($excluirId !== null) {
                $sql = "SELECT COUNT(*) FROM persona
                        WHERE cedula = :cedula AND eliminado = 0 AND id_persona <> :excluir";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute([':cedula' => $cedula, ':excluir' => $excluirId]);
            } else {
                $sql = "SELECT COUNT(*) FROM persona WHERE cedula = :cedula AND eliminado = 0";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute([':cedula' => $cedula]);
            }
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error en existeCedula: " . $e->getMessage());
            return false;
        }
    }

    // EXISTE RIF
    public function existeRif($rif, $excluirId = null)
    {
        try {
            if ($excluirId !== null) {
                $sql = "SELECT COUNT(*) FROM cliente_juridico
                        WHERE rif = :rif AND eliminado = 0 AND id_persona <> :excluir";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute([':rif' => $rif, ':excluir' => $excluirId]);
            } else {
                $sql = "SELECT COUNT(*) FROM cliente_juridico WHERE rif = :rif AND eliminado = 0";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute([':rif' => $rif]);
            }
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error en existeRif: " . $e->getMessage());
            return false;
        }
    }
}