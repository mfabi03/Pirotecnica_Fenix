<?php
// app/Controller/notasalidaController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\notasalidaModel;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\clientesModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\Validador;
use Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutaRaiz = dirname(__DIR__, 2);

// 1. MODELOS (heredan conexión)
$modelo        = new notasalidaModel();
$productoModel = new ProductoModel();
$clienteModel  = new clientesModel();

// 2. PERMISOS
$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$id_usuario_sesion = (int)($_SESSION['id_usuario'] ?? 0);
$permisoHelper = new PermisoHelper();

$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Notas de Salida', 'crear');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Notas de Salida', 'eliminar');

$type = $_GET['type'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = null;
$success = null;
$nota = null;
$notas = [];
$productos = [];
$clientes = [];
$resumen = [];
$tipo_mensaje = '';

$esAdmin = ((int)$id_rol_actual === 1) || (strtolower($_SESSION['nombre_rol'] ?? '') === 'administrador');
$id_usuario_filtro = $esAdmin ? null : $id_usuario_sesion;

// ==========================================
// VALIDACIÓN DE CIERRE DE CAJA (movida al modelo)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'store') {
    try {
        if ($modelo->cajaCerradaHoy($id_usuario_sesion)) {
            $_SESSION['mensaje'] = "Error: Ya has cerrado la caja de hoy. No puedes registrar más notas de salida hasta mañana.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: ?url=notasalida&type=list");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al verificar cierre de caja: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=notasalida&type=list");
        exit();
    }
}

