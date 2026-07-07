<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use PDOException;

class CategoriaModel {
    private $db;

    /**
     * CAMBIO: Ajuste de nombre según BD - Inyección de dependencia en lugar de new ConnectDB()
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener todas las categorías
     * CAMBIO: Ajuste de nombre según BD - Tabla 'categoria' en lugar de 'categorias'
     */
    public function obtenerCategorias() {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion 
                    FROM categoria 
                    ORDER BY id_categoria DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categorías: " . $e->getMessage());
        }
    }

    /**
     * Obtener una categoría por ID
     * CAMBIO: Ajuste de nombre según BD - Tabla 'categoria'
     */
    public function obtenerCategoriaPorId($id) {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion 
                    FROM categoria 
                    WHERE id_categoria = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categoría: " . $e->getMessage());
        }
    }

    /**
     * Registrar una nueva categoría
     * CAMBIO: Ajuste de nombre según BD - Tabla 'categoria'
     */
    public function registrarCategoria(array $datos) {
        try {
            // Verificar si ya existe una categoría con el mismo nombre
            $checkSql = "SELECT COUNT(*) FROM categoria WHERE nombre_categoria = :nombre";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':nombre' => $datos['nombre_categoria']]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Ya existe una categoría con el nombre '{$datos['nombre_categoria']}'.");
            }

            $sql = "INSERT INTO categoria (nombre_categoria, descripcion) 
                    VALUES (:nombre_categoria, :descripcion)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre_categoria' => $datos['nombre_categoria'],
                ':descripcion' => $datos['descripcion'] ?? null
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Actualizar una categoría
     * CAMBIO: Ajuste de nombre según BD - Tabla 'categoria'
     */
    public function actualizarCategoria($id, array $datos) {
        try {
            // Verificar si la categoría existe
            $checkSql = "SELECT COUNT(*) FROM categoria WHERE id_categoria = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            if ($checkStmt->fetchColumn() == 0) {
                throw new Exception("La categoría con ID {$id} no existe.");
            }

            // Verificar si ya existe otra categoría con el mismo nombre
            $checkNombreSql = "SELECT COUNT(*) FROM categoria 
                               WHERE nombre_categoria = :nombre AND id_categoria != :id";
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
                    WHERE id_categoria = :id";
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

    /**
     * Eliminar una categoría
     * CAMBIO: Ajuste de nombre según BD - Tabla 'categoria' y verificación de dependencias
     */
    public function eliminarCategoria($id) {
        try {
            // Verificar si la categoría tiene productos asociados
            $checkSql = "SELECT COUNT(*) FROM producto WHERE id_categoria = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar la categoría porque tiene productos asociados.");
            }

            $sql = "DELETE FROM categoria WHERE id_categoria = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Buscar categorías por término
     * CAMBIO: Ajuste de nombre según BD - Nuevo método
     */
    public function buscarCategorias($termino) {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion 
                    FROM categoria 
                    WHERE nombre_categoria LIKE :termino 
                       OR descripcion LIKE :termino
                    ORDER BY nombre_categoria ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':termino' => "%{$termino}%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al buscar categorías: " . $e->getMessage());
        }
    }

    /**
     * Obtener categorías para select (solo id y nombre)
     * CAMBIO: Ajuste de nombre según BD - Método para formularios
     */
    public function obtenerCategoriasParaSelect() {
        try {
            $sql = "SELECT id_categoria, nombre_categoria 
                    FROM categoria 
                    ORDER BY nombre_categoria ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener categorías: " . $e->getMessage());
        }
    }
}