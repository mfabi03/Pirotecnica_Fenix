<?php 

    namespace App\Pirotecnicafenix\Model;

    use App\Pirotecnicafenix\Config\Connect\ConnectDB;

    class userModel extends ConnectDB {

        // Definición de atributos
        private $id;
        private $name;
        private $lastname;
        private $email;
        private $conex;

        // Constructor de la clase
        public function __construct() {
            parent::__construct();

            $this->conex = $this->getConnection();
        }

        // Método para obtener todos los usuarios
        public function getAllUsers() {

            $consult = $this->conex->prepare("SELECT * FROM user WHERE estado = 1");
            $consult->execute();
            $users = $consult->fetchAll();
            return $users;
        }

        public function addUser (int $id, string $name, string $lastname, string $email) {
            
            $this->id = $id;
            $this->name = $name;
            $this->lastname = $lastname;
            $this->email = $email;

            return $this->registerUser();
        }

        private function registerUser() {
            

            // Preparar la consulta SQL
            $query = "INSERT INTO user (id, nombre, apellido, correo) VALUES (?, ?, ?, ?)";
            $stmt = $this->conex->prepare($query);

            // Vincular los parámetros
            $stmt->bindValue(1, $this->id);
            $stmt->bindValue(2, $this->name);
            $stmt->bindValue(3, $this->lastname);
            $stmt->bindValue(4, $this->email);
            $result = $stmt->execute();
            // Ejecutar la consulta
            if ($result) {
                return "<script>alert('Usuario registrado exitosamente');</script>";
            } else {
                return "<script>alert('Registro Invalido, Intente nuevamente');</script>";
            }
        }

        public function deleteUser(int $id) {
            $this->id = $id;

            return $this->deleteUserById();
        }

        private function deleteUserById() {
            try {
                // Preparar la consulta SQL
                $query = "UPDATE `user` SET estado = 0 WHERE id = ?";
                $delete = $this->conex->prepare($query);

                // Vincular el parámetro
                $delete->bindValue(1, $this->id);
                // Ejecutar la consulta
                $delete->execute();
                
                return "Usuario eliminado exitosamente";
            } catch (\PDOException $e) {
                return "Error al eliminar el usuario: " . $e->getMessage();
            }
        }


    }


?>