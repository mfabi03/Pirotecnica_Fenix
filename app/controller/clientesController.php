<?php
namespace App\Pirotecnicafenix\Controller;

// ⚠️ En producción: desactivar display_errors. Solo activar en dev.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\clientesModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\CheckPermiso;
use Exception;

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CARGA DEL MODELO
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'clientesModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR CRÍTICO: No se encuentra el archivo: " . $pathModel);
}

// 2. INICIALIZACIÓN DE CONEXIÓN Y MODELO
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new \App\Pirotecnicafenix\Model\clientesModel($db);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

$type = $_GET['type'] ?? 'list';
$id = $_GET['id'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;

unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);

// Parámetros de búsqueda
$busqueda = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));
$tipo = trim((string) ($_GET['tipo'] ?? 'todos'));

/* ============================================================
 *  HELPER: URL de redirección tras un POST
 * ============================================================ */
function _clientesRedirPost($return, $idCliente = null) {
    if ($return) {
        $param = $idCliente ? "&id_cliente=" . $idCliente : "";
        return "?url=" . urlencode($return) . "&type=create" . $param;
    }
    return "?url=clientes&type=list";
}

/**
 * Detecta si el mensaje de excepción es un duplicado y guarda el error
 * INLINE (para repintarlo en el campo) + los datos del formulario.
 *
 * @param string $msg        Mensaje de la excepción
 * @param bool   $esEdicion  Si es edición
 * @param string $formModal  Identificador del formulario a reabrir:
 *                           'registrar_natural' | 'registrar_juridico'
 *                           'editar_natural'    | 'editar_juridico'
 * @return bool  true si era duplicado
 */
function _clientesDetectarDuplicado($msg, $esEdicion = false, $formModal = null) {
    $map = [
        'DUPLICADO_CEDULA:' => ['campo' => 'cedula', 'etiqueta' => 'cédula'],
        'DUPLICADO_RIF:'    => ['campo' => 'rif',    'etiqueta' => 'RIF'],
    ];

    foreach ($map as $prefijo => $info) {
        if (strpos($msg, $prefijo) === 0) {
            $campo  = $info['campo'];
            $sufijo = $esEdicion ? ' en otro cliente' : '';

            // Error inline por campo
            $_SESSION['errores_campo'] = [
                $campo => 'Esta ' . $info['etiqueta'] . ' ya está registrada' . $sufijo
            ];

            // Qué modal reabrir + qué datos repoblar
            if ($formModal) {
                $_SESSION['form_modal'] = $formModal;
                $_SESSION['form_data']  = $_POST;
            }

            return true;
        }
    }
    return false;
}

/* ============================================================
 *  ENDPOINT AJAX: check_duplicado
 *  Uso:  ?url=clientes&type=check_duplicado&campo=cedula&valor=V-12345678
 *  Resp: {"existe": true|false, "campo": "...", "valor": "..."}
 * ============================================================ */
