<?php
// app/Controller/productosController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\ProveedoresModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\UploadHelper;
use App\Pirotecnicafenix\Traits\ValidadorTrait;
use Exception;

if (session_status() === PHP_SESSION_NONE) session_start();
$rutaRaiz = dirname(__DIR__, 2);

$modelo = new ProductoModel();
$proveedorModel = new ProveedoresModel();
$val = new class { use ValidadorTrait; };

$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$permisoHelper = new PermisoHelper();
$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Productos', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Productos', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Productos', 'eliminar');

$type = $_GET['type'] ?? 'list';
$producto = null; $productos = []; $categorias = []; $proveedores = [];

$estado_producto = strtolower($val->limpiar($_REQUEST['estado_producto'] ?? 'activos'));
if (!in_array($estado_producto, ['todos', 'activos', 'inactivos'], true)) $estado_producto = 'activos';

// check_duplicado
if ($type === 'check_duplicado') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$permisoHelper->tienePermiso($id_rol_actual, 'Productos', 'leer')) {
            http_response_code(403); throw new Exception('No tienes permiso para consultar productos.');
        }
        $campo = strtolower($val->limpiar($_GET['campo'] ?? ''));
        $descripcion = trim($val->limpiar($_GET['valor'] ?? ''));
        $excluirId = isset($_GET['excluir_id']) && is_numeric($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : null;
        if ($campo !== 'descripcion' || $descripcion === '') throw new Exception('Datos de verificación inválidos.');
        echo json_encode(['existe' => $modelo->existeProductoDescripcion($descripcion, $excluirId), 'campo' => $campo, 'valor' => $descripcion], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        if (http_response_code() < 400) http_response_code(500);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// historial
if ($type === 'historial' && isset($_GET['id'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $id = $val->validarId($_GET['id'], 'ID de producto');
        echo json_encode(['success' => true, 'proveedores' => $modelo->obtenerHistorialProveedores($id)], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// detalle_ajax
if ($type === 'detalle_ajax' && isset($_GET['id'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $id = $val->validarId($_GET['id'], 'ID de producto');
        $p = $modelo->obtenerProductoPorId($id);
        if (!$p) throw new Exception("Producto no encontrado");
        $p['estatus_texto'] = ($p['eliminado'] == 1) ? 'Inactivo' : 'Activo';
        $p['estatus_color'] = ($p['eliminado'] == 1) ? '#dc3545' : '#28a745';
        $p['estatus_bg']    = ($p['eliminado'] == 1) ? 'rgba(220,53,69,0.15)' : 'rgba(40,167,69,0.15)';
        echo json_encode(['success' => true, 'producto' => $p], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// store_rapido_ajax
if ($type === 'store_rapido_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$puede_crear) throw new Exception("No tienes permiso para registrar productos.");

        $datosProducto = [
            'descripcion' => $val->requerido($_POST['descripcion'] ?? '', 'descripción'),
            'cantidad' => 0,
            'stock_minimo' => (int)($_POST['stock_minimo'] ?? 10),
            'costo_unitario' => $val->numeroPositivo($_POST['costo_unitario'] ?? 0, 'costo unitario', 0),
            'id_categoria' => $val->validarId($_POST['id_categoria'] ?? null, 'categoría'),
            'especificaciones' => $val->limpiar($_POST['especificaciones'] ?? ''),
            'id_proveedor' => (int)($_POST['id_proveedor'] ?? 0)
        ];
        if (!$proveedorModel->proveedorActivoExiste($datosProducto['id_proveedor'])) throw new Exception("El proveedor seleccionado no existe o está inactivo.");

        $resultado = $modelo->registrarProducto($datosProducto);
        if ($resultado === false || intval($resultado) <= 0) throw new Exception('No se pudo registrar el producto.');

        echo json_encode([
            'success' => true, 'id_producto' => (int)$resultado,
            'descripcion' => $datosProducto['descripcion'],
            'costo_unitario' => $datosProducto['costo_unitario'],
            'stock' => 0,
            'nombre_categoria' => $modelo->obtenerNombreCategoria($datosProducto['id_categoria']),
            'id_categoria' => $datosProducto['id_categoria']
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($type === 'store') {
        if (!$puede_crear) { $_SESSION['mensaje'] = 'No tienes permiso para registrar productos.'; $_SESSION['tipo_mensaje'] = 'danger'; header("Location: ?url=productos&type=list"); exit(); }
        $rutaImagen = null;
        try {
            $datos = [
                'descripcion' => $val->requerido($_POST['descripcion'] ?? '', 'descripción'),
                'cantidad' => (int)($_POST['cantidad'] ?? 0),
                'stock_minimo' => (int)($_POST['stock_minimo'] ?? 10),
                'costo_unitario' => $val->numeroPositivo($_POST['costo_unitario'] ?? 0, 'costo unitario', 0),
                'id_categoria' => $val->validarId($_POST['id_categoria'] ?? null, 'categoría'),
                'especificaciones' => $val->limpiar($_POST['especificaciones'] ?? ''),
                'id_proveedor' => (int)($_POST['id_proveedor'] ?? 0)
            ];
            if (!$proveedorModel->proveedorActivoExiste($datos['id_proveedor'])) throw new Exception("El proveedor seleccionado no existe o está inactivo.");
            $rutaImagen = UploadHelper::guardarImagenProducto($_FILES['imagen'] ?? null);
            $datos['imagen'] = $rutaImagen;
            $resultado = $modelo->registrarProducto($datos);
            if ($resultado === false || intval($resultado) <= 0) throw new Exception("No se pudo registrar el producto.");
            $rutaImagen = null;
            $_SESSION['mensaje'] = 'Producto registrado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=productos&type=list"); exit();
        } catch (Exception $e) {
            if ($rutaImagen !== null) { try { UploadHelper::eliminarImagenProducto($rutaImagen); } catch (Exception $e2) { error_log($e2->getMessage()); } }
            $_SESSION['mensaje'] = $e->getMessage(); $_SESSION['tipo_mensaje'] = 'danger';
            header("Location: ?url=productos&type=list"); exit();
        }
    }

    if ($type === 'update') {
        if (!$puede_editar) { $_SESSION['mensaje'] = 'No tienes permiso para actualizar productos.'; $_SESSION['tipo_mensaje'] = 'danger'; header("Location: ?url=productos&type=list"); exit(); }
        try {
            $id_producto = $val->validarId($_POST['id_producto'] ?? null, 'ID de producto');
            $datos = [
                'descripcion' => $val->requerido($_POST['descripcion'] ?? '', 'descripción'),
                'stock_minimo' => (int)($_POST['stock_minimo'] ?? 10),
                'especificaciones' => $val->limpiar($_POST['especificaciones'] ?? '')
            ];
            if (!$modelo->actualizarProducto($id_producto, $datos)) throw new Exception("No se pudo actualizar el producto.");
            $_SESSION['mensaje'] = 'Producto actualizado exitosamente.'; $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=productos&type=list"); exit();
        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage(); $_SESSION['tipo_mensaje'] = 'danger';
            header("Location: ?url=productos&type=list"); exit();
        }
    }

    if ($type === 'delete') {
        if (!$puede_eliminar) { $_SESSION['mensaje'] = 'No tienes permiso para eliminar productos.'; $_SESSION['tipo_mensaje'] = 'danger'; header("Location: ?url=productos&type=list"); exit(); }
        try {
            $id_producto = $val->validarId($_POST['id_producto'] ?? null, 'ID de producto');
            if (!$modelo->eliminarProducto($id_producto)) throw new Exception("No se pudo eliminar el producto.");
            $_SESSION['mensaje'] = 'Producto eliminado exitosamente.'; $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) { $_SESSION['mensaje'] = $e->getMessage(); $_SESSION['tipo_mensaje'] = 'danger'; }
        header("Location: ?url=productos&type=list&estado_producto=activos"); exit();
    }

    if ($type === 'reactivar') {
        if (!$puede_editar) { $_SESSION['mensaje'] = 'No tienes permiso para reactivar productos.'; $_SESSION['tipo_mensaje'] = 'danger'; header("Location: ?url=productos&type=list&estado_producto=inactivos"); exit(); }
        try {
            $id_producto = $val->validarId($_POST['id_producto'] ?? null, 'ID de producto');
            $modelo->reactivarProducto($id_producto);
            $_SESSION['mensaje'] = 'Producto reactivado exitosamente.'; $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) { $_SESSION['mensaje'] = "Error al reactivar: " . $e->getMessage(); $_SESSION['tipo_mensaje'] = 'danger'; }
        header("Location: ?url=productos&type=list&estado_producto=inactivos"); exit();
    }
}

// LISTA
try {
    $buscar = $val->limpiar($_GET['busqueda'] ?? $_GET['buscar'] ?? '');
    $productos_full = !empty($buscar) ? $modelo->buscarProductos($buscar) : $modelo->obtenerProductos();
    if ($estado_producto === 'activos') $productos_full = array_values(array_filter($productos_full, fn($p) => ((int)($p['eliminado'] ?? 0)) === 0));
    elseif ($estado_producto === 'inactivos') $productos_full = array_values(array_filter($productos_full, fn($p) => ((int)($p['eliminado'] ?? 0)) === 1));

    $resumen = $modelo->getResumen();
    $categorias = $modelo->obtenerCategorias();
    $proveedores = $proveedorModel->obtenerProveedoresParaSelect();

    $por_pagina = (int)($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $por_pagina;
    $totalRegistros = count($productos_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    $productos = $por_pagina > 0 ? array_slice($productos_full, $offset, $por_pagina) : $productos_full;

    if (isset($_SESSION['mensaje'])) {
        $mensaje = $_SESSION['mensaje'];
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
    }
} catch (Exception $e) {
    die("ERROR al listar productos: " . $e->getMessage());
}

require_once $rutaRaiz . '/app/view/productos/listProductsView.php';