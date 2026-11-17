<?php
// app/Controller/categoriaController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\CategoriaModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\Validador;
use App\Pirotecnicafenix\Middleware\AuthMiddleware;
use Exception;

// 1. Instanciamos el modelo (se conecta solo por herencia)
$modelo = new CategoriaModel();

// 2. Instanciamos el helper (solo para calcular los permisos de la vista)
$permisoHelper = new PermisoHelper();
$id_rol_actual = $_SESSION['id_rol'] ?? 0;

// 3. Calculamos los permisos UNA SOLA VEZ
$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Categorias', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Categorias', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Categorias', 'eliminar');

$action   = $_GET['action'] ?? $_POST['action'] ?? 'lista';
$id       = $_GET['id'] ?? $_POST['id_categoria'] ?? null;
$busqueda = Validador::limpiar($_GET['busqueda'] ?? '');

$estado_categoria = strtolower(Validador::limpiar($_REQUEST['estado_categoria'] ?? 'activos'));
if (!in_array($estado_categoria, ['todos', 'activos', 'inactivos'], true)) {
    $estado_categoria = 'activos';
}

// ==========================================
// ENDPOINTS AJAX
// ==========================================
if ($action === 'check_duplicado') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$permisoHelper->tienePermiso($id_rol_actual, 'Categorias', 'leer')) {
            http_response_code(403);
            throw new Exception('No tienes permiso para consultar categorías.');
        }

        $campo = strtolower(Validador::limpiar($_GET['campo'] ?? ''));
        $nombre = trim(Validador::limpiar($_GET['valor'] ?? ''));
        $excluirId = isset($_GET['excluir_id']) && is_numeric($_GET['excluir_id'])
            ? (int)$_GET['excluir_id']
            : null;

        if ($campo !== 'nombre_categoria' || $nombre === '') {
            throw new Exception('Datos de verificación inválidos.');
        }

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
        // 🔥 Usamos las variables calculadas arriba
        if (!$puede_crear) {
            throw new Exception("No tienes permiso para registrar categorías.");
        }

        $nombre = Validador::requerido($_POST['nombre_categoria'] ?? '', 'nombre de la categoría');

        $datos = [
            'nombre_categoria' => $nombre,
            'descripcion'      => Validador::limpiar($_POST['descripcion'] ?? '')
        ];

        $id_categoria = $modelo->registrarCategoria($datos);

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
// PROCESAR POST
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? $action;

    // GUARDAR
    if ($accion === 'guardar' || $accion === 'create') {
        // 🔥 Usamos AuthMiddleware (Adiós CheckPermiso)
        AuthMiddleware::requirePermiso('Categorias', 'crear');
        try {
            $datos = [
                'nombre_categoria' => Validador::requerido($_POST['nombre_categoria'] ?? '', 'nombre de la categoría'),
                'descripcion'      => Validador::limpiar($_POST['descripcion'] ?? '')
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
        AuthMiddleware::requirePermiso('Categorias', 'actualizar');
        try {
            $id = Validador::validarId($_POST['id_categoria'] ?? null, 'ID de categoría');

            $datos = [
                'nombre_categoria' => Validador::requerido($_POST['nombre_categoria'] ?? '', 'nombre de la categoría'),
                'descripcion'      => Validador::limpiar($_POST['descripcion'] ?? '')
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
        AuthMiddleware::requirePermiso('Categorias', 'eliminar');
        try {
            $id = Validador::validarId($_POST['id_categoria'] ?? null, 'ID de categoría');
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

    // REACTIVAR
    if ($accion === 'reactivar' || $accion === 'restore') {
        AuthMiddleware::requirePermiso('Categorias', 'actualizar');
        try {
            $id = Validador::validarId($_POST['id_categoria'] ?? null, 'ID de categoría');
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
    if (!empty($busqueda)) {
        $categorias_full = $modelo->buscarCategorias($busqueda);
    } else {
        $categorias_full = $modelo->obtenerCategorias();
    }

    // FILTRADO POR ESTADO
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
    $pagina     = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset     = ($pagina - 1) * $por_pagina;

    $categorias     = $por_pagina > 0 ? array_slice($categorias_full, $offset, $por_pagina) : $categorias_full;
    $totalRegistros = count($categorias_full);
    $totalPaginas   = $por_pagina > 0 ? (int) ceil($totalRegistros / $por_pagina) : 1;
    $pagina_actual  = $pagina;

    $mensaje      = $_SESSION['mensaje'] ?? null;
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

    // 🔥 Las variables $puede_crear, $puede_editar y $puede_eliminar
    // ya están calculadas arriba y la vista las recibirá automáticamente.
    require_once $basePath . "listCategoriaView.php";
    exit();
}

// ... (las demás vistas: registrar, ver, editar se mantienen igual) ...