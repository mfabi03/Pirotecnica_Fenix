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
        // Obtenemos los datos de la sesión de forma segura
        $idRol = isset($_SESSION['id_rol']) ? (int)$_SESSION['id_rol'] : 0;
        $idUsuario = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;

        // 🔥 SOLUCIÓN: Si el rol no es válido (ej. viene vacío o es un texto), 
        // lo tratamos como un usuario normal (Rol 4 o 0) en lugar de expulsarlo y crear un bucle.
        if (!in_array($idRol, [1, 2, 3], true)) {
            $idRol = 4; // Asignamos un rol "genérico" para que no rompa el dashboard
            // Opcional: Podrías poner un mensaje en sesión para avisar
            // $_SESSION['error'] = 'Tu rol no tiene privilegios de administrador.'; 
        }

        $model = new DashboardModel($this->db);
        
        if (($_GET['action'] ?? '') === 'ajax_stats') {
            try {
                $data = $this->loadDashboardUpdates($model, $idRol, $idUsuario);
                $this->sendJson([
                    'stats' => $data['stats'],
                    'notas_entrada' => $data['notasEntrada'],
                    'notas_salida' => $data['notasSalida'],
                    'ultimo_id_nota_entrada' => (int) ($data['notasEntrada'][0]['id_nota_entrada'] ?? 0),
                    'ultimo_id_nota_salida' => (int) ($data['notasSalida'][0]['id_nota_salida'] ?? 0)
                ]);
            } catch (Exception $e) {
                error_log("Error AJAX en DashboardController: " . $e->getMessage());
                $this->sendJson(['error' => 'No fue posible actualizar el dashboard.'], 500);
            }
        }

        try {
            $data = $this->loadDashboardData($model, $idRol, $idUsuario);
        } catch (Exception $e) {
            error_log("Error en DashboardController: " . $e->getMessage());
            http_response_code(500);
            $_SESSION['error'] = 'No fue posible cargar el dashboard. Intente nuevamente.';
            $data = [
                'stats' => [],
                'usuariosDetalle' => [],
                'productosCriticosDetalle' => [],
                'clientesDetalle' => [],
                'notasEntradaDetalle' => [],
                'notasSalidaDetalle' => [],
                'inventarioDetalle' => []
            ];
        }

        extract($data, EXTR_SKIP);
        
        // Estas variables controlan qué tarjetas se muestran en la vista
        $mostrarUsuarios = ($idRol === 1);
        $mostrarStockCritico = in_array($idRol, [1, 2], true);
        $mostrarNotasEntrada = in_array($idRol, [1, 2], true);
        $esCajero = ($idRol === 3);
        
        require_once __DIR__ . '/../view/configuracion/Dashboard.php';
    }

    private function loadDashboardData(DashboardModel $model, int $idRol, ?int $idUsuario): array {
        $esCajero = ($idRol === 3);
        return [
            'stats' => $model->getEstadisticas($esCajero ? $idUsuario : null, $idRol === 1),
            'usuariosDetalle' => $idRol === 1 ? $model->getUsuariosResumen() : [],
            'productosCriticosDetalle' => in_array($idRol, [1, 2], true) ? $model->getProductosCriticos() : [],
            'clientesDetalle' => $model->getClientesResumen(),
            'notasEntradaDetalle' => in_array($idRol, [1, 2], true) ? $model->getNotasEntradaRecientes() : [],
            'notasSalidaDetalle' => $model->getNotasSalidaRecientes($esCajero ? $idUsuario : null),
            'inventarioDetalle' => $model->getProductosInventario()
        ];
    }

    private function loadDashboardUpdates(DashboardModel $model, int $idRol, ?int $idUsuario): array {
        $esCajero = ($idRol === 3);
        return [
            'stats' => $model->getEstadisticas($esCajero ? $idUsuario : null, $idRol === 1),
            'notasEntrada' => in_array($idRol, [1, 2], true) ? $model->getNotasEntradaRecientes() : [],
            'notasSalida' => $model->getNotasSalidaRecientes($esCajero ? $idUsuario : null)
        ];
    }

    private function sendJson(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit();
    }
}
?>