if ($type === 'check_duplicado') {
    header('Content-Type: application/json; charset=utf-8');

    $campo = strtolower(trim($_GET['campo'] ?? ''));
    $valor = trim($_GET['valor'] ?? '');
    $excluirId = isset($_GET['excluir_id']) && is_numeric($_GET['excluir_id'])
        ? (int) $_GET['excluir_id']
        : null;

    if (!in_array($campo, ['cedula', 'rif'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Campo inválido']);
        exit;
    }

    if ($valor === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Valor vacío']);
        exit;
    }

    try {
        if ($campo === 'cedula') {
            $existe = $modelo->existeCedula($valor, $excluirId);
        } else {
            $existe = $modelo->existeRif($valor, $excluirId);
        }
        echo json_encode([
            'existe' => $existe,
            'campo'  => $campo,
            'valor'  => $valor
        ]);
    } catch (Exception $e) {
        error_log("Error en check_duplicado: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo verificar']);
    }
    exit;
}

// ELIMINAR 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($type === 'delete' || (isset($_POST['accion']) && $_POST['accion'] === 'eliminar'))) {
    CheckPermiso::verificar($db, 'Clientes', 'eliminar', '?url=clientes&type=list');
    try {
        $id = $_POST['id_cliente'] ?? null;
        if (!$id || !is_numeric($id) || $id <= 0) {
            throw new Exception("ID de cliente inválido");
        }
        $resultado = $modelo->eliminarCliente($id);
        $_SESSION['mensaje'] = $resultado ? "✅ Cliente eliminado exitosamente" : "No se pudo eliminar el cliente";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=clientes&type=list");
    exit();
}

// REGISTRO NATURAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'register_natural') {
    CheckPermiso::verificar($db, 'Clientes', 'crear', '?url=clientes&type=list');
    $resultado = null;
    try {
        if (empty(trim($_POST['cedula']))) {
            throw new Exception("La cédula es obligatoria.");
        }

        $datos = [
            'cedula' => trim($_POST['cedula']),
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'telefono' => trim($_POST['telefono']),
            'correo_electronico' => trim($_POST['correo_electronico']),
            'direccion' => trim($_POST['direccion']),
            'fecha_de_nacimiento' => $_POST['fecha_de_nacimiento']
        ];
        
        $resultado = $modelo->registrarClienteNatural($datos);
        $_SESSION['mensaje'] = $resultado ? "✅ Cliente Natural registrado exitosamente" : "Error al registrar";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
        if ($resultado) {
            $_SESSION['nuevo_cliente_id'] = $resultado;
            $_SESSION['nuevo_cliente_nombre'] = $datos['nombre'] . ' ' . $datos['apellido'];
            $_SESSION['mensaje_rapido'] = "✅ Cliente Natural registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (!_clientesDetectarDuplicado($msg, false, 'registrar_natural')) {
            $_SESSION['mensaje'] = "Error al registrar: " . $msg;
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
    $return = $_REQUEST['return'] ?? null;
    header("Location: " . _clientesRedirPost($return, $resultado));
    exit();
}

// REGISTRO RÁPIDO CLIENTE 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'store_rapido') {
    CheckPermiso::verificar($db, 'Clientes', 'crear', '?url=clientes&type=list');
    try {
        if (empty($_POST['cedula']) || empty($_POST['nombre']) || empty($_POST['apellido'])) {
            throw new Exception("La cédula, nombre y apellido son obligatorios.");
        }

        $datosCliente = [
            'cedula' => trim($_POST['cedula']),
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo_electronico' => trim($_POST['correo_electronico'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'fecha_de_nacimiento' => $_POST['fecha_de_nacimiento'] ?? date('Y-m-d', strtotime('-18 years'))
        ];

        $id = $modelo->registrarClienteNatural($datosCliente);
        
        if ($id) {
            $_SESSION['nuevo_cliente_id'] = $id;
            $_SESSION['nuevo_cliente_nombre'] = $datosCliente['nombre'] . ' ' . $datosCliente['apellido'];
            $_SESSION['mensaje_rapido'] = "✅ Cliente registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';
            
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create&id_cliente=" . $id);
                exit;
            }

            header("Location: ?url=clientes&type=list");
            exit;
        } else {
            throw new Exception("No se pudo registrar el cliente.");
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();

        // Duplicado → guardar inline + reabrir modal
        if (_clientesDetectarDuplicado($msg, false, 'registrar_natural')) {
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
            } else {
                header("Location: ?url=clientes&type=list");
            }
            exit;
        }

        // Error genérico → mensaje rápido
        $_SESSION['mensaje_rapido'] = "❌ " . $msg;
        $_SESSION['tipo_rapido'] = 'danger';
        
        $return = $_REQUEST['return'] ?? null;
        if ($return) {
            header("Location: ?url=" . urlencode($return) . "&type=create");
            exit;
        }

        header("Location: ?url=clientes&type=list");
        exit;
    }
}

// REGISTRO JURIDICO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'register_juridico') {
    CheckPermiso::verificar($db, 'Clientes', 'crear', '?url=clientes&type=list');
    $resultado = null;
    try {
        if (empty(trim($_POST['rif']))) {
            throw new Exception("El RIF es obligatorio.");
        }

        $datos = [
            'cedula' => trim($_POST['rif']),
            'rif' => trim($_POST['rif']),
            'razon_social' => trim($_POST['razon_social']),
            'telefono' => trim($_POST['telefono']),
            'correo_electronico' => trim($_POST['correo_electronico']),
            'direccion' => trim($_POST['direccion'])
        ];
        
        $resultado = $modelo->registrarClienteJuridico($datos);
        $_SESSION['mensaje'] = $resultado ? "✅ Cliente Jurídico registrado exitosamente" : "Error al registrar";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
        if ($resultado) {
            $_SESSION['nuevo_cliente_id'] = $resultado;
            $_SESSION['nuevo_cliente_nombre'] = $datos['razon_social'];
            $_SESSION['mensaje_rapido'] = "✅ Cliente Jurídico registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (!_clientesDetectarDuplicado($msg, false, 'registrar_juridico')) {
            $_SESSION['mensaje'] = "Error al registrar: " . $msg;
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
    $return = $_REQUEST['return'] ?? null;
    header("Location: " . _clientesRedirPost($return, $resultado));
    exit();
}

// EDITAR NATURAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'edit_natural') {
    CheckPermiso::verificar($db, 'Clientes', 'actualizar', '?url=clientes&type=list');
    try {
        $id = $_POST['id_cliente'] ?? null;
        if (!$id) throw new Exception("ID de cliente no proporcionado");

        $datos = [
            'cedula' => trim($_POST['cedula']),
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'telefono' => trim($_POST['telefono']),
            'correo_electronico' => trim($_POST['correo_electronico']),
            'direccion' => trim($_POST['direccion']),
            'fecha_de_nacimiento' => $_POST['fecha_de_nacimiento']
        ];

        $modelo->actualizarClienteNatural($id, $datos);
        $_SESSION['mensaje'] = "✅ Cliente Natural editado exitosamente";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (!_clientesDetectarDuplicado($msg, true, 'editar_natural')) {
            $_SESSION['mensaje'] = "Error al editar: " . $msg;
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
    header("Location: ?url=clientes&type=list");
    exit();
}

// EDITAR JURIDICO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'edit_juridico') {
    CheckPermiso::verificar($db, 'Clientes', 'actualizar', '?url=clientes&type=list');
    try {
        $id = $_POST['id_cliente'] ?? null;
        if (!$id) throw new Exception("ID de cliente no proporcionado");

        $datos = [
            'cedula' => trim($_POST['rif']),
            'rif' => trim($_POST['rif']),
            'razon_social' => trim($_POST['razon_social']),
            'telefono' => trim($_POST['telefono']),
            'correo_electronico' => trim($_POST['correo_electronico']),
            'direccion' => trim($_POST['direccion'])
        ];

        $modelo->actualizarClienteJuridico($id, $datos);
        $_SESSION['mensaje'] = "✅ Cliente Jurídico editado exitosamente";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (!_clientesDetectarDuplicado($msg, true, 'editar_juridico')) {
            $_SESSION['mensaje'] = "Error al editar: " . $msg;
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
    header("Location: ?url=clientes&type=list");
    exit();
}

// 4. CARGAR VISTAS
$basePath = __DIR__ . "/../view/clientes/";

// OBTENER CLIENTE PARA DETALLE O EDICIÓN
if (in_array($type, ['view', 'edit', 'edit_juridico']) && $id) {
    $cliente = $modelo->obtenerClientePorId($id);
    if (!$cliente) {
        $_SESSION['mensaje'] = "Cliente no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }
}

// LISTA
if ($type === 'list' || $type === '') {
    $busqueda_trim = is_string($busqueda) ? trim($busqueda) : '';
    $tipo_param = $tipo ?? 'todos';

    if ($busqueda_trim === '' && ($tipo_param === 'todos' || $tipo_param === '')) {
        $clientes_full = $modelo->obtenerClientes();
    } else {
        $clientes_full = $modelo->buscarClientesFiltrados($busqueda_trim, $tipo_param);
    }

    //  PAGINACIÓN COMPLETA
    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    // Total de registros
    $totalRegistros = is_array($clientes_full) ? count($clientes_full) : 0;
    
    // Total de páginas
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    // Cortar el array para la página actual
    if ($por_pagina > 0) {
        $clientes = array_slice($clientes_full, $offset, $por_pagina);
    } else {
        $clientes = $clientes_full;
    }

    require_once $basePath . "listClienteView.php";
} else {
    switch ($type) {
        case 'register':
            require_once $basePath . "registroClientView.php";
            break;
        case 'register_juridico':
            require_once $basePath . "registroClienteJuridicoView.php";
            break;
        case 'view':
            require_once $basePath . "detalleClientView.php";
            break;
        case 'edit':
            require_once $basePath . "editarClientView.php";
            break;
        case 'edit_juridico':
            require_once $basePath . "editarClienteJuridicoView.php";
            break;
        default:
            require_once $basePath . "listClienteView.php";
            break;
    }
}
?>