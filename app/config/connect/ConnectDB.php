<?php
    namespace App\Pirotecnicafenix\Config\Connect;

    use PDO;
    use PDOException; 
        
    class ConnectDB {

        public function getConnection() {

            // Manejo de excepciones para la conexión a la base de datos
            try {

                // Crear una nueva conexión PDO
                $pdo = new PDO("mysql: host=localhost; dbname=Pirotecnica_Fenix", "root", "");
                
                // Establecer el modo de error de PDO a excepción
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            } catch (PDOException $e) {

                // Si hay un error, se lanza una excepción y se muestra un mensaje de error
                die('ERROR DE CONEXIÓN: No se ha podido conectar con la base de datos. ' . $e->getMessage());
            }

            // Retornar la conexión establecida
            return $pdo;
        }
    }

?>