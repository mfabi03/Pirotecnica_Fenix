<?php
// app/Controller/notaentradaController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\NotaentradaModel;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\ProveedoresModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\Validador;
use Exception;

$type = $_POST['type'] ?? $_GET['type'] ?? 'list';
$id = $_POST['id'] ?? $_GET['id'] ?? null;
$esSolicitudJson = in_array($type, ['show_json', 'detalle_json', 'estado_ajax'], true);

error_reporting(E_ALL);
ini_set('display_errors', $esSolicitudJson ? '0' : '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$id_usuario_sesion = (int)($_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? 0);

// 🔒 Bloqueo de cajero (rol 3)
if ((int)$id_rol_actual === 3) {
    if ($esSolicitudJson) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'No tienes permiso para acceder a las Notas de Entrada.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $_SESSION['error'] = 'No tienes permiso para acceder a las Notas de Entrada.';
    header('Location: ?url=dashboard');
    exit();
}

// 1. MODELOS (heredan conexión)
$modelo         = new NotaentradaModel();
$productoModel  = new ProductoModel();
$proveedorModel = new ProveedoresModel();

// 2. PERMISOS (una sola vez)
$permisoHelper = new PermisoHelper();
$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Notas de Entrada', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Notas de Entrada', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Notas de Entrada', 'eliminar');

$error = null;
$success = null;
$nota = null;
$notas = [];
$productos = [];
$proveedores = [];
$resumen = [];
$tipo_mensaje = '';

$rutaRaiz = dirname(__DIR__, 2);

// ==========================================
// AJAX: CAMBIO DE ESTADO (inactivar)
// ==========================================
if ($type === 'estado_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    $accion = $_POST['accion'] ?? '';

    try {
        if (!$puede_eliminar) {
            http_response_code(403);
            throw new Exception('No tienes permiso para cambiar el estado de las notas.');
        }
        $id = Validador::validarId($id, 'ID de nota');

        if ($accion === 'inactivar') {
            $motivo = Validador::requerido($_POST['motivo'] ?? '', 'motivo de anulación');
            $modelo->anularNotaEntrada($id, $motivo, $modelo->obtenerIdUsuarioValido($id_usuario_sesion));
            echo json_encode(['success' => true, 'message' => 'Nota anulada y stock revertido.'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            throw new Exception('Acción de estado no válida.');
        }
    } catch (Exception $e) {
        if (http_response_code() < 400) {
            http_response_code(500);
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

// ==========================================
// POST
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ACTUALIZAR
    if ($type === 'update') {
        if (!$puede_editar) {
            $_SESSION['mensaje'] = 'No tienes permiso para actualizar notas de entrada.';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: ?url=notaentrada&type=list');
            exit();
        }
        $idNota = $_POST['id_nota_entrada'] ?? '';
        try {
            $idNota = Validador::validarId($idNota, 'ID de nota');
            $fechaEntrada = Validador::validarFecha($_POST['fecha_ingreso'] ?? '', 'fecha de ingreso', 'Y-m-d\TH:i');
            $idProveedor = Validador::validarId($_POST['id_proveedor'] ?? null, 'proveedor');

            $productosIds = $_POST['detalle_producto'] ?? [];
            $cantidades   = $_POST['detalle_cantidad'] ?? [];
            $costos       = $_POST['detalle_costo'] ?? [];
            if (!is_array($productosIds) || !is_array($cantidades) || !is_array($costos)
                || count($productosIds) !== count($cantidades) || count($productosIds) !== count($costos)) {
                throw new Exception('Los detalles de productos enviados no son válidos.');
            }

            $detalles = [];
            foreach ($productosIds as $indice => $productoId) {
                $detalles[] = [
                    'id_producto'   => Validador::validarId($productoId, 'producto'),
                    'cantidad'      => (int)Validador::numeroPositivo($cantidades[$indice] ?? 0, 'cantidad', 1),
                    'costo_unitario'=> Validador::numeroPositivo($costos[$indice] ?? 0, 'costo unitario', 0)
                ];
            }

            $descripcion = Validador::limpiar($_POST['descripcion'] ?? '');

            $modelo->actualizarNotaEntradaCompleta($idNota, [
                'fecha_ingreso' => date('Y-m-d H:i:s', strtotime($fechaEntrada)),
                'id_proveedor'  => $idProveedor,
                'descripcion'   => $descripcion
            ], $detalles);

            $_SESSION['mensaje'] = 'Nota de Entrada actualizada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: ?url=notaentrada&type=list');
            exit();
        } catch (Exception $e) {
            $error = 'No se pudo actualizar la nota: ' . $e->getMessage();
            $type = 'edit';
            $id = is_scalar($idNota) ? (string)$idNota : '';
        }
    }

    // REGISTRAR NOTA
    if ($type === 'store') {
        if (!$puede_crear) {
            $_SESSION['mensaje'] = 'No tienes permiso para crear notas de entrada.';
            $_SESSION['tipo_mensaje'] = 'danger';
            header('Location: ?url=notaentrada&type=list');
            exit();
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
                        'id_producto'    => Validador::validarId($productos_ids[$i], 'producto'),
                        'cantidad'       => (int)Validador::numeroPositivo($cantidades[$i], 'cantidad', 1),
                        'costo_unitario' => Validador::numeroPositivo($costos[$i] ?? 0, 'costo unitario', 0)
                    ];
                }
            }

            if (empty($detalles)) {
                throw new Exception("Debe agregar al menos un producto.");
            }

            $idProveedor = Validador::validarId($_POST['id_proveedor'] ?? null, 'proveedor');
            $fecha = Validador::validarFecha($_POST['fecha_ingreso'] ?? '', 'fecha de ingreso', 'Y-m-d\TH:i');

            $datos = [
                'fecha_ingreso' => date('Y-m-d H:i:s', strtotime($fecha)),
                'id_proveedor'  => $idProveedor,
                'descripcion'   => Validador::limpiar($_POST['descripcion'] ?? '')
            ];

            $modelo->guardarNotaEntradaCompleta($datos, $detalles, $idUsuario);

            $_SESSION['mensaje'] = '✅ Nota de Entrada registrada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?url=notaentrada&type=list");
            exit();
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }

    // REGISTRO RÁPIDO DE PRODUCTO
    if ($type === 'store_rapido_producto') {
        if (!$puede_crear) {
            $_SESSION['error'] = '❌ No tienes permiso para registrar productos.';
            header("Location: ?url=notaentrada&type=create");
            exit;
        }
        try {
            $cantidadOriginal    = (int)($_POST['cantidad'] ?? 1);
            $costoOriginal       = Validador::numeroPositivo($_POST['costo_unitario'] ?? 0, 'costo unitario', 0);
            $idProveedorOriginal = Validador::validarId($_POST['id_proveedor'] ?? null, 'proveedor');

            $datosProducto = [
                'descripcion'      => Validador::requerido($_POST['descripcion'] ?? '', 'descripción'),
                'id_categoria'     => Validador::validarId($_POST['id_categoria'] ?? null, 'categoría'),
                'id_proveedor'     => $idProveedorOriginal,
                'cantidad'         => 0,
                'costo_unitario'   => $costoOriginal,
                'especificaciones' => ''
            ];

            $id_producto = $productoModel->registrarProducto($datosProducto);
            if (!$id_producto) {
                throw new Exception("Error al guardar el producto");
            }

            $_SESSION['nuevo_producto_id'] = $id_producto;
            $_SESSION['nuevo_producto_nombre'] = $datosProducto['descripcion'];
            $_SESSION['nuevo_producto_costo'] = $costoOriginal;
            $_SESSION['mensaje_rapido'] = "✅ Producto '{$datosProducto['descripcion']}' registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';

            $return = $_REQUEST['return'] ?? 'notaentrada';
            $params = [
                'url'          => $return,
                'type'         => 'create',
                'id_producto'  => $id_producto,
                'id_proveedor' => $idProveedorOriginal,
                'cantidad'     => $cantidadOriginal,
                'costo'        => $costoOriginal
            ];
            header("Location: ?" . http_build_query($params));
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = '❌ ' . $e->getMessage();
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }
            header("Location: ?url=notaentrada&type=create");
            exit;
        }
    }

    // REGISTRO RÁPIDO DE PROVEEDOR
    if ($type === 'store_rapido_proveedor') {
        if (!$puede_crear) {
            $_SESSION['error'] = '❌ No tienes permiso para registrar proveedores.';
            header("Location: ?url=notaentrada&type=create");
            exit;
        }
        try {
            $datosProveedor = [
                'rif'                => Validador::requerido($_POST['rif'] ?? '', 'RIF'),
                'razon_social'       => Validador::requerido($_POST['razon_social'] ?? '', 'razón social'),
                'numero_contacto'    => Validador::requerido($_POST['numero_contacto'] ?? '', 'número de contacto'),
                'direccion'          => Validador::requerido($_POST['direccion'] ?? '', 'dirección'),
                'correo_electronico' => Validador::limpiar($_POST['correo_electronico'] ?? '')
            ];

            $id_proveedor = $proveedorModel->registrarProveedor($datosProveedor);
            if (!$id_proveedor) {
                throw new Exception("Error al guardar el proveedor");
            }

            $_SESSION['nuevo_proveedor_id'] = (int)$id_proveedor;
            $_SESSION['nuevo_proveedor_nombre'] = $datosProveedor['razon_social'];
            $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datosProveedor['razon_social']}' registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';

            $return = $_REQUEST['return'] ?? 'notaentrada';
            header("Location: ?url=" . urlencode($return) . "&type=create&id_proveedor=" . (int)$id_proveedor);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = '❌ ' . $e->getMessage();
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }
            header("Location: ?url=notaentrada&type=create");
            exit;
        }
    }

    // ANULAR
    if ($type === 'anular') {
        if (!$puede_eliminar) {
            $_SESSION['mensaje'] = 'No tienes permiso para anular notas de entrada.';
            $_SESSION['tipo_mensaje'] = 'danger';
            header("Location: ?url=notaentrada&type=list");
            exit();
        }
        try {
            $id = Validador::validarId($_POST['id_nota_entrada'] ?? null, 'ID de nota');
            $motivo = Validador::requerido($_POST['motivo_eliminacion'] ?? $_POST['motivo_anulacion'] ?? '', 'motivo de anulación');
            $idUsuario = $modelo->obtenerIdUsuarioValido($id_usuario_sesion);

            $modelo->anularNotaEntrada($id, $motivo, $idUsuario);

            $_SESSION['contador_anulaciones_notaentrada'] = (int)($modelo->getResumen()['total_anuladas'] ?? 0);
            $_SESSION['mensaje'] = '✅ Nota de Entrada anulada exitosamente. El stock ha sido revertido.';
            $_SESSION['tipo_mensaje'] = 'warning';
            header("Location: ?url=notaentrada&type=list");
            exit();
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
}

// ==========================================
// AJAX: DETALLE JSON
// ==========================================
if ($esSolicitudJson) {
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', '0');

    try {
        $id = Validador::validarId($id, 'ID de nota');
        $notaJson = $modelo->obtenerNotaEntradaPorId($id);
        if (!$notaJson) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Nota no encontrada'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
        }
        echo json_encode(['success' => true, 'nota' => $notaJson], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Throwable $e) {
        error_log('Error al obtener detalle JSON de nota de entrada: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo cargar el detalle de la nota'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

// ==========================================
// VISTAS
// ==========================================
$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'nota_entrada';

// PDF
if ($type === 'generar_pdf' && $id) {
    $archivoPdf = $baseViewPath . DIRECTORY_SEPARATOR . 'generar_pdf_entrada.php';
    if (!file_exists($archivoPdf)) {
        $archivoPdf = $baseViewPath . DIRECTORY_SEPARATOR . 'generar_pdf.php';
    }
    if (file_exists($archivoPdf)) {
        require_once $archivoPdf;
        exit();
    }
    die("ERROR: No se encuentra el archivo generar_pdf_entrada.php ni generar_pdf.php en: " . $baseViewPath);
}

// CREAR
if ($type === 'create') {
    try {
        $productos   = $modelo->obtenerProductos();
        $proveedores = $modelo->obtenerProveedores();
        $categorias  = $productoModel->obtenerCategorias();
    } catch (Exception $e) {
        die("ERROR al obtener datos: " . $e->getMessage());
    }

    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "registroNotaEntradaView.php";
    if (!file_exists($viewFile)) {
        die("ERROR: No se encuentra registroNotaEntradaView.php");
    }
    require_once $viewFile;
    exit();
}

// EDITAR
if ($type === 'edit' && $id) {
    if (!$puede_editar) {
        $_SESSION['error'] = 'No tienes permiso para editar notas de entrada.';
        header('Location: ?url=notaentrada&type=list');
        exit();
    }
    try {
        $id = Validador::validarId($id, 'ID de nota');
        $nota = $modelo->obtenerNotaEntradaPorId($id);
        if (!$nota || (int)$nota['eliminado'] === 1) {
            throw new Exception('La nota no existe o ya fue anulada.');
        }
        $productos   = $modelo->obtenerProductos();
        $proveedores = $modelo->obtenerProveedores();
    } catch (Exception $e) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = 'No se pudo cargar la nota para editar: ' . $e->getMessage();
        } else {
            $_SESSION['error'] = 'No se pudo abrir la nota para editar: ' . $e->getMessage();
            header('Location: ?url=notaentrada&type=list');
            exit();
        }
    }

    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . 'editarNotaEntradaView.php';
    if (!file_exists($viewFile)) {
        die('ERROR: No se encuentra editarNotaEntradaView.php');
    }
    require_once $viewFile;
    exit();
}

// VER DETALLE
if ($type === 'show' && $id) {
    try {
        $id = Validador::validarId($id, 'ID de nota');
        $nota = $modelo->obtenerNotaEntradaPorId($id);
        if (!$nota) {
            die("ERROR: Nota de entrada no encontrada.");
        }
    } catch (Exception $e) {
        die("ERROR al obtener nota: " . $e->getMessage());
    }

    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "detalleNotaEntradaView.php";
    if (!file_exists($viewFile)) {
        die("ERROR: No se encuentra detalleNotaEntradaView.php");
    }
    require_once $viewFile;
    exit();
}

// LISTAR
try {
    $buscar = Validador::limpiar($_GET['busqueda'] ?? $_GET['buscar'] ?? '');
    $estado_nota = !empty($_GET['ver_anuladas']) ? 'inactivos' : 'activos';
    $notas_full = !empty($buscar)
        ? $modelo->buscarNotasEntrada($buscar, $estado_nota)
        : $modelo->obtenerNotasEntrada($estado_nota);
    $resumen = $modelo->getResumen();

    $por_pagina    = (int)($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;

    $totalRegistros = is_array($notas_full) ? count($notas_full) : 0;
    $totalPaginas   = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    $notas          = $por_pagina > 0 ? array_slice($notas_full, $offset, $por_pagina) : $notas_full;

    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
    }
    if (isset($_SESSION['tipo_mensaje'])) {
        $tipo_mensaje = $_SESSION['tipo_mensaje'];
        unset($_SESSION['tipo_mensaje']);
    }
} catch (Exception $e) {
    die("ERROR al obtener notas: " . $e->getMessage());
}

$viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "listNotaEntradaView.php";
if (!file_exists($viewFile)) {
    die("ERROR: No se encuentra listNotaEntradaView.php");
}
require_once $viewFile;