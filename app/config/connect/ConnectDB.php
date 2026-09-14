<?php
namespace App\Pirotecnicafenix\Config\Connect;

use PDO;
use PDOException;

class ConnectDB {

    public function getConnection() {
        try {
            // Conectando a la base de datos
            $dsn = "mysql:host=localhost;dbname=pirotecnica_fenix;charset=utf8mb4";
            $pdo = new PDO($dsn, "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            // Lanzar excepción para que el llamador decida cómo mostrarla
            throw new \Exception('ERROR DE CONEXIÓN: ' . $e->getMessage());
        }
    }
}
?>