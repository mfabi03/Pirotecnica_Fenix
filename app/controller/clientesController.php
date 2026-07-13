<?php
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\clientesModel;
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

// ELIMINAR (vía ?url=clientes&type=delete)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'delete') {
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


// ELIMINAR

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
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
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    if (isset($_GET['return'])) {
        header("Location: ?url=" . $_GET['return'] . "&type=create");
    } else {
        header("Location: ?url=clientes&type=list");
    }
    exit();
}

// REGISTRO RÁPIDO CLIENTE 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'store_rapido') {
    try {
        // Validar campos requeridos
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
            //  GUARDAR EN SESIÓN PARA EL RETORNO
            $_SESSION['nuevo_cliente_id'] = $id;
            $_SESSION['nuevo_cliente_nombre'] = $datosCliente['nombre'] . ' ' . $datosCliente['apellido'];
            $_SESSION['mensaje_rapido'] = "✅ Cliente registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';
            
            //  REDIRIGIR DE VUELTA (si viene de registro rápido)
            if (isset($_GET['return'])) {
                header("Location: ?url=" . $_GET['return'] . "&type=create");
                exit;
            }
            
            header("Location: ?url=clientes&type=list");
            exit;
        } else {
            throw new Exception("No se pudo registrar el cliente.");
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_rapido'] = "❌ " . $e->getMessage();
        $_SESSION['tipo_rapido'] = 'danger';
        
        if (isset($_GET['return'])) {
            header("Location: ?url=" . $_GET['return'] . "&type=create");
            exit;
        }
        
        header("Location: ?url=clientes&type=list");
        exit;
    }
}

// REGISTRO JURIDICO

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'register_juridico') {
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
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    if (isset($_GET['return'])) {
        header("Location: ?url=" . urlencode($_GET['return']) . "&type=create");
    } else {
        header("Location: ?url=clientes&type=list");
    }
    exit();
}


// EDITAR NATURAL

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'edit_natural') {
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
        $_SESSION['mensaje'] = "Error al editar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: ?url=clientes&type=list");
    exit();
}


// EDITAR JURIDICO

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'edit_juridico') {
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
        $_SESSION['mensaje'] = "Error al editar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
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
        $clientes = $modelo->obtenerClientes();
    } else {
        $clientes = $modelo->buscarClientesFiltrados($busqueda_trim, $tipo_param);
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