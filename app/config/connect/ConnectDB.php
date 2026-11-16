<?php
namespace App\Pirotecnicafenix\Config\Connect;

use PDO;
use PDOException;

abstract class ConnectDB {
    private $conex;
    
    public function __construct() {
        $this->getConnection();
    }

    public function getConnection(): PDO {
        try {
           
            $this->conex = new PDO("mysql:host=localhost;dbname=pirotecnica_fenix;charset=utf8mb4", "root", "");
            
            $this->conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            
            $this->conex->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            
            die('ERROR DE CONEXION: ' . $e->getMessage());
        }

        return $this->conex;
    }
}