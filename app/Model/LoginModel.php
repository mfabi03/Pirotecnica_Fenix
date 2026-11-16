<?php
// app/Model/LoginModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

class LoginModel extends ConnectDB
{
    private $conex;

    public function __construct()
    {
        parent::__construct();
        $this->conex = $this->getConnection();
    }

    public function autenticar($usuario, $clave)
    {
        try {
            $sql = "SELECT 
                        u.id_usuario,
                        u.usuario,
                        u.clave,
                        u.id_rol,
                        p.nombre,
                        p.apellido
                    FROM usuario u
                    LEFT JOIN persona p ON u.id_persona = p.id_persona 
                    WHERE u.usuario = :usuario
                    LIMIT 1";
            
            $stmt = $this->conex->prepare($sql);
            $stmt->execute(['usuario' => $usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // PRIMERO: Comparar texto plano (porque las contraseñas están así)
                if ($clave === $user['clave']) {
                    return $user;
                }
                
                // SEGUNDO: Intentar con password_verify (por si alguna está hasheada)
                if (password_verify($clave, $user['clave'])) {
                    return $user;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error en autenticar: " . $e->getMessage());
            return false;
        }
    }
}