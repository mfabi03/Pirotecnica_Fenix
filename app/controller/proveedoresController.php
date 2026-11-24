<?php
// app/Controller/proveedoresController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\ProveedoresModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Traits\ValidadorTrait;
use Exception;

$modelo = new ProveedoresModel();
$val = new class { use ValidadorTrait; };

if (session_status() === PHP_SESSION_NONE) session_start();
$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$permisoHelper = new PermisoHelper();

$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Proveedores', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Proveedores', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Proveedores', 'eliminar');

$type   = $_POST['type'] ?? $_GET['type'] ?? 'list';
$action = $_GET['action'] ?? '';
$esSolicitudAjax = in_array($type, ['listar_ajax', 'detalle_ajax', 'estado_ajax', 'store_ajax', 'update_ajax', 'store_rapido_ajax'], true)
    || in_array($action, ['check_rif', 'check_unique'], true);

error_reporting(E_ALL);
ini_set('display_errors', $esSolicitudAjax ? '0' : '1');

$rutaRaiz = dirname(__DIR__, 2);
$error = null; $success = null; $proveedor = null; $proveedores = [];

// ============================================
// AJAX
// ============================================
if ($action === 'check_unique') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $campo = $val->limpiar($_GET['campo'] ?? '');
        $valor = $val->limpiar($_GET['valor'] ?? '');
        $excluirId = $_GET['excluir_id'] ?? '';

        if ($valor === '') throw new Exception('Valor vacío.');
        if ($excluirId !== '' && (!ctype_digit((string)$excluirId) || (int)$excluirId < 1)) throw new Exception('ID a excluir no válido.');
        if ($campo !== 'telefono_proveedor') throw new Exception('Campo de validación no válido.');

        $existe = $modelo->existeNumeroContacto($valor, $excluirId === '' ? null : (int)$excluirId);
        echo json_encode(['existe' => $existe, 'campo' => $campo, 'valor' => $valor], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        if (http_response_code() < 400) http_response_code(400);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($action === 'check_rif') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $tipo_doc = $val->limpiar($_GET['tipo_documento'] ?? 'J');
        $digito   = $val->limpiar($_GET['digito_rif'] ?? '');
        $rif = $val->documentoCompleto($tipo_doc, $_GET['valor'] ?? '', $digito !== '' ? $digito : null);
        $existe = $modelo->existeRif($rif);
        echo json_encode(['existe' => $existe, 'campo' => 'rif', 'valor' => $rif], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        if (http_response_code() < 400) http_response_code(500);
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($type === 'listar_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        echo json_encode(['success' => true, 'proveedores' => $modelo->obtenerProveedores()], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($type === 'detalle_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $id = $val->validarId($_POST['id_proveedor'] ?? null, 'ID de proveedor');
        $detalle = $modelo->obtenerProveedorPorId($id, true);
        if (!$detalle) throw new Exception('Proveedor no encontrado.');
        echo json_encode(['success' => true, 'proveedor' => $detalle], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        if (http_response_code() < 400) http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

// 🔹 CAMBIO: estado_ajax unificado — maneja inactivar y reactivar con el mismo endpoint
if ($type === 'estado_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$puede_eliminar) throw new Exception('No tienes permiso para cambiar el estado de proveedores.');
        $id = $val->validarId($_POST['id_proveedor'] ?? null, 'ID de proveedor');
        $accion = $val->limpiar($_POST['accion'] ?? '');
        if ($accion === 'inactivar') { $modelo->eliminarProveedor($id); $mensaje = 'Proveedor inactivado.'; }
        elseif ($accion === 'reactivar') { $modelo->reactivarProveedor($id); $mensaje = 'Proveedor reactivado.'; }
        else throw new Exception('Acción de estado no válida.');
        $_SESSION['mensaje'] = $mensaje; $_SESSION['tipo_mensaje'] = 'success';
        echo json_encode(['success' => true, 'message' => $mensaje], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        if (http_response_code() < 400) http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

if (in_array($type, ['store_ajax', 'update_ajax'], true)) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $esActualizacion = ($type === 'update_ajax');
        if ($esActualizacion && !$puede_editar) throw new Exception('No tienes permiso para actualizar proveedores.');
        if (!$esActualizacion && !$puede_crear) throw new Exception('No tienes permiso para registrar proveedores.');

        $tipo_doc = $val->limpiar($_POST['tipo_documento'] ?? 'J');
        $digito   = $val->limpiar($_POST['digito_rif'] ?? '');
        $rif = $val->documentoCompleto($tipo_doc, $_POST['rif'] ?? '', $digito !== '' ? $digito : null);

        $datos = [
            'rif' => $rif,
            'razon_social' => $val->requerido($_POST['razon_social'] ?? '', 'razón social'),
            'numero_contacto' => $val->requerido($_POST['numero_contacto'] ?? '', 'número de contacto'),
            'direccion' => $val->requerido($_POST['direccion'] ?? '', 'dirección'),
            'correo_electronico' => $val->limpiar($_POST['correo_electronico'] ?? '')
        ];

        if ($esActualizacion) {
            $idProveedor = $val->validarId($_POST['id_proveedor'] ?? null, 'ID de proveedor');
            $datos['eliminado'] = $_POST['eliminado'] ?? '0';
            $datos['nombre_contacto'] = 'Proveedor';
            $datos['apellido_contacto'] = '';
            $modelo->actualizarProveedor($idProveedor, $datos);
            $mensaje = 'Proveedor actualizado.';
            $_SESSION['mensaje'] = $mensaje; $_SESSION['tipo_mensaje'] = 'success';
            echo json_encode(['success' => true, 'message' => $mensaje], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } else {
            $idProveedor = $modelo->registrarProveedor($datos);
            $mensaje = 'Proveedor registrado.';
            $_SESSION['mensaje'] = $mensaje; $_SESSION['tipo_mensaje'] = 'success';
            echo json_encode(['success' => true, 'id_proveedor' => (int)$idProveedor, 'razon_social' => $datos['razon_social'], 'rif' => $datos['rif'], 'message' => $mensaje], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    } catch (Exception $e) {
        if (http_response_code() < 400) http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

if ($type === 'store_rapido_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$puede_crear) throw new Exception("No tienes permiso para registrar proveedores.");
        $tipo_doc = $val->limpiar($_POST['tipo_documento'] ?? 'J');
        $digito   = $val->limpiar($_POST['digito_rif'] ?? '');
        $rif = $val->documentoCompleto($tipo_doc, $_POST['rif'] ?? '', $digito !== '' ? $digito : null);

        $datos = [
            'rif' => $rif,
            'razon_social' => $val->requerido($_POST['razon_social'] ?? '', 'razón social'),
            'numero_contacto' => $val->requerido($_POST['numero_contacto'] ?? '', 'número de contacto'),
            'direccion' => $val->requerido($_POST['direccion'] ?? '', 'dirección'),
            'correo_electronico' => $val->limpiar($_POST['correo_electronico'] ?? ''),
            'nombre_contacto' => $val->limpiar($_POST['nombre_contacto'] ?? 'Proveedor'),
            'apellido_contacto' => $val->limpiar($_POST['apellido_contacto'] ?? '')
        ];
        $id_proveedor = $modelo->registrarProveedor($datos);
        if (!$id_proveedor) throw new Exception("No se pudo registrar el proveedor.");
        echo json_encode(['success' => true, 'id_proveedor' => (int)$id_proveedor, 'razon_social' => $datos['razon_social'], 'rif' => $datos['rif']], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ============================================
// POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($type === 'store') {
        if (!$puede_crear) { $_SESSION['mensaje'] = 'No tienes permiso para registrar proveedores.'; $_SESSION['tipo_mensaje'] = 'danger'; header("Location: ?url=proveedores&type=list"); exit(); }
        try {
            $tipo_doc = $val->limpiar($_POST['tipo_documento'] ?? 'J');
            $digito   = $val->limpiar($_POST['digito_rif'] ?? '');
            $rif = $val->documentoCompleto($tipo_doc, $_POST['rif'] ?? '', $digito !== '' ? $digito : null);

            $datos = [
                'rif' => $rif,
                'razon_social' => $val->requerido($_POST['razon_social'] ?? '', 'razón social'),
                'numero_contacto' => $val->requerido($_POST['numero_contacto'] ?? '', 'número de contacto'),
                'direccion' => $val->requerido($_POST['direccion'] ?? '', 'dirección'),
                'correo_electronico' => $val->limpiar($_POST['correo_electronico'] ?? '')
            ];

            $resultado = $modelo->registrarProveedor($datos);
            if (!$resultado) throw new Exception('No se pudo registrar el proveedor.');

            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                $id_proveedor = (int)$resultado;
                $_SESSION['nuevo_proveedor_id'] = $id_proveedor;
                $_SESSION['nuevo_proveedor_nombre'] = $datos['razon_social'];
                $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datos['razon_social']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                header('Location: ?url=' . urlencode($return) . '&type=create&id_proveedor=' . $id_proveedor);
                exit;
            }

            $_SESSION['mensaje'] = 'Proveedor registrado.'; $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=proveedores&type=list"); exit();
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage(); $_SESSION['tipo_mensaje'] = 'danger';
            header("Location: ?url=proveedores&type=list"); exit();
        }
    }

    if ($type === 'store_rapido') {
        if (!$puede_crear) { $_SESSION['mensaje_rapido'] = "❌ No tienes permiso para registrar proveedores."; $_SESSION['tipo_rapido'] = 'danger'; header("Location: ?url=proveedores&type=list"); exit(); }
        try {
            $tipo_doc = $val->limpiar($_POST['tipo_documento'] ?? 'J');
            $digito   = $val->limpiar($_POST['digito_rif'] ?? '');
            $rif = $val->documentoCompleto($tipo_doc, $_POST['rif'] ?? '', $digito !== '' ? $digito : null);

            $datosProveedor = [
                'rif' => $rif,
                'razon_social' => $val->requerido($_POST['razon_social'] ?? '', 'razón social'),
                'numero_contacto' => $val->requerido($_POST['numero_contacto'] ?? '', 'número de contacto'),
                'direccion' => $val->requerido($_POST['direccion'] ?? '', 'dirección'),
                'correo_electronico' => $val->limpiar($_POST['correo_electronico'] ?? ''),
                'nombre_contacto' => $val->limpiar($_POST['nombre_contacto'] ?? 'Proveedor'),
                'apellido_contacto' => $val->limpiar($_POST['apellido_contacto'] ?? '')
            ];
            $resultado = $modelo->registrarProveedor($datosProveedor);
            if (!$resultado) throw new Exception('No se pudo registrar el proveedor.');

            $idProveedor = (int)$resultado;
            $_SESSION['nuevo_proveedor_id'] = $idProveedor;
            $_SESSION['nuevo_proveedor_nombre'] = $datosProveedor['razon_social'];
            $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datosProveedor['razon_social']}' registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';

            $return = $_REQUEST['return'] ?? null;
            if ($return) { header('Location: ?url=' . urlencode($return) . '&type=create&id_proveedor=' . $idProveedor); exit; }
            header("Location: ?url=proveedores&type=list"); exit();
        } catch (Exception $e) {
            $_SESSION['mensaje_rapido'] = "❌ " . $e->getMessage(); $_SESSION['tipo_rapido'] = 'danger';
            $return = $_REQUEST['return'] ?? null;
            if ($return) { header("Location: ?url=" . urlencode($return) . "&type=create"); exit; }
            header("Location: ?url=proveedores&type=list"); exit;
        }
    }

    if ($type === 'update') {
        if (!$puede_editar) { $_SESSION['mensaje'] = 'No tienes permiso para actualizar proveedores.'; $_SESSION['tipo_mensaje'] = 'danger'; header("Location: ?url=proveedores&type=list"); exit(); }
        try {
            $id = $val->validarId($_POST['id_proveedor'] ?? null, 'ID de proveedor');
            $tipo_doc = $val->limpiar($_POST['tipo_documento'] ?? 'J');
            $digito   = $val->limpiar($_POST['digito_rif'] ?? '');
            $rif = $val->documentoCompleto($tipo_doc, $_POST['rif'] ?? '', $digito !== '' ? $digito : null);

            $datos = [
                'rif' => $rif,
                'razon_social' => $val->requerido($_POST['razon_social'] ?? '', 'razón social'),
                'numero_contacto' => $val->requerido($_POST['numero_contacto'] ?? '', 'número de contacto'),
                'direccion' => $val->requerido($_POST['direccion'] ?? '', 'dirección'),
                'correo_electronico' => $val->limpiar($_POST['correo_electronico'] ?? ''),
                'eliminado' => $val->limpiar($_POST['eliminado'] ?? '0')
            ];

            $modelo->actualizarProveedor($id, $datos);
            $_SESSION['mensaje'] = 'Proveedor actualizado.'; $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=proveedores&type=list"); exit();
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage(); $_SESSION['tipo_mensaje'] = 'danger';
            header("Location: ?url=proveedores&type=list"); exit();
        }
    }

    if ($type === 'delete') {
        if (!$puede_eliminar) { $_SESSION['mensaje'] = 'No tienes permiso para eliminar proveedores.'; $_SESSION['tipo_mensaje'] = 'danger'; header("Location: ?url=proveedores&type=list"); exit(); }
        try {
            $id = $val->validarId($_POST['id_proveedor'] ?? null, 'ID de proveedor');
            if ($modelo->eliminarProveedor($id)) { $_SESSION['mensaje'] = 'Proveedor eliminado.'; $_SESSION['tipo_mensaje'] = 'success'; }
            else throw new Exception('No se pudo eliminar.');
        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage(); $_SESSION['tipo_mensaje'] = 'danger';
        }
        header("Location: ?url=proveedores&type=list"); exit();
    }
}

// ============================================
// VISTAS
// ============================================
if ($type === 'create') { require_once $rutaRaiz . '/app/view/proveedores/registroProveedoresView.php'; exit(); }
if ($type === 'edit') {
    try { $id = $val->validarId($_GET['id'] ?? null, 'ID de proveedor'); $proveedor = $modelo->obtenerProveedorPorId($id); if (!$proveedor) throw new Exception("Proveedor no encontrado."); }
    catch (Exception $e) { die("ERROR: " . $e->getMessage()); }
    require_once $rutaRaiz . '/app/view/proveedores/editarProveedoresView.php'; exit();
}
if ($type === 'show') {
    try { $id = $val->validarId($_GET['id'] ?? null, 'ID de proveedor'); $proveedor = $modelo->obtenerProveedorPorId($id); if (!$proveedor) throw new Exception("Proveedor no encontrado."); }
    catch (Exception $e) { die("ERROR: " . $e->getMessage()); }
    require_once $rutaRaiz . '/app/view/proveedores/verProveedorView.php'; exit();
}

try {
    $buscar = $val->limpiar($_GET['busqueda'] ?? $_GET['buscar'] ?? '');
    $verEliminados = !empty($_GET['ver_eliminados']);
    $proveedores_full = !empty($buscar) ? $modelo->buscarProveedores($buscar, true) : $modelo->obtenerProveedores(true);
    $proveedores_full = array_values(array_filter($proveedores_full, fn($p) => (int)($p['eliminado'] ?? 0) === ($verEliminados ? 1 : 0)));

    $por_pagina = (int)($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $por_pagina;
    $totalRegistros = count($proveedores_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    $proveedores = $por_pagina > 0 ? array_slice($proveedores_full, $offset, $por_pagina) : $proveedores_full;

    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
    }
} catch (Exception $e) { die("ERROR al listar: " . $e->getMessage()); }

require_once $rutaRaiz . '/app/view/proveedores/listProveedoresView.php';