<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\NotaentradaModel;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\ProveedoresModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\CheckPermiso;
use Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión para mensajes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CARGA DE MODELOS
$rutaRaiz = dirname(__DIR__, 2);

$pathNotaModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'NotaentradaModel.php';
$pathProductoModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ProductoModel.php';
$pathProveedorModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ProveedoresModel.php';

if (file_exists($pathNotaModel)) {
    require_once $pathNotaModel;
} else {
    die("ERROR CRÍTICO: No se encuentra NotaentradaModel.php");
}

if (file_exists($pathProductoModel)) {
    require_once $pathProductoModel;
} else {
    die("ERROR CRÍTICO: No se encuentra ProductoModel.php");
}

if (file_exists($pathProveedorModel)) {
    require_once $pathProveedorModel;
} else {
    die("ERROR CRÍTICO: No se encuentra ProveedoresModel.php");
}

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new NotaentradaModel($db);
    $productoModel = new ProductoModel($db);
    $proveedorModel = new ProveedoresModel($db);
} catch (Exception $e) {
    die("ERROR de conexión: " . $e->getMessage());
}

$type = $_GET['type'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = null;
$success = null;
$nota = null;
$notas = [];
$productos = [];
$proveedores = [];
$resumen = [];
$tipo_mensaje = '';

// 2. FUNCIÓN PARA OBTENER ID USUARIO VÁLIDO (CORREGIDA)
function obtenerIdUsuarioValido($db) {
    // ✅ Buscar en la sesión con el nombre CORRECTO
    $idUsuario = $_SESSION['id_usuario'] 
              ?? $_SESSION['usuario_id'] 
              ?? null;
    
    if (!$idUsuario) {
        throw new Exception("No hay usuario en sesión. Por favor, cierre sesión y vuelva a entrar.");
    }
    
    $stmt = $db->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? AND eliminado = 0");
    $stmt->execute([$idUsuario]);
    
    if (!$stmt->fetch()) {
        throw new Exception("El usuario en sesión no existe o está inactivo.");
    }
    
    return (int) $idUsuario;
}

// 3. PROCESAMIENTO POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // REGISTRAR NOTA DE ENTRADA 
    if ($type === 'store') {
        CheckPermiso::verificar($db, 'Notas de Entrada', 'crear', '?url=notaentrada&type=list');
        try {
            $idUsuario = obtenerIdUsuarioValido($db);
            
            $detalles = [];
            $productos_ids = $_POST['detalle_producto'] ?? [];
            $cantidades = $_POST['detalle_cantidad'] ?? [];
            $costos = $_POST['detalle_costo'] ?? [];
            
            for ($i = 0; $i < count($productos_ids); $i++) {
                if (!empty($productos_ids[$i]) && !empty($cantidades[$i])) {
                    $detalles[] = [
                        'id_producto' => intval($productos_ids[$i]),
                        'cantidad' => intval($cantidades[$i]),
                        'costo_unitario' => floatval($costos[$i] ?? 0)
                    ];
                }
            }
            
            if (empty($detalles) || count($detalles) === 0) {
                throw new Exception("Debe agregar al menos un producto.");
            }
            
            if (empty($_POST['id_proveedor'])) {
                throw new Exception("Debe seleccionar un proveedor.");
            }
            
            foreach ($detalles as $d) {
                if ($d['cantidad'] <= 0) {
                    throw new Exception("La cantidad debe ser mayor a 0.");
                }
            }
            
            $datos = [
                'fecha_ingreso' => $_POST['fecha_ingreso'],
                'id_proveedor' => intval($_POST['id_proveedor']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];
            
            $resultado = $modelo->guardarNotaEntradaCompleta($datos, $detalles, $idUsuario);
            
            if ($resultado) {
                $_SESSION['mensaje'] = '✅ Nota de Entrada registrada exitosamente.';
                $_SESSION['tipo_mensaje'] = 'success';
                header("Location: ?url=notaentrada&type=list");
                exit();
            }
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
    
    // REGISTRO RÁPIDO DE PRODUCTO
    if ($type === 'store_rapido_producto') {
        CheckPermiso::verificar($db, 'Notas de Entrada', 'crear', '?url=notaentrada&type=list');
        try {
            if (empty($_POST['descripcion']) || empty($_POST['id_categoria']) || empty($_POST['id_proveedor'])) {
                throw new Exception("Por favor complete todos los campos requeridos (*)");
            }
            
            $cantidadOriginal = intval($_POST['cantidad'] ?? 1);
            $costoOriginal = floatval($_POST['costo_unitario'] ?? 0);
            $idProveedorOriginal = intval($_POST['id_proveedor'] ?? 0);

            $datosProducto = [
                'descripcion' => trim($_POST['descripcion']),
                'id_categoria' => intval($_POST['id_categoria']),
                'id_proveedor' => $idProveedorOriginal,
                'cantidad' => 0,
                'costo_unitario' => $costoOriginal,
                'especificaciones' => ''
            ];
            
            $id_producto = $productoModel->registrarProducto($datosProducto);
            
            if ($id_producto) {
                $_SESSION['nuevo_producto_id'] = $id_producto;
                $_SESSION['nuevo_producto_nombre'] = $datosProducto['descripcion'];
                $_SESSION['nuevo_producto_costo'] = $costoOriginal;
                $_SESSION['mensaje_rapido'] = "✅ Producto '{$datosProducto['descripcion']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                
                $return = $_REQUEST['return'] ?? 'notaentrada';
                $params = [
                    'url' => $return,
                    'type' => 'create',
                    'id_producto' => $id_producto,
                    'id_proveedor' => $idProveedorOriginal,
                    'cantidad' => $cantidadOriginal,
                    'costo' => $costoOriginal
                ];
                header("Location: ?" . http_build_query($params));
                exit;
            } else {
                throw new Exception("Error al guardar el producto");
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = '❌ ' . $e->getMessage();
            
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }
            header("Location: ?url=notaentrada&type=create");
            exit;
        }
    }
    
    // REGISTRO RÁPIDO DE PROVEEDOR 
    if ($type === 'store_rapido_proveedor') {
        CheckPermiso::verificar($db, 'Notas de Entrada', 'crear', '?url=notaentrada&type=list');
        try {
            if (empty($_POST['rif']) || empty($_POST['razon_social']) || empty($_POST['numero_contacto']) || empty($_POST['direccion'])) {
                throw new Exception("Por favor complete todos los campos requeridos (*)");
            }
            
            $datosProveedor = [
                'rif' => trim($_POST['rif']),
                'razon_social' => trim($_POST['razon_social']),
                'numero_contacto' => trim($_POST['numero_contacto']),
                'direccion' => trim($_POST['direccion']),
                'correo_electronico' => trim($_POST['correo_electronico'] ?? '')
            ];
            
            $id_proveedor = $proveedorModel->registrarProveedor($datosProveedor);
            
            if ($id_proveedor) {
                $_SESSION['nuevo_proveedor_id'] = (int) $id_proveedor;
                $_SESSION['nuevo_proveedor_nombre'] = $datosProveedor['razon_social'];
                $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datosProveedor['razon_social']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                
                $return = $_REQUEST['return'] ?? 'notaentrada';
                header("Location: ?url=" . urlencode($return) . "&type=create&id_proveedor=" . (int) $id_proveedor);
                exit;
            } else {
                throw new Exception("Error al guardar el proveedor");
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = '❌ ' . $e->getMessage();
            
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }
            header("Location: ?url=notaentrada&type=create");
            exit;
        }
    }
    
    // ANULAR
    if ($type === 'anular') {
        CheckPermiso::verificar($db, 'Notas de Entrada', 'eliminar', '?url=notaentrada&type=list');
        try {
            $id = $_POST['id_nota_entrada'] ?? 0;
            $motivo = trim($_POST['motivo_anulacion'] ?? '');
            $idUsuario = obtenerIdUsuarioValido($db);
            
            if (empty($motivo)) {
                throw new Exception("Debe indicar el motivo de anulación.");
            }
            
            if (!$id || !is_numeric($id) || $id <= 0) {
                throw new Exception("ID de nota inválido.");
            }
            
            $resultado = $modelo->anularNotaEntrada($id, $motivo, $idUsuario);
            
            if ($resultado) {
                $_SESSION['contador_anulaciones_notaentrada'] = (int) ($modelo->getResumen()['total_anuladas'] ?? 0);

                $_SESSION['mensaje'] = '✅ Nota de Entrada anulada exitosamente. El stock ha sido revertido.';
                $_SESSION['tipo_mensaje'] = 'warning';
                header("Location: ?url=notaentrada&type=list");
                exit();
            } else {
                throw new Exception("No se pudo anular la nota.");
            }
            
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
}

// 4. VISTAS
$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'nota_entrada';

// CREAR
if ($type === 'create') {
    try {
        $productos = $modelo->obtenerProductos();
        $proveedores = $modelo->obtenerProveedores();
        $categorias = $productoModel->obtenerCategorias();
    } catch (Exception $e) {
        die("ERROR al obtener datos: " . $e->getMessage());
    }
    
    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "registroNotaEntradaView.php";
    if (!file_exists($viewFile)) {
        die("ERROR: No se encuentra registroNotaEntradaView.php");
    }
    require_once $viewFile;
    exit();
}

// VER DETALLE
if ($type === 'show' && $id) {
    try {
        $nota = $modelo->obtenerNotaEntradaPorId($id);
        if (!$nota) {
            die("ERROR: Nota de entrada no encontrada.");
        }
    } catch (Exception $e) {
        die("ERROR al obtener nota: " . $e->getMessage());
    }
    
    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "detalleNotaEntradaView.php";
    if (!file_exists($viewFile)) {
        die("ERROR: No se encuentra detalleNotaEntradaView.php");
    }
    require_once $viewFile;
    exit();
}

// LISTAR
try {
    $buscar = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));
    $notas_full = !empty($buscar) ? $modelo->buscarNotasEntrada($buscar) : $modelo->obtenerNotasEntrada();
    $resumen = $modelo->getResumen();

    // ✅ PAGINACIÓN COMPLETA
    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    // Total de registros
    $totalRegistros = is_array($notas_full) ? count($notas_full) : 0;
    
    // Total de páginas
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    // Cortar el array para la página actual
    if ($por_pagina > 0) {
        $notas = array_slice($notas_full, $offset, $por_pagina);
    } else {
        $notas = $notas_full;
    }
    
    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
    }
    if (isset($_SESSION['tipo_mensaje'])) {
        $tipo_mensaje = $_SESSION['tipo_mensaje'];
        unset($_SESSION['tipo_mensaje']);
    }
} catch (Exception $e) {
    die("ERROR al obtener notas: " . $e->getMessage());
}

$viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "listNotaEntradaView.php";
if (!file_exists($viewFile)) {
    die("ERROR: No se encuentra listNotaEntradaView.php");
}
require_once $viewFile;
?>