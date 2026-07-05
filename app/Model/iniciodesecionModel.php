<?php
namespace App\Pirotecnicafenix\Model;

class iniciodesecionModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function verificarUsuario($usuario, $password) {
        $sql = "SELECT u.*, p.nombre, p.apellido 
                FROM usuarios u 
                INNER JOIN persona p ON u.id_persona = p.id_persona 
                WHERE p.correo_electronico = :usuario OR p.cedula = :usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch();

        // Verificar hash de contraseña
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}