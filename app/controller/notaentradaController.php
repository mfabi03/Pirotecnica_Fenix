<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\NotaentradaModel;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\ProveedoresModel;
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

// 2. FUNCIÓN PARA OBTENER ID USUARIO VÁLIDO

function obtenerIdUsuarioValido($db) {
    $idUsuario = $_SESSION['usuario_id'] ?? null;
    
    if ($idUsuario) {
        $stmt = $db->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        if ($stmt->fetch()) {
            return $idUsuario;
        }
    }
    
    $stmt = $db->query("SELECT id_usuario FROM usuario LIMIT 1");
    $usuario = $stmt->fetch();
    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        return $usuario['id_usuario'];
    }
    
    throw new Exception("No hay usuarios disponibles en la base de datos.");
}

// 3. PROCESAMIENTO POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // REGISTRAR NOTA DE ENTRADA 

    if ($type === 'store') {
        try {
            $idUsuario = obtenerIdUsuarioValido($db);
            
            // RECIBIR DETALLES COMO ARRAYS 
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
        try {
            // Validar campos requeridos
            if (empty($_POST['descripcion']) || empty($_POST['id_categoria']) || empty($_POST['id_proveedor'])) {
                throw new Exception("Por favor complete todos los campos requeridos (*)");
            }
            
            // Preparar datos del producto
            $datosProducto = [
                'descripcion' => trim($_POST['descripcion']),
                'id_categoria' => intval($_POST['id_categoria']),
                'id_proveedor' => intval($_POST['id_proveedor']),
                'cantidad' => intval($_POST['cantidad'] ?? 0),
                'costo_unitario' => floatval($_POST['costo_unitario'] ?? 0),
                'especificaciones' => ''
            ];
            
            // Guardar producto usando el modelo
            $id_producto = $productoModel->registrarProducto($datosProducto);
            
            if ($id_producto) {

                // GUARDAR EN SESIÓN PARA EL RETORNO
                $_SESSION['nuevo_producto_id'] = $id_producto;
                $_SESSION['nuevo_producto_nombre'] = $datosProducto['descripcion'];
                $_SESSION['nuevo_producto_costo'] = $datosProducto['costo_unitario'];
                $_SESSION['mensaje_rapido'] = "✅ Producto '{$datosProducto['descripcion']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                
                // REDIRIGIR DE VUELTA (si viene de registro rápido)
                if (isset($_GET['return'])) {
                    header("Location: ?url=" . $_GET['return'] . "&type=create");
                    exit;
                }
                
                header("Location: ?url=notaentrada&type=create");
                exit;
            } else {
                throw new Exception("Error al guardar el producto");
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = '❌ ' . $e->getMessage();
            
            if (isset($_GET['return'])) {
                header("Location: ?url=" . $_GET['return'] . "&type=create");
                exit;
            }
            header("Location: ?url=notaentrada&type=create");
            exit;
        }
    }
    
    // REGISTRO RÁPIDO DE PROVEEDOR 

    if ($type === 'store_rapido_proveedor') {
        try {
            // Validar campos requeridos
            if (empty($_POST['rif']) || empty($_POST['razon_social']) || empty($_POST['numero_contacto']) || empty($_POST['direccion'])) {
                throw new Exception("Por favor complete todos los campos requeridos (*)");
            }
            
            // Preparar datos del proveedor
            $datosProveedor = [
                'rif' => trim($_POST['rif']),
                'razon_social' => trim($_POST['razon_social']),
                'numero_contacto' => trim($_POST['numero_contacto']),
                'direccion' => trim($_POST['direccion']),
                'correo_electronico' => trim($_POST['correo_electronico'] ?? '')
            ];
            
            // Guardar proveedor usando el modelo
            $id_proveedor = $proveedorModel->registrarProveedor($datosProveedor);
            
            if ($id_proveedor) {
                // GUARDAR EN SESIÓN PARA EL RETORNO
                $_SESSION['nuevo_proveedor_id'] = $id_proveedor;
                $_SESSION['nuevo_proveedor_nombre'] = $datosProveedor['razon_social'];
                $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datosProveedor['razon_social']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                
                // REDIRIGIR DE VUELTA (si viene de registro rápido)
                if (isset($_GET['return'])) {
                    header("Location: ?url=" . $_GET['return'] . "&type=create");
                    exit;
                }
                
                header("Location: ?url=notaentrada&type=create");
                exit;
            } else {
                throw new Exception("Error al guardar el proveedor");
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = '❌ ' . $e->getMessage();
            
            if (isset($_GET['return'])) {
                header("Location: ?url=" . $_GET['return'] . "&type=create");
                exit;
            }
            header("Location: ?url=notaentrada&type=create");
            exit;
        }
    }
    
    // ANULAR

    if ($type === 'anular') {
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
                if (!isset($_SESSION['contador_anulaciones_notaentrada'])) {
                    $_SESSION['contador_anulaciones_notaentrada'] = 0;
                }
                $_SESSION['contador_anulaciones_notaentrada']++;
                
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
    $notas = $modelo->obtenerNotasEntrada();
    $resumen = $modelo->getResumen();
    
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