<?php
namespace App\Pirotecnicafenix\Controller;

class FrontController {

    private $dir;
    private $controller;        
    private $url;

    public function __construct() {
        if (isset($_REQUEST["url"])) {
            $this->url = $_REQUEST["url"];
            $this->dir = 'app/controller/';
            $this->controller = 'Controller.php';
            $this->getURL();
        } else {
            // Redirigir si no hay URL
            header("Location: ?url=configuracion");
            exit;
        }
    }

    private function getURL() {
        // Usamos ucfirst para que 'user' se convierta en 'User'
        $nombreControlador = ucfirst($this->url) . $this->controller;
        $rutaCompleta = $this->dir . $nombreControlador;

        if (file_exists($rutaCompleta)) {
            require_once($rutaCompleta);
            
            // Construimos el nombre de la clase con Namespace
            $nombreClase = "\\App\\Pirotecnicafenix\\Controller\\" . ucfirst($this->url) . "Controller";
            
            if (class_exists($nombreClase)) {
                $instancia = new $nombreClase();
                
                // Ejecutar acción o método por defecto
                $metodo = $_REQUEST["type"] ?? 'index';
                
                if (method_exists($instancia, $metodo)) {
                    $instancia->$metodo();
                } else {
                    echo "Error: El método '$metodo' no existe en $nombreClase";
                }
            } else {
                echo "Error: La clase $nombreClase no fue encontrada en $rutaCompleta";
            }
        } else {
            echo "Error: No se encuentra el archivo $rutaCompleta";
        }
    }
}