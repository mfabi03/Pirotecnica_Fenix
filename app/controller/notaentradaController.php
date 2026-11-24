<?php
// app/Controller/notaentradaController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\NotaentradaModel;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\ProveedoresModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Traits\ValidadorTrait;
use Exception;

$type = $_POST['type'] ?? $_GET['type'] ?? 'list';
$id = $_POST['id'] ?? $_GET['id'] ?? null;
$esSolicitudJson = in_array($type, ['show_json', 'detalle_json', 'estado_ajax'], true);

if (session_status() === PHP_SESSION_NONE) session_start();

$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$id_usuario_sesion = (int)($_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? 0);

if ((int)$id_rol_actual === 3) {
    if ($esSolicitudJson) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'No tienes permiso para acceder a las Notas de Entrada.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $_SESSION['error'] = 'No tienes permiso para acceder a las Notas de Entrada.';
    header('Location: ?url=dashboard'); exit();
}

$modelo         = new NotaentradaModel();
$productoModel  = new ProductoModel();
$proveedorModel = new ProveedoresModel();
$val = new class { use ValidadorTrait; };

$permisoHelper = new PermisoHelper();
$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Notas de Entrada', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Notas de Entrada', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Notas de Entrada', 'eliminar');

$error = null; $success = null; $nota = null;
$notas = []; $productos = []; $proveedores = []; $resumen = []; $tipo_mensaje = '';
$rutaRaiz = dirname(__DIR__, 2);

/* ============================================================
 * ENDPOINT AJAX: DETALLE DE NOTA (JSON) — para el modal
 * URL: ?url=notaentrada&type=detalle_json&id=123
 * ============================================================ */
