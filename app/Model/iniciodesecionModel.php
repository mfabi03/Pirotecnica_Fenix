<?php
namespace App\Pirotecnicafenix\Model;

class iniciodesecionModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function verificarUsuario($usuario, $password) {
        $sql = "SELECT * FROM usuarios WHERE correo = :usuario OR cedula = :usuario";
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