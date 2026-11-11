<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\ReporteCajaModel;
use App\Pirotecnicafenix\Model\notasalidaModel;
use Exception;
use PDO;

class ReporteCajaController {
    private $db;
    private $model;
    private $modeloNota;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = $db;
        $this->model = new ReporteCajaModel($db);
        $this->modeloNota = new notasalidaModel($db);
    }

    private function esAdmin() {
        return (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (strtolower($_SESSION['nombre_rol'] ?? '') === 'administrador');
    }

    public function index() {
        $esAdmin = $this->esAdmin();
        $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : date('Y-m-d');
        $busqueda = $_GET['busqueda'] ?? null;
        
        $por_pagina = (int)($_GET['por_pagina'] ?? 10);
        $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset = ($pagina_actual - 1) * $por_pagina;

        $id_usuario_filtro = (int)($_SESSION['id_usuario'] ?? 0);

        $cajaCerrada = $this->model->existeCierreCaja($fecha, $id_usuario_filtro);

        if ($cajaCerrada) {
            $movimientos = [];
            $totalRegistros = 0;
            $totalDocumentos = 0;
            $totalUnidades = 0;
        } else {
            $movimientos = $this->model->obtenerSalidasCajero($id_usuario_filtro, $fecha, $busqueda, null, $por_pagina, $offset);
            $totalRegistros = count($movimientos);
            $totalDocumentos = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnidades = array_sum(array_column($movimientos, 'cantidad'));
        }
        
        $porPaginaActual = $por_pagina;
        $paginaActual = $pagina_actual;
        $totalPaginas = $por_pagina > 0 ? ceil($totalRegistros / $por_pagina) : 1;
        $fechaFiltro = $fecha; 
        $db = $this->db;

        require_once dirname(__DIR__) . '/view/reporteCaja/reporteCajaView.php';
    }

    public function historial() {
        if (!$this->esAdmin()) { header('Location: ?url=dashboard'); exit(); }
        try {
            $sql = "SELECT c.*, CONCAT(p.nombre, ' ', p.apellido) AS nombre_usuario, p.cedula AS cedula_usuario
                    FROM cierre_caja c
                    LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                    LEFT JOIN persona p ON u.id_persona = p.id_persona
                    ORDER BY c.fecha_cierre DESC, c.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $cierres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { $cierres = []; $error = $e->getMessage(); }
        $db = $this->db;
        require_once dirname(__DIR__) . '/view/reporteCaja/historialCierresView.php';
    }

    public function obtenerCierreDiaAjax() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
            $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : (isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : date('Y-m-d'));
            
            $movimientos = $this->model->obtenerSalidasParaCierre($id_usuario, $fecha);
            
            $totalDocs = count(array_unique(array_column($movimientos, 'id_documento')));
            $totalUnds = array_sum(array_column($movimientos, 'cantidad'));

            $detalle = [];
            foreach ($movimientos as $m) {
                $detalle[] = [
                    'id_documento' => $m['id_documento'],
                    'fecha_movimiento' => $m['fecha_movimiento'],
                    'usuario_activo' => $m['usuario_activo'],
                    'nombre_cliente' => $m['nombre_cliente'],
                    'tipo_cliente' => $m['tipo_cliente'] ?? 'Natural',
                    'nombre_producto' => $m['nombre_producto'],
                    'categoria' => $m['categoria'],
                    'cantidad' => $m['cantidad']
                ];
            }
            echo json_encode(['success' => true, 'data' => ['total_documentos' => $totalDocs, 'total_unidades' => $totalUnds, 'detalle' => $detalle]]);
        } catch (Exception $e) { 
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]); 
        }
        exit();
    }

    public function confirmarCierreCajaAjax() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idSesion = $_SESSION['id_usuario'] ?? null;
            if ($idSesion === null) { echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']); exit(); }

            $fecha_solicitada = $_POST['fecha'] ?? $_GET['fecha'] ?? date('Y-m-d');
            $id_usuario = (int)$idSesion;

            if ($this->model->existeCierreCaja($fecha_solicitada, $id_usuario)) {
                echo json_encode(['success' => false, 'mensaje' => 'Esta caja ya fue cerrada hoy.']);
                exit();
            }

            $movimientos = $this->model->obtenerSalidasParaCierre($id_usuario, $fecha_solicitada);
            $total_docs = 0; $total_unds = 0; $total_prods = 0; $total_cats = 0; $total_clientes = 0; $hora_apertura = null;
            $notas_vistas = []; $prods_vistos = []; $cats_vistas = []; $clientes_vistos = [];

            if (!empty($movimientos)) {
                $primeraVenta = end($movimientos);
                $hora_apertura = date('H:i:s', strtotime($primeraVenta['fecha_movimiento']));

                foreach ($movimientos as $m) {
                    if (!in_array($m['id_documento'], $notas_vistas)) { 
                        $notas_vistas[] = $m['id_documento']; 
                        $total_docs++; 
                    }
                    
                    $total_unds += (int)$m['cantidad'];
                    
                    $prod_limpio = preg_replace('/\s*\(x\d+\)/', '', $m['nombre_producto']);
                    if (!in_array($prod_limpio, $prods_vistos)) { 
                        $prods_vistos[] = $prod_limpio; 
                        $total_prods++; 
                    }

                    $cat_actual = $m['categoria'] ?? 'Sin Categoría';
                    if (!in_array($cat_actual, $cats_vistas)) {
                        $cats_vistas[] = $cat_actual;
                        $total_cats++;
                    }

                    if (!in_array($m['nombre_cliente'], $clientes_vistos)) { 
                        $clientes_vistos[] = $m['nombre_cliente']; 
                        $total_clientes++; 
                    }
                }
            }

            $guardado = $this->model->guardarCierreCaja(
                $fecha_solicitada, 
                $id_usuario, 
                $total_docs, 
                $total_unds, 
                $total_cats, 
                $hora_apertura, 
                $total_prods, 
                $total_clientes
            );

            if (!$guardado) { 
                echo json_encode(['success' => false, 'mensaje' => 'No se pudo guardar el cierre']); 
                exit(); 
            }

            echo json_encode([
                'success' => true,
                'mensaje' => 'Cierre del día guardado correctamente',
                'data' => [
                    'fecha' => $fecha_solicitada,
                    'hora_apertura' => date('h:i A', strtotime($hora_apertura)),
                    'total_documentos' => $total_docs,
                    'total_unidades' => $total_unds,
                    'total_productos' => $total_prods,
                    'total_categorias' => $total_cats,
                    'total_clientes' => $total_clientes,
                    'encargado' => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')),
                    'detalle' => array_map(function($m) { return [
                        'id_documento' => $m['id_documento'], 
                        'fecha_movimiento' => $m['fecha_movimiento'],
                        'nombre_cliente' => $m['nombre_cliente'], 
                        'tipo_cliente' => $m['tipo_cliente'] ?? 'Natural',
                        'nombre_producto' => $m['nombre_producto'], 
                        'categoria' => $m['categoria'] ?? 'Sin categoría', 
                        'cantidad' => $m['cantidad'],
                        'usuario_activo' => $m['usuario_activo']
                    ]; }, $movimientos)
                ]
            ]);
            exit();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensaje' => 'Error al guardar el cierre: ' . $e->getMessage()]);
            exit();
        }
    }

    public function verDetalleAjax() {
        header('Content-Type: application/json; charset=utf-8');
        $id = $_GET['id'] ?? 0;
        try {
            $nota = $this->modeloNota->obtenerNotaPorId($id);
            if ($nota) { echo json_encode(['success' => true, 'nota' => $nota]); } else { echo json_encode(['success' => false, 'error' => 'Nota no encontrada']); }
        } catch (Exception $e) { echo json_encode(['success' => false, 'error' => $e->getMessage()]); }
        exit();
    }

    public function exportarPDF() {
        try {
            $id_cierre = isset($_GET['id_cierre']) ? (int)$_GET['id_cierre'] : 0;

            if ($id_cierre > 0) {
                $sqlCierre = "SELECT c.*, CONCAT(p.nombre, ' ', p.apellido) AS nombre_usuario
                              FROM cierre_caja c
                              LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                              LEFT JOIN persona p ON u.id_persona = p.id_persona
                              WHERE c.id_cierre = :id LIMIT 1";
                $stmtC = $this->db->prepare($sqlCierre);
                $stmtC->execute([':id' => $id_cierre]);
                $cierre = $stmtC->fetch(PDO::FETCH_ASSOC);

                if (!$cierre) { die("Error: Cierre no encontrado."); }

                $sqlDet = "SELECT ns.id_nota_salida AS id_documento, DATE_FORMAT(ns.fecha, '%d/%m/%Y %h:%i %p') AS fecha_movimiento,
                                  GROUP_CONCAT(DISTINCT CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') SEPARATOR ', ') AS nombre_producto,
                                  SUM(ds.cantidad) AS cantidad,
                                  COUNT(DISTINCT prod.id_producto) AS total_productos,
                                  COUNT(DISTINCT c.id_categoria) AS total_categorias,
                                  COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
                                  CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
                                  COALESCE(CONCAT(per.nombre, ' ', per.apellido), 'N/A') AS usuario_activo
                           FROM nota_de_salida ns
                           INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                           INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                           LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                           LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                           LEFT JOIN persona per ON u.id_persona = per.id_persona
                           LEFT JOIN persona per_cliente ON ns.id_persona = per_cliente.id_persona
                           LEFT JOIN cliente_juridico cj ON per_cliente.id_persona = cj.id_persona
                           WHERE ns.id_nota_salida <= :ultimo_id 
                             AND ns.id_usuario = :id_usuario
                             AND DATE(ns.fecha) = :fecha_cierre
                             AND ns.eliminado = 0
                           GROUP BY ns.id_nota_salida, ns.fecha, cj.razon_social, per_cliente.nombre, per_cliente.apellido, cj.id_cliente_juridico, per.nombre, per.apellido
                           ORDER BY ns.fecha DESC";
                
                $stmtDet = $this->db->prepare($sqlDet);
                $stmtDet->execute([
                    ':ultimo_id' => $cierre['ultimo_id_nota_salida'],
                    ':id_usuario' => $cierre['id_usuario'],
                    ':fecha_cierre' => $cierre['fecha_cierre']
                ]);
                $movimientos = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

                $datos_pdf = [
                    'fecha' => date('d/m/Y', strtotime($cierre['fecha_cierre'])),
                    'hora_apertura' => $cierre['hora_apertura'] ? date('h:i A', strtotime($cierre['hora_apertura'])) : '—',
                    'total_documentos' => $cierre['total_documentos'],
                    'total_unidades' => $cierre['total_unidades'],
                    'total_productos' => $cierre['total_productos_distintos'],
                    'total_clientes' => $cierre['total_clientes'],
                    'movimientos' => $movimientos,
                    'encargado' => $cierre['nombre_usuario']
                ];

            } else {
                $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
                $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : date('Y-m-d');
                
                $sql = "SELECT ns.id_nota_salida AS id_documento, DATE_FORMAT(ns.fecha, '%d/%m/%Y %h:%i %p') AS fecha_movimiento,
                        GROUP_CONCAT(DISTINCT CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') SEPARATOR ', ') AS nombre_producto,
                        SUM(ds.cantidad) AS cantidad,
                        COUNT(DISTINCT prod.id_producto) AS total_productos,
                        COUNT(DISTINCT c.id_categoria) AS total_categorias,
                        COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
                        CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
                        COALESCE(CONCAT(per.nombre, ' ', per.apellido), 'N/A') AS usuario_activo
                        FROM nota_de_salida ns
                        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                        INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                        LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                        LEFT JOIN persona per ON u.id_persona = per.id_persona
                        LEFT JOIN persona per_cliente ON ns.id_persona = per_cliente.id_persona
                        LEFT JOIN cliente_juridico cj ON per_cliente.id_persona = cj.id_persona
                        WHERE ns.eliminado = 0 AND DATE(ns.fecha) = :fecha AND ns.id_usuario = :id_usuario
                        GROUP BY ns.id_nota_salida, ns.fecha
                        ORDER BY ns.fecha DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':fecha' => $fecha, ':id_usuario' => $id_usuario]);
                $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $totalDocs = count(array_unique(array_column($movimientos, 'id_documento')));
                $totalUnds = array_sum(array_column($movimientos, 'cantidad'));
                $hora_apertura = 'Sin ventas'; $total_productos = 0; $total_clientes = 0;
                
                if (!empty($movimientos)) {
                    $primeraVenta = end($movimientos);
                    $hora_apertura = date('h:i A', strtotime($primeraVenta['fecha_movimiento']));
                    $prods_tmp = []; $clientes_tmp = [];
                    foreach ($movimientos as $m) {
                        $p = preg_replace('/\s*\(x\d+\)/', '', $m['nombre_producto']);
                        $prods_tmp[$p] = true; $clientes_tmp[$m['nombre_cliente']] = true;
                    }
                    $total_productos = count($prods_tmp); $total_clientes = count($clientes_tmp);
                }

                $usuario_activo = 'Administrador';
                $id_usuario_sesion = (int)($_SESSION['id_usuario'] ?? 0);
                if ($id_usuario_sesion > 0) {
                    $stmtUsuario = $this->db->prepare("SELECT p.nombre, p.apellido FROM usuario u INNER JOIN persona p ON u.id_persona = p.id_persona WHERE u.id_usuario = :id");
                    $stmtUsuario->execute([':id' => $id_usuario_sesion]);
                    $datosPersona = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
                    if ($datosPersona) { $usuario_activo = trim($datosPersona['nombre'] . ' ' . $datosPersona['apellido']); }
                }

                $datos_pdf = [
                    'fecha' => $fecha ? date('d/m/Y', strtotime($fecha)) : date('d/m/Y'),
                    'hora_apertura' => $hora_apertura,
                    'total_documentos' => $totalDocs,
                    'total_unidades' => $totalUnds,
                    'total_productos' => $total_productos,
                    'total_clientes' => $total_clientes,
                    'movimientos' => $movimientos,
                    'encargado' => $usuario_activo
                ];
            }

            require_once dirname(__DIR__) . '/view/reporteCaja/generar_pdfCajaView.php';
            exit();
        } catch (Exception $e) { die("Error al generar el PDF: " . $e->getMessage()); }
    }

    // 🔥 FUNCIÓN PARA EXPORTAR EL EXCEL/CSV
    public function exportarCSV() {
        $id_cierre = isset($_GET['id_cierre']) ? (int)$_GET['id_cierre'] : 0;
        $id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
        $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : date('Y-m-d');

        try {
            if ($id_cierre > 0) {
                $sqlCierre = "SELECT c.*, CONCAT(p.nombre, ' ', p.apellido) AS nombre_usuario
                              FROM cierre_caja c
                              LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                              LEFT JOIN persona p ON u.id_persona = p.id_persona
                              WHERE c.id_cierre = :id LIMIT 1";
                $stmtC = $this->db->prepare($sqlCierre);
                $stmtC->execute([':id' => $id_cierre]);
                $cierre = $stmtC->fetch(PDO::FETCH_ASSOC);

                if (!$cierre) { die("Error: Cierre no encontrado."); }

                $sqlDet = "SELECT ns.id_nota_salida AS id_documento, DATE_FORMAT(ns.fecha, '%d/%m/%Y %h:%i %p') AS fecha_movimiento,
                                  GROUP_CONCAT(DISTINCT CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') SEPARATOR ', ') AS nombre_producto,
                                  SUM(ds.cantidad) AS cantidad,
                                  GROUP_CONCAT(DISTINCT c.nombre_categoria SEPARATOR ', ') AS categoria,
                                  COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
                                  CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
                                  COALESCE(CONCAT(per.nombre, ' ', per.apellido), 'N/A') AS usuario_activo
                           FROM nota_de_salida ns
                           INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                           INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                           LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                           LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                           LEFT JOIN persona per ON u.id_persona = per.id_persona
                           LEFT JOIN persona per_cliente ON ns.id_persona = per_cliente.id_persona
                           LEFT JOIN cliente_juridico cj ON per_cliente.id_persona = cj.id_persona
                           WHERE ns.id_nota_salida <= :ultimo_id 
                             AND ns.id_usuario = :id_usuario
                             AND DATE(ns.fecha) = :fecha_cierre
                             AND ns.eliminado = 0
                           GROUP BY ns.id_nota_salida, ns.fecha, cj.razon_social, per_cliente.nombre, per_cliente.apellido, cj.id_cliente_juridico, per.nombre, per.apellido
                           ORDER BY ns.fecha DESC";
                
                $stmtDet = $this->db->prepare($sqlDet);
                $stmtDet->execute([
                    ':ultimo_id' => $cierre['ultimo_id_nota_salida'],
                    ':id_usuario' => $cierre['id_usuario'],
                    ':fecha_cierre' => $cierre['fecha_cierre']
                ]);
                $movimientos = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
                $fechaArchivo = $cierre['fecha_cierre'];

            } else {
                $sql = "SELECT ns.id_nota_salida AS id_documento, DATE_FORMAT(ns.fecha, '%d/%m/%Y %h:%i %p') AS fecha_movimiento,
                        GROUP_CONCAT(DISTINCT CONCAT(prod.descripcion, ' (x', ds.cantidad, ')') SEPARATOR ', ') AS nombre_producto,
                        SUM(ds.cantidad) AS cantidad,
                        GROUP_CONCAT(DISTINCT c.nombre_categoria SEPARATOR ', ') AS categoria,
                        COALESCE(cj.razon_social, CONCAT(per_cliente.nombre, ' ', per_cliente.apellido), 'Sin cliente') AS nombre_cliente,
                        CASE WHEN cj.id_cliente_juridico IS NOT NULL THEN 'Jurídico' ELSE 'Natural' END AS tipo_cliente,
                        COALESCE(CONCAT(per.nombre, ' ', per.apellido), 'N/A') AS usuario_activo
                        FROM nota_de_salida ns
                        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                        INNER JOIN producto prod ON ds.id_producto = prod.id_producto
                        LEFT JOIN categoria c ON prod.id_categoria = c.id_categoria
                        LEFT JOIN usuario u ON ns.id_usuario = u.id_usuario
                        LEFT JOIN persona per ON u.id_persona = per.id_persona
                        LEFT JOIN persona per_cliente ON ns.id_persona = per_cliente.id_persona
                        LEFT JOIN cliente_juridico cj ON per_cliente.id_persona = cj.id_persona
                        WHERE ns.eliminado = 0 AND DATE(ns.fecha) = :fecha AND ns.id_usuario = :id_usuario
                        GROUP BY ns.id_nota_salida, ns.fecha
                        ORDER BY ns.fecha DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':fecha' => $fecha, ':id_usuario' => $id_usuario]);
                $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $fechaArchivo = $fecha;
            }

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="Cierre_Caja_' . $fechaArchivo . '.csv"');
            
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF"); 

            fputcsv($output, ['ID Nota', 'Fecha/Hora', 'Cliente', 'Tipo Cliente', 'Productos (Cantidad)', 'Categorías', 'Unidades', 'Encargado']);
            $totalUnidades = 0;
            foreach ($movimientos as $m) {
                $totalUnidades += (int)$m['cantidad'];
                fputcsv($output, [
                    '#' . $m['id_documento'],
                    $m['fecha_movimiento'],
                    $m['nombre_cliente'],
                    $m['tipo_cliente'],
                    $m['nombre_producto'],
                    $m['categoria'],
                    $m['cantidad'],
                    $m['usuario_activo']
                ]);
            }
            fputcsv($output, ['', '', '', '', '', 'TOTALES', $totalUnidades, '']);
            fclose($output);
            exit();

        } catch (Exception $e) {
            die("Error al generar el CSV: " . $e->getMessage());
        }
    }
}
?>