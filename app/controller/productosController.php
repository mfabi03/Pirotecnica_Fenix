<?php
namespace App\Pirotecnicafenix\Controller;

use PDO;
use Exception;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\proveedoresModel;

// CONFIGURACIÓN INICIAL

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión para mensajes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutaRaiz = dirname(__DIR__, 2);

// CARGA DE MODELOS

function cargarModelo($nombre, $rutaRaiz) {
    $path = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . $nombre . '.php';
    if (!file_exists($path)) {
        die("ERROR: No se encuentra {$nombre}.php en: " . $path);
    }
    require_once $path;
}

cargarModelo('ProductoModel', $rutaRaiz);
cargarModelo('proveedoresModel', $rutaRaiz);

// CONEXIÓN A BASE DE DATOS

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new ProductoModel($db);
    $proveedorModel = new proveedoresModel($db);
} catch (Exception $e) {
    die("ERROR de conexión: " . $e->getMessage());
}

// VARIABLES GLOBALES

$type = $_GET['type'] ?? 'list';
$error = null;
$success = null;
$producto = null;
$productos = [];
$categorias = [];
$proveedores = [];

// GESTOR DE ARCHIVO JSON (MANTENIDO PARA ESPECIFICACIONES)

function getProductosJsonPath() {
    return __DIR__ . '/../../public/uploads/products_imagenes.json';
}

