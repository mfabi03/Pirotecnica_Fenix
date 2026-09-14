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
            
            $sql = "SELECT COUNT(*) FROM producto WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_productos'] = $stmt->fetchColumn() ?? 0;

            
            $sql = "SELECT COUNT(*) FROM usuario WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_usuarios'] = $stmt->fetchColumn() ?? 0;

            // 3. Total de notas de SALIDA (solo activas)
            $sql = "SELECT COUNT(*) FROM nota_de_salida WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_nota_salida'] = $stmt->fetchColumn() ?? 0;

            // 4. Total de notas de ENTRADA (solo activas)
            $sql = "SELECT COUNT(*) FROM nota_de_entrada WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_nota_entrada'] = $stmt->fetchColumn() ?? 0;

            // 5. ⭐ STOCK CRÍTICO: usa el campo stock_minimo de cada producto
            $sql = "SELECT COUNT(*) FROM producto 
                    WHERE cantidad <= stock_minimo 
                      AND eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['productos_criticos'] = $stmt->fetchColumn() ?? 0;

            // 6. Total de clientes (natural + jurídico, activos)
            $sql = "SELECT COUNT(*) FROM cliente_natural WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $totalNaturales = $stmt->fetchColumn() ?? 0;

            $sql = "SELECT COUNT(*) FROM cliente_juridico WHERE eliminado = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $totalJuridicos = $stmt->fetchColumn() ?? 0;

            $stats['total_clientes'] = $totalNaturales + $totalJuridicos;

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

    public function getProductosCriticos() {
        try {
            $sql = "SELECT id_producto, descripcion, cantidad, stock_minimo
                    FROM producto
                    WHERE cantidad <= stock_minimo 
                      AND eliminado = 0
                    ORDER BY cantidad ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error en getProductosCriticos: " . $e->getMessage());
            return [];
        }
    }
}