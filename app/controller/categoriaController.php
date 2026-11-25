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
$accion   = $_POST['accion'] ?? $action; // El modal envía "accion"
$id       = $_GET['id'] ?? $_POST['id_categoria'] ?? null;
$busqueda = $val->limpiar($_GET['busqueda'] ?? '');

$estado_categoria = strtolower($val->limpiar($_REQUEST['estado_categoria'] ?? 'activos'));
if (!in_array($estado_categoria, ['todos', 'activos', 'inactivos'], true)) {
    $estado_categoria = 'activos';
}

// AJAX: Verificar duplicados
if ($action === 'check_duplicado') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $campo = strtolower($val->limpiar($_GET['campo'] ?? ''));
        $nombre = trim($val->limpiar($_GET['valor'] ?? ''));
        $excluirId = isset($_GET['excluir_id']) && is_numeric($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : null;

        if ($campo !== 'nombre_categoria' || $nombre === '') throw new Exception('Datos inválidos.');

        echo json_encode(['existe' => $modelo->existeCategoria($nombre, $excluirId)], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// POST: Registrar y Actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($accion === 'guardar' || $accion === 'create' || $accion === 'store') {
        AuthMiddleware::requirePermiso('Categorias', 'crear');
        try {
            $datos = [
                'nombre_categoria' => $val->requerido($_POST['nombre_categoria'] ?? '', 'nombre'),
                'descripcion'      => $val->limpiar($_POST['descripcion'] ?? '')
            ];
            $modelo->registrarCategoria($datos);
            $_SESSION['mensaje'] = "Categoría registrada exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias&action=lista");
        exit();
    }

    if ($accion === 'actualizar' || $accion === 'edit' || $accion === 'update') {
        AuthMiddleware::requirePermiso('Categorias', 'actualizar');
        try {
            $idCat = $val->validarId($_POST['id_categoria'] ?? null, 'ID de categoría');
            $datos = [
                'nombre_categoria' => $val->requerido($_POST['nombre_categoria'] ?? '', 'nombre'),
                'descripcion'      => $val->limpiar($_POST['descripcion'] ?? '')
            ];
            $modelo->actualizarCategoria($idCat, $datos);
            $_SESSION['mensaje'] = "Categoría actualizada exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias&action=lista");
        exit();
    }

    if ($accion === 'eliminar' || $accion === 'delete') {
        AuthMiddleware::requirePermiso('Categorias', 'eliminar');
        try {
            $idCat = $val->validarId($_POST['id_categoria'] ?? null, 'ID de categoría');
            $modelo->eliminarCategoria($idCat);
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
            $idCat = $val->validarId($_POST['id_categoria'] ?? null, 'ID de categoría');
            $modelo->reactivarCategoria($idCat);
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

// VISTAS
$basePath = __DIR__ . "/../view/configuracion/";

if ($action === 'lista' || $action === '' || $action === 'list') {
    $categorias_full = !empty($busqueda) ? $modelo->buscarCategorias($busqueda) : $modelo->obtenerCategorias();

    if ($estado_categoria === 'activos') {
        $categorias_full = array_values(array_filter($categorias_full, fn($cat) => ((int)($cat['eliminado'] ?? 0)) === 0));
    } elseif ($estado_categoria === 'inactivos') {
        $categorias_full = array_values(array_filter($categorias_full, fn($cat) => ((int)($cat['eliminado'] ?? 0)) === 1));
    }

    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina     = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset     = ($pagina - 1) * $por_pagina;

    $categorias     = $por_pagina > 0 ? array_slice($categorias_full, $offset, $por_pagina) : $categorias_full;
    $totalRegistros = count($categorias_full);
    $totalPaginas   = $por_pagina > 0 ? (int) ceil($totalRegistros / $por_pagina) : 1;
    $pagina_actual  = $pagina;

    $mensaje      = $_SESSION['mensaje'] ?? null;
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

    require_once $basePath . "listCategoriaView.php";
    exit();
}