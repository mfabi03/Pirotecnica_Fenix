<?php
// app/Controller/categoriaController.php
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\CategoriaModel;
use App\Pirotecnicafenix\Helpers\CheckPermiso;
use Exception;

// ==========================================
// 1. INICIAR SESIÓN
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 2. VERIFICAR AUTENTICACIÓN
// ==========================================
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    $_SESSION['error_permiso'] = "Debes iniciar sesión para acceder a esta sección.";
    header('Location: ?url=login');
    exit();
}

// ==========================================
// 3. CARGAR MODELO
// ==========================================
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'categoriaModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR: No se encuentra categoriaModel.php en: " . $pathModel);
}

// ==========================================
// 4. INICIALIZACIÓN
// ==========================================
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new CategoriaModel($db);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ==========================================
// 5. OBTENER ACCIÓN (GET o POST)
// ==========================================
$action = $_GET['action'] ?? $_POST['action'] ?? $_GET['type'] ?? 'lista';
$id = $_GET['id'] ?? $_POST['id_categoria'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
$busqueda = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));

unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);

// ==========================================
// 6. PROCESAR POST (GUARDAR, ACTUALIZAR, ELIMINAR)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? $action;
    
    // === GUARDAR ===
    if ($accion === 'guardar' || $accion === 'create' || $accion === 'store') {
        CheckPermiso::verificar($db, 'Categorias', 'crear', '?url=categorias');
        try {
            if (empty(trim($_POST['nombre_categoria']))) {
                throw new Exception("El nombre de la categoría es obligatorio.");
            }

            $datos = [
                'nombre_categoria' => trim($_POST['nombre_categoria']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            $resultado = $modelo->registrarCategoria($datos);
            
            if ($resultado) {
                $idCategoria = (int) $db->lastInsertId();
                $_SESSION['nueva_categoria_id'] = $idCategoria;
                $_SESSION['nueva_categoria_nombre'] = $datos['nombre_categoria'];
                $_SESSION['mensaje'] = "✅ Categoría registrada exitosamente";
                $_SESSION['tipo_mensaje'] = "success";

                $return = $_REQUEST['return'] ?? null;
                if (!empty($return)) {
                    header("Location: ?url=" . urlencode($return) . "&type=create&id_categoria=" . $idCategoria);
                    exit();
                }
            } else {
                $_SESSION['mensaje'] = "Error al registrar categoría";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
            $return = $_REQUEST['return'] ?? null;
            if (!empty($return)) {
                header("Location: ?url=categorias&action=registrar&return=" . urlencode($return));
                exit();
            }
        }
        header("Location: ?url=categorias");
        exit();
    }
    
    // === ACTUALIZAR ===
    if ($accion === 'actualizar' || $accion === 'edit' || $accion === 'update') {
        CheckPermiso::verificar($db, 'Categorias', 'actualizar', '?url=categorias');
        try {
            $id = $_POST['id_categoria'] ?? null;
            if (!$id) throw new Exception("ID de categoría no proporcionado");

            if (empty(trim($_POST['nombre_categoria']))) {
                throw new Exception("El nombre de la categoría es obligatorio.");
            }

            $datos = [
                'nombre_categoria' => trim($_POST['nombre_categoria']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            $resultado = $modelo->actualizarCategoria($id, $datos);
            
            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Categoría actualizada exitosamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar categoría";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }
    
    // === ELIMINAR ===
    if ($accion === 'eliminar' || $accion === 'delete') {
        CheckPermiso::verificar($db, 'Categorias', 'eliminar', '?url=categorias');
        try {
            $id = $_POST['id_categoria'] ?? null;
            if (!$id || !is_numeric($id) || $id <= 0) {
                throw new Exception("ID de categoría inválido");
            }

            $resultado = $modelo->eliminarCategoria($id);
            
            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Categoría eliminada exitosamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar categoría";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }
}

// ==========================================
// 7. CARGAR VISTAS (GET)
// ==========================================
$basePath = __DIR__ . "/../view/configuracion/";

// === LISTA ===
if ($action === 'lista' || $action === '' || $action === 'list') {
    // Obtener todas las categorías
    if (!empty($busqueda)) {
        $categorias_full = $modelo->buscarCategorias($busqueda);
    } else {
        $categorias_full = $modelo->obtenerCategorias();
    }
    
    if (!is_array($categorias_full)) {
        $categorias_full = [];
    }

    // ✅ PAGINACIÓN COMPLETA
    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    // Total de registros
    $totalRegistros = count($categorias_full);
    
    // Total de páginas
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    // Cortar el array para la página actual
    if ($por_pagina > 0) {
        $categorias = array_slice($categorias_full, $offset, $por_pagina);
    } else {
        $categorias = $categorias_full;
    }
    
    require_once $basePath . "listCategoriaView.php";
    exit();
}

// === REGISTRAR ===
if ($action === 'registrar' || $action === 'crear' || $action === 'create') {
    require_once $basePath . "registrarCategoriaView.php";
    exit();
}

// === VER ===
if ($action === 'ver' || $action === 'show') {
    if (!$id) {
        $_SESSION['mensaje'] = "ID de categoría no proporcionado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=categorias");
        exit();
    }
    $categoria = $modelo->obtenerCategoriaPorId($id);
    if (!$categoria) {
        $_SESSION['mensaje'] = "Categoría no encontrada";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=categorias");
        exit();
    }
    require_once $basePath . "detalleCategoriaView.php";
    exit();
}

// === EDITAR ===
if ($action === 'editar' || $action === 'edit') {
    if (!$id) {
        $_SESSION['mensaje'] = "ID de categoría no proporcionado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=categorias");
        exit();
    }
    $categoria = $modelo->obtenerCategoriaPorId($id);
    if (!$categoria) {
        $_SESSION['mensaje'] = "Categoría no encontrada";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=categorias");
        exit();
    }
    require_once $basePath . "editarCategoriaView.php";
    exit();
}

// === DEFAULT: LISTA ===
$categorias_full = $modelo->obtenerCategorias();
if (!is_array($categorias_full)) {
    $categorias_full = [];
}

// ✅ PAGINACIÓN COMPLETA
$por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
$pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
$offset        = ($pagina_actual - 1) * $por_pagina;

$totalRegistros = count($categorias_full);
$totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;

if ($por_pagina > 0) {
    $categorias = array_slice($categorias_full, $offset, $por_pagina);
} else {
    $categorias = $categorias_full;
}

require_once $basePath . "listCategoriaView.php";
?>