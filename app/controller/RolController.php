<?php
// app/Controller/RolController.php
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\RolModel;
use Exception;
use PDO;

// ==========================================
// 1. INICIAR SESIÓN Y VERIFICAR PERMISOS
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ SOLO ADMIN (ROL 1) PUEDE ACCEDER
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header('Location: ?url=dashboard');
    exit();
}

// ==========================================
// 2. CARGA DEL MODELO
// ==========================================
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'RolModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR CRÍTICO: No se encuentra el archivo: " . $pathModel);
}

// ==========================================
// 3. INICIALIZACIÓN DE CONEXIÓN Y MODELO
// ==========================================
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new \App\Pirotecnicafenix\Model\RolModel($db);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ==========================================
// 4. PARÁMETROS DE LA URL
// ==========================================
$action = $_GET['action'] ?? 'lista';
$id = $_GET['id'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;

unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);

// Parámetros de búsqueda
$busqueda = trim((string) ($_GET['busqueda'] ?? ''));

// ==========================================
// 5. PROCESAR POST (CRUD)
// ==========================================

// ===== GUARDAR NUEVO ROL =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    try {
        if (empty(trim($_POST['nombre_rol']))) {
            throw new Exception("El nombre del rol es obligatorio.");
        }

        $nombre = trim($_POST['nombre_rol']);
        $resultado = $modelo->crearRol($nombre);
        
        $_SESSION['mensaje'] = $resultado ? "✅ Rol registrado exitosamente" : "Error al registrar rol";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=roles&action=lista");
    exit();
}

// ===== ACTUALIZAR ROL =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    try {
        $id = $_POST['id_rol'] ?? null;
        if (!$id) throw new Exception("ID de rol no proporcionado");

        if (empty(trim($_POST['nombre_rol']))) {
            throw new Exception("El nombre del rol es obligatorio.");
        }

        // ✅ No permitir modificar el rol de administrador (id_rol = 1)
        if ($id == 1) {
            throw new Exception("No puedes modificar el rol de Administrador");
        }

        $nombre = trim($_POST['nombre_rol']);
        $resultado = $modelo->actualizarRol($id, $nombre);
        
        $_SESSION['mensaje'] = $resultado ? "✅ Rol actualizado exitosamente" : "Error al actualizar rol";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=roles&action=lista");
    exit();
}

// ===== ELIMINAR ROL =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    try {
        $id = $_POST['id_rol'] ?? null;
        if (!$id || !is_numeric($id) || $id <= 0) {
            throw new Exception("ID de rol inválido");
        }
        
        // ✅ No permitir eliminar el rol de administrador (id_rol = 1)
        if ($id == 1) {
            throw new Exception("No puedes eliminar el rol de Administrador");
        }
        
        // ✅ CORREGIDO: tabla 'usuario' (singular) y columna 'id_rol'
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE id_rol = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $total = $stmt->fetchColumn();
        
        if ($total > 0) {
            throw new Exception("No puedes eliminar este rol porque tiene $total usuarios asignados");
        }
        
        $resultado = $modelo->eliminarRol($id);
        $_SESSION['mensaje'] = $resultado ? "✅ Rol eliminado exitosamente" : "No se pudo eliminar el rol";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=roles&action=lista");
    exit();
}

// ===== ELIMINAR ROL (Vía GET) =====
if (isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
    try {
        $id = (int) $_GET['id'];
        if ($id <= 0) throw new Exception("ID de rol inválido");
        
        if ($id == 1) {
            throw new Exception("No puedes eliminar el rol de Administrador");
        }
        
        // ✅ CORREGIDO: tabla 'usuario' (singular) y columna 'id_rol'
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE id_rol = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $total = $stmt->fetchColumn();
        
        if ($total > 0) {
            throw new Exception("No puedes eliminar este rol porque tiene $total usuarios asignados");
        }
        
        $resultado = $modelo->eliminarRol($id);
        $_SESSION['mensaje'] = $resultado ? "✅ Rol eliminado exitosamente" : "No se pudo eliminar el rol";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=roles&action=lista");
    exit();
}

// ==========================================
// 6. OBTENER DATOS PARA VISTAS
// ==========================================

// Obtener rol para edición o visualización
if (in_array($action, ['editar', 'ver']) && $id) {
    $rol = $modelo->getRolById($id);
    if (!$rol) {
        $_SESSION['mensaje'] = "Rol no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=roles&action=lista");
        exit();
    }
}

// ==========================================
// 7. CARGAR VISTAS
// ==========================================

$basePath = __DIR__ . "/../view/configuracion/";

// ===== LISTA DE ROLES =====
if ($action === 'lista' || $action === '' || $action === 'roles') {
    $busqueda_trim = is_string($busqueda) ? trim($busqueda) : '';
    
    // ✅ CORREGIDO: tabla 'usuario' (singular) y columna 'id_rol'
    $sql = "SELECT 
                r.id_rol, 
                r.nombre_rol,
                (SELECT COUNT(*) FROM usuario u WHERE u.id_rol = r.id_rol) as total_usuarios
            FROM rol r";
    
    if ($busqueda_trim !== '') {
        $sql .= " WHERE r.nombre_rol LIKE :busqueda";
        $stmt = $db->prepare($sql);
        $stmt->execute(['busqueda' => '%' . $busqueda_trim . '%']);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
    
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!is_array($roles)) {
        $roles = [];
    }
    
    require_once $basePath . "rolLista.php";
    exit();
}

// ===== REGISTRAR ROL =====
if ($action === 'registrar' || $action === 'crear') {
    require_once $basePath . "rolRegistro.php";
    exit();
}

// ===== VER ROL =====
if ($action === 'ver' && $id) {
    require_once $basePath . "rolVer.php";
    exit();
}

// ===== EDITAR ROL =====
if ($action === 'editar' && $id) {
    require_once $basePath . "rolEditar.php";
    exit();
}

// ===== DEFAULT: LISTA =====
$roles = $modelo->getAllRoles();
if (!is_array($roles)) {
    $roles = [];
}
require_once $basePath . "rolLista.php";
?>