// ==========================================
// AJAX: DETALLE
// ==========================================
if ($type === 'detalle_json' && $id) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $id = Validador::validarId($id, 'ID de nota');
        $nota = $modelo->obtenerNotaPorId($id);
        if (!$nota) {
            echo json_encode(['success' => false, 'error' => 'Nota no encontrada'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(['success' => true, 'nota' => $nota], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ==========================================
// AJAX: CLIENTE RÁPIDO
// ==========================================
if ($type === 'store_rapido_cliente_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!$permisoHelper->tienePermiso($id_rol_actual, 'Clientes', 'crear')) {
            throw new Exception("No tienes permiso para registrar clientes.");
        }

        $tipoCliente = Validador::limpiar($_POST['tipo_cliente'] ?? 'natural');

        if ($tipoCliente === 'juridico') {
            $datos = [
                'rif'                => Validador::requerido($_POST['rif'] ?? '', 'RIF'),
                'razon_social'       => Validador::requerido($_POST['razon_social'] ?? '', 'razón social'),
                'telefono'           => Validador::limpiar($_POST['telefono'] ?? ''),
                'correo_electronico' => Validador::limpiar($_POST['correo_electronico'] ?? ''),
                'direccion'          => Validador::limpiar($_POST['direccion'] ?? ''),
                'tipo'               => 'juridico'
            ];
            if (!Validador::validarRIF($datos['rif'])) {
                throw new Exception("El formato del RIF no es válido.");
            }
            $id_cliente = $clienteModel->registrarClienteJuridico($datos);
            $nombre_cliente = $datos['razon_social'];
            $documento = $datos['rif'];
            $tipo_label = 'Jurídico';
        } else {
            $fechaNac = $_POST['fecha_de_nacimiento'] ?? date('Y-m-d', strtotime('-18 years'));
            $datos = [
                'cedula'              => Validador::requerido($_POST['cedula'] ?? '', 'cédula'),
                'nombre'              => Validador::requerido($_POST['nombre'] ?? '', 'nombre'),
                'apellido'            => Validador::requerido($_POST['apellido'] ?? '', 'apellido'),
                'telefono'            => Validador::limpiar($_POST['telefono'] ?? ''),
                'correo_electronico'  => Validador::limpiar($_POST['correo_electronico'] ?? ''),
                'direccion'           => Validador::limpiar($_POST['direccion'] ?? ''),
                'fecha_de_nacimiento' => $fechaNac,
                'tipo'                => 'natural'
            ];
            if (!Validador::validarCedula($datos['cedula'])) {
                throw new Exception("El formato de la cédula no es válido.");
            }
            if (!Validador::validarEdad($fechaNac, 18)) {
                throw new Exception("El cliente debe ser mayor de edad.");
            }
            $id_cliente = $clienteModel->registrarClienteNatural($datos);
            $nombre_cliente = $datos['nombre'] . ' ' . $datos['apellido'];
            $documento = $datos['cedula'];
            $tipo_label = 'Natural';
        }

        if (!$id_cliente) throw new Exception("No se pudo registrar el cliente.");

        echo json_encode([
            'success'        => true,
            'id_cliente'     => (int)$id_cliente,
            'nombre_cliente' => $nombre_cliente,
            'documento'      => $documento,
            'tipo'           => $tipo_label
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ==========================================
// EXPORTAR CSV
// ==========================================
if ($type === 'exportar_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="notas_salida_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['ID', 'Fecha', 'Cliente', 'Documento', 'Productos', 'Categorías', 'Total Unidades', 'Encargado']);

    $notas_export = $modelo->listarNotasSalida($id_usuario_filtro);
    foreach ($notas_export as $n) {
        $clienteNombre = !empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : trim($n['cliente_nombre'] . ' ' . $n['cliente_apellido']);
        $encargado = trim($n['usuario_nombre'] . ' ' . $n['usuario_apellido']);
        fputcsv($output, [
            $n['id_nota_salida'], $n['fecha'], $clienteNombre, $n['cedula'] ?? 'N/A',
            $n['productos_lista'], $n['categorias_lista'], $n['total_unidades'], $encargado
        ]);
    }
    fclose($output);
    exit();
}

// ==========================================
// POST: STORE / ELIMINAR
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // REGISTRAR SALIDA
    if ($type === 'store') {
        if (!$puede_crear) {
            $_SESSION['mensaje'] = 'No tienes permiso para crear notas de salida.';
            $_SESSION['tipo_mensaje'] = 'danger';
            header("Location: ?url=notasalida&type=list");
            exit();
        }
        try {
            $idUsuario = $modelo->obtenerIdUsuarioValido($id_usuario_sesion);
            $detalles = [];
            $productos_ids = $_POST['detalle_producto'] ?? [];
            $cantidades    = $_POST['detalle_cantidad'] ?? [];

            for ($i = 0; $i < count($productos_ids); $i++) {
                if (!empty($productos_ids[$i]) && !empty($cantidades[$i])) {
                    $detalles[] = [
                        'id_producto' => Validador::validarId($productos_ids[$i], 'producto'),
                        'cantidad'    => (int)Validador::numeroPositivo($cantidades[$i], 'cantidad', 1)
                    ];
                }
            }

            if (empty($detalles)) throw new Exception("Debe agregar al menos un producto.");

            $idCliente = Validador::validarId($_POST['id_cliente'] ?? null, 'cliente');
            $datos = ['id_cliente' => $idCliente];

            $resultado = $modelo->registrarSalida($datos, $detalles, $idUsuario);

            if ($resultado) {
                $_SESSION['mensaje'] = 'Nota de Salida registrada exitosamente.';
                $_SESSION['tipo_mensaje'] = 'success';
                header("Location: ?url=notasalida&type=list");
                exit();
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // ELIMINAR (anular)
    if ($type === 'eliminar') {
        if (!$puede_eliminar) {
            $_SESSION['mensaje'] = 'No tienes permiso para anular notas de salida.';
            $_SESSION['tipo_mensaje'] = 'danger';
            header("Location: ?url=notasalida&type=list");
            exit();
        }
        try {
            $idNota = Validador::validarId($_POST['id_nota_salida'] ?? null, 'ID de nota');
            $motivo = Validador::requerido($_POST['motivo_eliminacion'] ?? $_POST['motivo_anulacion'] ?? '', 'motivo de anulación');
            $idUsuario = $modelo->obtenerIdUsuarioValido($id_usuario_sesion);

            $resultado = $modelo->eliminarNota($idNota, $motivo);
            if ($resultado) {
                $_SESSION['mensaje'] = 'Nota de Salida anulada exitosamente.';
                $_SESSION['tipo_mensaje'] = 'warning';
                header("Location: ?url=notasalida&type=list&ver_anuladas=1");
                exit();
            }
            throw new Exception("No se pudo anular la nota.");
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// ==========================================
// VISTAS
// ==========================================
$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'nota_salida';

// PDF
if ($type === 'generar_pdf' && $id) {
    $archivoPdf = $baseViewPath . DIRECTORY_SEPARATOR . 'generar_pdf.php';
    if (file_exists($archivoPdf)) {
        require_once $archivoPdf;
        exit();
    }
    die("ERROR: No se encuentra generar_pdf.php");
}

// CREAR
if ($type === 'create') {
    try {
        $productos   = $modelo->obtenerProductosConCategoria();
        $clientes    = $clienteModel->obtenerClientes(true);
        $categorias  = $productoModel->obtenerCategorias();
    } catch (Exception $e) {
        die("ERROR al obtener datos: " . $e->getMessage());
    }
    $viewFile = $baseViewPath . "/registroNotaSalidaView.php";
    if (!file_exists($viewFile)) die("ERROR: No se encuentra registroNotaSalidaView.php");
    require_once $viewFile;
    exit();
}

// SHOW
if ($type === 'show' && $id) {
    try {
        $id = Validador::validarId($id, 'ID de nota');
        $nota = $modelo->obtenerNotaPorId($id);
        if (!$nota) die("ERROR: Nota de salida no encontrada.");
    } catch (Exception $e) {
        die("ERROR al obtener nota: " . $e->getMessage());
    }
    $viewFile = $baseViewPath . "/detalleNotasalidaView.php";
    if (!file_exists($viewFile)) die("ERROR: No se encuentra detalleNotasalidaView.php");
    require_once $viewFile;
    exit();
}

// LISTAR
try {
    $buscar = Validador::limpiar($_GET['busqueda'] ?? $_GET['buscar'] ?? '');
    $estado_nota = !empty($_REQUEST['ver_anuladas']) ? 'inactivos' : 'activos';

    $notas_full = !empty($buscar)
        ? $modelo->buscarNotasSalida($buscar, $id_usuario_filtro)
        : $modelo->listarNotasSalida($id_usuario_filtro);

    if ($estado_nota === 'activos') {
        $notas_full = array_values(array_filter($notas_full, fn($n) => ((int)($n['eliminado'] ?? 0)) === 0));
    } else {
        $notas_full = array_values(array_filter($notas_full, fn($n) => ((int)($n['eliminado'] ?? 0)) === 1));
    }

    $resumen = $modelo->getResumen($id_usuario_filtro);

    $por_pagina = (int)($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $por_pagina;

    $totalRegistros = count($notas_full);
    $totalPaginas   = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    $notas = $por_pagina > 0 ? array_slice($notas_full, $offset, $por_pagina) : $notas_full;

    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        $mensaje = $_SESSION['mensaje'];
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
    }

    if ($error) {
        $mensaje = $error;
        $tipo_mensaje = 'danger';
    }
} catch (Exception $e) {
    die("ERROR al obtener notas: " . $e->getMessage());
}

require_once $baseViewPath . "/listNotasalidaView.php";