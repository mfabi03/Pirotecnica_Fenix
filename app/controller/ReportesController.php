<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\ReportesModel;
use Exception;

class ReportesController {
    private $db;
    private $modelo;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = $db;
        $this->modelo = new ReportesModel($this->db);
    }

    public function index() {
        // Obtener filtros desde GET
        $fecha_inicio = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : null;
        $fecha_fin = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : null;
        $id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : null;
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $tipo_movimiento = isset($_GET['tipo_movimiento']) ? trim($_GET['tipo_movimiento']) : null;
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;

        // Calcular offset para paginación
        $offset = ($pagina - 1) * $porPagina;

        // Obtener datos del modelo
        $movimientos = $this->modelo->obtenerMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento, $porPagina, $offset);
        $totalRegistros = $this->modelo->contarMovimientosDiarios($fecha_inicio, $fecha_fin, $id_categoria, $busqueda, $tipo_movimiento);
        $totalesGlobales = $this->modelo->obtenerTotalesGlobales();

        // Calcular paginación
        $totalPaginas = ceil($totalRegistros / $porPagina);
        $paginaActual = $pagina;
        $porPaginaActual = $porPagina;
        $totalEntradas = $totalesGlobales['total_entradas'] ?? 0;
        $totalSalidas = $totalesGlobales['total_salidas'] ?? 0;

        // Pasar $db a la vista para obtener categorías
        $db = $this->db;

        // Cargar vista
        require_once __DIR__ . '/../view/reportes/reportesView.php';
    }

    /**
     * Exportar a CSV (Excel)
     */
    public function exportarCSV() {
        $movimientos = $this->modelo->obtenerMovimientosDiarios(null, null, null, null, null, 999999, 0);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_movimientos_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Producto', 'Tipo de Movimiento', 'Cantidad', 'Costo Unitario', 'Fecha y Hora', 'Responsable']);
        
        foreach ($movimientos as $m) {
            $cantidad = $m['cantidad'];
            if ($m['tipo_movimiento'] === 'Salida') {
                $cantidad = '-' . abs($cantidad);
            }
            
            fputcsv($output, [
                $m['nombre_producto'],
                $m['tipo_movimiento'],
                $cantidad,
                $m['costo_proveedor'] ?? 0,
                $m['fecha_movimiento'],
                $m['usuario_activo']
            ]);
        }
        
        fclose($output);
        exit();
    }
}