<?php
namespace App\Pirotecnicafenix\Controller;

use PDO;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\notasalidaModel;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\clientesModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\CheckPermiso;
use Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutaRaiz = dirname(__DIR__, 2);

$pathNotaModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'notasalidaModel.php';
$pathProductoModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'ProductoModel.php';
$pathClienteModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'clientesModel.php';

if (!file_exists($pathNotaModel)) {
    $pathNotaModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'notasalidaModel.php';
}
if (!file_exists($pathProductoModel)) {
    $pathProductoModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ProductoModel.php';
}
if (!file_exists($pathClienteModel)) {
    $pathClienteModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'clientesModel.php';
}

if (file_exists($pathNotaModel)) { require_once $pathNotaModel; } else { die("ERROR: No se encuentra notasalidaModel.php"); }
if (file_exists($pathProductoModel)) { require_once $pathProductoModel; } else { die("ERROR: No se encuentra ProductoModel.php"); }
if (file_exists($pathClienteModel)) { require_once $pathClienteModel; } else { die("ERROR: No se encuentra clientesModel.php"); }

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new \App\Pirotecnicafenix\Model\notasalidaModel($db);
    $productoModel = new \App\Pirotecnicafenix\Model\ProductoModel($db);
    $clienteModel = new \App\Pirotecnicafenix\Model\clientesModel($db);
} catch (Exception $e) {
    die("ERROR de conexión: " . $e->getMessage());
}

function obtenerIdUsuarioValido($db) {
    $idUsuario = $_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? null;
    if (!$idUsuario) throw new Exception("No hay usuario en sesión.");
    
    $stmt = $db->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? AND eliminado = 0");
    $stmt->execute([$idUsuario]);
    
    if (!$stmt->fetch()) throw new Exception("El usuario no existe o está inactivo.");
    return (int) $idUsuario;
}

$type = $_GET['type'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = null;
$success = null;
$nota = null;
$notas = [];
$productos = [];
$clientes = [];
$resumen = [];
$tipo_mensaje = '';

$esAdmin = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (strtolower($_SESSION['nombre_rol'] ?? '') === 'administrador');
$id_usuario_filtro = $esAdmin ? null : (int)($_SESSION['id_usuario'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'store') {
    $fecha_hoy = date('Y-m-d');
    $id_usuario_real = (int)($_SESSION['id_usuario'] ?? 0);
    
    $sqlCierre = "SELECT COUNT(*) FROM cierre_caja WHERE fecha_cierre = :fecha AND id_usuario = :id_usuario";
    $stmtCierre = $db->prepare($sqlCierre);
    $stmtCierre->execute([':fecha' => $fecha_hoy, ':id_usuario' => $id_usuario_real]);
    
    if ($stmtCierre->fetchColumn() > 0) {
        $_SESSION['mensaje'] = "Error: Ya has cerrado la caja de hoy. No puedes registrar más notas de salida hasta mañana.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=notasalida&type=list");
        exit();
    }
}

if ($type === 'detalle_json' && $id) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $nota = $modelo->obtenerNotaPorId($id);
        if (!$nota) { 
            echo json_encode(['success' => false, 'error' => 'Nota no encontrada']); 
            exit; 
        }
        
        if ($nota['eliminado'] == 1) {
            if (!empty($nota['fecha_anulacion'])) {
                $fechaAnulacion = new \DateTime($nota['fecha_anulacion']);
                $nota['fecha_anulacion_fmt'] = $fechaAnulacion->format('d/m/Y h:i A');
            } else {
                $nota['fecha_anulacion_fmt'] = 'N/A';
            }
            
            $stmtTotal = $db->prepare("SELECT COALESCE(SUM(cantidad), 0) FROM detalle_salida WHERE id_nota_salida = ?");
            $stmtTotal->execute([$id]);
            $nota['total_productos_anulados'] = (int)$stmtTotal->fetchColumn();
        }

        echo json_encode(['success' => true, 'nota' => $nota], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { 
        echo json_encode(['success' => false, 'error' => $e->getMessage()]); 
    }
    exit;
}

if ($type === 'store_rapido_cliente_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!PermisoHelper::tienePermiso($db, $_SESSION['id_rol'] ?? 0, 'Clientes', 'crear')) {
            throw new Exception("No tienes permiso para registrar clientes.");
        }
        
        $tipoCliente = $_POST['tipo_cliente'] ?? 'natural';
        if ($tipoCliente === 'juridico') {
            if (empty($_POST['rif']) || empty($_POST['razon_social'])) throw new Exception("RIF y Razón Social obligatorios.");
            $datos = ['rif' => trim($_POST['rif']), 'razon_social' => trim($_POST['razon_social']), 'telefono' => trim($_POST['telefono'] ?? ''), 'correo_electronico' => trim($_POST['correo_electronico'] ?? ''), 'direccion' => trim($_POST['direccion'] ?? ''), 'tipo' => 'juridico'];
            $id_cliente = $clienteModel->registrarClienteJuridico($datos);
            $nombre_cliente = $datos['razon_social']; $documento = $datos['rif']; $tipo_label = 'Jurídico';
        } else {
            if (empty($_POST['cedula']) || empty($_POST['nombre']) || empty($_POST['apellido'])) throw new Exception("Cédula, Nombre y Apellido obligatorios.");
            $datos = ['cedula' => trim($_POST['cedula']), 'nombre' => trim($_POST['nombre']), 'apellido' => trim($_POST['apellido']), 'telefono' => trim($_POST['telefono'] ?? ''), 'correo_electronico' => trim($_POST['correo_electronico'] ?? ''), 'direccion' => trim($_POST['direccion'] ?? ''), 'fecha_de_nacimiento' => $_POST['fecha_de_nacimiento'] ?? date('Y-m-d', strtotime('-18 years')), 'tipo' => 'natural'];
            $id_cliente = $clienteModel->registrarClienteNatural($datos);
            $nombre_cliente = $datos['nombre'] . ' ' . $datos['apellido']; $documento = $datos['cedula']; $tipo_label = 'Natural';
        }
        
        if (!$id_cliente) throw new Exception("No se pudo registrar el cliente.");
        echo json_encode(['success' => true, 'id_cliente' => (int) $id_cliente, 'nombre_cliente' => $nombre_cliente, 'documento' => $documento, 'tipo' => $tipo_label], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { 
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE); 
    }
    exit;
}

