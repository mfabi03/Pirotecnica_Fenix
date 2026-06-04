<?php
namespace App\Pirotecnicafenix\Controller;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

class FrontController {
    private $db;
    private $dir = 'app/controller/';
    private $controller = 'Controller.php';
    private $url;

    public function __construct() {
        $conexion = new ConnectDB(); // Creamos el objeto
        $this->db = $conexion->getConnection(); // Obtenemos el objeto PDO
    }

    public function getURL($url) {
        $nombreClase = "\\App\\Pirotecnicafenix\\Controller\\" . ucfirst($this->url) . "Controller";
        $rutaCompleta = $this->dir . ucfirst($this->url) . $this->controller;

        if (file_exists($rutaCompleta)) {
            require_once($rutaCompleta);
            
            if (class_exists($nombreClase)) {
                // Pasamos la conexión $db al controlador
                $instancia = new $nombreClase($this->db);
                
                // Ruteo específico para Configuración
                if ($this->url == 'configuracion') {
                    $instancia->usuarios();
                } else {
                    $metodo = $_REQUEST["type"] ?? 'index';
                    if (method_exists($instancia, $metodo)) {
                        $instancia->$metodo();
                    }
                }
            }
        }
    }
}