<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\ReporteCajaModel;
use Exception;

class ReporteCajaController {
    private $db;
    private $modelo;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = $db;
        $this->modelo = new ReporteCajaModel($this->db);
    }

    public function index() {
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null;
        $tipo_movimiento = isset($_GET['tipo_movimiento']) ? trim($_GET['tipo_movimiento']) : null;
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;

        $offset = ($pagina - 1) * $porPagina;

        $movimientos = $this->modelo->obtenerMovimientosCaja($fecha, $id_categoria, $busqueda, $tipo_movimiento, $porPagina, $offset);
        $totalRegistros = $this->modelo->contarMovimientosCaja($fecha, $id_categoria, $busqueda, $tipo_movimiento);
        $totalesGlobales = $this->modelo->obtenerTotalesGlobales($fecha, $id_categoria, $busqueda, $tipo_movimiento);
        $resumenCaja = $this->modelo->obtenerResumenCaja($fecha, $id_categoria, $busqueda);

        $totalPaginas = $porPagina > 0 ? (int) ceil($totalRegistros / $porPagina) : 1;
        $paginaActual = $pagina;
        $porPaginaActual = $porPagina;
        $totalEntradas = $totalesGlobales['total_entradas'] ?? 0;
        $totalSalidas = $totalesGlobales['total_salidas'] ?? 0;

        $db = $this->db;

        require_once __DIR__ . '/../view/reportes/reporteCajaView.php';
    }

    public function exportarCSV() {
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null;
        $tipo_movimiento = isset($_GET['tipo_movimiento']) ? trim($_GET['tipo_movimiento']) : null;

        $movimientos = $this->modelo->obtenerMovimientosCaja($fecha, $id_categoria, $busqueda, $tipo_movimiento, 999999, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_caja_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Producto', 'Categoría', 'Tipo', 'Cantidad', 'Costo', 'Fecha', 'Responsable']);

        foreach ($movimientos as $m) {
            $cantidad = $m['cantidad'];
            if ($m['tipo_movimiento'] === 'Salida') {
                $cantidad = '-' . abs($cantidad);
            }
            fputcsv($output, [
                $m['nombre_producto'],
                $m['categoria'] ?? 'Sin categoría',
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