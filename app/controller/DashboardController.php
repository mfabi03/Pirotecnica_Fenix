<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\DashboardModel;
use Exception;

class DashboardController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        try {
            $model = new DashboardModel($this->db);
            $stats = $model->getEstadisticas();
            
<<<<<<< HEAD
            // Pasar estadísticas a la vista
            require_once __DIR__ . '/../View/configuracion/Dashboard.php';
=======
            require_once __DIR__ . '/../view/configuracion/Dashboard.php';
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
            
        } catch (Exception $e) {
            error_log("Error en DashboardController: " . $e->getMessage());
            $stats = [
                'total_productos' => 0,
                'total_usuarios' => 0,
                'total_nota_salida' => 0,
                'total_nota_entrada' => 0,
                'productos_criticos' => 0,
                'total_clientes' => 0
            ];
<<<<<<< HEAD
            require_once __DIR__ . '/../View/configuracion/Dashboard.php';
=======
            require_once __DIR__ . '/../view/configuracion/Dashboard.php';
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
        }
    }
}
?>