<?php
// app/Controller/CategoriaController.php
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\CategoriaModel;
use Exception;

// ==========================================
// 1. INICIAR SESIÓN
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 2. VERIFICAR AUTENTICACIÓN (SOLO LOGIN, SIN RESTRICCIÓN DE ROL)
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
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'CategoriaModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR: No se encuentra CategoriaModel.php en: " . $pathModel);
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
$action = $_GET['action'] ?? $_POST['action'] ?? 'lista';
$id = $_GET['id'] ?? $_POST['id_categoria'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
$busqueda = $_GET['busqueda'] ?? '';

unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);

// ==========================================
// 6. PROCESAR POST (GUARDAR, ACTUALIZAR, ELIMINAR)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? $action;
    
    // === GUARDAR === (Accesible para todos los usuarios)
    if ($accion === 'guardar' || $accion === 'create') {
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
                $_SESSION['mensaje'] = "✅ Categoría registrada exitosamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al registrar categoría";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }
    
    // === ACTUALIZAR === (Accesible para todos los usuarios)
    if ($accion === 'actualizar' || $accion === 'edit' || $accion === 'update') {
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
    
    // === ELIMINAR === (Accesible para todos los usuarios)
    if ($accion === 'eliminar' || $accion === 'delete') {
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

// ✅ CORRECCIÓN: Ruta CORRECTA con la tilde
$basePath = __DIR__ . "/../view/configuracion/";

// === LISTA === (Visible para todos)
if ($action === 'lista' || $action === '' || $action === 'list') {
    if (!empty($busqueda)) {
        $categorias = $modelo->buscarCategorias($busqueda);
    } else {
        $categorias = $modelo->obtenerCategorias();
    }
    
    if (!is_array($categorias)) {
        $categorias = [];
    }
    require_once $basePath . "listCategoriaView.php";
    exit();
}

// === REGISTRAR === (Visible para todos)
if ($action === 'registrar' || $action === 'crear' || $action === 'create') {
    require_once $basePath . "registrarCategoriaView.php";
    exit();
}

// === VER === (Visible para todos)
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

// === EDITAR === (Visible para todos)
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
$categorias = $modelo->obtenerCategorias();
if (!is_array($categorias)) {
    $categorias = [];
}
require_once $basePath . "listCategoriaView.php";
