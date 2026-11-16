<?php
// app/Controller/clientesController.php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Model\clientesModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\Validador;
use Exception;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 1. Sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Modelo (hereda conexión)
$modelo = new clientesModel();

// 3. Permisos (una sola vez)
$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$permisoHelper = new PermisoHelper();

$puede_crear    = $permisoHelper->tienePermiso($id_rol_actual, 'Clientes', 'crear');
$puede_editar   = $permisoHelper->tienePermiso($id_rol_actual, 'Clientes', 'actualizar');
$puede_eliminar = $permisoHelper->tienePermiso($id_rol_actual, 'Clientes', 'eliminar');

// 4. Entrada
$type     = $_GET['type'] ?? 'list';
$id       = $_GET['id'] ?? null;
$mensaje  = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

$busqueda = Validador::limpiar($_REQUEST['busqueda'] ?? $_REQUEST['buscar'] ?? '');
$tipo     = Validador::limpiar($_REQUEST['tipo'] ?? 'todos');

$estado_cliente = strtolower(Validador::limpiar($_REQUEST['estado_cliente'] ?? 'activos'));
if (!in_array($estado_cliente, ['todos', 'activos', 'inactivos'], true)) {
    $estado_cliente = 'activos';
}

/* ============================================================
 *  HELPERS INTERNOS DEL CONTROLADOR
 * ============================================================ */
function _clientesRedirPost($return, $idCliente = null)
{
    if ($return) {
        $param = $idCliente ? "&id_cliente=" . $idCliente : "";
        return "?url=" . urlencode($return) . "&type=create" . $param;
    }
    return "?url=clientes&type=list";
}

function _clientesDetectarDuplicado($msg, $esEdicion = false, $formModal = null)
{
    $map = [
        'DUPLICADO_CEDULA:' => ['campo' => 'cedula', 'etiqueta' => 'cédula'],
        'DUPLICADO_RIF:'    => ['campo' => 'rif',    'etiqueta' => 'RIF'],
    ];

    foreach ($map as $prefijo => $info) {
        if (strpos($msg, $prefijo) === 0) {
            $campo  = $info['campo'];
            $sufijo = $esEdicion ? ' en otro cliente' : '';

            $_SESSION['errores_campo'] = [
                $campo => 'Esta ' . $info['etiqueta'] . ' ya está registrada' . $sufijo
            ];

            if ($formModal) {
                $_SESSION['form_modal'] = $formModal;
                $_SESSION['form_data']  = $_POST;
            }

            return true;
        }
    }
    return false;
}

