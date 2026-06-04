<?php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class UsuarioModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // --- ESTE ES EL MÉTODO QUE ALIMENTA TU TABLA EN usuarios_lista.php ---
    public function obtenerUsuariosConPersona() {
        $sql = "SELECT u.id_usuario, p.nombre, p.apellido, p.cedula, p.telefono, p.correo_electronico, u.rol 
                FROM usuarios u
                INNER JOIN persona p ON u.id_persona = p.id_persona";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function registrarUsuarioCompleto($datosPersona, $datosUsuario) {
    try {
        // 1. Abrimos transacción para proteger la integridad
        $this->db->beginTransaction();

        // 2. Insertamos la PERSONA primero
        $sqlPersona = "INSERT INTO persona (nombre, apellido, cedula, telefono, correo_electronico) VALUES (?, ?, ?, ?, ?)";
        $stmtP = $this->db->prepare($sqlPersona);
        $stmtP->execute([
            $datosPersona['nombre'], 
            $datosPersona['apellido'], 
            $datosPersona['cedula'], 
            $datosPersona['telefono'],
            $datosPersona['correo']
        ]);
        
        // 3. Obtenemos el ID de esta persona para usarlo en la nota de salida
        $idPersona = $this->db->lastInsertId();

        // 4. Insertamos el USUARIO
        $sqlUsuario = "INSERT INTO usuarios (id_persona, rol, password) VALUES (?, ?, ?)";
        $stmtU = $this->db->prepare($sqlUsuario);
        $stmtU->execute([$idPersona, $datosUsuario['rol'], $datosUsuario['password']]);
        
        // 5. Obtenemos el ID del usuario recién creado
        $idUsuario = $this->db->lastInsertId();

        // 6. ACTUALIZAMOS a la persona con su nuevo id_usuario (para cerrar el vínculo)
        $sqlUpdate = "UPDATE persona SET id_usuario = ? WHERE id_persona = ?";
        $stmtUpd = $this->db->prepare($sqlUpdate);
        $stmtUpd->execute([$idUsuario, $idPersona]);

        // 7. Todo guardado con éxito
        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollBack();
        return false;
    }
}
}