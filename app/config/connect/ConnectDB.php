<?php
    namespace App\Pirotecnicafenix\Config\Connect;

    use PDO;
    use PDOException; 
    class ConnectDB {

        // Atributos de la clase
        private $conex;

        public function __construct() {
            // Llamar al método para establecer la conexión a la base de datos
            $this->getConnection();
        }

        // Método para conectar a la base de datos
        public function getConnection(): PDO {

            // Manejo de excepciones para la conexión a la base de datos
            try {

                // Nombre exacto en minúsculas y sin espacios internos en la configuración
                $this->conex = new PDO("mysql:host=localhost;dbname=pirotecnica_fenix", "root", "");
                
                // Establecer el modo de error de PDO a excepción
                $this->conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            } catch (PDOException $e) {

                // Si hay un error, se corta la ejecución y muestra el fallo real
                die('ERROR DE CONEXIÓN: No se ha podido conectar con la base de datos. ' . $e->getMessage());
            }

            // Retornar la conexión establecida
            return $this->conex;
        }
    }
?>