// ==========================================
// ENDPOINT: CHECK DUPLICADO (AJAX)
// ==========================================
if ($type === 'check_duplicado') {
    header('Content-Type: application/json; charset=utf-8');

    if (!$permisoHelper->tienePermiso($id_rol_actual, 'Clientes', 'leer')) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para consultar clientes.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $campo     = strtolower(Validador::limpiar($_GET['campo'] ?? ''));
        $valor     = Validador::limpiar($_GET['valor'] ?? '');
        $excluirId = isset($_GET['excluir_id']) && is_numeric($_GET['excluir_id'])
            ? (int) $_GET['excluir_id']
            : null;

        if (!in_array($campo, ['cedula', 'rif'], true)) {
            throw new Exception('Campo inválido');
        }
        if ($valor === '') {
            throw new Exception('Valor vacío');
        }

        if ($campo === 'cedula') {
            $existe = $modelo->existeCedula($valor, $excluirId);
        } else {
            $existe = $modelo->existeRif($valor, $excluirId);
        }

        echo json_encode([
            'existe' => $existe,
            'campo'  => $campo,
            'valor'  => $valor
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        error_log("Error en check_duplicado: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo verificar'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ==========================================
// POST: ELIMINAR (lógico)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($type === 'delete' || (isset($_POST['accion']) && $_POST['accion'] === 'eliminar'))) {
    if (!$puede_eliminar) {
        $_SESSION['mensaje'] = "No tienes permiso para eliminar clientes.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }
    try {
        $id = Validador::validarId($_POST['id_cliente'] ?? null, 'ID de cliente');
        $resultado = $modelo->eliminarCliente($id);
        $_SESSION['mensaje'] = $resultado ? "✅ Cliente eliminado exitosamente" : "No se pudo eliminar el cliente";
        $_SESSION['tipo_mensaje'] = $resultado ? "success" : "danger";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if ($msg === "No se puede eliminar porque tiene registros asociados") {
            $_SESSION['mensaje'] = "No se puede eliminar porque tiene registros asociados";
            $_SESSION['tipo_mensaje'] = "warning";
        } else {
            $_SESSION['mensaje'] = $msg;
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
    header("Location: ?url=clientes&type=list&estado_cliente=inactivos");
    exit();
}

// ==========================================
// POST: REACTIVAR
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($type === 'reactivar' || (isset($_POST['accion']) && $_POST['accion'] === 'reactivar'))) {
    if (!$puede_editar) {
        $_SESSION['mensaje'] = "No tienes permiso para reactivar clientes.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }
    try {
        $id = Validador::validarId($_POST['id_cliente'] ?? null, 'ID de cliente');
        $modelo->reactivarCliente($id);
        $_SESSION['mensaje'] = "✅ Cliente reactivado exitosamente";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=clientes&type=list&estado_cliente=activos");
    exit();
}

// ==========================================
// POST: REGISTRO NATURAL
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'register_natural') {
    if (!$puede_crear) {
        $_SESSION['mensaje'] = "No tienes permiso para registrar clientes.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }
    $resultado = null;
    try {
        $cedula = Validador::requerido($_POST['cedula'] ?? '', 'cédula');
        if (!Validador::validarCedula($cedula)) {
            throw new Exception("El formato de la cédula no es válido (ej: V-12345678).");
        }

        $fechaNac = Validador::validarFecha($_POST['fecha_de_nacimiento'] ?? '', 'fecha de nacimiento');
        if (!Validador::validarEdad($fechaNac, 18)) {
            throw new Exception("El cliente debe ser mayor de edad (mínimo 18 años).");
        }

        $datos = [
            'cedula'              => $cedula,
            'nombre'              => Validador::requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido'            => Validador::requerido($_POST['apellido'] ?? '', 'apellido'),
            'telefono'            => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo_electronico'  => Validador::limpiar($_POST['correo_electronico'] ?? ''),
            'direccion'           => Validador::limpiar($_POST['direccion'] ?? ''),
            'fecha_de_nacimiento' => $fechaNac
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

// ==========================================
// POST: REGISTRO RÁPIDO CLIENTE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'store_rapido') {
    if (!$puede_crear) {
        $_SESSION['mensaje_rapido'] = "❌ No tienes permiso para registrar clientes.";
        $_SESSION['tipo_rapido'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }
    try {
        $cedula = Validador::requerido($_POST['cedula'] ?? '', 'cédula');
        if (!Validador::validarCedula($cedula)) {
            throw new Exception("El formato de la cédula no es válido.");
        }

        $fechaNac = $_POST['fecha_de_nacimiento'] ?? date('Y-m-d', strtotime('-18 years'));
        if (!Validador::validarEdad($fechaNac, 18)) {
            throw new Exception("El cliente debe ser mayor de edad.");
        }

        $datosCliente = [
            'cedula'              => $cedula,
            'nombre'              => Validador::requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido'            => Validador::requerido($_POST['apellido'] ?? '', 'apellido'),
            'telefono'            => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo_electronico'  => Validador::limpiar($_POST['correo_electronico'] ?? ''),
            'direccion'           => Validador::limpiar($_POST['direccion'] ?? ''),
            'fecha_de_nacimiento' => $fechaNac
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
        }
        throw new Exception("No se pudo registrar el cliente.");
    } catch (Exception $e) {
        $msg = $e->getMessage();

        if (_clientesDetectarDuplicado($msg, false, 'registrar_natural')) {
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
            } else {
                header("Location: ?url=clientes&type=list");
            }
            exit;
        }

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

// ==========================================
// POST: REGISTRO JURÍDICO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'register_juridico') {
    if (!$puede_crear) {
        $_SESSION['mensaje'] = "No tienes permiso para registrar clientes.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }
    $resultado = null;
    try {
        $rif = Validador::requerido($_POST['rif'] ?? '', 'RIF');
        if (!Validador::validarRIF($rif)) {
            throw new Exception("El formato del RIF no es válido (ej: J-12345678-9).");
        }

        $datos = [
            'cedula'             => $rif,
            'rif'                => $rif,
            'razon_social'       => Validador::requerido($_POST['razon_social'] ?? '', 'razón social'),
            'telefono'           => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo_electronico' => Validador::limpiar($_POST['correo_electronico'] ?? ''),
            'direccion'          => Validador::limpiar($_POST['direccion'] ?? '')
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

// ==========================================
// POST: EDITAR NATURAL
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'edit_natural') {
    if (!$puede_editar) {
        $_SESSION['mensaje'] = "No tienes permiso para editar clientes.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }
    try {
        $id = Validador::validarId($_POST['id_cliente'] ?? null, 'ID de cliente');

        $cedula = Validador::requerido($_POST['cedula'] ?? '', 'cédula');
        if (!Validador::validarCedula($cedula)) {
            throw new Exception("El formato de la cédula no es válido.");
        }

        $fechaNac = $_POST['fecha_de_nacimiento'] ?? '';
        if ($fechaNac !== '' && !Validador::validarEdad($fechaNac, 18)) {
            throw new Exception("El cliente debe ser mayor de edad.");
        }

        $datos = [
            'cedula'              => $cedula,
            'nombre'              => Validador::requerido($_POST['nombre'] ?? '', 'nombre'),
            'apellido'            => Validador::requerido($_POST['apellido'] ?? '', 'apellido'),
            'telefono'            => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo_electronico'  => Validador::limpiar($_POST['correo_electronico'] ?? ''),
            'direccion'           => Validador::limpiar($_POST['direccion'] ?? ''),
            'fecha_de_nacimiento' => $fechaNac
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

// ==========================================
// POST: EDITAR JURÍDICO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'edit_juridico') {
    if (!$puede_editar) {
        $_SESSION['mensaje'] = "No tienes permiso para editar clientes.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }
    try {
        $id = Validador::validarId($_POST['id_cliente'] ?? null, 'ID de cliente');

        $rif = Validador::requerido($_POST['rif'] ?? '', 'RIF');
        if (!Validador::validarRIF($rif)) {
            throw new Exception("El formato del RIF no es válido.");
        }

        $datos = [
            'cedula'             => $rif,
            'rif'                => $rif,
            'razon_social'       => Validador::requerido($_POST['razon_social'] ?? '', 'razón social'),
            'telefono'           => Validador::limpiar($_POST['telefono'] ?? ''),
            'correo_electronico' => Validador::limpiar($_POST['correo_electronico'] ?? ''),
            'direccion'          => Validador::limpiar($_POST['direccion'] ?? '')
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

// ==========================================
// VISTAS
// ==========================================
$basePath = __DIR__ . "/../view/clientes/";

// OBTENER CLIENTE PARA DETALLE/EDICIÓN
if (in_array($type, ['view', 'edit', 'edit_juridico'], true) && $id) {
    try {
        $id = Validador::validarId($id, 'ID de cliente');
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "ID de cliente inválido";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=clientes&type=list");
        exit();
    }

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
    $ajax = (!empty($_GET['ajax']) || !empty($_POST['ajax']));

    if ($busqueda === '' && ($tipo === 'todos' || $tipo === '')) {
        $clientes_full = $modelo->obtenerClientes();
    } else {
        $clientes_full = $modelo->buscarClientesFiltrados($busqueda, $tipo);
    }

    if ($estado_cliente === 'activos') {
        $clientes_full = array_values(array_filter($clientes_full, fn($c) => ((int)($c['eliminado'] ?? 0)) === 0));
    } elseif ($estado_cliente === 'inactivos') {
        $clientes_full = array_values(array_filter($clientes_full, fn($c) => ((int)($c['eliminado'] ?? 0)) === 1));
    }

    $por_pagina    = (int) ($_REQUEST['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_REQUEST['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;

    $totalRegistros = count($clientes_full);
    $totalPaginas   = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    $clientes       = $por_pagina > 0 ? array_slice($clientes_full, $offset, $por_pagina) : $clientes_full;

    if ($ajax) {
        ob_start();
        require_once $basePath . "listClienteView.php";
        $html = ob_get_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'html' => $html], JSON_UNESCAPED_UNICODE);
        exit();
    }

    require_once $basePath . "listClienteView.php";
} else {
    switch ($type) {
        case 'register':
        case 'register_juridico':
        case 'view':
        case 'edit':
        case 'edit_juridico':
            header("Location: ?url=clientes&type=list");
            exit();
            break;
        default:
            require_once $basePath . "listClienteView.php";
            break;
    }
}