if ($type === 'exportar_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="notas_salida_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['ID', 'Fecha', 'Cliente', 'Documento', 'Productos', 'Categorías', 'Total Unidades', 'Encargado']);
    
    $notas_export = $modelo->listarNotasSalida($id_usuario_filtro);
    foreach ($notas_export as $n) {
        $clienteNombre = !empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : trim($n['cliente_nombre'] . ' ' . $n['cliente_apellido']);
        $encargado = trim($n['usuario_nombre'] . ' ' . $n['usuario_apellido']);
        fputcsv($output, [
            $n['id_nota_salida'], $n['fecha'], $clienteNombre, $n['cedula'] ?? 'N/A', 
            $n['productos_lista'], $n['categorias_lista'], $n['total_unidades'], $encargado
        ]);
    }
    fclose($output);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'store') {
        CheckPermiso::verificar($db, 'Notas de Salida', 'crear', '?url=notasalida&type=list');
        try {
            $idUsuario = obtenerIdUsuarioValido($db);
            $detalles = [];
            $productos_ids = $_POST['detalle_producto'] ?? [];
            $cantidades = $_POST['detalle_cantidad'] ?? [];
            
            for ($i = 0; $i < count($productos_ids); $i++) {
                if (!empty($productos_ids[$i]) && !empty($cantidades[$i])) {
                    $idProducto = intval($productos_ids[$i]);
                    
                    $stmtValidar = $db->prepare("SELECT eliminado, descripcion FROM producto WHERE id_producto = ?");
                    $stmtValidar->execute([$idProducto]);
                    $productoBD = $stmtValidar->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$productoBD) {
                        throw new Exception("El producto con ID " . $idProducto . " no existe.");
                    }
                    if ($productoBD['eliminado'] == 1) {
                        throw new Exception("El producto '" . htmlspecialchars($productoBD['descripcion']) . "' está eliminado y no se puede vender.");
                    }

                    $detalles[] = ['id_producto' => $idProducto, 'cantidad' => intval($cantidades[$i])];
                }
            }
            
            if (empty($detalles) || count($detalles) === 0) throw new Exception("Debe agregar al menos un producto.");
            if (empty($_POST['id_cliente'])) throw new Exception("Debe seleccionar un cliente.");
            foreach ($detalles as $d) { if ($d['cantidad'] <= 0) throw new Exception("La cantidad debe ser mayor a 0."); }
            
            $datos = ['id_cliente' => intval($_POST['id_cliente'])];
            $resultado = $modelo->registrarSalida($datos, $detalles, $idUsuario);
            
            if ($resultado) {
                $_SESSION['mensaje'] = 'Nota de Salida registrada exitosamente.'; 
                $_SESSION['tipo_mensaje'] = 'success';
                header("Location: ?url=notasalida&type=list"); 
                exit();
            }
        } catch (Exception $e) { 
            $error = $e->getMessage(); 
        }
    }
    
    if ($type === 'eliminar') {
        CheckPermiso::verificar($db, 'Notas de Salida', 'eliminar', '?url=notasalida&type=list');
        try {
            $idNota = $_POST['id_nota_salida'] ?? 0;
            $motivo = trim($_POST['motivo_eliminacion'] ?? $_POST['motivo_anulacion'] ?? '');
            $idUsuario = obtenerIdUsuarioValido($db);
            
            if (empty($motivo)) throw new Exception("Debe indicar el motivo de eliminación.");
            
            $resultado = $modelo->eliminarNota($idNota, $motivo);
            if ($resultado) {
                $_SESSION['mensaje'] = 'Nota de Salida anulada exitosamente.'; 
                $_SESSION['tipo_mensaje'] = 'warning';
                header("Location: ?url=notasalida&type=list&ver_anuladas=1");
                exit();
            } else { 
                throw new Exception("No se pudo anular la nota."); 
            }
        } catch (Exception $e) { 
            $error = $e->getMessage(); 
        }
    }
}

