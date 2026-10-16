<?php
// app/Controller/UsuarioController.php
namespace App\Pirotecnicafenix\Controller;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PDO;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\UsuarioModel;
use Exception;

// ==========================================
// 1. INICIAR SESIÓN
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 2. VERIFICAR ADMIN
// ==========================================
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    header('Location: ?url=login');
    exit();
}

// ==========================================
// 3. CARGAR MODELO
// ==========================================
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'UsuarioModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR: No se encuentra UsuarioModel.php");
}

// ==========================================
// 4. INICIALIZACIÓN
// ==========================================
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new UsuarioModel($db);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ==========================================
// 5. PARÁMETROS
// ==========================================
$action = $_GET['action'] ?? 'lista';
$id = $_GET['id'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;

unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);

$busqueda = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));

// ==========================================
// 6. ENDPOINTS AJAX (tuneo del sistema)
// ==========================================

// ===== AJAX 1: Validar usuario único =====
if ($action === 'check_usuario') {
    header('Content-Type: application/json');
    $usuario = trim($_GET['valor'] ?? '');
    $idExcluir = isset($_GET['excluir_id']) ? (int) $_GET['excluir_id'] : null;
    
    if (empty($usuario)) {
        echo json_encode(['existe' => false]);
        exit;
    }
    
    try {
        $existe = $modelo->existeUsuario($usuario, $idExcluir);
        echo json_encode(['existe' => $existe]);
    } catch (Exception $e) {
        echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ===== AJAX 2: Validar cédula única =====
if ($action === 'check_cedula') {
    header('Content-Type: application/json');
    $cedula = trim($_GET['valor'] ?? '');
    $idExcluir = isset($_GET['excluir_id']) ? (int) $_GET['excluir_id'] : null;
    
    if (empty($cedula)) {
        echo json_encode(['existe' => false]);
        exit;
    }
    
    try {
        // Para excluir, necesitamos el id_persona del usuario
        $idPersonaExcluir = null;
        if ($idExcluir !== null) {
            $sql = "SELECT id_persona FROM usuario WHERE id_usuario = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $idExcluir]);
            $idPersonaExcluir = $stmt->fetchColumn() ?: null;
        }
        
        $existe = $modelo->existeCedula($cedula, $idPersonaExcluir);
        echo json_encode(['existe' => $existe]);
    } catch (Exception $e) {
        echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ===== AJAX 3: Obtener detalle de un usuario =====
if ($action === 'show_ajax') {
    header('Content-Type: application/json');
    $id = (int) ($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'ID inválido']);
        exit;
    }
    
    try {
        $usuario = $modelo->obtenerUsuarioPorId($id);
        
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id_usuario' => (int) $usuario['id_usuario'],
                'usuario' => $usuario['usuario'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'cedula' => $usuario['cedula'],
                'telefono' => $usuario['telefono'],
                'correo_electronico' => $usuario['correo_electronico'],
                'direccion' => $usuario['direccion'],
                'id_rol' => (int) $usuario['id_rol'],
                'rol_nombre' => $usuario['rol_nombre']
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

// ===== AJAX 4: Guardar usuario =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'store_ajax') {
    header('Content-Type: application/json');
    
    try {
        // Validaciones básicas
        if (empty(trim($_POST['usuario'] ?? ''))) {
            throw new Exception("El nombre de usuario es obligatorio.");
        }
        if (empty(trim($_POST['clave'] ?? ''))) {
            throw new Exception("La contraseña es obligatoria.");
        }
        if (empty(trim($_POST['id_rol'] ?? ''))) {
            throw new Exception("El rol es obligatorio.");
        }
        if (empty(trim($_POST['nombre'] ?? ''))) {
            throw new Exception("El nombre es obligatorio.");
        }
        if (empty(trim($_POST['cedula'] ?? ''))) {
            throw new Exception("La cédula es obligatoria.");
        }
        
        $datosPersona = [
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'cedula' => trim($_POST['cedula']),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo_electronico'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? '')
        ];

        $datosUsuario = [
            'usuario' => trim($_POST['usuario']),
            'clave' => password_hash(trim($_POST['clave']), PASSWORD_DEFAULT),
            'id_rol' => (int) $_POST['id_rol']
        ];

        $resultado = $modelo->registrarUsuarioCompleto($datosPersona, $datosUsuario);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'mensaje' => "Usuario '{$datosUsuario['usuario']}' registrado exitosamente"
            ]);
        } else {
            throw new Exception($modelo->getLastError() ?: "No se pudo registrar el usuario.");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
    exit;
}

// ===== AJAX 5: Actualizar usuario =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'update_ajax') {
    header('Content-Type: application/json');
    
    try {
        $id = (int) ($_POST['id_usuario'] ?? 0);
        
        if ($id <= 0) throw new Exception("ID de usuario inválido.");
        if (empty(trim($_POST['usuario'] ?? ''))) throw new Exception("El nombre de usuario es obligatorio.");
        if (empty(trim($_POST['nombre'] ?? ''))) throw new Exception("El nombre es obligatorio.");
        if (empty(trim($_POST['cedula'] ?? ''))) throw new Exception("La cédula es obligatoria.");
        
        $sqlGet = "SELECT id_persona FROM usuario WHERE id_usuario = :id";
        $stmtGet = $db->prepare($sqlGet);
        $stmtGet->execute([':id' => $id]);
        $usuarioData = $stmtGet->fetch(PDO::FETCH_ASSOC);
        if (!$usuarioData) {
            throw new Exception("Usuario no encontrado");
        }

        $idPersona = $usuarioData['id_persona'];
        
        $sqlPersona = "UPDATE persona SET 
                            nombre = :nombre,
                            apellido = :apellido,
                            cedula = :cedula,
                            telefono = :telefono,
                            correo_electronico = :correo,
                            direccion = :direccion
                        WHERE id_persona = :id_persona";
        $stmtP = $db->prepare($sqlPersona);
        $stmtP->execute([
            ':nombre' => trim($_POST['nombre']),
            ':apellido' => trim($_POST['apellido'] ?? ''),
            ':cedula' => trim($_POST['cedula']),
            ':telefono' => trim($_POST['telefono'] ?? ''),
            ':correo' => trim($_POST['correo_electronico'] ?? ''),
            ':direccion' => trim($_POST['direccion'] ?? ''),
            ':id_persona' => $idPersona
        ]);
        
        $sqlUsuario = "UPDATE usuario SET 
                            usuario = :usuario,
                            id_rol = :id_rol";
        
        if (!empty(trim($_POST['clave'] ?? ''))) {
            $sqlUsuario .= ", clave = :clave";
        }
        
        $sqlUsuario .= " WHERE id_usuario = :id";
        
        $params = [
            ':usuario' => trim($_POST['usuario']),
            ':id_rol' => (int) $_POST['id_rol'],
            ':id' => $id
        ];
        
        if (!empty(trim($_POST['clave'] ?? ''))) {
            $params[':clave'] = password_hash(trim($_POST['clave']), PASSWORD_DEFAULT);
        }
        
        $stmtU = $db->prepare($sqlUsuario);
        $stmtU->execute($params);
        
        echo json_encode([
            'success' => true,
            'mensaje' => "Usuario actualizado exitosamente"
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => $e->getMessage()
        ]);
    }
    exit;
}

// ===== AJAX 6: Eliminar usuario =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'delete_ajax') {
    header('Content-Type: application/json');
    
    try {
        $id = (int) ($_POST['id_usuario'] ?? 0);
        
        if ($id <= 0) throw new Exception("ID de usuario inválido.");
        if ($id == $_SESSION['id_usuario']) throw new Exception("No puedes eliminar tu propio usuario.");
        
        $resultado = $modelo->eliminarUsuario($id);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'mensaje' => "Usuario eliminado exitosamente"
            ]);
        } else {
            throw new Exception($modelo->getLastError() ?: "No se pudo eliminar el usuario.");
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
// 7. PROCESAR POST TRADICIONALES (legacy)
// ==========================================

// Guardar usuario (legacy)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    try {
        if (empty(trim($_POST['usuario']))) throw new Exception("El nombre de usuario es obligatorio.");
        if (empty(trim($_POST['clave']))) throw new Exception("La contraseña es obligatoria.");
        if (empty(trim($_POST['id_rol']))) throw new Exception("El rol es obligatorio.");

        $checkSql = "SELECT COUNT(*) FROM usuario WHERE usuario = :usuario";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute(['usuario' => trim($_POST['usuario'])]);
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception("El usuario '" . trim($_POST['usuario']) . "' ya existe.");
        }

        $datosPersona = [
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'cedula' => trim($_POST['cedula']),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo_electronico'] ?? '')
        ];

        $datosUsuario = [
            'usuario' => trim($_POST['usuario']),
            'clave' => password_hash(trim($_POST['clave']), PASSWORD_DEFAULT),
            'id_rol' => (int) $_POST['id_rol']
        ];

        $resultado = $modelo->registrarUsuarioCompleto($datosPersona, $datosUsuario);
        
        $_SESSION['mensaje'] = $resultado ? "✅ Usuario registrado exitosamente" : "Error al registrar usuario";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=usuarios");
    exit();
}

