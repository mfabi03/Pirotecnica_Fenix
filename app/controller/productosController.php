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

// NUEVO: Filtro de estado
$estado_producto = strtolower(trim((string) ($_REQUEST['estado_producto'] ?? 'todos')));
if (!in_array($estado_producto, ['todos', 'activos', 'inactivos'], true)) {
    $estado_producto = 'todos';
}

class ProveedorProcessor {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function obtenerProveedores() {
        $sql = "SELECT id_proveedor, razon_social, rif, numero_contacto FROM proveedor WHERE eliminado = 0 AND status = 'activo' ORDER BY razon_social ASC";
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
        if (!isset($datos['costo_unitario']) || $datos['costo_unitario'] < 0) {
            $errores[] = "El costo unitario debe ser mayor o igual a 0.";
        }
        if (empty($datos['id_categoria']) || $datos['id_categoria'] <= 0) {
            $errores[] = "Debe seleccionar una categoría válida.";
        }
        return $errores;
    }
}

// ENDPOINTS AJAX
if ($type === 'historial' && isset($_GET['id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $proveedores = $modelo->obtenerHistorialProveedores((int)$_GET['id']);
    echo json_encode(['success' => true, 'proveedores' => $proveedores]);
    exit;
}

if ($type === 'detalle_ajax' && isset($_GET['id'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $p = $modelo->obtenerProductoPorId((int)$_GET['id']);
        if (!$p) throw new Exception("Producto no encontrado");
        
        $p['estatus_texto'] = ($p['eliminado'] == 1) ? 'Inactivo' : 'Activo';
        $p['estatus_color'] = ($p['eliminado'] == 1) ? '#dc3545' : '#28a745';
        $p['estatus_bg']    = ($p['eliminado'] == 1) ? 'rgba(220,53,69,0.15)' : 'rgba(40,167,69,0.15)';

        echo json_encode(['success' => true, 'producto' => $p]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($type === 'store_rapido_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!PermisoHelper::tienePermiso($db, $_SESSION['id_rol'] ?? 0, 'Productos', 'crear')) {
            throw new Exception("No tienes permiso para registrar productos.");
        }
        if (empty($_POST['descripcion']) || empty($_POST['id_categoria'])) {
            throw new Exception("Descripción y Categoría son obligatorios.");
        }
        $idProveedor = intval($_POST['id_proveedor'] ?? 0);
        if (!$proveedorModel->proveedorActivoExiste($idProveedor)) {
            throw new Exception("El proveedor seleccionado no existe o está inactivo.");
        }
        $datosProducto = [
            'descripcion' => trim($_POST['descripcion']),
            'cantidad' => 0,
            'stock_minimo' => intval($_POST['stock_minimo'] ?? 10),
            'costo_unitario' => floatval($_POST['costo_unitario'] ?? 0),
            'id_categoria' => intval($_POST['id_categoria']),
            'especificaciones' => trim($_POST['especificaciones'] ?? ''),
            'id_proveedor' => $idProveedor
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
            'costo_unitario' => $datosProducto['costo_unitario'],
            'stock' => 0,
            'nombre_categoria' => $nombreCategoria,
            'id_categoria' => $datosProducto['id_categoria']
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// PROCESAR POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
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
            if (!$proveedorModel->proveedorActivoExiste($datos['id_proveedor'])) {
                throw new Exception("El proveedor seleccionado no existe o está inactivo.");
            }
            
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
            $_SESSION['mensaje'] = $error;
            $_SESSION['tipo_mensaje'] = 'danger';
        }
    }
    
    if ($type === 'update') {
        CheckPermiso::verificar($db, 'Productos', 'actualizar', '?url=productos');
        try {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            $datos = [
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'stock_minimo' => intval($_POST['stock_minimo'] ?? 10),
                'especificaciones' => trim($_POST['especificaciones'] ?? ''),
                'id_proveedor' => intval($_POST['id_proveedor'] ?? 0)
            ];
            if (!$proveedorModel->proveedorActivoExiste($datos['id_proveedor'])) {
                throw new Exception("El proveedor seleccionado no existe o está inactivo.");
            }
            $resultado = $modelo->actualizarProducto($id_producto, $datos);
            if (!$resultado) throw new Exception("No se pudo actualizar el producto.");
            
            $_SESSION['mensaje'] = 'Producto actualizado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=productos&type=list");
            exit();
        } catch (Exception $e) {
            $error = $e->getMessage();
            $_SESSION['mensaje'] = $error;
            $_SESSION['tipo_mensaje'] = 'danger';
        }
    }
    
    if ($type === 'delete') {
        CheckPermiso::verificar($db, 'Productos', 'eliminar', '?url=productos');
        try {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            if (!$id_producto || $id_producto <= 0) throw new Exception("ID de producto inválido.");
            
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

    if ($type === 'reactivar') {
        CheckPermiso::verificar($db, 'Productos', 'actualizar', '?url=productos');
        try {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            if (!$id_producto || $id_producto <= 0) throw new Exception("ID de producto inválido.");
            
            $modelo->reactivarProducto($id_producto);
            
            $_SESSION['mensaje'] = 'Producto reactivado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al reactivar: " . $e->getMessage();
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
        $productos_full = $modelo->buscarProductos($buscar);
    } else {
        $productos_full = $modelo->obtenerProductos();
    }

    // FILTRADO POR ESTADO
    if ($estado_producto === 'activos') {
        $productos_full = array_values(array_filter($productos_full, function ($p) {
            return ((int) ($p['eliminado'] ?? 0)) === 0;
        }));
    } elseif ($estado_producto === 'inactivos') {
        $productos_full = array_values(array_filter($productos_full, function ($p) {
            return ((int) ($p['eliminado'] ?? 0)) === 1;
        }));
    }
    
    $resumen = $modelo->getResumen();
    $categorias = $modelo->obtenerCategorias();
    $proveedores = $proveedorProcessor->obtenerProveedores();

    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $por_pagina;
    
    $totalRegistros = count($productos_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    if ($por_pagina > 0) {
        $productos = array_slice($productos_full, $offset, $por_pagina);
    } else {
        $productos = $productos_full;
    }
    
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