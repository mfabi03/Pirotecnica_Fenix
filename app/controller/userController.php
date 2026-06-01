<?php

    use App\PracticaCrud\Model\userModel;
    $object = new userModel();

    if (isset($_GET['type'])) {

        // Se verifica si el tipo de vista es 'list' y se llama al método correspondiente

        if ($_GET['type'] == 'list') {

            $result = $object->getAllUsers();
            
            include 'app/view/user/listView.php';
        } 

        // Se verifica si el tipo de vista es 'register' y se llama al método correspondiente
        
        elseif ($_GET['type'] == 'register') {

            if (isset($_POST['cedula']) && isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['correo'])) {
                
                $result = $object->addUser($_POST['cedula'], $_POST['nombre'], $_POST['apellido'], $_POST['correo']);
            }

            include 'app/view/user/registerView.php';
        }

        elseif ($_GET['type'] == 'main') {

            if(isset($_POST["getUsers"])) {
                
                $result = $object->getAllUsers();
                echo json_encode($result);
                die();
            }

            if(isset($_POST["deleteUser"])) {
                $result = $object->deleteUser($_POST["idUser"]);
                echo json_encode($result);
                die();
            }
            
            include 'app/view/user/userView.php';
        }

        // Si el tipo de vista no es válido, se muestra un mensaje de error
        
        else {
            echo "Error: Tipo de vista no válido.";
        }
    } else {
        include 'app/view/welcomeView.php';
    }


?>