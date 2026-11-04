<?php
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\CategoriaModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\CheckPermiso;
use Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    $_SESSION['error_permiso'] = "Debes iniciar sesión para acceder a esta sección.";
    header('Location: ?url=login');
    exit();
}

$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'CategoriaModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR: No se encuentra CategoriaModel.php en: " . $pathModel);
}

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new CategoriaModel($db);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'lista';
$id = $_GET['id'] ?? $_POST['id_categoria'] ?? null;
$busqueda = $_GET['busqueda'] ?? '';

// NUEVO: Parámetro de estado
$estado_categoria = strtolower(trim((string) ($_REQUEST['estado_categoria'] ?? 'todos')));
if (!in_array($estado_categoria, ['todos', 'activos', 'inactivos'], true)) {
    $estado_categoria = 'todos';
}

// ENDPOINTS AJAX
if (isset($_GET['action']) && $_GET['action'] === 'listar_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $categorias = $modelo->obtenerCategorias();
        echo json_encode(['success' => true, 'categorias' => $categorias], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'store_rapido_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!PermisoHelper::tienePermiso($db, $_SESSION['id_rol'] ?? 0, 'Categorias', 'crear')) {
            throw new Exception("No tienes permiso para registrar categorías.");
        }
        if (empty(trim($_POST['nombre_categoria'] ?? ''))) {
            throw new Exception("El nombre de la categoría es obligatorio.");
        }
        
        $datos = [
            'nombre_categoria' => trim($_POST['nombre_categoria']),
            'descripcion' => trim($_POST['descripcion'] ?? '')
        ];
        
        $modelo->registrarCategoria($datos);
        $id_categoria = (int) $db->lastInsertId();
        
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

// PROCESAR POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? $action;
    
    // GUARDAR
    if ($accion === 'guardar' || $accion === 'create') {
        CheckPermiso::verificar($db, 'Categorias', 'crear', '?url=categorias');
        try {
            if (empty(trim($_POST['nombre_categoria']))) {
                throw new Exception("El nombre de la categoría es obligatorio.");
            }
            $datos = [
                'nombre_categoria' => trim($_POST['nombre_categoria']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];
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
    
    // ACTUALIZAR
    if ($accion === 'actualizar' || $accion === 'edit' || $accion === 'update') {
        CheckPermiso::verificar($db, 'Categorias', 'actualizar', '?url=categorias');
        try {
            $id = $_POST['id_categoria'] ?? null;
            if (!$id) throw new Exception("ID de categoría no proporcionado");
            if (empty(trim($_POST['nombre_categoria']))) throw new Exception("El nombre es obligatorio.");

            $datos = [
                'nombre_categoria' => trim($_POST['nombre_categoria']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
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
    
    // ELIMINAR
    if ($accion === 'eliminar' || $accion === 'delete') {
        CheckPermiso::verificar($db, 'Categorias', 'eliminar', '?url=categorias');
        try {
            $id = $_POST['id_categoria'] ?? null;
            if (!$id || !is_numeric($id) || $id <= 0) {
                throw new Exception("ID de categoría inválido");
            }
            $modelo->eliminarCategoria($id);
            $_SESSION['mensaje'] = "Categoría eliminada exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }

    // REACTIVAR (NUEVO)
    if ($accion === 'reactivar' || $accion === 'restore') {
        CheckPermiso::verificar($db, 'Categorias', 'actualizar', '?url=categorias');
        try {
            $id = $_POST['id_categoria'] ?? null;
            if (!$id || !is_numeric($id) || $id <= 0) {
                throw new Exception("ID de categoría inválido");
            }
            $modelo->reactivarCategoria($id);
            $_SESSION['mensaje'] = "Categoría reactivada exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al reactivar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }
}

// VISTA DE LISTA
$basePath = __DIR__ . "/../view/configuracion/";

if ($action === 'lista' || $action === '' || $action === 'list') {
    if (!empty($busqueda)) {
        $categorias_full = $modelo->buscarCategorias($busqueda);
    } else {
        $categorias_full = $modelo->obtenerCategorias();
    }

    // FILTRADO POR ESTADO (NUEVO)
    if ($estado_categoria === 'activos') {
        $categorias_full = array_values(array_filter($categorias_full, function ($cat) {
            return ((int) ($cat['eliminado'] ?? 0)) === 0;
        }));
    } elseif ($estado_categoria === 'inactivos') {
        $categorias_full = array_values(array_filter($categorias_full, function ($cat) {
            return ((int) ($cat['eliminado'] ?? 0)) === 1;
        }));
    }

    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset = ($pagina - 1) * $por_pagina;
    
    $categorias = $por_pagina > 0 ? array_slice($categorias_full, $offset, $por_pagina) : $categorias_full;
    $totalRegistros = count($categorias_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    $pagina_actual = $pagina;
    
    // Pasamos los mensajes a la vista
    $mensaje = $_SESSION['mensaje'] ?? null;
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

    require_once $basePath . "listCategoriaView.php";
    exit();
}

// VER, EDITAR, REGISTRAR
if ($action === 'registrar' || $action === 'crear' || $action === 'create') {
    require_once $basePath . "registrarCategoriaView.php";
    exit();
}

if ($action === 'ver' || $action === 'show') {
    $categoria = $modelo->obtenerCategoriaPorId($id);
    require_once $basePath . "detalleCategoriaView.php";
    exit();
}

if ($action === 'editar' || $action === 'edit') {
    $categoria = $modelo->obtenerCategoriaPorId($id);
    require_once $basePath . "editarCategoriaView.php";
    exit();
}
?>