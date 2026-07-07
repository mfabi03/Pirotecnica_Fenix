<?php
// app/Model/LoginModel.php
namespace App\Pirotecnicafenix\Model;

use PDO;
use Exception;

class LoginModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function autenticar($usuario, $clave) {
        try {
            // ✅ CORREGIDO: usar id_persona para la relación
            $sql = "SELECT 
                        u.id_usuario,
                        u.usuario,
                        u.clave,
                        u.id_rol,
                        p.nombre,
                        p.apellido
                    FROM usuario u
                    LEFT JOIN persona p ON u.id_persona = p.id_persona  -- ✅ CORRECTO
                    WHERE u.usuario = :usuario
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['usuario' => $usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // ✅ PRIMERO: Comparar texto plano (porque las contraseñas están así)
                if ($clave === $user['clave']) {
                    return $user;
                }
                
                // ✅ SEGUNDO: Intentar con password_verify (por si alguna está hasheada)
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
?>