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

// 1. INICIAR SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. VERIFICAR ADMIN
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header('Location: ?url=main');
    exit();
}

// 3. CARGAR MODELO
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'UsuarioModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR: No se encuentra UsuarioModel.php");
}

// 4. INICIALIZACIÓN
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new UsuarioModel($db);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 5. PARÁMETROS
$action = $_GET['action'] ?? 'lista';
$id = $_GET['id'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;

unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);

$busqueda = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));

// 6. PROCESAR POST

// Guardar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? $_GET['action'] ?? '';
    
    if ($accion === 'guardar') {
        try {
            if (empty(trim($_POST['usuario']))) {
                throw new Exception("El nombre de usuario es obligatorio.");
            }
            if (empty(trim($_POST['clave']))) {
                throw new Exception("La contraseña es obligatoria.");
            }
            if (empty(trim($_POST['id_rol']))) {
                throw new Exception("El rol es obligatorio.");
            }

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
            
            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Usuario registrado exitosamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al registrar usuario: " . $modelo->getLastError();
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=usuarios");
        exit();
    }
}

// Actualizar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    try {
        $id = $_POST['id_usuario'] ?? null;
        if (!$id) throw new Exception("ID de usuario no proporcionado");

        if (empty(trim($_POST['usuario']))) {
            throw new Exception("El nombre de usuario es obligatorio.");
        }

        $sqlGet = "SELECT id_persona FROM usuario WHERE id_usuario = :id";
        $stmtGet = $db->prepare($sqlGet);
        $stmtGet->execute(['id' => $id]);
        $usuarioData = $stmtGet->fetch(PDO::FETCH_ASSOC);
        if (!$usuarioData) {
            throw new Exception("Usuario no encontrado");
        }

        if (empty($usuarioData['id_persona'])) {
            throw new Exception("El usuario no tiene persona asociada.");
        }

        $idPersona = $usuarioData['id_persona'];

        $sqlPersona = "UPDATE persona SET 
                          nombre = :nombre,
                          apellido = :apellido,
                          cedula = :cedula,
                          telefono = :telefono,
                          correo_electronico = :correo
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

        $sqlUsuario = "UPDATE usuario SET 
                          usuario = :usuario,
                          id_rol = :id_rol";
        
        if (!empty(trim($_POST['clave']))) {
            $sqlUsuario .= ", clave = :clave";
        }
        
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
        header("Location: ?url=usuarios");
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=usuarios");
        exit();
    }
}

// Eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    try {
        $id = $_POST['id_usuario'] ?? null;
        if (!$id || !is_numeric($id) || $id <= 0) {
            throw new Exception("ID de usuario inválido");
        }
        
        if ($id == $_SESSION['id_usuario']) {
            throw new Exception("No puedes eliminar tu propio usuario");
        }
        
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

// 7. CARGAR VISTAS
$basePath = __DIR__ . "/../view/configuracion/";

// ===== LISTA DE USUARIOS =====
if ($action === 'lista' || $action === '') {
    // Obtener todos los usuarios (con o sin búsqueda)
    if (!empty($busqueda)) {
        $usuarios_full = $modelo->buscarUsuarios($busqueda);
    } else {
        $usuarios_full = $modelo->obtenerUsuariosConPersona();
    }
    
    if (!is_array($usuarios_full)) {
        $usuarios_full = [];
    }

    //  PAGINACIÓN COMPLETA
    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    // Total de registros
    $totalRegistros = count($usuarios_full);
    
    // Total de páginas
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    // Cortar el array para la página actual
    if ($por_pagina > 0) {
        $usuarios = array_slice($usuarios_full, $offset, $por_pagina);
    } else {
        $usuarios = $usuarios_full;
    }
    
    require_once $basePath . "usuarioLista.php";
    exit();
}

// ===== REGISTRAR USUARIO =====
if ($action === 'registrar' || $action === 'crear') {
    $roles = [];
    try {
        $sql = "SELECT id_rol, nombre_rol FROM rol ORDER BY id_rol ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error al obtener roles: " . $e->getMessage());
    }
    require_once $basePath . "usuarioRegistro.php";
    exit();
}

// ===== EDITAR USUARIO =====
if ($action === 'editar' && $id) {
    $usuario = $modelo->obtenerUsuarioPorId($id);
    if (!$usuario) {
        $_SESSION['mensaje'] = "Usuario no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=usuarios");
        exit();
    }
    
    $roles = [];
    try {
        $sql = "SELECT id_rol, nombre_rol FROM rol ORDER BY id_rol ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error al obtener roles: " . $e->getMessage());
    }
    
    require_once $basePath . "usuarioEditar.php";
    exit();
}

// ===== VER USUARIO =====
if ($action === 'ver' && $id) {
    $usuario = $modelo->obtenerUsuarioPorId($id);
    if (!$usuario) {
        $_SESSION['mensaje'] = "Usuario no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=usuarios");
        exit();
    }
    require_once $basePath . "usuarioVer.php";
    exit();
}

// ===== DEFAULT: LISTA =====
$usuarios_full = $modelo->obtenerUsuariosConPersona();
if (!is_array($usuarios_full)) {
    $usuarios_full = [];
}

//  PAGINACIÓN COMPLETA
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

require_once $basePath . "usuarioLista.php";
?>