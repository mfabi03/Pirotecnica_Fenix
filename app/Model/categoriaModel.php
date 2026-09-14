<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;

class CategoriaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // OBTENER TODAS LAS CATEGORIAS

    public function obtenerCategorias() {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion 
                    FROM categoria 
                    WHERE eliminado = 0
                    ORDER BY id_categoria DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categorías: " . $e->getMessage());
        }
    }

    // OBTENER CATEGORIA POR ID

    public function obtenerCategoriaPorId($id) {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion 
                    FROM categoria 
                    WHERE id_categoria = :id AND eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categoría: " . $e->getMessage());
        }
    }

    // REGISTRAR CATEGORIA

    public function registrarCategoria(array $datos) {
        try {
            $checkSql = "SELECT COUNT(*) FROM categoria WHERE nombre_categoria = :nombre AND eliminado = 0";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':nombre' => $datos['nombre_categoria']]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Ya existe una categoría con el nombre '{$datos['nombre_categoria']}'.");
            }

            $sql = "INSERT INTO categoria (nombre_categoria, descripcion, eliminado) 
                    VALUES (:nombre_categoria, :descripcion, 0)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre_categoria' => $datos['nombre_categoria'],
                ':descripcion' => $datos['descripcion'] ?? null
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ACTUALIZAR CATEGORIA

    public function actualizarCategoria($id, array $datos) {
        try {
            $checkSql = "SELECT COUNT(*) FROM categoria WHERE id_categoria = :id AND eliminado = 0";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            if ($checkStmt->fetchColumn() == 0) {
                throw new Exception("La categoría con ID {$id} no existe.");
            }

            $checkNombreSql = "SELECT COUNT(*) FROM categoria 
                               WHERE nombre_categoria = :nombre AND id_categoria != :id AND eliminado = 0";
            $checkNombreStmt = $this->db->prepare($checkNombreSql);
            $checkNombreStmt->execute([
                ':nombre' => $datos['nombre_categoria'],
                ':id' => $id
            ]);
            if ($checkNombreStmt->fetchColumn() > 0) {
                throw new Exception("Ya existe otra categoría con el nombre '{$datos['nombre_categoria']}'.");
            }

            $sql = "UPDATE categoria 
                    SET nombre_categoria = :nombre_categoria, 
                        descripcion = :descripcion 
                    WHERE id_categoria = :id AND eliminado = 0";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':nombre_categoria' => $datos['nombre_categoria'],
                ':descripcion' => $datos['descripcion'] ?? null
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ELIMINAR CATEGORIA

    public function eliminarCategoria($id) {
        try {
            $checkSql = "SELECT COUNT(*) FROM producto WHERE id_categoria = :id AND eliminado = 0";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar la categoría porque tiene productos asociados.");
            }

            $sql = "UPDATE categoria SET eliminado = 1 WHERE id_categoria = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    // BUSCAR CATEGORIAS

    public function buscarCategorias($termino) {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion 
                    FROM categoria 
                    WHERE eliminado = 0
                    AND (nombre_categoria LIKE :termino 
                       OR descripcion LIKE :termino)
                    ORDER BY nombre_categoria ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':termino' => "%{$termino}%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al buscar categorías: " . $e->getMessage());
        }
    }

    // OBTENER CATEGORIAS PARA SELECT

    public function obtenerCategoriasParaSelect() {
        try {
            $sql = "SELECT id_categoria, nombre_categoria 
                    FROM categoria 
                    WHERE eliminado = 0
                    ORDER BY nombre_categoria ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categorías: " . $e->getMessage());
        }
    }
}