function cargarProductosJson() {
    $jsonPath = getProductosJsonPath();
    if (!file_exists($jsonPath)) {
        file_put_contents($jsonPath, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return [];
    }
    $data = json_decode(file_get_contents($jsonPath), true);
    return is_array($data) ? $data : [];
}

function guardarProductoJson($id, array $datos) {
    $jsonPath = getProductosJsonPath();
    $data = cargarProductosJson();
    $data[$id] = $datos;
    file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function eliminarProductoJson($id) {
    $jsonPath = getProductosJsonPath();
    $data = cargarProductosJson();
    if (isset($data[$id])) {
        unset($data[$id]);
        file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}

// PROCESADOR DE PROVEEDORES

class ProveedorProcessor {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function obtenerNombre($id_proveedor) {
        if (empty($id_proveedor) || $id_proveedor <= 0) {
            return '';
        }
        
        $sql = "SELECT razon_social FROM proveedor WHERE id_proveedor = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_proveedor]);
        $prov = $stmt->fetch(PDO::FETCH_ASSOC);
        return $prov['razon_social'] ?? '';
    }
    
    public function obtenerProveedores() {
        $sql = "SELECT id_proveedor, razon_social, rif, numero_contacto 
                FROM proveedor 
                ORDER BY razon_social ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$proveedorProcessor = new ProveedorProcessor($db);

// VALIDADOR DE PRODUCTOS

class ProductoValidator {
    public static function validarDatos($datos, $requireCantidad = true) {
        $errores = [];

        if (empty($datos['descripcion'])) {
            $errores[] = "La descripción del producto es obligatoria.";
        }

        if ($requireCantidad) {
            if (!isset($datos['cantidad']) || $datos['cantidad'] === '' || $datos['cantidad'] < 0) {
                $errores[] = "La cantidad debe ser mayor o igual a 0.";
            }
        }

        if (empty($datos['costo_unitario']) || $datos['costo_unitario'] <= 0) {
            $errores[] = "El costo unitario debe ser mayor a 0.";
        }

        if (empty($datos['id_categoria']) || $datos['id_categoria'] <= 0) {
            $errores[] = "Debe seleccionar una categoría válida.";
        }

        return $errores;
    }
}

// PROCESAR POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // REGISTRAR PRODUCTO

    if ($type === 'store') {
        try {
            $datos = [
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'cantidad' => intval($_POST['cantidad'] ?? 0),
                'costo_unitario' => floatval($_POST['costo_unitario'] ?? 0),
                'id_categoria' => intval($_POST['id_categoria'] ?? 0)
            ];
            
            $errores = ProductoValidator::validarDatos($datos);
            if (!empty($errores)) {
                throw new Exception(implode(' ', $errores));
            }
            
            $resultado = $modelo->registrarProducto($datos);
            if ($resultado === false || intval($resultado) <= 0) {
                throw new Exception("No se pudo registrar el producto.");
            }
            
            $id_producto = intval($resultado);
            $id_proveedor = intval($_POST['id_proveedor'] ?? 0);
            $nombreProveedor = $proveedorProcessor->obtenerNombre($id_proveedor);
            
            guardarProductoJson($id_producto, [
                'especificaciones' => trim($_POST['especificaciones'] ?? ''),
                'proveedor' => $nombreProveedor,
                'id_proveedor' => $id_proveedor
            ]);
            
            // REDIRIGIR DE VUELTA (si viene de registro rápido)
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                $_SESSION['nuevo_producto_id'] = $id_producto;
                $_SESSION['nuevo_producto_nombre'] = $datos['descripcion'];
                $_SESSION['mensaje_rapido'] = "✅ Producto '{$datos['descripcion']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }
            
            $_SESSION['mensaje'] = '✅ Producto registrado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=productos&type=list");
            exit();
            
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
   
    // REGISTRO RÁPIDO PRODUCTO 

    if ($type === 'store_rapido') {
        try {
            // Validar campos requeridos
            if (empty($_POST['descripcion']) || empty($_POST['id_categoria'])) {
                throw new Exception('Por favor complete todos los campos requeridos');
            }
            
            $datosProducto = [
                'descripcion' => trim($_POST['descripcion']),
                'cantidad' => intval($_POST['cantidad'] ?? 0),
                'costo_unitario' => floatval($_POST['costo_unitario'] ?? 0.0),
                'id_categoria' => intval($_POST['id_categoria'] ?? 0)
            ];
            
            $errores = ProductoValidator::validarDatos($datosProducto);
            if (!empty($errores)) {
                throw new Exception(implode(' ', $errores));
            }
            
            $resultado = $modelo->registrarProducto($datosProducto);
            if ($resultado === false || intval($resultado) <= 0) {
                throw new Exception('No se pudo registrar el producto.');
            }
            
            $id_producto = intval($resultado);
            
            // GUARDAR EN SESIÓN PARA EL RETORNO
            $_SESSION['nuevo_producto_id'] = $id_producto;
            $_SESSION['nuevo_producto_nombre'] = $datosProducto['descripcion'];
            $_SESSION['mensaje_rapido'] = "✅ Producto '{$datosProducto['descripcion']}' registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';
            
            // REDIRIGIR DE VUELTA (si viene de registro rápido)
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }

            header("Location: ?url=productos&type=list");
            exit;
            
        } catch (Exception $e) {
            $_SESSION['mensaje_rapido'] = "❌ " . $e->getMessage();
            $_SESSION['tipo_rapido'] = 'danger';

            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }
            header("Location: ?url=productos&type=list");
            exit;
        }
    }
    
    // ACTUALIZAR PRODUCTO

    if ($type === 'update') {
        try {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            
            // No permitir modificar la cantidad (stock) desde el formulario de edición.
            $datos = [
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'costo_unitario' => floatval($_POST['costo_unitario'] ?? 0),
                'id_categoria' => intval($_POST['id_categoria'] ?? 0)
            ];

            // Validar sin requerir el campo cantidad (se actualiza solo por entradas/salidas)
            $errores = ProductoValidator::validarDatos($datos, false);
            if (!empty($errores)) {
                throw new Exception(implode(' ', $errores));
            }
            
            $resultado = $modelo->actualizarProducto($id_producto, $datos);
            if (!$resultado) {
                throw new Exception("No se pudo actualizar el producto.");
            }
            
            $id_proveedor = intval($_POST['id_proveedor'] ?? 0);
            $nombreProveedor = $proveedorProcessor->obtenerNombre($id_proveedor);
            
            guardarProductoJson($id_producto, [
                'especificaciones' => trim($_POST['especificaciones'] ?? ''),
                'proveedor' => $nombreProveedor,
                'id_proveedor' => $id_proveedor
            ]);
            
            $_SESSION['mensaje'] = '✅ Producto actualizado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=productos&type=list");
            exit();
            
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
    
    // ELIMINAR PRODUCTO
   
    if ($type === 'delete') {
        try {
            $id_producto = intval($_POST['id_producto'] ?? 0);
            
            if (!$id_producto || $id_producto <= 0) {
                throw new Exception("ID de producto inválido.");
            }
            
            $resultado = $modelo->eliminarProducto($id_producto);
            if (!$resultado) {
                throw new Exception("No se pudo eliminar el producto.");
            }

            eliminarProductoJson($id_producto);
            
            $_SESSION['mensaje'] = '✅ Producto eliminado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=productos&type=list");
            exit();
            
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
}

// FUNCIONES AUXILIARES PARA VISTAS

function cargarDatosProducto($id_producto, $modelo, $proveedorProcessor) {
    $producto = $modelo->obtenerProductoPorId($id_producto);
    if (!$producto) {
        die("ERROR: Producto no encontrado.");
    }
    
    $jsonData = cargarProductosJson();
    if (isset($jsonData[$id_producto])) {
        $producto['especificaciones'] = $jsonData[$id_producto]['especificaciones'] ?? '';
        $producto['proveedor'] = $jsonData[$id_producto]['proveedor'] ?? '';
        $producto['id_proveedor'] = $jsonData[$id_producto]['id_proveedor'] ?? 0;
    } else {
        $producto['especificaciones'] = '';
        $producto['proveedor'] = '';
        $producto['id_proveedor'] = 0;
    }
    
    return $producto;
}

// RUTEO DE VISTAS

if ($type === 'create') {
    try {
        $categorias = $modelo->obtenerCategorias();
        $proveedores = $proveedorProcessor->obtenerProveedores();
    } catch (Exception $e) {
        die("ERROR al obtener datos: " . $e->getMessage());
    }
    require_once $rutaRaiz . '/app/view/productos/registrarProductoView.php';
    exit();
}

if ($type === 'show') {
    $id_producto = intval($_GET['id'] ?? 0);
    try {
        $producto = cargarDatosProducto($id_producto, $modelo, $proveedorProcessor);
    } catch (Exception $e) {
        die("ERROR al obtener producto: " . $e->getMessage());
    }
    require_once $rutaRaiz . '/app/view/productos/detalleProductoView.php';
    exit();
}

if ($type === 'edit') {
    $id_producto = intval($_GET['id'] ?? 0);
    try {
        $producto = cargarDatosProducto($id_producto, $modelo, $proveedorProcessor);
        $categorias = $modelo->obtenerCategorias();
        $proveedores = $proveedorProcessor->obtenerProveedores();
    } catch (Exception $e) {
        die("ERROR al obtener producto: " . $e->getMessage());
    }
    require_once $rutaRaiz . '/app/view/productos/editarProductoView.php';
    exit();
}

// VISTA DE LISTA DE PRODUCTOS

try {
    $buscar = trim($_GET['buscar'] ?? '');
    $dataJson = cargarProductosJson();
    
    $sqlBase = "SELECT 
                    p.id_producto,
                    p.descripcion,
                    p.cantidad AS stock,
                    p.costo_unitario,
                    p.id_categoria,
                    c.nombre_categoria
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria";
    
    $sql = $sqlBase;
    $params = [];
    
    if (!empty($buscar)) {
        $sql .= " WHERE p.descripcion LIKE :termino";
        $params['termino'] = "%$buscar%";
    }
    
    $sql .= " ORDER BY p.id_producto DESC";

    // Paginación: calcular total y aplicar LIMIT/OFFSET
    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset = ($pagina - 1) * $por_pagina;

    // Contar total con la misma condición
    $sqlCount = preg_replace('/SELECT\s+[\s\S]*?FROM\s+producto\s+p/i', 'SELECT COUNT(*) AS cnt FROM producto p', $sqlBase);
    if (!empty($buscar)) {
        $sqlCount .= " WHERE p.descripcion LIKE :termino";
    }
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute($params);
    $totalProductos = (int) ($stmtCount->fetchColumn() ?? 0);

    if ($por_pagina > 0) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($productos as &$producto) {
        $id = $producto['id_producto'];
        if (isset($dataJson[$id])) {
            $producto['especificaciones'] = $dataJson[$id]['especificaciones'] ?? '';
        } else {
            $producto['especificaciones'] = '';
        }
    }
    unset($producto);

    $sqlResumen = "SELECT 
                        COUNT(*) AS total_productos,
                        SUM(cantidad) AS total_stock,
                        COUNT(DISTINCT id_categoria) AS total_categorias
                    FROM producto";
    $stmtResumen = $db->prepare($sqlResumen);
    $stmtResumen->execute();
    $resumen = $stmtResumen->fetch(PDO::FETCH_ASSOC);
    
    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
    }
    
} catch (Exception $e) {
    die("ERROR al listar productos: " . $e->getMessage());
}

require_once $rutaRaiz . '/app/view/productos/listProductsView.php';
?>