// Actualizar usuario (legacy)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    try {
        $id = $_POST['id_usuario'] ?? null;
        if (!$id) throw new Exception("ID de usuario no proporcionado");

        $sqlGet = "SELECT id_persona FROM usuario WHERE id_usuario = :id";
        $stmtGet = $db->prepare($sqlGet);
        $stmtGet->execute(['id' => $id]);
        $usuarioData = $stmtGet->fetch(PDO::FETCH_ASSOC);
        if (!$usuarioData) throw new Exception("Usuario no encontrado");

        $idPersona = $usuarioData['id_persona'];

        $sqlPersona = "UPDATE persona SET 
                          nombre = :nombre, apellido = :apellido, cedula = :cedula,
                          telefono = :telefono, correo_electronico = :correo
                          WHERE id_persona = :id_persona";
        $stmtP = $db->prepare($sqlPersona);
        $stmtP->execute([
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'cedula' => trim($_POST['cedula'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo_electronico'] ?? ''),
            'id_persona' => $idPersona
        ]);

        $sqlUsuario = "UPDATE usuario SET usuario = :usuario, id_rol = :id_rol";
        if (!empty(trim($_POST['clave']))) $sqlUsuario .= ", clave = :clave";
        $sqlUsuario .= " WHERE id_usuario = :id";
        
        $params = [
            'usuario' => trim($_POST['usuario']),
            'id_rol' => (int) $_POST['id_rol'],
            'id' => $id
        ];
        if (!empty(trim($_POST['clave']))) {
            $params['clave'] = password_hash(trim($_POST['clave']), PASSWORD_DEFAULT);
        }
        
        $stmtU = $db->prepare($sqlUsuario);
        $stmtU->execute($params);

        $_SESSION['mensaje'] = "✅ Usuario actualizado exitosamente";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=usuarios");
    exit();
}

// Eliminar usuario (legacy)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    try {
        $id = $_POST['id_usuario'] ?? null;
        if (!$id || !is_numeric($id) || $id <= 0) throw new Exception("ID de usuario inválido");
        if ($id == $_SESSION['id_usuario']) throw new Exception("No puedes eliminar tu propio usuario");
        
        $resultado = $modelo->eliminarUsuario($id);
        $_SESSION['mensaje'] = $resultado ? "✅ Usuario eliminado exitosamente" : "No se pudo eliminar el usuario";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=usuarios");
    exit();
}

