<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\UsuarioModel;
use App\Pirotecnicafenix\Model\RolModel;

class ConfiguracionController {

   public function index(){ 
    require_once "app/view/configuracion/main.php";

   }

   public function usuarios() {
    require_once "app/view/configuracion/usuarios_lista.php";
   }

   public function roles() {
    require_once "app/view/configuracion/roles_permisos.php";
   }
}
?>