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

// Parámetros de búsqueda
$busqueda = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));

// ==========================================
// 5. ENDPOINTS AJAX (tuneo del sistema)
// ==========================================

// ===== AJAX 1: Validar nombre único =====
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

// ===== AJAX 2: Obtener detalle de un rol =====
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
                'total_usuarios' => (int) ($rol['total_usuarios'] ?? 0)
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
    exit;
}

// ===== AJAX 3: Guardar rol =====
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
                'data' => [
                    'id' => (int) $resultado,
                    'nombre' => $nombre
                ]
            ]);
        } else {
            throw new Exception("No se pudo registrar el rol. Verifica que el nombre no esté duplicado.");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
    exit;
}

// ===== AJAX 4: Actualizar rol =====
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
            echo json_encode([
                'success' => true,
                'mensaje' => "Rol actualizado exitosamente",
                'data' => ['id' => $id, 'nombre' => $nombre]
            ]);
        } else {
            throw new Exception("No se pudo actualizar el rol.");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
    exit;
}

// ===== AJAX 5: Eliminar rol =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'delete_ajax') {
    header('Content-Type: application/json');
    
    try {
        $id = (int) ($_POST['id_rol'] ?? 0);
        
        if ($id <= 0) throw new Exception("ID de rol inválido.");
        if ($id === 1) throw new Exception("No puedes eliminar el Administrador.");
        
        // Verificar si tiene usuarios asignados
        $sql = "SELECT COUNT(*) FROM usuario WHERE id_rol = :id AND eliminado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $total = $stmt->fetchColumn();
        
        if ($total > 0) {
            throw new Exception("No puedes eliminar este rol porque tiene {$total} usuario(s) asignado(s).");
        }
        
        $resultado = $modelo->eliminarRol($id);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'mensaje' => "Rol eliminado exitosamente"
            ]);
        } else {
            throw new Exception("No se pudo eliminar el rol.");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
    exit;
}

// ===== AJAX 6: Obtener datos de permisos de un rol (rol + módulos + permisos) =====
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
        
        // 1. Datos del rol
        $rolData = $modelo->getRolById($id_rol);
        if (!$rolData) {
            throw new Exception("Rol no encontrado");
        }
        
        // 2. Todos los módulos
        $modulos = $permisoModel->obtenerModulos();
        
        // 3. Permisos actuales del rol
        $permisosRaw = $permisoModel->obtenerPermisosPorRol($id_rol);
        
        // 4. Reorganizar: [id_modulo => [crear => 1, leer => 1, ...]]
        $permisosActuales = [];
        foreach ($permisosRaw as $idModulo => $acciones) {
            $permisosActuales[$idModulo] = $acciones;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'rol' => [
                    'id_rol' => (int) $rolData['id_rol'],
                    'nombre_rol' => $rolData['nombre_rol']
                ],
                'modulos' => $modulos,
                'permisos' => $permisosActuales
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
    exit;
}

// ===== AJAX 7: Guardar permisos de un rol =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'save_permisos_ajax') {
    header('Content-Type: application/json');
    
    try {
        $id_rol = (int) ($_POST['id_rol'] ?? 0);
        $permisosPost = $_POST['permisos'] ?? [];
        
        if ($id_rol <= 0) {
            throw new Exception("Rol inválido");
        }
        
        if ($id_rol === 1) {
            throw new Exception("No se pueden modificar los permisos del Administrador");
        }
        
        require_once $rutaRaiz . '/app/Model/PermisoModel.php';
        $permisoModel = new \App\Pirotecnicafenix\Model\PermisoModel($db);
        
        $resultado = $permisoModel->actualizarPermisos($id_rol, $permisosPost);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'mensaje' => "Permisos actualizados correctamente"
            ]);
        } else {
            throw new Exception("Error al actualizar los permisos");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
    exit;
}



// ==========================================
// 7. CARGAR VISTAS
// ==========================================
$basePath = __DIR__ . "/../view/configuracion/";

// ===== BLOQUEAR ACCESO A RUTAS LEGACY =====
// Si alguien intenta acceder por URL directa a registrar/editar/ver,
// lo redirigimos al listado (ahora son modales)
if (in_array($action, ['registrar', 'crear', 'editar', 'ver'])) {
    header("Location: ?url=roles&action=lista");
    exit();
}

// ===== LISTA DE ROLES (VISTA PRINCIPAL) =====
if ($action === 'lista' || $action === '' || $action === 'roles') {
    $busqueda_trim = is_string($busqueda) ? trim($busqueda) : '';
    
    // Obtener todos los roles (con o sin búsqueda)
    if ($busqueda_trim !== '') {
        $roles_full = $modelo->buscarRoles($busqueda_trim);
    } else {
        $roles_full = $modelo->getAllRoles();
    }
    
    if (!is_array($roles_full)) {
        $roles_full = [];
    }

    //  PAGINACIÓN COMPLETA
    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    // Total de registros
    $totalRegistros = count($roles_full);
    
    // Total de páginas
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    // Cortar el array para la página actual
    if ($por_pagina > 0) {
        $roles = array_slice($roles_full, $offset, $por_pagina);
    } else {
        $roles = $roles_full;
    }
    
    require_once $basePath . "rolListar.php";
    exit();
}

// ===== GESTIONAR PERMISOS DE UN ROL (VISTA INDEPENDIENTE - AÚN SE USA) =====
if ($action === 'permisos' && $id) {
    require_once $rutaRaiz . '/app/Model/PermisoModel.php';
    $permisoModel = new \App\Pirotecnicafenix\Model\PermisoModel($db);
    
    $modulos = $permisoModel->obtenerModulos();
    $permisosActuales = $permisoModel->obtenerPermisosPorRol($id);
    
    // Buscar el rol para pasarlo a la vista
    $rol = $modelo->getRolById($id);
    if (!$rol) {
        $_SESSION['mensaje'] = "Rol no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=roles&action=lista");
        exit();
    }
    
    require_once $basePath . "rolPermisos.php";
    exit();
}

// ===== DEFAULT: LISTA =====
$roles_full = $modelo->getAllRoles();
if (!is_array($roles_full)) {
    $roles_full = [];
}

//  PAGINACIÓN COMPLETA
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