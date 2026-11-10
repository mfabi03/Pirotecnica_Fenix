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

// SOLO ADMIN (ROL 1) PUEDE ACCEDER
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

$busqueda = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));

// ==========================================
// 5. ENDPOINTS AJAX
// ==========================================

if ($action === 'check_nombre') {
    header('Content-Type: application/json');
    $nombre = trim($_GET['valor'] ?? '');
    $idExcluir = isset($_GET['id_excluir']) ? (int) $_GET['id_excluir'] : null;
    
    if (empty($nombre)) {
        echo json_encode(['existe' => false]);
        exit;
    }
    
    try {
        $existe = $modelo->existeRol($nombre, $idExcluir);
        echo json_encode(['existe' => $existe]);
    } catch (Exception $e) {
        echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'show_ajax') {
    header('Content-Type: application/json');
    $id = (int) ($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'ID inválido']);
        exit;
    }
    
    try {
        $rol = $modelo->getRolById($id);
        
        if (!$rol) {
            throw new Exception("Rol no encontrado");
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id_rol' => (int) $rol['id_rol'],
                'nombre_rol' => $rol['nombre_rol'],
                'total_usuarios' => (int) ($rol['total_usuarios'] ?? 0),
                'eliminado' => (int) $rol['eliminado']
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'store_ajax') {
    header('Content-Type: application/json');
    
    try {
        $nombre = trim($_POST['nombre_rol'] ?? '');
        
        if (empty($nombre)) {
            throw new Exception("El nombre del rol es obligatorio.");
        }
        
        $resultado = $modelo->crearRol($nombre);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'mensaje' => "Rol '{$nombre}' registrado exitosamente",
                'data' => ['id' => (int) $resultado, 'nombre' => $nombre]
            ]);
        } else {
            throw new Exception("No se pudo registrar el rol.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'update_ajax') {
    header('Content-Type: application/json');
    
    try {
        $id = (int) ($_POST['id_rol'] ?? 0);
        $nombre = trim($_POST['nombre_rol'] ?? '');
        
        if ($id <= 0) throw new Exception("ID de rol inválido.");
        if (empty($nombre)) throw new Exception("El nombre del rol es obligatorio.");
        if ($id === 1) throw new Exception("No puedes modificar el rol de Administrador.");
        
        $resultado = $modelo->actualizarRol($id, $nombre);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => "Rol actualizado exitosamente", 'data' => ['id' => $id, 'nombre' => $nombre]]);
        } else {
            throw new Exception("No se pudo actualizar el rol.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'delete_ajax') {
    header('Content-Type: application/json');
    
    try {
        $id = (int) ($_POST['id_rol'] ?? 0);
        
        if ($id <= 0) throw new Exception("ID de rol inválido.");
        if ($id === 1) throw new Exception("No puedes inactivar el Administrador.");
        
        $sql = "SELECT COUNT(*) FROM usuario WHERE id_rol = :id AND eliminado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $total = $stmt->fetchColumn();
        
        if ($total > 0) {
            throw new Exception("No puedes inactivar este rol porque tiene {$total} usuario(s) asignado(s).");
        }
        
        $resultado = $modelo->eliminarRol($id);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => "Rol inactivado exitosamente"]);
        } else {
            throw new Exception("No se pudo inactivar el rol.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'reactivar_ajax') {
    header('Content-Type: application/json');
    
    try {
        $id = (int) ($_POST['id_rol'] ?? 0);
        if ($id <= 0) throw new Exception("ID de rol inválido.");
        
        $resultado = $modelo->reactivarRol($id);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => "Rol reactivado exitosamente"]);
        } else {
            throw new Exception("No se pudo reactivar el rol.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'permisos_ajax') {
    header('Content-Type: application/json');
    $id_rol = (int) ($_GET['id_rol'] ?? 0);
    
    if ($id_rol <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'ID de rol inválido']);
        exit;
    }
    
    try {
        require_once $rutaRaiz . '/app/Model/PermisoModel.php';
        $permisoModel = new \App\Pirotecnicafenix\Model\PermisoModel($db);
        
        $rolData = $modelo->getRolById($id_rol);
        if (!$rolData) throw new Exception("Rol no encontrado");
        
        $modulos = $permisoModel->obtenerModulos();
        $permisosRaw = $permisoModel->obtenerPermisosPorRol($id_rol);
        
        $permisosActuales = [];
        foreach ($permisosRaw as $idModulo => $acciones) {
            $permisosActuales[$idModulo] = $acciones;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'rol' => ['id_rol' => (int) $rolData['id_rol'], 'nombre_rol' => $rolData['nombre_rol']],
                'modulos' => $modulos,
                'permisos' => $permisosActuales
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'save_permisos_ajax') {
    header('Content-Type: application/json');
    
    try {
        $id_rol = (int) ($_POST['id_rol'] ?? 0);
        $permisosPost = $_POST['permisos'] ?? [];
        
        if ($id_rol <= 0) throw new Exception("Rol inválido");
        if ($id_rol === 1) throw new Exception("No se pueden modificar los permisos del Administrador");
        
        require_once $rutaRaiz . '/app/Model/PermisoModel.php';
        $permisoModel = new \App\Pirotecnicafenix\Model\PermisoModel($db);
        
        $resultado = $permisoModel->actualizarPermisos($id_rol, $permisosPost);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => "Permisos actualizados correctamente"]);
        } else {
            throw new Exception("Error al actualizar los permisos");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 7. CARGAR VISTAS
// ==========================================
$basePath = __DIR__ . "/../view/configuracion/";

if (in_array($action, ['registrar', 'crear', 'editar', 'ver'])) {
    header("Location: ?url=roles&action=lista");
    exit();
}

// ===== LISTA DE ROLES =====
if ($action === 'lista' || $action === '' || $action === 'roles') {
    $busqueda_trim = is_string($busqueda) ? trim($busqueda) : '';
    
    if ($busqueda_trim !== '') {
        $roles_full = $modelo->buscarRoles($busqueda_trim);
    } else {
        $roles_full = $modelo->getAllRoles();
    }
    
    if (!is_array($roles_full)) $roles_full = [];

    // 🔥 FILTRO DE PAPELERA
    $ver_papelera = isset($_GET['papelera']) && $_GET['papelera'] == '1';
    
    $roles_full = array_values(array_filter($roles_full, function ($rol) use ($ver_papelera) {
        $esta_eliminado = ((int) ($rol['eliminado'] ?? 0)) === 1;
        return $ver_papelera ? $esta_eliminado : !$esta_eliminado;
    }));

    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    $totalRegistros = count($roles_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    if ($por_pagina > 0) {
        $roles = array_slice($roles_full, $offset, $por_pagina);
    } else {
        $roles = $roles_full;
    }
    
    require_once $basePath . "rolListar.php";
    exit();
}

// ===== DEFAULT: LISTA =====
$roles_full = $modelo->getAllRoles();
if (!is_array($roles_full)) $roles_full = [];

$por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
$pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
$offset        = ($pagina_actual - 1) * $por_pagina;

$totalRegistros = count($roles_full);
$totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;

if ($por_pagina > 0) {
    $roles = array_slice($roles_full, $offset, $por_pagina);
} else {
    $roles = $roles_full;
}

require_once $basePath . "rolListar.php";
?>