// ==========================================
// 8. BLOQUEAR RUTAS LEGACY DE VISTAS
// ==========================================
if (in_array($action, ['registrar', 'crear', 'editar', 'ver'])) {
    header("Location: ?url=usuarios&action=lista");
    exit();
}

// ==========================================
// 9. CARGAR VISTAS
// ==========================================
$basePath = __DIR__ . "/../view/configuracion/";

// ===== LISTA DE USUARIOS =====
if ($action === 'lista' || $action === '') {
    if (!empty($busqueda)) {
        $usuarios_full = $modelo->buscarUsuarios($busqueda);
    } else {
        $usuarios_full = $modelo->obtenerUsuariosConPersona();
    }
    
    if (!is_array($usuarios_full)) $usuarios_full = [];

    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    $totalRegistros = count($usuarios_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    if ($por_pagina > 0) {
        $usuarios = array_slice($usuarios_full, $offset, $por_pagina);
    } else {
        $usuarios = $usuarios_full;
    }
    
    // Cargar roles para el modal de registrar/editar
    $roles = [];
    try {
        $sql = "SELECT id_rol, nombre_rol FROM rol WHERE eliminado = 0 ORDER BY id_rol ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error al obtener roles: " . $e->getMessage());
    }
    
    require_once $basePath . "usuarioLista.php";
    exit();
}

// ===== DEFAULT: LISTA =====
$usuarios_full = $modelo->obtenerUsuariosConPersona();
if (!is_array($usuarios_full)) $usuarios_full = [];

$por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
$pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
$offset        = ($pagina_actual - 1) * $por_pagina;

$totalRegistros = count($usuarios_full);
$totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;

if ($por_pagina > 0) {
    $usuarios = array_slice($usuarios_full, $offset, $por_pagina);
} else {
    $usuarios = $usuarios_full;
}

$roles = [];
try {
    $sql = "SELECT id_rol, nombre_rol FROM rol WHERE eliminado = 0 ORDER BY id_rol ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener roles: " . $e->getMessage());
}

require_once $basePath . "usuarioLista.php";
?>