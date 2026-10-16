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
            $sql = "SELECT p.id_producto, p.descripcion, p.cantidad, p.stock_minimo,
                        COALESCE((
                            SELECT pr.razon_social
                            FROM detalle_entrada de
                            INNER JOIN nota_de_entrada ne ON ne.id_nota_entrada = de.id_nota_entrada
                            LEFT JOIN proveedor pr ON pr.id_proveedor = ne.id_proveedor
                            WHERE de.id_producto = p.id_producto AND ne.eliminado = 0
                            ORDER BY ne.id_nota_entrada DESC
                            LIMIT 1
                        ), 'Sin proveedor') AS proveedor
                    FROM producto p
                    WHERE p.cantidad <= p.stock_minimo
                      AND p.eliminado = 0
                    ORDER BY p.cantidad ASC, p.descripcion ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error en getProductosCriticos: " . $e->getMessage());
            return [];
        }
    }

    public function getUsuariosResumen() {
        try {
            $sql = "SELECT u.id_usuario, p.nombre, p.apellido, p.correo_electronico,
                        COALESCE(r.nombre_rol, 'Sin rol') AS rol, 'Activo' AS estado
                    FROM usuario u
                    INNER JOIN persona p ON p.id_persona = u.id_persona
                    LEFT JOIN rol r ON r.id_rol = u.id_rol
                    WHERE u.eliminado = 0 AND p.eliminado = 0
                    ORDER BY u.id_usuario DESC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error en getUsuariosResumen: " . $e->getMessage());
            return [];
        }
    }

    public function getClientesResumen() {
        try {
            $sql = "SELECT p.cedula AS documento,
                        CONCAT_WS(' ', p.nombre, p.apellido) AS nombre,
                        'Natural' AS tipo_cliente, p.telefono, p.correo_electronico
                    FROM cliente_natural cn
                    INNER JOIN persona p ON p.id_persona = cn.id_persona
                    WHERE cn.eliminado = 0 AND p.eliminado = 0
                    UNION ALL
                    SELECT cj.rif AS documento, cj.razon_social AS nombre,
                        'Jurídico' AS tipo_cliente, p.telefono, p.correo_electronico
                    FROM cliente_juridico cj
                    INNER JOIN persona p ON p.id_persona = cj.id_persona
                    WHERE cj.eliminado = 0 AND p.eliminado = 0
                    ORDER BY nombre ASC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error en getClientesResumen: " . $e->getMessage());
            return [];
        }
    }

    public function getNotasEntradaRecientes() {
        try {
            $sql = "SELECT n.id_nota_entrada, n.fecha_ingreso, n.costo_total,
                        COALESCE(p.razon_social, 'Sin proveedor') AS proveedor,
                        'Activa' AS estado
                    FROM nota_de_entrada n
                    LEFT JOIN proveedor p ON p.id_proveedor = n.id_proveedor
                    WHERE n.eliminado = 0
                    ORDER BY n.id_nota_entrada DESC
                    LIMIT 10";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error en getNotasEntradaRecientes: " . $e->getMessage());
            return [];
        }
    }

    public function getNotasSalidaRecientes() {
        try {
            $sql = "SELECT ns.id_nota_salida, ns.fecha,
                        CASE
                            WHEN cj.id_cliente_juridico IS NOT NULL THEN cj.razon_social
                            ELSE CONCAT_WS(' ', p.nombre, p.apellido)
                        END AS cliente,
                        (SELECT COALESCE(SUM(ds.cantidad), 0)
                         FROM detalle_salida ds
                         WHERE ds.id_nota_salida = ns.id_nota_salida) AS total,
                        'Activa' AS estado
                    FROM nota_de_salida ns
                    LEFT JOIN persona p ON p.id_persona = ns.id_persona
                    LEFT JOIN cliente_juridico cj ON cj.id_persona = p.id_persona
                    WHERE ns.eliminado = 0
                    ORDER BY ns.id_nota_salida DESC
                    LIMIT 10";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error en getNotasSalidaRecientes: " . $e->getMessage());
            return [];
        }
    }

    public function getProductosInventario(): array {
        try {
            $sql = "SELECT p.id_producto, p.descripcion, c.nombre_categoria,
                        p.cantidad, p.costo_unitario AS precio
                    FROM producto p
                    LEFT JOIN categoria c ON c.id_categoria = p.id_categoria
                    WHERE p.eliminado = 0
                    ORDER BY p.descripcion ASC";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error en getProductosInventario: " . $e->getMessage());
            return [];
        }
    }
}