if ($type === 'detalle_json') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    try {
        $idNota = $val->validarId($id, 'ID de nota');
        $notaJson = $modelo->obtenerNotaEntradaPorId($idNota);
        if (!$notaJson) {
            throw new Exception('Nota de entrada no encontrada.');
        }

        // Normalizar campos que espera el modal
        $notaJson['cliente']   = $notaJson['razon_social'] ?? 'N/A';
        $notaJson['documento'] = $notaJson['rif'] ?? 'N/A';
        $notaJson['fecha']     = $notaJson['fecha_ingreso'] ?? '';
        $notaJson['encargado'] = $notaJson['encargado_nombre'] ?? 'N/A';
        $notaJson['tipo_base'] = 'Entrada';

        $detalles = $notaJson['detalles'] ?? [];
        if (is_array($detalles)) {
            foreach ($detalles as &$d) {
                $d['nombre_producto'] = $d['nombre_producto'] ?? 'N/A';
                $d['categoria']       = $d['nombre_categoria'] ?? 'Sin categoría';
                $d['cantidad']        = (int)($d['cantidad'] ?? 0);
                $d['costo_unitario']  = (float)($d['costo_unitario'] ?? 0);

                $esp = trim((string)($d['especificaciones'] ?? ''));
                $d['especificaciones'] = $esp !== '' ? $esp : ($notaJson['descripcion'] ?? '');
                $d['descripcion']      = $d['especificaciones'];
            }
            unset($d);
        } else {
            $notaJson['detalles'] = [];
        }

        echo json_encode([
            'success' => true,
            'nota'    => $notaJson,
            'tipo'    => 'Entrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Throwable $e) {
        http_response_code(200); // 200 para que fetch no lance por status
        echo json_encode([
            'success' => false,
            'error'   => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* ============================================================
 * ENDPOINT AJAX: ANULAR NOTA (JSON)
 * ============================================================ */
if ($type === 'estado_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    $accion = $_POST['accion'] ?? '';
    try {
        if (!$puede_eliminar) { http_response_code(403); throw new Exception('No tienes permiso para cambiar el estado de las notas.'); }
        $id = $val->validarId($id, 'ID de nota');
        if ($accion === 'inactivar') {
            $motivo = $val->requerido($_POST['motivo'] ?? '', 'motivo de anulación');
            $modelo->anularNotaEntrada($id, $motivo, $modelo->obtenerIdUsuarioValido($id_usuario_sesion));
            echo json_encode(['success' => true, 'message' => 'Nota anulada y stock revertido.'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            throw new Exception('Acción de estado no válida.');
        }
    } catch (Exception $e) {
        if (http_response_code() < 400) http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

/* ============================================================
 * POST (store / update)
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($type === 'update') {
        if (!$puede_editar) {
            $_SESSION['mensaje'] = 'No tienes permiso para actualizar notas de entrada.';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: ?url=notaentrada&type=list'); exit();
        }
        $idNota = $_POST['id_nota_entrada'] ?? '';
        try {
            $idNota = $val->validarId($idNota, 'ID de nota');
            $fechaEntrada = $val->validarFechaOExcepcion($_POST['fecha_ingreso'] ?? '', 'fecha de ingreso', 'Y-m-d\TH:i');
            $idProveedor = $val->validarId($_POST['id_proveedor'] ?? null, 'proveedor');

            $productosIds = $_POST['detalle_producto'] ?? [];
            $cantidades   = $_POST['detalle_cantidad'] ?? [];
            $costos       = $_POST['detalle_costo'] ?? [];
            if (!is_array($productosIds) || !is_array($cantidades) || !is_array($costos)
                || count($productosIds) !== count($cantidades) || count($productosIds) !== count($costos)) {
                throw new Exception('Los detalles de productos enviados no son válidos.');
            }

            $detalles = [];
            foreach ($productosIds as $i => $productoId) {
                $detalles[] = [
                    'id_producto'    => $val->validarId($productoId, 'producto'),
                    'cantidad'       => (int)$val->numeroPositivo($cantidades[$i] ?? 0, 'cantidad', 1),
                    'costo_unitario' => $val->numeroPositivo($costos[$i] ?? 0, 'costo unitario', 0)
                ];
            }
            $descripcion = $val->limpiar($_POST['descripcion'] ?? '');

            $modelo->actualizarNotaEntradaCompleta($idNota, [
                'fecha_ingreso' => date('Y-m-d H:i:s', strtotime($fechaEntrada)),
                'id_proveedor'  => $idProveedor,
                'descripcion'   => $descripcion
            ], $detalles);

            $_SESSION['mensaje'] = 'Nota de Entrada actualizada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: ?url=notaentrada&type=list'); exit();
        } catch (Exception $e) {
            $error = 'No se pudo actualizar la nota: ' . $e->getMessage();
            $type = 'edit';
            $id = is_scalar($idNota) ? (string)$idNota : '';
        }
    }

    if ($type === 'store') {
        if (!$puede_crear) {
            $_SESSION['mensaje'] = 'No tienes permiso para crear notas de entrada.';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: ?url=notaentrada&type=list'); exit();
        }
        try {
            $idUsuario = $modelo->obtenerIdUsuarioValido($id_usuario_sesion);
            $detalles = [];
            $productos_ids = $_POST['detalle_producto'] ?? [];
            $cantidades    = $_POST['detalle_cantidad'] ?? [];
            $costos        = $_POST['detalle_costo'] ?? [];

            for ($i = 0; $i < count($productos_ids); $i++) {
                if (!empty($productos_ids[$i]) && !empty($cantidades[$i])) {
                    $detalles[] = [
                        'id_producto'    => $val->validarId($productos_ids[$i], 'producto'),
                        'cantidad'       => (int)$val->numeroPositivo($cantidades[$i], 'cantidad', 1),
                        'costo_unitario' => $val->numeroPositivo($costos[$i] ?? 0, 'costo unitario', 0)
                    ];
                }
            }
            if (empty($detalles)) throw new Exception("Debe agregar al menos un producto.");

            $idProveedor = $val->validarId($_POST['id_proveedor'] ?? null, 'proveedor');
            $fechaEntrada = $val->validarFechaOExcepcion($_POST['fecha_ingreso'] ?? date('Y-m-d\TH:i'), 'fecha de ingreso', 'Y-m-d\TH:i');
            $descripcion = $val->limpiar($_POST['descripcion'] ?? '');

            $idNota = $modelo->guardarNotaEntradaCompleta([
                'fecha_ingreso' => date('Y-m-d H:i:s', strtotime($fechaEntrada)),
                'id_proveedor'  => $idProveedor,
                'descripcion'   => $descripcion
            ], $detalles, $idUsuario);

            $_SESSION['mensaje'] = 'Nota de Entrada registrada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: ?url=notaentrada&type=list'); exit();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

/* ============================================================
 * VISTAS
 * ============================================================ */
$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'nota_entrada';

if ($type === 'create') {
    try {
        $productos = $modelo->obtenerProductos();
        $proveedores = $modelo->obtenerProveedores();
    } catch (Exception $e) { die("ERROR al obtener datos: " . $e->getMessage()); }
    require_once $baseViewPath . "/registroNotaEntradaView.php"; exit();
}

if ($type === 'edit' && $id) {
    try {
        $id = $val->validarId($id, 'ID de nota');
        $nota = $modelo->obtenerNotaEntradaPorId($id);
        if (!$nota) die("ERROR: Nota no encontrada.");
        $productos = $modelo->obtenerProductos();
        $proveedores = $modelo->obtenerProveedores();
    } catch (Exception $e) { die("ERROR al obtener nota: " . $e->getMessage()); }
    require_once $baseViewPath . "/editarNotaEntradaView.php"; exit();
}

if ($type === 'show' && $id) {
    try {
        $id = $val->validarId($id, 'ID de nota');
        $nota = $modelo->obtenerNotaEntradaPorId($id);
        if (!$nota) die("ERROR: Nota no encontrada.");
    } catch (Exception $e) { die("ERROR al obtener nota: " . $e->getMessage()); }
    require_once $baseViewPath . "/detalleNotaEntradaView.php"; exit();
}

try {
    $buscar = $val->limpiar($_GET['busqueda'] ?? $_GET['buscar'] ?? '');
    $estado_nota = !empty($_REQUEST['ver_anuladas']) ? 'inactivos' : 'activos';
    $notas_full = !empty($buscar) ? $modelo->buscarNotasEntrada($buscar, $estado_nota) : $modelo->obtenerNotasEntrada($estado_nota);
    $resumen = $modelo->getResumen();
    $por_pagina = (int)($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $por_pagina;
    $totalRegistros = count($notas_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    $notas = $por_pagina > 0 ? array_slice($notas_full, $offset, $por_pagina) : $notas_full;

    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        $mensaje = $_SESSION['mensaje'];
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
    }
    if ($error) { $mensaje = $error; $tipo_mensaje = 'danger'; }
} catch (Exception $e) { die("ERROR al obtener notas: " . $e->getMessage()); }

require_once $baseViewPath . "/listNotaEntradaView.php";
exit();