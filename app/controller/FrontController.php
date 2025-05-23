<?php

    namespace App\PracticaCrud\Controller;
    class FrontController {

        // Definicion de atributos que serán constantes
        private $dir;
        private $controller;        
        private $url;

        public function __construct() {

            // Si existe y no está vacía una request con el nombre de url

            if (isset($_REQUEST["url"])) {

                //se asigna el valor de la request a la variable url
                $this->url = $_REQUEST["url"];

                //directorio donde se encuentran los controladores
                $this->dir = 'app/controller/';

                //concatenación del nombre del controlador con el nombre de la clase
                $this->controller = 'Controller.php';

                //se ejecuta el método getURL que se encarga de cargar el controlador correspondiente
                $this->getURL();

            } else {

                //si no existe la request se asigna el valor conocido por defecto a la variable url 
                
                echo "Error 404: la url no existe";
                
                die("<script>location='?url=user'</script>");
            }
        }

        private function getURL() {

            //si existe el controlador en la carpeta de controladores
            
            if(file_exists($this->dir.$this->url.$this->controller)) {
                
                //se llama al controlador correspondiente
                require_once($this->dir.$this->url.$this->controller);
            
            } else {

                echo "<script>location='?url=user'</script>";
            }
        }

    }

?>