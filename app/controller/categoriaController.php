<?php
<<<<<<< HEAD
// app/Controller/CategoriaController.php
=======
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
namespace App\Pirotecnicafenix\Controller;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
<<<<<<< HEAD
use App\Pirotecnicafenix\Model\CategoriaModel;
use Exception;

// 1. INICIAR SESIÓN
=======
use App\Pirotecnicafenix\Model\clientesModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\CheckPermiso;
use Exception;

// Iniciar sesión
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
// 2. VERIFICAR AUTENTICACIÓN (SOLO LOGIN, SIN RESTRICCIÓN DE ROL)

if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    $_SESSION['error_permiso'] = "Debes iniciar sesión para acceder a esta sección.";
    header('Location: ?url=login');
    exit();
}

// 3. CARGAR MODELO
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'CategoriaModel.php';
=======
// 1. CARGA DEL MODELO

$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'clientesModel.php';
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
<<<<<<< HEAD
    die("ERROR: No se encuentra CategoriaModel.php en: " . $pathModel);
}

// 4. INICIALIZACIÓN
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new CategoriaModel($db);
=======
    die("ERROR CRÍTICO: No se encuentra el archivo: " . $pathModel);
}

// 2. INICIALIZACIÓN DE CONEXIÓN Y MODELO

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new \App\Pirotecnicafenix\Model\clientesModel($db);
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

<<<<<<< HEAD
// 5. OBTENER ACCIÓN (GET o POST)
$action = $_GET['action'] ?? $_POST['action'] ?? 'lista';
$id = $_GET['id'] ?? $_POST['id_categoria'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
$busqueda = $_GET['busqueda'] ?? '';
=======
$type = $_GET['type'] ?? 'list';
$id = $_GET['id'] ?? null;
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d

unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);

<<<<<<< HEAD
// 6. PROCESAR POST (GUARDAR, ACTUALIZAR, ELIMINAR)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? $action;
    
    // === GUARDAR === (Accesible para todos los usuarios)
    if ($accion === 'guardar' || $accion === 'create') {
        try {
            if (empty(trim($_POST['nombre_categoria']))) {
                throw new Exception("El nombre de la categoría es obligatorio.");
            }

            $datos = [
                'nombre_categoria' => trim($_POST['nombre_categoria']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            $resultado = $modelo->registrarCategoria($datos);
            
            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Categoría registrada exitosamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al registrar categoría";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }
    
    // === ACTUALIZAR === (Accesible para todos los usuarios)
    if ($accion === 'actualizar' || $accion === 'edit' || $accion === 'update') {
        try {
            $id = $_POST['id_categoria'] ?? null;
            if (!$id) throw new Exception("ID de categoría no proporcionado");

            if (empty(trim($_POST['nombre_categoria']))) {
                throw new Exception("El nombre de la categoría es obligatorio.");
            }

            $datos = [
                'nombre_categoria' => trim($_POST['nombre_categoria']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            $resultado = $modelo->actualizarCategoria($id, $datos);
            
            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Categoría actualizada exitosamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar categoría";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
        exit();
    }
    
    // === ELIMINAR === (Accesible para todos los usuarios)
    if ($accion === 'eliminar' || $accion === 'delete') {
        try {
            $id = $_POST['id_categoria'] ?? null;
            if (!$id || !is_numeric($id) || $id <= 0) {
                throw new Exception("ID de categoría inválido");
            }

            $resultado = $modelo->eliminarCategoria($id);
            
            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Categoría eliminada exitosamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar categoría";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        header("Location: ?url=categorias");
=======
// Parámetros de búsqueda
$busqueda = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));
$tipo = trim((string) ($_GET['tipo'] ?? 'todos'));

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
    $return = $_REQUEST['return'] ?? null;
    if ($return) {
        header("Location: ?url=" . urlencode($return) . "&type=create");
    } else {
        header("Location: ?url=clientes&type=list");
    }
    exit();
}

// REGISTRO RÁPIDO CLIENTE 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $type === 'store_rapido') {
    CheckPermiso::verificar($db, 'Clientes', 'crear', '?url=clientes&type=list');
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
                $return = $_REQUEST['return'] ?? null;
                if ($return) {
                    header("Location: ?url=" . urlencode($return) . "&type=create");
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
    $return = $_REQUEST['return'] ?? null;
    if ($return) {
        header("Location: ?url=" . urlencode($return) . "&type=create");
    } else {
        header("Location: ?url=clientes&type=list");
    }
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
        $_SESSION['mensaje'] = "Error al editar: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
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
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
        exit();
    }
}

<<<<<<< HEAD
// 7. CARGAR VISTAS (GET)

$basePath = __DIR__ . "/../view/configuracion/";

// === LISTA === (Visible para todos)
if ($action === 'lista' || $action === '' || $action === 'list') {
    if (!empty($busqueda)) {
        $categorias_full = $modelo->buscarCategorias($busqueda);
    } else {
        $categorias_full = $modelo->obtenerCategorias();
    }

    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset = ($pagina - 1) * $por_pagina;
    if ($por_pagina > 0) {
        $categorias = array_slice($categorias_full, $offset, $por_pagina);
    } else {
        $categorias = $categorias_full;
    }
    
    if (!is_array($categorias)) {
        $categorias = [];
    }
    require_once $basePath . "listCategoriaView.php";
    exit();
}

// === REGISTRAR === (Visible para todos)
if ($action === 'registrar' || $action === 'crear' || $action === 'create') {
    require_once $basePath . "registrarCategoriaView.php";
    exit();
}

// === VER === (Visible para todos)
if ($action === 'ver' || $action === 'show') {
    if (!$id) {
        $_SESSION['mensaje'] = "ID de categoría no proporcionado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=categorias");
        exit();
    }
    $categoria = $modelo->obtenerCategoriaPorId($id);
    if (!$categoria) {
        $_SESSION['mensaje'] = "Categoría no encontrada";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=categorias");
        exit();
    }
    require_once $basePath . "detalleCategoriaView.php";
    exit();
}

// === EDITAR === (Visible para todos)
if ($action === 'editar' || $action === 'edit') {
    if (!$id) {
        $_SESSION['mensaje'] = "ID de categoría no proporcionado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=categorias");
        exit();
    }
    $categoria = $modelo->obtenerCategoriaPorId($id);
    if (!$categoria) {
        $_SESSION['mensaje'] = "Categoría no encontrada";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ?url=categorias");
        exit();
    }
    require_once $basePath . "editarCategoriaView.php";
    exit();
}

// === DEFAULT: LISTA ===
$categorias = $modelo->obtenerCategorias();
if (!is_array($categorias)) {
    $categorias = [];
}
require_once $basePath . "listCategoriaView.php";
=======
// LISTA
if ($type === 'list' || $type === '') {
    $busqueda_trim = is_string($busqueda) ? trim($busqueda) : '';
    $tipo_param = $tipo ?? 'todos';

    if ($busqueda_trim === '' && ($tipo_param === 'todos' || $tipo_param === '')) {
        $clientes_full = $modelo->obtenerClientes();
    } else {
        $clientes_full = $modelo->buscarClientesFiltrados($busqueda_trim, $tipo_param);
    }

    // Paginación
    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
    $total_registros = is_array($clientes_full) ? count($clientes_full) : 0;
    $offset = ($pagina - 1) * $por_pagina;
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
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
