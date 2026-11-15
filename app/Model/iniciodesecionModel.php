<?php
namespace App\Pirotecnicafenix\Model;

class iniciodesecionModel extends ConnectDB {
    private $conex;

    public function __construct() {
        
    parent::__construct();

    $this->conex = $this->getConnection();
    }

    public function verificarUsuario($usuario, $password) {
        $sql = "SELECT u.*, p.nombre, p.apellido 
                FROM usuarios u 
                INNER JOIN persona p ON u.id_usuario = p.id_usuario 
                WHERE p.correo_electronico = :usuario OR p.cedula = :usuario";
        $stmt = $this->conex->prepare($sql);
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch();

        // Verificar hash de contraseña
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}