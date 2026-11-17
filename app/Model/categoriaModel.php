<?php
// app/Model/CategoriaModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Interfaces\InterfacesCrudModel;

class CategoriaModel extends ConnectDB implements InterfacesCrudModel
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
        return $this->obtenerCategorias();
    }

    public function obtenerPorId($id)
    {
        return $this->obtenerCategoriaPorId($id);
    }

    public function crear($datos)
    {
        return $this->registrarCategoria($datos);
    }

    public function actualizar($id, $datos)
    {
        return $this->actualizarCategoria($id, $datos);
    }

    public function eliminar($id)
    {
        return $this->eliminarCategoria($id);
    }

    /* ============================================================
     *  MÉTODOS ORIGINALES
     * ============================================================ */

    // OBTENER TODAS LAS CATEGORIAS
    public function obtenerCategorias()
    {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion, eliminado
                    FROM categoria
                    ORDER BY eliminado ASC, id_categoria DESC";
            $stmt = $this->conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categorías: " . $e->getMessage());
        }
    }

    // CONTAR CATEGORÍAS
    public function contarCategorias($termino = null)
    {
        try {
            $termino = trim((string) $termino);

            if ($termino !== '') {
                $sql = "SELECT COUNT(*) FROM categoria
                        WHERE (nombre_categoria LIKE :termino OR descripcion LIKE :termino)";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute([':termino' => "%{$termino}%"]);
            } else {
                $sql = "SELECT COUNT(*) FROM categoria";
                $stmt = $this->conex->prepare($sql);
                $stmt->execute();
            }

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Error al contar categorías: " . $e->getMessage());
        }
    }

    // OBTENER CATEGORIA POR ID
    public function obtenerCategoriaPorId($id)
    {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion, eliminado
                    FROM categoria
                    WHERE id_categoria = :id";
            $stmt = $this->conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categoría: " . $e->getMessage());
        }
    }

    public function existeCategoria($nombre, $excluirId = null)
    {
        try {
            $sql = "SELECT COUNT(*) FROM categoria
                    WHERE LOWER(TRIM(nombre_categoria)) = LOWER(TRIM(:nombre))
                      AND eliminado = 0";
            $parametros = [':nombre' => trim((string)$nombre)];

            if ($excluirId !== null) {
                $sql .= " AND id_categoria <> :excluir_id";
                $parametros[':excluir_id'] = (int)$excluirId;
            }

            $stmt = $this->conex->prepare($sql);
            $stmt->execute($parametros);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("No se pudo verificar el nombre de la categoría.", 0, $e);
        }
    }

    // REGISTRAR CATEGORIA
    public function registrarCategoria(array $datos)
    {
        try {
            if ($this->existeCategoria($datos['nombre_categoria'])) {
                throw new Exception("Ya existe una categoría activa con el nombre '{$datos['nombre_categoria']}'.");
            }

            $sql = "INSERT INTO categoria (nombre_categoria, descripcion, eliminado)
                    VALUES (:nombre_categoria, :descripcion, 0)";
            $stmt = $this->conex->prepare($sql);

            $stmt->execute([
                ':nombre_categoria' => $datos['nombre_categoria'],
                ':descripcion' => $datos['descripcion'] ?? null
            ]);

            return (int) $this->conex->lastInsertId();

        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ACTUALIZAR CATEGORIA
    public function actualizarCategoria($id, array $datos)
    {
        try {
            $checkSql = "SELECT COUNT(*) FROM categoria WHERE id_categoria = :id AND eliminado = 0";
            $checkStmt = $this->conex->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            if ($checkStmt->fetchColumn() == 0) {
                throw new Exception("La categoría con ID {$id} no existe o está inactiva.");
            }

            if ($this->existeCategoria($datos['nombre_categoria'], $id)) {
                throw new Exception("Ya existe otra categoría activa con el nombre '{$datos['nombre_categoria']}'.");
            }

            $sql = "UPDATE categoria
                    SET nombre_categoria = :nombre_categoria,
                        descripcion = :descripcion
                    WHERE id_categoria = :id AND eliminado = 0";
            $stmt = $this->conex->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':nombre_categoria' => $datos['nombre_categoria'],
                ':descripcion' => $datos['descripcion'] ?? null
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ELIMINAR CATEGORIA (BORRADO LÓGICO)
    public function eliminarCategoria($id)
    {
        try {
            $checkSql = "SELECT COUNT(*) FROM producto WHERE id_categoria = :id AND eliminado = 0";
            $checkStmt = $this->conex->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar la categoría porque tiene productos asociados.");
            }

            $sql = "UPDATE categoria SET eliminado = 1 WHERE id_categoria = :id";
            $stmt = $this->conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // REACTIVAR CATEGORIA
    public function reactivarCategoria($id)
    {
        try {
            $stmtCheck = $this->conex->prepare(
                "SELECT id_categoria, nombre_categoria FROM categoria WHERE id_categoria = :id AND eliminado = 1 LIMIT 1"
            );
            $stmtCheck->execute([':id' => $id]);
            $categoria = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$categoria) {
                throw new Exception("La categoría no existe o ya está activa.");
            }
            if ($this->existeCategoria($categoria['nombre_categoria'], $id)) {
                throw new Exception("No se puede reactivar la categoría porque ya existe otra categoría activa con ese nombre.");
            }

            $stmt = $this->conex->prepare(
                "UPDATE categoria SET eliminado = 0 WHERE id_categoria = :id"
            );
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // BUSCAR CATEGORIAS
    public function buscarCategorias($termino)
    {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion, eliminado
                    FROM categoria
                    WHERE (nombre_categoria LIKE :termino
                       OR descripcion LIKE :termino)
                    ORDER BY eliminado ASC, nombre_categoria ASC";
            $stmt = $this->conex->prepare($sql);
            $stmt->execute([':termino' => "%{$termino}%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al buscar categorías: " . $e->getMessage());
        }
    }
}