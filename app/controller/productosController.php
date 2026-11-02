<?php
namespace App\Pirotecnicafenix\Controller;

use PDO;
use Exception;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\proveedoresModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\CheckPermiso;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutaRaiz = dirname(__DIR__, 2);

function cargarModelo($nombre, $rutaRaiz) {
    $path = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . $nombre . '.php';
    if (!file_exists($path)) {
        die("ERROR: No se encuentra {$nombre}.php en: " . $path);
    }
    require_once $path;
}

cargarModelo('ProductoModel', $rutaRaiz);
cargarModelo('proveedoresModel', $rutaRaiz);

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new ProductoModel($db);
    $proveedorModel = new proveedoresModel($db);
} catch (Exception $e) {
    die("ERROR de conexión: " . $e->getMessage());
}

$type = $_GET['type'] ?? 'list';
$error = null;
$success = null;
$producto = null;
$productos = [];
$categorias = [];
$proveedores = [];

class ProveedorProcessor {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function obtenerProveedores() {
        $sql = "SELECT id_proveedor, razon_social, rif, numero_contacto 
                FROM proveedor 
                WHERE eliminado = 0
                ORDER BY razon_social ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$proveedorProcessor = new ProveedorProcessor($db);

class ProductoValidator {
    public static function validarDatos($datos, $requireCantidad = true) {
        $errores = [];
        if (empty($datos['descripcion'])) $errores[] = "La descripción del producto es obligatoria.";
        if ($requireCantidad) {
            if (!isset($datos['cantidad']) || $datos['cantidad'] === '' || $datos['cantidad'] < 0) {
                $errores[] = "La cantidad debe ser mayor o igual a 0.";
            }
        }
        if (!isset($datos['costo_unitario']) || $datos['costo_unitario'] < 0) {
            $errores[] = "El costo unitario debe ser mayor o igual a 0.";
        }
        if (empty($datos['id_categoria']) || $datos['id_categoria'] <= 0) {
            $errores[] = "Debe seleccionar una categoría válida.";
        }
        if (isset($datos['stock_minimo']) && $datos['stock_minimo'] < 1) {
            $errores[] = "El stock mínimo debe ser mayor o igual a 1.";
        }
        return $errores;
    }
}

// ENDPOINT AJAX: HISTORIAL DE PROVEEDORES
if ($type === 'historial' && isset($_GET['id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $proveedores = $modelo->obtenerHistorialProveedores((int)$_GET['id']);
    echo json_encode(['success' => true, 'proveedores' => $proveedores]);
    exit;
}

// ENDPOINT AJAX: DETALLE DE PRODUCTO (AQUÍ AGREGAMOS EL ESTATUS)
if ($type === 'detalle_ajax' && isset($_GET['id'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $p = $modelo->obtenerProductoPorId((int)$_GET['id']);
        if (!$p) throw new Exception("Producto no encontrado");
        
        // Calculamos el estatus directamente en PHP
        $p['estatus_texto'] = ($p['eliminado'] == 1) ? 'Inactivo' : 'Activo';
        $p['estatus_color'] = ($p['eliminado'] == 1) ? '#dc3545' : '#28a745';
        $p['estatus_bg']    = ($p['eliminado'] == 1) ? 'rgba(220,53,69,0.15)' : 'rgba(40,167,69,0.15)';

        echo json_encode(['success' => true, 'producto' => $p]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ENDPOINT AJAX: REGISTRO RÁPIDO DE PRODUCTO
if ($type === 'store_rapido_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        if (!PermisoHelper::tienePermiso($db, $_SESSION['id_rol'] ?? 0, 'Productos', 'crear')) {
            throw new Exception("No tienes permiso para registrar productos.");
        }
        
        if (empty($_POST['descripcion']) || empty($_POST['id_categoria']) || empty($_POST['id_proveedor'])) {
            throw new Exception("Descripción, Categoría y Proveedor son obligatorios.");
        }
        
        $costoOriginal = floatval($_POST['costo_unitario'] ?? 0);
        $id_proveedor = intval($_POST['id_proveedor'] ?? 0);
        
        $datosProducto = [
            'descripcion' => trim($_POST['descripcion']),
            'cantidad' => 0,
            'stock_minimo' => intval($_POST['stock_minimo'] ?? 10),
            'costo_unitario' => $costoOriginal,
            'id_categoria' => intval($_POST['id_categoria']),
            'especificaciones' => trim($_POST['especificaciones'] ?? ''),
            'id_proveedor' => $id_proveedor
        ];
        
        $resultado = $modelo->registrarProducto($datosProducto);
        if ($resultado === false || intval($resultado) <= 0) {
            throw new Exception('No se pudo registrar el producto.');
        }
        
        $id_producto = intval($resultado);
        
        $sqlCat = "SELECT nombre_categoria FROM categoria WHERE id_categoria = ?";
        $stmtCat = $db->prepare($sqlCat);
        $stmtCat->execute([$datosProducto['id_categoria']]);
        $cat = $stmtCat->fetch(PDO::FETCH_ASSOC);
        $nombreCategoria = $cat['nombre_categoria'] ?? 'Sin categoría';
        
        echo json_encode([
            'success' => true,
            'id_producto' => $id_producto,
            'descripcion' => $datosProducto['descripcion'],
            'costo_unitario' => $costoOriginal,
            'stock' => 0,
            'nombre_categoria' => $nombreCategoria,
            'id_categoria' => $datosProducto['id_categoria']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// PROCESAR POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // REGISTRAR PRODUCTO
    if ($type === 'store') {
        CheckPermiso::verificar($db, 'Productos', 'crear', '?url=productos');
        try {
            $datos = [
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'cantidad' => intval($_POST['cantidad'] ?? 0),
                'stock_minimo' => intval($_POST['stock_minimo'] ?? 10),
                'costo_unitario' => floatval($_POST['costo_unitario'] ?? 0),
                'id_categoria' => intval($_POST['id_categoria'] ?? 0),
                'especificaciones' => trim($_POST['especificaciones'] ?? ''),
                'id_proveedor' => intval($_POST['id_proveedor'] ?? 0)
            ];
            
            $errores = ProductoValidator::validarDatos($datos);
            if (!empty($errores)) throw new Exception(implode(' ', $errores));
            
            $resultado = $modelo->registrarProducto($datos);
            if ($resultado === false || intval($resultado) <= 0) {
                throw new Exception("No se pudo registrar el producto.");
            }
            
            $_SESSION['mensaje'] = 'Producto registrado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=productos&type=list");
            exit();
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // ACTUALIZAR PRODUCTO
    if ($type === 'update') {
        CheckPermiso::verificar($db, 'Productos', 'actualizar', '?url=productos');
        try {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            
            $datos = [
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'stock_minimo' => intval($_POST['stock_minimo'] ?? 10),
                'especificaciones' => trim($_POST['especificaciones'] ?? '')
            ];

            $resultado = $modelo->actualizarProducto($id_producto, $datos);
            if (!$resultado) throw new Exception("No se pudo actualizar el producto.");
            
            $_SESSION['mensaje'] = 'Producto actualizado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=productos&type=list");
            exit();
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // ELIMINAR PRODUCTO
    if ($type === 'delete') {
        CheckPermiso::verificar($db, 'Productos', 'eliminar', '?url=productos');
        try {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            
            if (!$id_producto || $id_producto <= 0) {
                throw new Exception("ID de producto inválido.");
            }
            
            $resultado = $modelo->eliminarProducto($id_producto);
            if (!$resultado) throw new Exception("No se pudo eliminar el producto.");
            
            $_SESSION['mensaje'] = 'Producto eliminado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            
        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }
        header("Location: ?url=productos&type=list");
        exit();
    }
}

// VISTA DE LISTA
try {
    $buscar = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));
    
    if (!empty($buscar)) {
        $productos = $modelo->buscarProductos($buscar);
    } else {
        $productos = $modelo->obtenerProductos();
    }
    
    $resumen = $modelo->getResumen();
    $categorias = $modelo->obtenerCategorias();
    $proveedores = $proveedorProcessor->obtenerProveedores();

    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $por_pagina;
    
    $totalRegistros = count($productos);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    if ($por_pagina > 0) {
        $productos = array_slice($productos, $offset, $por_pagina);
    }
    
    // AQUÍ ESTÁ LA MAGIA PARA QUE SALGA EL MENSAJE
    if (isset($_SESSION['mensaje'])) {
        $mensaje = $_SESSION['mensaje'];
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
    }
    
} catch (Exception $e) {
    die("ERROR al listar productos: " . $e->getMessage());
}

require_once $rutaRaiz . '/app/view/productos/listProductsView.php';
?>
