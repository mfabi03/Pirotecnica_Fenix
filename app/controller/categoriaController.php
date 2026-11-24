<?php
// app/Controller/categoriaController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\CategoriaModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Traits\ValidadorTrait;
use App\Pirotecnicafenix\Middleware\AuthMiddleware;
use Exception;

$modelo = new CategoriaModel();
$val = new class { use ValidadorTrait; };
$permisoHelper = new PermisoHelper();
$id_rol_actual = $_SESSION['id_rol'] ?? 0;

$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Categorias', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Categorias', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Categorias', 'eliminar');

$action   = $_GET['action'] ?? $_POST['action'] ?? 'lista';
$id       = $_GET['id'] ?? $_POST['id_categoria'] ?? null;
$busqueda = $val->limpiar($_GET['busqueda'] ?? '');

$estado_categoria = strtolower($val->limpiar($_REQUEST['estado_categoria'] ?? 'activos'));
if (!in_array($estado_categoria, ['todos', 'activos', 'inactivos'], true)) {
    $estado_categoria = 'activos';
}

// ==========================================
// AJAX
// ==========================================
if ($action === 'check_duplicado') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$permisoHelper->tienePermiso($id_rol_actual, 'Categorias', 'leer')) {
            http_response_code(403);
            throw new Exception('No tienes permiso para consultar categorías.');
        }

        $campo = strtolower($val->limpiar($_GET['campo'] ?? ''));
        $nombre = trim($val->limpiar($_GET['valor'] ?? ''));
        $excluirId = isset($_GET['excluir_id']) && is_numeric($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : null;

        if ($campo !== 'nombre_categoria' || $nombre === '') throw new Exception('Datos de verificación inválidos.');

        echo json_encode([
            'existe' => $modelo->existeCategoria($nombre, $excluirId),
            'campo' => $campo,
            'valor' => $nombre
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        if (http_response_code() < 400) http_response_code(500);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'listar_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        echo json_encode(['success' => true, 'categorias' => $modelo->obtenerCategorias()], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'store_rapido_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$puede_crear) throw new Exception("No tienes permiso para registrar categorías.");

        $datos = [
            'nombre_categoria' => $val->requerido($_POST['nombre_categoria'] ?? '', 'nombre de la categoría'),
            'descripcion'      => $val->limpiar($_POST['descripcion'] ?? '')
        ];

        $modelo->setNombreCategoria($datos['nombre_categoria']);
        $id_categoria = $modelo->registrarCategoria($datos); // Corregido: pasar $datos

        echo json_encode([
            'success' => true,
            'id_categoria' => $id_categoria,
            'nombre_categoria' => $datos['nombre_categoria']
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ==========================================
// POST
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? $action;

    if ($accion === 'guardar' || $accion === 'create') {
        AuthMiddleware::requirePermiso('Categorias', 'crear');
        try {
            $datos = [
                'nombre_categoria' => $val->requerido($_POST['nombre_categoria'] ?? '', 'nombre de la categoría'),
                'descripcion'      => $val->limpiar($_POST['descripcion'] ?? '')
            ];
            $modelo->setNombreCategoria($datos['nombre_categoria']); // Necesario para el modelo
            $modelo->registrarCategoria($datos);
            $_SESSION['mensaje'] = "Categoría registrada exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }

    if ($accion === 'actualizar' || $accion === 'edit' || $accion === 'update') {
        AuthMiddleware::requirePermiso('Categorias', 'actualizar');
        try {
            $id = $val->validarId($_POST['id_categoria'] ?? null, 'ID de categoría');
            $datos = [
                'nombre_categoria' => $val->requerido($_POST['nombre_categoria'] ?? '', 'nombre de la categoría'),
                'descripcion'      => $val->limpiar($_POST['descripcion'] ?? '')
            ];
            $modelo->actualizarCategoria($id, $datos);
            $_SESSION['mensaje'] = "Categoría actualizada exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }

    if ($accion === 'eliminar' || $accion === 'delete') {
        AuthMiddleware::requirePermiso('Categorias', 'eliminar');
        try {
            $id = $val->validarId($_POST['id_categoria'] ?? null, 'ID de categoría');
            $modelo->eliminarCategoria($id);
            $_SESSION['mensaje'] = "Categoría eliminada exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias&action=lista&estado_categoria=activos");
        exit();
    }

    if ($accion === 'reactivar' || $accion === 'restore') {
        AuthMiddleware::requirePermiso('Categorias', 'actualizar');
        try {
            $id = $val->validarId($_POST['id_categoria'] ?? null, 'ID de categoría');
            $modelo->reactivarCategoria($id);
            $_SESSION['mensaje'] = "Categoría reactivada exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al reactivar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias&action=lista&estado_categoria=inactivos");
        exit();
    }
}

// ==========================================
// VISTAS
// ==========================================
$basePath = __DIR__ . "/../view/configuracion/";

if ($action === 'lista' || $action === '' || $action === 'list') {
    
    // Obtenemos TODAS las categorías (sin paginar en PHP, DataTables lo hará)
    $categorias_full = !empty($busqueda) ? $modelo->buscarCategorias($busqueda) : $modelo->obtenerCategorias();

    // Filtramos por estado (activos/inactivos/todos)
    if ($estado_categoria === 'activos') {
        $categorias_full = array_values(array_filter($categorias_full, fn($cat) => ((int)($cat['eliminado'] ?? 0)) === 0));
    } elseif ($estado_categoria === 'inactivos') {
        $categorias_full = array_values(array_filter($categorias_full, fn($cat) => ((int)($cat['eliminado'] ?? 0)) === 1));
    }

    $categorias = $categorias_full;
    $totalRegistros = count($categorias);

    $mensaje      = $_SESSION['mensaje'] ?? null;
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

    require_once $basePath . "listCategoriaView.php";
    exit();
}