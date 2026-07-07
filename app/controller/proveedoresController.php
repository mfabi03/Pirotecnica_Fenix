<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\proveedoresModel;
use Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión para mensajes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutaRaiz = dirname(__DIR__, 2);

$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'proveedoresModel.php';
if (!file_exists($pathModel)) {
    die("ERROR: No se encuentra proveedoresModel.php en: " . $pathModel);
}
require_once $pathModel;

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new proveedoresModel($db);
} catch (Exception $e) {
    die("ERROR de conexión: " . $e->getMessage());
}

$type = $_GET['type'] ?? 'list';
$error = null;
$success = null;
$proveedor = null;
$proveedores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==========================================
    // REGISTRAR PROVEEDOR
    // ==========================================
    if ($type === 'store') {
        $datos = [
            'rif' => trim($_POST['rif'] ?? ''),
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'numero_contacto' => trim($_POST['numero_contacto'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'correo_electronico' => trim($_POST['correo_electronico'] ?? '')
        ];

        if (empty($datos['rif']) || empty($datos['razon_social']) || empty($datos['numero_contacto']) || empty($datos['direccion'])) {
            $error = 'RIF, Razón Social, Contacto y Dirección son obligatorios.';
        } else {
            try {
                $resultado = $modelo->registrarProveedor($datos);
                if ($resultado) {
                    // 🔥 REDIRIGIR DE VUELTA (si viene de registro rápido)
                    if (isset($_GET['return'])) {
                        $id_proveedor = $db->lastInsertId();
                        $_SESSION['nuevo_proveedor_id'] = $id_proveedor;
                        $_SESSION['nuevo_proveedor_nombre'] = $datos['razon_social'];
                        $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datos['razon_social']}' registrado exitosamente";
                        $_SESSION['tipo_rapido'] = 'success';
                        header("Location: ?url=" . $_GET['return'] . "&type=create");
                        exit;
                    }
                    
                    $_SESSION['mensaje'] = 'Proveedor registrado.';
                    header("Location: ?url=proveedores&type=list");
                    exit();
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }

    // ==========================================
    // 🔥 REGISTRO RÁPIDO PROVEEDOR (MODIFICADO - SIN JSON)
    // ==========================================
    if ($type === 'store_rapido') {
        try {
            // Validar campos requeridos
            if (empty($_POST['rif']) || empty($_POST['razon_social']) || empty($_POST['numero_contacto']) || empty($_POST['direccion'])) {
                throw new Exception('RIF, razón social, contacto y dirección son obligatorios.');
            }

            $datosProveedor = [
                'rif' => trim($_POST['rif']),
                'razon_social' => trim($_POST['razon_social']),
                'numero_contacto' => trim($_POST['numero_contacto']),
                'direccion' => trim($_POST['direccion']),
                'correo_electronico' => trim($_POST['correo_electronico'] ?? ''),
                'nombre_contacto' => trim($_POST['nombre_contacto'] ?? 'Proveedor'),
                'apellido_contacto' => trim($_POST['apellido_contacto'] ?? '')
            ];

            $resultado = $modelo->registrarProveedor($datosProveedor);
            if (!$resultado) {
                throw new Exception('No se pudo registrar el proveedor.');
            }

            $idProveedor = $db->lastInsertId();
            
            // 🔥 GUARDAR EN SESIÓN PARA EL RETORNO
            $_SESSION['nuevo_proveedor_id'] = $idProveedor;
            $_SESSION['nuevo_proveedor_nombre'] = $datosProveedor['razon_social'];
            $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datosProveedor['razon_social']}' registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';
            
            // 🔥 REDIRIGIR DE VUELTA (si viene de registro rápido)
            if (isset($_GET['return'])) {
                header("Location: ?url=" . $_GET['return'] . "&type=create");
                exit;
            }
            
            header("Location: ?url=proveedores&type=list");
            exit;
            
        } catch (Exception $e) {
            $_SESSION['mensaje_rapido'] = "❌ " . $e->getMessage();
            $_SESSION['tipo_rapido'] = 'danger';
            
            if (isset($_GET['return'])) {
                header("Location: ?url=" . $_GET['return'] . "&type=create");
                exit;
            }
            header("Location: ?url=proveedores&type=list");
            exit;
        }
    }

    // ==========================================
    // ACTUALIZAR PROVEEDOR
    // ==========================================
    if ($type === 'update') {
        $id = $_POST['id_proveedor'] ?? 0;
        $datos = [
            'rif' => trim($_POST['rif'] ?? ''),
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'numero_contacto' => trim($_POST['numero_contacto'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'correo_electronico' => trim($_POST['correo_electronico'] ?? '')
        ];

        if (empty($datos['rif']) || empty($datos['razon_social']) || empty($datos['numero_contacto']) || empty($datos['direccion'])) {
            $error = 'RIF, Razón Social, Contacto y Dirección son obligatorios.';
        } else {
            try {
                if ($modelo->actualizarProveedor($id, $datos)) {
                    $_SESSION['mensaje'] = 'Proveedor actualizado.';
                    header("Location: ?url=proveedores&type=list");
                    exit();
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }

    // ==========================================
    // ELIMINAR PROVEEDOR
    // ==========================================
    if ($type === 'delete') {
        $id = $_POST['id_proveedor'] ?? 0;
        try {
            if ($modelo->eliminarProveedor($id)) {
                $_SESSION['mensaje'] = 'Proveedor eliminado.';
            } else {
                $_SESSION['mensaje'] = 'No se pudo eliminar.';
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
        }
        header("Location: ?url=proveedores&type=list");
        exit();
    }
}

// ==========================================
// VISTAS
// ==========================================

if ($type === 'create') {
    require_once 'C:/xampp/htdocs/Pirotecnica_Fenix/app/view/proveedores/registroProveedoresView.php';
    exit();
}

if ($type === 'edit') {
    $id = $_GET['id'] ?? 0;
    try {
        $proveedor = $modelo->obtenerProveedorPorId($id);
        if (!$proveedor) {
            die("ERROR: Proveedor no encontrado.");
        }
    } catch (Exception $e) {
        die("ERROR: " . $e->getMessage());
    }
    require_once 'C:/xampp/htdocs/Pirotecnica_Fenix/app/view/proveedores/editarProveedoresView.php';
    exit();
}

if ($type === 'show') {
    $id = $_GET['id'] ?? 0;
    try {
        $proveedor = $modelo->obtenerProveedorPorId($id);
        if (!$proveedor) {
            die("ERROR: Proveedor no encontrado.");
        }
    } catch (Exception $e) {
        die("ERROR: " . $e->getMessage());
    }
    require_once 'C:/xampp/htdocs/Pirotecnica_Fenix/app/view/proveedores/verProveedorView.php';
    exit();
}

try {
    $buscar = trim($_GET['buscar'] ?? '');
    if (!empty($buscar)) {
        $proveedores = $modelo->buscarProveedores($buscar);
    } else {
        $proveedores = $modelo->obtenerProveedores();
    }
    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
    }
} catch (Exception $e) {
    die("ERROR al listar: " . $e->getMessage());
}

require_once 'C:/xampp/htdocs/Pirotecnica_Fenix/app/view/proveedores/listProveedoresView.php';
?>