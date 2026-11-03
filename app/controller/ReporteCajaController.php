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

    // ============================================================
    // HELPERS DE SESIÓN / PERMISOS
    // ============================================================
    private function usuarioActual() {
        return $_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? null;
    }

    private function rolActual() {
        return $_SESSION['rol'] ?? $_SESSION['nombre_rol'] ?? $_SESSION['id_rol'] ?? null;
    }

    private function esAdmin() {
        $rol = $this->rolActual();
        if (is_numeric($rol)) {
            return (int)$rol === 1; // Ajusta el ID de admin si es necesario
        }
        return in_array(strtolower((string)$rol), ['administrador', 'admin'], true);
    }

    private function resolverIdUsuarioFiltro($idSolicitado = null) {
        $idSesion = $this->usuarioActual();
        if ($this->esAdmin()) {
            return ($idSolicitado !== null && $idSolicitado !== '') ? (int)$idSolicitado : null;
        }
        return $idSesion !== null ? (int)$idSesion : -1;
    }

    // ============================================================
    // VISTA PRINCIPAL — REPORTE DE CAJA
    // ============================================================
    public function index() {
        $ajax = (!empty($_GET['ajax']) || !empty($_POST['ajax']));
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null;
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
        $offset = ($pagina - 1) * $porPagina;

        $id_usuario_filtro = $this->resolverIdUsuarioFiltro($_GET['id_usuario'] ?? null);

        $movimientos    = $this->modelo->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria, $porPagina, $offset);
        $totalRegistros = $this->modelo->contarSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria);
        $resumen        = $this->modelo->obtenerResumenCierreCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria);

        $totalPaginas    = $porPagina > 0 ? (int)ceil($totalRegistros / $porPagina) : 1;
        $paginaActual    = $pagina;
        $porPaginaActual = $porPagina;

        $idsNotas = [];
        $totalUnidades = 0;
        foreach ($movimientos as $mov) {
            $idNota = (int)($mov['id_documento'] ?? 0);
            if ($idNota > 0) {
                $idsNotas[$idNota] = true;
            }
            $totalUnidades += (int)($mov['cantidad'] ?? 0);
        }

        $totalEntradas   = 0;
        $totalSalidas    = $resumen['total_unidades'];
        $stockReal       = 0;
        $totalDocumentos = count($idsNotas);

        $db = $this->db;

        if ($ajax) {
            ob_start();
            require_once __DIR__ . '/../view/reportes/reporteCajaView.php';
            $html = ob_get_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'html' => $html], JSON_UNESCAPED_UNICODE);
            exit();
        }

        require_once __DIR__ . '/../view/reportes/reporteCajaView.php';
    }

    // ============================================================
    // AJAX: resumen del cierre del día + DETALLE DE NOTAS
    // ============================================================
    public function obtenerCierreDiaAjax() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idSesion = $this->usuarioActual();
            if ($idSesion === null) {
                echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
                exit();
            }

            $fecha = $_GET['fecha'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                echo json_encode(['success' => false, 'mensaje' => 'Fecha inválida']);
                exit();
            }

            $id_usuario = $this->esAdmin() ? null : (int)$idSesion;

            // 1. Totales
            $resumen = $this->modelo->obtenerResumenCierreDia($id_usuario, $fecha, $_GET['busqueda'] ?? null, isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null);
            
            // 2. Detalle de notas para el modal
            $detalle = $this->modelo->obtenerSalidasCajero($id_usuario, $fecha, null, null, 999999, 0);

            $nombreCajero = $this->esAdmin()
                ? 'Administrador (Global)'
                : $this->modelo->obtenerNombreCajero((int)$idSesion);

            echo json_encode([
                'success' => true,
                'data' => [
                    'fecha'            => $fecha,
                    'usuario'          => $nombreCajero,
                    'total_documentos' => $resumen['total_documentos'],
                    'total_unidades'   => $resumen['total_unidades'],
                    'total_lineas'     => $resumen['total_lineas'],
                    'detalle'          => $detalle
                ]
            ]);
            exit();

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensaje' => 'Error al calcular el cierre']);
            exit();
        }
    }

    public function confirmarCierreCajaAjax() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idSesion = $this->usuarioActual();
            if ($idSesion === null) {
                echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
                exit();
            }

            $fecha = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                echo json_encode(['success' => false, 'mensaje' => 'Fecha inválida']);
                exit();
            }

            $id_usuario = $this->esAdmin() ? null : (int)$idSesion;
            $resumen = $this->modelo->obtenerResumenCierreDia($id_usuario, $fecha);

            $guardado = $this->modelo->guardarCierreCaja(
                $fecha,
                $this->esAdmin() ? null : (int)$idSesion,
                (int)($resumen['total_documentos'] ?? 0),
                (int)($resumen['total_unidades'] ?? 0),
                (int)($resumen['total_lineas'] ?? 0)
            );

            if (!$guardado) {
                echo json_encode(['success' => false, 'mensaje' => 'No se pudo guardar el cierre del día']);
                exit();
            }

            echo json_encode([
                'success' => true,
                'mensaje' => 'Cierre del día guardado correctamente',
                'data' => [
                    'fecha' => $fecha,
                    'total_documentos' => (int)($resumen['total_documentos'] ?? 0),
                    'total_unidades' => (int)($resumen['total_unidades'] ?? 0),
                ]
            ]);
            exit();

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensaje' => 'Error al guardar el cierre']);
            exit();
        }
    }

    // ============================================================
    // PDF DEL REPORTE DE CAJA
    // ============================================================
    public function exportarPDF() {
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null;

        $id_usuario_filtro = $this->resolverIdUsuarioFiltro($_GET['id_usuario'] ?? null);

        $movimientos = $this->modelo->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria, 999999, 0);
        $resumen     = $this->modelo->obtenerResumenCierreCajero($id_usuario_filtro, $fecha);

        $pdf = $this->generarPdfCierreCaja($fecha, $movimientos, $resumen);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="reporte_caja_' . $fecha . '.pdf"');
        echo $pdf;
        exit();
    }

    // ============================================================
    // CSV
    // ============================================================
    public function exportarCSV() {
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] > 0 ? (int)$_GET['id_categoria'] : null;

        $id_usuario_filtro = $this->resolverIdUsuarioFiltro($_GET['id_usuario'] ?? null);

        $movimientos = $this->modelo->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, $id_categoria, 999999, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_caja_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Nota #', 'Producto', 'Categoría', 'Cantidad', 'Fecha', 'Responsable']);

        foreach ($movimientos as $m) {
            fputcsv($output, [
                $m['id_documento'],
                $m['nombre_producto'],
                $m['categoria'] ?? 'Sin categoría',
                $m['cantidad'],
                $m['fecha_movimiento'],
                $m['usuario_activo']
            ]);
        }

        fclose($output);
        exit();
    }

    // ============================================================
    // HTML DEL REPORTE (para PDF)
    // ============================================================
    private function renderReporteHTML($fecha, $movimientos, $resumen) {
        $filas = '';
        foreach ($movimientos as $m) {
            $filas .= "<tr>
                <td>{$m['id_documento']}</td>
                <td>" . htmlspecialchars($m['nombre_producto']) . "</td>
                <td>" . htmlspecialchars($m['categoria'] ?? '-') . "</td>
                <td style='text-align:right'>{$m['cantidad']}</td>
                <td>" . date('d/m/Y H:i', strtotime($m['fecha_movimiento'])) . "</td>
                <td>" . htmlspecialchars($m['usuario_activo']) . "</td>
            </tr>";
        }
        if ($filas === '') {
            $filas = "<tr><td colspan='6' style='text-align:center'>Sin movimientos registrados</td></tr>";
        }

        $fechaFmt = date('d/m/Y', strtotime($fecha));

        return "<!DOCTYPE html><html><head><meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            h2 { text-align:center; margin-bottom:4px; }
            .sub { text-align:center; color:#666; font-size:12px; margin-bottom:16px; }
            .kpis { display:flex; gap:20px; justify-content:center; margin-bottom:16px; }
            .kpi { border:1px solid #ccc; padding:10px 20px; border-radius:6px; text-align:center; }
            .kpi b { display:block; font-size:20px; }
            table { width:100%; border-collapse:collapse; font-size:12px; }
            th, td { border:1px solid #ccc; padding:6px; }
            th { background:#f0f0f0; }
        </style></head><body>
            <h2>Reporte de Caja</h2>
            <div class='sub'>Fecha: {$fechaFmt}</div>
            <div class='kpis'>
                <div class='kpi'><b>{$resumen['total_unidades']}</b>Unidades</div>
                <div class='kpi'><b>{$resumen['total_documentos']}</b>Notas</div>
                <div class='kpi'><b>{$resumen['total_lineas']}</b>Líneas</div>
            </div>
            <table>
                <thead><tr>
                    <th>Nota #</th><th>Producto</th><th>Categoría</th>
                    <th style='text-align:right'>Cantidad</th><th>Fecha</th><th>Responsable</th>
                </tr></thead>
                <tbody>{$filas}</tbody>
            </table>
        </body></html>";
    }

    private function generarPdfCierreCaja($fecha, $movimientos, $resumen) {
        $normalizar = function ($texto) {
            $texto = trim((string)$texto);
            $texto = str_replace(['á', 'à', 'ä', 'â'], 'a', $texto);
            $texto = str_replace(['é', 'è', 'ë', 'ê'], 'e', $texto);
            $texto = str_replace(['í', 'ì', 'ï', 'î'], 'i', $texto);
            $texto = str_replace(['ó', 'ò', 'ö', 'ô'], 'o', $texto);
            $texto = str_replace(['ú', 'ù', 'ü', 'û'], 'u', $texto);
            $texto = str_replace(['ñ'], 'n', $texto);
            $texto = str_replace(['Á', 'À', 'Ä', 'Â'], 'A', $texto);
            $texto = str_replace(['É', 'È', 'Ë', 'Ê'], 'E', $texto);
            $texto = str_replace(['Í', 'Ì', 'Ï', 'Î'], 'I', $texto);
            $texto = str_replace(['Ó', 'Ò', 'Ö', 'Ô'], 'O', $texto);
            $texto = str_replace(['Ú', 'Ù', 'Ü', 'Û'], 'U', $texto);
            $texto = str_replace(['Ñ'], 'N', $texto);
            $texto = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
            return preg_replace('/[^A-Za-z0-9 .,:_\-\/]+/', '', $texto);
        };

        $stream = "BT\n/F1 18 Tf\n50 780 Td\n(" . $normalizar('Reporte Diario de Caja') . ") Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n50 760 Td\n(" . $normalizar('Fecha: ' . $fecha) . ") Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n50 745 Td\n(" . $normalizar('Total unidades: ' . ($resumen['total_unidades'] ?? 0)) . ") Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n220 745 Td\n(" . $normalizar('Total notas: ' . ($resumen['total_documentos'] ?? 0)) . ") Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n50 700 Td\n(Nota)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n100 700 Td\n(Producto)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n275 700 Td\n(Cantidad)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n355 700 Td\n(Fecha)Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n470 700 Td\n(Responsable)Tj\nET\n";

        $lineY = 682;
        foreach ($movimientos as $m) {
            if ($lineY < 80) {
                break;
            }
            $stream .= "BT\n/F1 8 Tf\n50 " . $lineY . " Td\n(" . $normalizar($m['id_documento'] ?? 'N/A') . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n100 " . $lineY . " Td\n(" . $normalizar($m['nombre_producto'] ?? 'N/A') . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n275 " . $lineY . " Td\n(" . $normalizar((string)($m['cantidad'] ?? 0)) . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n355 " . $lineY . " Td\n(" . $normalizar($m['fecha_movimiento'] ?? '') . ") Tj\nET\n";
            $stream .= "BT\n/F1 8 Tf\n470 " . $lineY . " Td\n(" . $normalizar($m['usuario_activo'] ?? 'N/A') . ") Tj\nET\n";
            $lineY -= 14;
        }

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj;
        }
        $xrefStart = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string)$offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . $xrefStart . "\n%%EOF\n";

        return $pdf;
    }
}