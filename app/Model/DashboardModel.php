<?php
namespace App\Pirotecnicafenix\Model;

use PDO;

class DashboardModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Obtiene estadísticas basadas en las tablas
     */
    public function getEstadisticas() {
        $stats = [];

        try {
            // 1. Total de productos (inventario)
            $sql = "SELECT COUNT(*) as total FROM producto";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_productos'] = $stmt->fetchColumn() ?? 0;

            // 2. Total de usuarios
            $sql = "SELECT COUNT(*) as total FROM usuario";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_usuarios'] = $stmt->fetchColumn() ?? 0;

            // 3. Total de notas de SALIDA (CORREGIDO)
            $sql = "SELECT COUNT(*) as total FROM nota_de_salida";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_nota_salida'] = $stmt->fetchColumn() ?? 0;

            // 4. Total de notas de ENTRADA (CORREGIDO)
            $sql = "SELECT COUNT(*) as total FROM nota_de_entrada";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_nota_entrada'] = $stmt->fetchColumn() ?? 0;

            // 5. Stock crítico (productos con stock menor a 5)
            $sql = "SELECT COUNT(*) as total FROM producto WHERE cantidad < 5";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['productos_criticos'] = $stmt->fetchColumn() ?? 0;

            // 6. Total de clientes
            $sql = "SELECT COUNT(*) as total FROM cliente_natural";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $totalClientes = $stmt->fetchColumn() ?? 0;

            $sql = "SELECT COUNT(*) as total FROM cliente_juridico";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_clientes'] = $totalClientes + ($stmt->fetchColumn() ?? 0);

            return $stats;

        } catch (\Exception $e) {
            error_log("Error en DashboardModel: " . $e->getMessage());
            return [
                'total_productos' => 0,
                'total_usuarios' => 0,
                'total_nota_salida' => 0,
                'total_nota_entrada' => 0,
                'productos_criticos' => 0,
                'total_clientes' => 0
            ];
        }
    }
}
?>