<?php
// app/Controller/UsuarioController.php
namespace App\Pirotecnicafenix\Controller;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Pirotecnicafenix\Model\UsuarioModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\Validador;
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
    header('Location: ?url=login');
    exit();
}

// ==========================================
// 3. MODELO (hereda conexión)
// ==========================================
$modelo = new UsuarioModel();
$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$permisoHelper = new PermisoHelper();

$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Usuarios', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Usuarios', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Usuarios', 'eliminar');

// ==========================================
// 4. PARÁMETROS
// ==========================================
$action = $_GET['action'] ?? 'lista';
$id = $_GET['id'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;

unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

$busqueda = Validador::limpiar($_GET['busqueda'] ?? $_GET['buscar'] ?? '');

// ==========================================
// 5. ENDPOINTS AJAX
// ==========================================

if ($action === 'check_usuario') {
    header('Content-Type: application/json');
    try {
        $usuario = Validador::limpiar($_GET['valor'] ?? '');
        $idExcluir = isset($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : null;

        if ($usuario === '') {
            echo json_encode(['existe' => false]);
            exit;
        }

        $existe = $modelo->existeUsuario($usuario, $idExcluir);
        echo json_encode(['existe' => $existe]);
    } catch (Exception $e) {
        echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'check_cedula') {
    header('Content-Type: application/json');
    try {
        $cedula = Validador::limpiar($_GET['valor'] ?? '');
        $idExcluir = isset($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : null;

        if ($cedula === '') {
            echo json_encode(['existe' => false]);
            exit;
        }

        $idPersonaExcluir = null;
        if ($idExcluir !== null) {
            $idPersonaExcluir = $modelo->obtenerIdPersonaPorUsuario($idExcluir);
        }

        $existe = $modelo->existeCedula($cedula, $idPersonaExcluir);
        echo json_encode(['existe' => $existe]);
    } catch (Exception $e) {
        echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'show_ajax') {
    header('Content-Type: application/json');
    try {
        $id = Validador::validarId($_GET['id'] ?? null, 'ID de usuario');
        $usuario = $modelo->obtenerUsuarioPorId($id);

        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'id_usuario'         => (int)$usuario['id_usuario'],
                'usuario'            => $usuario['usuario'],
                'nombre'             => $usuario['nombre'],
                'apellido'           => $usuario['apellido'],
                'cedula'             => $usuario['cedula'],
                'telefono'           => $usuario['telefono'],
                'correo_electronico' => $usuario['correo_electronico'],
                'direccion'          => $usuario['direccion'],
                'id_rol'             => (int)$usuario['id_rol'],
                'rol_nombre'         => $usuario['rol_nombre']
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// ===== AJAX: GUARDAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'store_ajax') {
    header('Content-Type: application/json');
    try {
        if (!$puede_crear) {
            throw new Exception('No tienes permiso para registrar usuarios.');
        }

        $datosPersona = [
            'nombre'     => Validador::requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido'   => Validador::limpiar($_POST['apellido'] ?? ''),
            'cedula'     => Validador::requerido($_POST['cedula'] ?? '', 'cédula'),
            'telefono'   => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo'     => Validador::limpiar($_POST['correo_electronico'] ?? ''),
            'direccion'  => Validador::limpiar($_POST['direccion'] ?? '')
        ];

        $datosUsuario = [
            'usuario' => Validador::requerido($_POST['usuario'] ?? '', 'usuario'),
            'clave'   => password_hash(Validador::requerido($_POST['clave'] ?? '', 'contraseña'), PASSWORD_DEFAULT),
            'id_rol'  => Validador::validarId($_POST['id_rol'] ?? null, 'rol')
        ];

        $resultado = $modelo->registrarUsuarioCompleto($datosPersona, $datosUsuario);

        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => "Usuario '{$datosUsuario['usuario']}' registrado exitosamente"]);
        } else {
            throw new Exception($modelo->getLastError() ?: "No se pudo registrar el usuario.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// ===== AJAX: ACTUALIZAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'update_ajax') {
    header('Content-Type: application/json');
    try {
        if (!$puede_editar) {
            throw new Exception('No tienes permiso para actualizar usuarios.');
        }

        $id = Validador::validarId($_POST['id_usuario'] ?? null, 'ID de usuario');

        $datos = [
            'usuario'            => Validador::requerido($_POST['usuario'] ?? '', 'usuario'),
            'nombre'             => Validador::requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido'           => Validador::limpiar($_POST['apellido'] ?? ''),
            'cedula'             => Validador::requerido($_POST['cedula'] ?? '', 'cédula'),
            'telefono'           => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo_electronico' => Validador::limpiar($_POST['correo_electronico'] ?? ''),
            'direccion'          => Validador::limpiar($_POST['direccion'] ?? ''),
            'id_rol'             => Validador::validarId($_POST['id_rol'] ?? null, 'rol'),
            'clave'              => Validador::limpiar($_POST['clave'] ?? '')
        ];

        $resultado = $modelo->actualizarUsuario($id, $datos);

        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => "Usuario actualizado exitosamente"]);
        } else {
            throw new Exception($modelo->getLastError() ?: "No se pudo actualizar el usuario.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// ===== AJAX: ELIMINAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'delete_ajax') {
    header('Content-Type: application/json');
    try {
        if (!$puede_eliminar) {
            throw new Exception('No tienes permiso para inactivar usuarios.');
        }

        $id = Validador::validarId($_POST['id_usuario'] ?? null, 'ID de usuario');

        if ($id == $_SESSION['id_usuario']) throw new Exception("No puedes inactivar tu propio usuario.");

        $resultado = $modelo->eliminarUsuario($id);

        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => "Usuario inactivado exitosamente"]);
        } else {
            throw new Exception($modelo->getLastError() ?: "No se pudo inactivar el usuario.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// ===== AJAX: REACTIVAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'reactivar_ajax') {
    header('Content-Type: application/json');
    try {
        if (!$puede_editar) {
            throw new Exception('No tienes permiso para reactivar usuarios.');
        }

        $id = Validador::validarId($_POST['id_usuario'] ?? null, 'ID de usuario');
        $resultado = $modelo->reactivarUsuario($id);

        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => "Usuario reactivado exitosamente"]);
        } else {
            throw new Exception($modelo->getLastError() ?: "No se pudo reactivar el usuario.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 6. PROCESAR POST TRADICIONALES (legacy)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    try {
        if (!$puede_crear) {
            throw new Exception('No tienes permiso para registrar usuarios.');
        }

        $datosPersona = [
            'nombre'    => Validador::requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido'  => Validador::limpiar($_POST['apellido'] ?? ''),
            'cedula'    => Validador::requerido($_POST['cedula'] ?? '', 'cédula'),
            'telefono'  => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo'    => Validador::limpiar($_POST['correo_electronico'] ?? '')
        ];

        $datosUsuario = [
            'usuario' => Validador::requerido($_POST['usuario'] ?? '', 'usuario'),
            'clave'   => password_hash(Validador::requerido($_POST['clave'] ?? '', 'contraseña'), PASSWORD_DEFAULT),
            'id_rol'  => Validador::validarId($_POST['id_rol'] ?? null, 'rol')
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    try {
        if (!$puede_editar) {
            throw new Exception('No tienes permiso para actualizar usuarios.');
        }

        $id = Validador::validarId($_POST['id_usuario'] ?? null, 'ID de usuario');

        $datos = [
            'usuario'            => Validador::requerido($_POST['usuario'] ?? '', 'usuario'),
            'nombre'             => Validador::limpiar($_POST['nombre'] ?? ''),
            'apellido'           => Validador::limpiar($_POST['apellido'] ?? ''),
            'cedula'             => Validador::limpiar($_POST['cedula'] ?? ''),
            'telefono'           => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo_electronico' => Validador::limpiar($_POST['correo_electronico'] ?? ''),
            'id_rol'             => Validador::validarId($_POST['id_rol'] ?? null, 'rol'),
            'clave'              => Validador::limpiar($_POST['clave'] ?? '')
        ];

        $resultado = $modelo->actualizarUsuario($id, $datos);

        $_SESSION['mensaje'] = $resultado ? "✅ Usuario actualizado exitosamente" : "Error al actualizar usuario";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=usuarios");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    try {
        if (!$puede_eliminar) {
            throw new Exception('No tienes permiso para inactivar usuarios.');
        }

        $id = Validador::validarId($_POST['id_usuario'] ?? null, 'ID de usuario');

        if ($id == $_SESSION['id_usuario']) throw new Exception("No puedes eliminar tu propio usuario");

        $resultado = $modelo->eliminarUsuario($id);
        $_SESSION['mensaje'] = $resultado ? "✅ Usuario inactivado exitosamente" : "No se pudo inactivar el usuario";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=usuarios");
    exit();
}

// ==========================================
// 7. BLOQUEAR RUTAS LEGACY
// ==========================================
if (in_array($action, ['registrar', 'crear', 'editar', 'ver'], true)) {
    header("Location: ?url=usuarios&action=lista");
    exit();
}

// ==========================================
// 8. CARGAR VISTAS
// ==========================================
$basePath = __DIR__ . "/../view/configuracion/";

// LISTA DE USUARIOS
if ($action === 'lista' || $action === '') {
    if (!empty($busqueda)) {
        $usuarios_full = $modelo->buscarUsuarios($busqueda);
    } else {
        $usuarios_full = $modelo->obtenerUsuariosConPersona();
    }

    if (!is_array($usuarios_full)) $usuarios_full = [];

    // FILTRO DE PAPELERA
    $ver_papelera = isset($_GET['papelera']) && $_GET['papelera'] == '1';

    $usuarios_full = array_values(array_filter($usuarios_full, function ($usuario) use ($ver_papelera) {
        $esta_eliminado = ((int)($usuario['eliminado'] ?? 0)) === 1;
        return $ver_papelera ? $esta_eliminado : !$esta_eliminado;
    }));

    $por_pagina    = (int)($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;

    $totalRegistros = count($usuarios_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;

    if ($por_pagina > 0) {
        $usuarios = array_slice($usuarios_full, $offset, $por_pagina);
    } else {
        $usuarios = $usuarios_full;
    }

    $roles = $modelo->obtenerRoles();

    require_once $basePath . "usuarioLista.php";
    exit();
}

// DEFAULT: LISTA
$usuarios_full = $modelo->obtenerUsuariosConPersona();
if (!is_array($usuarios_full)) $usuarios_full = [];

$por_pagina    = (int)($_GET['por_pagina'] ?? 10);
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset        = ($pagina_actual - 1) * $por_pagina;

$totalRegistros = count($usuarios_full);
$totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;

if ($por_pagina > 0) {
    $usuarios = array_slice($usuarios_full, $offset, $por_pagina);
} else {
    $usuarios = $usuarios_full;
}

$roles = $modelo->obtenerRoles();

require_once $basePath . "usuarioLista.php";