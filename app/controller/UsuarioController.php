<?php
// app/Controller/UsuarioController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\UsuarioModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Traits\ValidadorTrait;
use Exception;

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    header('Location: ?url=login');
    exit();
}

$modelo = new UsuarioModel();
$val = new class { use ValidadorTrait; };

$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$permisoHelper = new PermisoHelper();

$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Usuarios', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Usuarios', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Usuarios', 'eliminar');

$action = $_GET['action'] ?? 'lista';
$id = $_GET['id'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

$busqueda = $val->limpiar($_GET['busqueda'] ?? $_GET['buscar'] ?? '');

// AJAX: CHECK USUARIO
if ($action === 'check_usuario') {
    header('Content-Type: application/json');
    try {
        $usuario = $val->limpiar($_GET['valor'] ?? '');
        $idExcluir = isset($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : null;
        if ($usuario === '') { echo json_encode(['existe' => false]); exit; }
        echo json_encode(['existe' => $modelo->existeUsuario($usuario, $idExcluir)]);
    } catch (Exception $e) {
        echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// AJAX: CHECK CÉDULA
if ($action === 'check_cedula') {
    header('Content-Type: application/json');
    try {
        $cedula = $val->limpiar($_GET['valor'] ?? '');
        $idExcluir = isset($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : null;
        if ($cedula === '') { echo json_encode(['existe' => false]); exit; }
        $idPersonaExcluir = $idExcluir !== null ? $modelo->obtenerIdPersonaPorUsuario($idExcluir) : null;
        echo json_encode(['existe' => $modelo->existeCedula($cedula, $idPersonaExcluir)]);
    } catch (Exception $e) {
        echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// AJAX: SHOW
if ($action === 'show_ajax') {
    header('Content-Type: application/json');
    try {
        $id = $val->validarId($_GET['id'] ?? null, 'ID de usuario');
        $usuario = $modelo->obtenerUsuarioPorId($id);
        if (!$usuario) throw new Exception("Usuario no encontrado");
        echo json_encode(['success' => true, 'data' => [
            'id_usuario' => (int)$usuario['id_usuario'],
            'usuario' => $usuario['usuario'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'cedula' => $usuario['cedula'],
            'telefono' => $usuario['telefono'],
            'correo_electronico' => $usuario['correo_electronico'],
            'direccion' => $usuario['direccion'] ?? '',
            'id_rol' => (int)$usuario['id_rol'],
            'rol_nombre' => $usuario['rol_nombre'] ?? ''
        ]]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// AJAX: STORE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'store_ajax') {
    header('Content-Type: application/json');
    try {
        if (!$puede_crear) throw new Exception('No tienes permisos para registrar usuarios.');

        $tipoDoc = $val->limpiar($_POST['tipo_documento'] ?? 'V');
        $cedula  = $val->documentoCompleto($tipoDoc, $_POST['cedula'] ?? '');

        $datosPersona = [
            'nombre'    => $val->requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido'  => $val->limpiar($_POST['apellido'] ?? ''),
            'cedula'    => $cedula,
            'telefono'  => $val->limpiar($_POST['telefono'] ?? ''),
            'correo'    => $val->limpiar($_POST['correo_electronico'] ?? ''),
            'direccion' => $val->limpiar($_POST['direccion'] ?? '')
        ];
        $datosUsuario = [
            'usuario' => $val->requerido($_POST['usuario'] ?? '', 'usuario'),
            'clave'   => password_hash($val->requerido($_POST['clave'] ?? '', 'contraseña'), PASSWORD_DEFAULT),
            'id_rol'  => $val->validarId($_POST['id_rol'] ?? null, 'rol')
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

// AJAX: UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'update_ajax') {
    header('Content-Type: application/json');
    try {
        if (!$puede_editar) throw new Exception('No tienes permisos para actualizar usuarios.');
        $id = $val->validarId($_POST['id_usuario'] ?? null, 'ID de usuario');

        $tipoDoc = $val->limpiar($_POST['tipo_documento'] ?? 'V');
        $cedula  = $val->documentoCompleto($tipoDoc, $_POST['cedula'] ?? '');

        $datos = [
            'usuario'            => $val->requerido($_POST['usuario'] ?? '', 'usuario'),
            'nombre'             => $val->requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido'           => $val->limpiar($_POST['apellido'] ?? ''),
            'cedula'             => $cedula,
            'telefono'           => $val->limpiar($_POST['telefono'] ?? ''),
            'correo_electronico' => $val->limpiar($_POST['correo_electronico'] ?? ''),
            'direccion'          => $val->limpiar($_POST['direccion'] ?? ''),
            'id_rol'             => $val->validarId($_POST['id_rol'] ?? null, 'rol'),
            'clave'              => $val->limpiar($_POST['clave'] ?? '')
        ];
        $resultado = $modelo->actualizarUsuario($id, $datos);
        if ($resultado) echo json_encode(['success' => true, 'mensaje' => "Usuario actualizado exitosamente"]);
        else throw new Exception($modelo->getLastError() ?: "No se pudo actualizar el usuario.");
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// AJAX: DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'delete_ajax') {
    header('Content-Type: application/json');
    try {
        if (!$puede_eliminar) throw new Exception('No tienes permisos para inactivar usuarios.');
        $id = $val->validarId($_POST['id_usuario'] ?? null, 'ID de usuario');
        if ($id == $_SESSION['id_usuario']) throw new Exception("No puedes inactivar tu propio usuario.");
        $resultado = $modelo->eliminarUsuario($id);
        if ($resultado) echo json_encode(['success' => true, 'mensaje' => "Usuario inactivado exitosamente"]);
        else throw new Exception($modelo->getLastError() ?: "No se pudo inactivar el usuario.");
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// AJAX: REACTIVAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'reactivar_ajax') {
    header('Content-Type: application/json');
    try {
        if (!$puede_editar) throw new Exception('No tienes permisos para reactivar usuarios.');
        $id = $val->validarId($_POST['id_usuario'] ?? null, 'ID de usuario');
        $resultado = $modelo->reactivarUsuario($id);
        if ($resultado) echo json_encode(['success' => true, 'mensaje' => "Usuario reactivado exitosamente"]);
        else throw new Exception($modelo->getLastError() ?: "No se pudo reactivar el usuario.");
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// POST legacy: guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    try {
        if (!$puede_crear) throw new Exception('No tienes permisos para registrar usuarios.');
        $tipoDoc = $val->limpiar($_POST['tipo_documento'] ?? 'V');
        $cedula  = $val->documentoCompleto($tipoDoc, $_POST['cedula'] ?? '');

        $datosPersona = [
            'nombre' => $val->requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido' => $val->limpiar($_POST['apellido'] ?? ''),
            'cedula' => $cedula,
            'telefono' => $val->limpiar($_POST['telefono'] ?? ''),
            'correo' => $val->limpiar($_POST['correo_electronico'] ?? ''),
            'direccion' => $val->limpiar($_POST['direccion'] ?? '')
        ];
        $datosUsuario = [
            'usuario' => $val->requerido($_POST['usuario'] ?? '', 'usuario'),
            'clave' => password_hash($val->requerido($_POST['clave'] ?? '', 'contraseña'), PASSWORD_DEFAULT),
            'id_rol' => $val->validarId($_POST['id_rol'] ?? null, 'rol')
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

// POST legacy: actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar') {
    try {
        if (!$puede_editar) throw new Exception('No tienes permisos para actualizar usuarios.');
        $id = $val->validarId($_POST['id_usuario'] ?? null, 'ID de usuario');
        $tipoDoc = $val->limpiar($_POST['tipo_documento'] ?? 'V');
        $cedula  = $val->documentoCompleto($tipoDoc, $_POST['cedula'] ?? '');
        $datos = [
            'usuario' => $val->requerido($_POST['usuario'] ?? '', 'usuario'),
            'nombre' => $val->limpiar($_POST['nombre'] ?? ''),
            'apellido' => $val->limpiar($_POST['apellido'] ?? ''),
            'cedula' => $cedula,
            'telefono' => $val->limpiar($_POST['telefono'] ?? ''),
            'correo_electronico' => $val->limpiar($_POST['correo_electronico'] ?? ''),
            'direccion' => $val->limpiar($_POST['direccion'] ?? ''),
            'id_rol' => $val->validarId($_POST['id_rol'] ?? null, 'rol'),
            'clave' => $val->limpiar($_POST['clave'] ?? '')
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

// POST legacy: eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    try {
        if (!$puede_eliminar) throw new Exception('No tienes permisos para inactivar usuarios.');
        $id = $val->validarId($_POST['id_usuario'] ?? null, 'ID de usuario');
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

// Bloquear rutas legacy
if (in_array($action, ['registrar', 'crear', 'editar', 'ver'], true)) {
    header("Location: ?url=usuarios&action=lista");
    exit();
}

// Vistas
$basePath = __DIR__ . "/../view/configuracion/";

if ($action === 'lista' || $action === '') {
    if (!empty($busqueda)) $usuarios_full = $modelo->buscarUsuarios($busqueda);
    else $usuarios_full = $modelo->obtenerUsuariosConPersona();

    if (!is_array($usuarios_full)) $usuarios_full = [];

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
    $usuarios = $por_pagina > 0 ? array_slice($usuarios_full, $offset, $por_pagina) : $usuarios_full;
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
$usuarios = $por_pagina > 0 ? array_slice($usuarios_full, $offset, $por_pagina) : $usuarios_full;
$roles = $modelo->obtenerRoles();
require_once $basePath . "usuarioLista.php";