$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'nota_salida';

if ($type === 'generar_pdf' && $id) {
    $archivoPdf = $baseViewPath . DIRECTORY_SEPARATOR . 'generar_pdf.php';
    if (file_exists($archivoPdf)) { 
        require_once $archivoPdf; 
        exit(); 
    } else { 
        die("ERROR: No se encuentra generar_pdf.php"); 
    }
}

if ($type === 'create') {
    try {
        $productos = $modelo->obtenerProductosConCategoria();
        $clientes = $clienteModel->obtenerClientes(true); 
        $categorias = $productoModel->obtenerCategorias();
    } catch (Exception $e) { 
        die("ERROR al obtener datos: " . $e->getMessage()); 
    }
    $viewFile = $baseViewPath . "/registroNotaSalidaView.php";
    if (!file_exists($viewFile)) die("ERROR: No se encuentra registroNotaSalidaView.php");
    require_once $viewFile; 
    exit();
}

if ($type === 'show' && $id) {
    try {
        $nota = $modelo->obtenerNotaPorId($id);
        if (!$nota) die("ERROR: Nota de salida no encontrada.");
    } catch (Exception $e) { 
        die("ERROR al obtener nota: " . $e->getMessage()); 
    }
    $viewFile = $baseViewPath . "/detalleNotasalidaView.php";
    if (!file_exists($viewFile)) die("ERROR: No se encuentra detalleNotasalidaView.php");
    require_once $viewFile; 
    exit();
}

try {
    $buscar = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));

    $estado_nota = !empty($_REQUEST['ver_anuladas']) ? 'inactivos' : 'activos';

    $notas_full = !empty($buscar) 
        ? $modelo->buscarNotasSalida($buscar, $id_usuario_filtro) 
        : $modelo->listarNotasSalida($id_usuario_filtro);
    
    if ($estado_nota === 'activos') {
        $notas_full = array_values(array_filter($notas_full, function ($n) {
            return ((int) ($n['eliminado'] ?? 0)) === 0;
        }));
    } else {
        $notas_full = array_values(array_filter($notas_full, function ($n) {
            return ((int) ($n['eliminado'] ?? 0)) === 1;
        }));
    }

    $resumen = $modelo->getResumen($id_usuario_filtro);
    
    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $por_pagina;
    
    $totalRegistros = is_array($notas_full) ? count($notas_full) : 0;
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    $notas = $por_pagina > 0 ? array_slice($notas_full, $offset, $por_pagina) : $notas_full;
    
    if (isset($_SESSION['mensaje'])) { 
        $success = $_SESSION['mensaje']; 
        $mensaje = $_SESSION['mensaje']; 
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info'; 
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); 
    }
    
    if ($error) { 
        $mensaje = $error; 
        $tipo_mensaje = 'danger'; 
    }
} catch (Exception $e) { 
    die("ERROR al obtener notas: " . $e->getMessage()); 
}

require_once $baseViewPath . "/listNotasalidaView.php";