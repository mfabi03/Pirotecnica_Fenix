<?php
namespace App\Pirotecnicafenix\Controller;

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

// Carga de modelos con fallback por si la carpeta está en minúscula
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

// Helper local para validar que el usuario en sesión siga activo en la BD
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

// Filtro de visibilidad: el admin ve todo, los demás solo ven sus propias notas
$esAdmin = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) || (strtolower($_SESSION['nombre_rol'] ?? '') === 'administrador');
$id_usuario_filtro = $esAdmin ? null : (int)($_SESSION['id_usuario'] ?? 0);

// Validar que no se registren salidas si la caja del día ya fue cerrada
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

// Endpoint AJAX para cargar el detalle de la nota en el modal
if ($type === 'detalle_json' && $id) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $nota = $modelo->obtenerNotaPorId($id);
        if (!$nota) { echo json_encode(['success' => false, 'error' => 'Nota no encontrada']); exit; }
        echo json_encode(['success' => true, 'nota' => $nota], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { 
        echo json_encode(['success' => false, 'error' => $e->getMessage()]); 
    }
    exit;
}

// Endpoint para registro rápido de cliente desde el formulario de salida
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

// Exportación a CSV
if ($type === 'exportar_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="notas_salida_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF"); // BOM para que Excel reconozca UTF-8
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
    // Guardar nueva nota de salida
    if ($type === 'store') {
        CheckPermiso::verificar($db, 'Notas de Salida', 'crear', '?url=notasalida&type=list');
        try {
            $idUsuario = obtenerIdUsuarioValido($db);
            $detalles = [];
            $productos_ids = $_POST['detalle_producto'] ?? [];
            $cantidades = $_POST['detalle_cantidad'] ?? [];
            
            for ($i = 0; $i < count($productos_ids); $i++) {
                if (!empty($productos_ids[$i]) && !empty($cantidades[$i])) {
                    $detalles[] = ['id_producto' => intval($productos_ids[$i]), 'cantidad' => intval($cantidades[$i])];
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
    
    // Anular nota (eliminado lógico)
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
                // Redirige a inactivos tras eliminar
                header("Location: ?url=notasalida&type=list&estado_nota=inactivos"); 
                exit();
            } else { 
                throw new Exception("No se pudo anular la nota."); 
            }
        } catch (Exception $e) { 
            $error = $e->getMessage(); 
        }
    }

    // Reactivar nota (quitar eliminado lógico)
    if ($type === 'reactivar') {
        CheckPermiso::verificar($db, 'Notas de Salida', 'actualizar', '?url=notasalida&type=list');
        try {
            $idNota = $_POST['id_nota_salida'] ?? 0;
            if (!$idNota || $idNota <= 0) throw new Exception("ID inválido");
            
            $modelo->reactivarNota($idNota);
            $_SESSION['mensaje'] = 'Nota de Salida reactivada exitosamente.'; 
            $_SESSION['tipo_mensaje'] = 'success';
            
            // Redirige a activos tras reactivar
            header("Location: ?url=notasalida&type=list&estado_nota=activos"); 
            exit();
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al reactivar: " . $e->getMessage(); 
            $_SESSION['tipo_mensaje'] = 'danger';
            header("Location: ?url=notasalida&type=list&estado_nota=inactivos"); 
            exit();
        }
    }
}

$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'nota_salida';

// Generar PDF
if ($type === 'generar_pdf' && $id) {
    $archivoPdf = $baseViewPath . DIRECTORY_SEPARATOR . 'generar_pdf.php';
    if (file_exists($archivoPdf)) { 
        require_once $archivoPdf; 
        exit(); 
    } else { 
        die("ERROR: No se encuentra generar_pdf.php"); 
    }
}

// Formulario de creación
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

// Detalle individual
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

// Listado principal con filtros y paginación
try {
    $buscar = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));

    // Por defecto muestra solo las activas
    $estado_nota = strtolower(trim((string) ($_REQUEST['estado_nota'] ?? 'activos')));
    if (!in_array($estado_nota, ['todos', 'activos', 'inactivos'], true)) {
        $estado_nota = 'activos';
    }

    $notas_full = !empty($buscar) 
        ? $modelo->buscarNotasSalida($buscar, $id_usuario_filtro) 
        : $modelo->listarNotasSalida($id_usuario_filtro);
    
    // Filtro en PHP porque el modelo ya trae todo (evita otra consulta)
    if ($estado_nota === 'activos') {
        $notas_full = array_values(array_filter($notas_full, function ($n) {
            return ((int) ($n['eliminado'] ?? 0)) === 0;
        }));
    } elseif ($estado_nota === 'inactivos') {
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