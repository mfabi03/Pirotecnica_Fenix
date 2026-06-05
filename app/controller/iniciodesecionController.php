<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\iniciodesecionModel;

class iniciodesecionController {
    
    // Método para mostrar el formulario de login
    public function index() {
        require_once 'views/iniciodesecion.php';
    }

    // Método para mostrar la página de registro
    public function registro() {
        // Aquí podrías validar si el usuario ya es admin si es necesario
        require_once 'views/registro.php';
    }

    // Método para procesar el login
    public function autenticar() {
        // Lógica de validación con el Modelo
    }

}