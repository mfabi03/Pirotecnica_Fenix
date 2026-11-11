<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\proveedoresModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\CheckPermiso;
use Exception;

$type = $_POST['type'] ?? $_GET['type'] ?? 'list';
$action = $_GET['action'] ?? '';
$esSolicitudAjax = in_array($type, ['listar_ajax', 'detalle_ajax', 'estado_ajax'], true)
    || in_array($type, ['store_ajax', 'update_ajax'], true)
    || in_array($action, ['check_rif', 'check_unique'], true);

error_reporting(E_ALL);
ini_set('display_errors', $esSolicitudAjax ? '0' : '1');

// Iniciar sesión para mensajes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutaRaiz = dirname(__DIR__, 2);

$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'proveedoresModel.php';
if (!file_exists($pathModel)) {
    if ($esSolicitudAjax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'No se pudo cargar el modelo de proveedores.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    die("ERROR: No se encuentra proveedoresModel.php en: " . $pathModel);
}
require_once $pathModel;

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new proveedoresModel($db);
} catch (Exception $e) {
    if ($esSolicitudAjax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Error de conexión al procesar la solicitud.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    die("ERROR de conexión: " . $e->getMessage());
}

$error = null;
$success = null;
$proveedor = null;
$proveedores = [];

if ($action === 'check_unique') {
    header('Content-Type: application/json; charset=utf-8');
    $campo = $_GET['campo'] ?? '';
    $valor = trim($_GET['valor'] ?? '');
    $excluirId = $_GET['excluir_id'] ?? '';
    if ($valor === '' || ($excluirId !== '' && (!ctype_digit((string) $excluirId) || (int) $excluirId < 1))) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos de validación no válidos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        if ($campo !== 'telefono_proveedor') {
            http_response_code(400);
            throw new Exception('Campo de validación no válido.');
        }
        echo json_encode([
            'existe' => $modelo->existeNumeroContacto($valor, $excluirId === '' ? null : (int) $excluirId),
            'campo' => $campo,
            'valor' => $valor
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        error_log('Error al verificar el teléfono del proveedor: ' . $e->getMessage());
        if (http_response_code() < 400) {
            http_response_code(500);
        }
        echo json_encode(['error' => 'No se pudo verificar el número de contacto.'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($action === 'check_rif') {
    header('Content-Type: application/json; charset=utf-8');
    $rif = trim($_GET['valor'] ?? '');
    if ($rif === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Valor vacío'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        echo json_encode([
            'existe' => $modelo->existeRif($rif),
            'campo' => 'rif',
            'valor' => $rif
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        error_log('Error en check_rif de proveedores: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo verificar el RIF.'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ============================================
// ENDPOINT AJAX: LISTAR PROVEEDORES EN JSON
// ============================================
if (isset($_GET['type']) && $_GET['type'] === 'listar_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $proveedores = $modelo->obtenerProveedores();
        echo json_encode([
            'success' => true,
            'proveedores' => $proveedores
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($type === 'detalle_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $id = $_POST['id_proveedor'] ?? 0;
        if (!ctype_digit((string) $id) || (int) $id < 1) {
            http_response_code(400);
            throw new Exception('ID de proveedor no válido.');
        }
        $detalle = $modelo->obtenerProveedorPorId((int) $id, true);
        if (!$detalle) {
            http_response_code(404);
            throw new Exception('Proveedor no encontrado.');
        }
        echo json_encode(['success' => true, 'proveedor' => $detalle], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        if (http_response_code() < 400) {
            http_response_code(500);
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

if ($type === 'estado_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!PermisoHelper::tienePermiso($db, $_SESSION['id_rol'] ?? 0, 'Proveedores', 'eliminar')) {
            http_response_code(403);
            throw new Exception('No tienes permiso para cambiar el estado de proveedores.');
        }
        $id = $_POST['id_proveedor'] ?? 0;
        if (!ctype_digit((string) $id) || (int) $id < 1) {
            http_response_code(400);
            throw new Exception('ID de proveedor no válido.');
        }

        if (($_POST['accion'] ?? '') === 'inactivar') {
            $modelo->eliminarProveedor((int) $id);
            $mensaje = 'Proveedor eliminado.';
        } elseif (($_POST['accion'] ?? '') === 'reactivar') {
            $modelo->reactivarProveedor((int) $id);
            $mensaje = 'Proveedor reactivado.';
        } else {
            http_response_code(400);
            throw new Exception('Acción de estado no válida.');
        }

        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = 'success';
        echo json_encode(['success' => true, 'message' => $mensaje], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Exception $e) {
        if (http_response_code() < 400) {
            http_response_code(400);
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

if (in_array($type, ['store_ajax', 'update_ajax'], true)) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $esActualizacion = $type === 'update_ajax';
        $permiso = $esActualizacion ? 'actualizar' : 'crear';
        if (!PermisoHelper::tienePermiso($db, $_SESSION['id_rol'] ?? 0, 'Proveedores', $permiso)) {
            http_response_code(403);
            throw new Exception('No tienes permiso para ' . ($esActualizacion ? 'actualizar' : 'registrar') . ' proveedores.');
        }

        $datos = [
            'rif' => trim($_POST['rif'] ?? ''),
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'numero_contacto' => trim($_POST['numero_contacto'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'correo_electronico' => trim($_POST['correo_electronico'] ?? '')
        ];
        if ($datos['rif'] === '' || $datos['razon_social'] === '' || $datos['numero_contacto'] === '' || $datos['direccion'] === '') {
            http_response_code(400);
            throw new Exception('RIF, Razón Social, Contacto y Dirección son obligatorios.');
        }

        if ($esActualizacion) {
            $idProveedor = $_POST['id_proveedor'] ?? '';
            if (!ctype_digit((string) $idProveedor) || (int) $idProveedor < 1) {
                http_response_code(400);
                throw new Exception('ID de proveedor no válido.');
            }
            $datos['eliminado'] = $_POST['eliminado'] ?? '0';
            $datos['nombre_contacto'] = 'Proveedor';
            $datos['apellido_contacto'] = '';
            $modelo->actualizarProveedor((int) $idProveedor, $datos);
            $mensaje = 'Proveedor actualizado.';
            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = 'success';
            echo json_encode(['success' => true, 'message' => $mensaje], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } else {
            $idProveedor = $modelo->registrarProveedor($datos);
            $mensaje = 'Proveedor registrado.';
            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = 'success';
            echo json_encode([
                'success' => true,
                'id_proveedor' => (int) $idProveedor,
                'razon_social' => $datos['razon_social'],
                'rif' => $datos['rif'],
                'message' => $mensaje
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    } catch (Exception $e) {
        if (http_response_code() < 400) {
            http_response_code(400);
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

// ============================================
// ENDPOINT AJAX: REGISTRO RÁPIDO DE PROVEEDOR (para modal)
// ============================================
if ($type === 'store_rapido_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        // Verificar permisos
        if (!PermisoHelper::tienePermiso($db, $_SESSION['id_rol'] ?? 0, 'Proveedores', 'crear')) {
            throw new Exception("No tienes permiso para registrar proveedores.");
        }
        
        // Validar campos requeridos
        if (empty($_POST['rif']) || empty($_POST['razon_social']) || 
            empty($_POST['numero_contacto']) || empty($_POST['direccion'])) {
            throw new Exception("RIF, Razón Social, Contacto y Dirección son obligatorios.");
        }
        
        $datos = [
            'rif' => trim($_POST['rif']),
            'razon_social' => trim($_POST['razon_social']),
            'numero_contacto' => trim($_POST['numero_contacto']),
            'direccion' => trim($_POST['direccion']),
            'correo_electronico' => trim($_POST['correo_electronico'] ?? ''),
            'nombre_contacto' => trim($_POST['nombre_contacto'] ?? 'Proveedor'),
            'apellido_contacto' => trim($_POST['apellido_contacto'] ?? '')
        ];
        
        $id_proveedor = $modelo->registrarProveedor($datos);
        
        if (!$id_proveedor) {
            throw new Exception("No se pudo registrar el proveedor.");
        }
        
        echo json_encode([
            'success' => true,
            'id_proveedor' => (int) $id_proveedor,
            'razon_social' => $datos['razon_social'],
            'rif' => $datos['rif']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // REGISTRAR PROVEEDOR
    if ($type === 'store') {
        CheckPermiso::verificar($db, 'Proveedores', 'crear', '?url=proveedores&type=list');
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
                    $return = $_REQUEST['return'] ?? null;
                    if ($return) {
                        $id_proveedor = (int) $resultado;
                        $_SESSION['nuevo_proveedor_id'] = $id_proveedor;
                        $_SESSION['nuevo_proveedor_nombre'] = $datos['razon_social'];
                        $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datos['razon_social']}' registrado exitosamente";
                        $_SESSION['tipo_rapido'] = 'success';

                        $redirect = '?url=' . urlencode($return) . '&type=create&id_proveedor=' . $id_proveedor;
                        if ($return === 'notaentrada' || $return === 'productos') {
                            header('Location: ' . $redirect);
                            exit;
                        }

                        header('Location: ' . $redirect);
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

    // REGISTRO RÁPIDO PROVEEDOR (NO-AJAX, conservada por compatibilidad)
    if ($type === 'store_rapido') {
        CheckPermiso::verificar($db, 'Proveedores', 'crear', '?url=proveedores&type=list');
        try {
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

            $idProveedor = (int) $resultado;

            $_SESSION['nuevo_proveedor_id'] = $idProveedor;
            $_SESSION['nuevo_proveedor_nombre'] = $datosProveedor['razon_social'];
            $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datosProveedor['razon_social']}' registrado exitosamente";
            $_SESSION['tipo_rapido'] = 'success';

            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header('Location: ?url=' . urlencode($return) . '&type=create&id_proveedor=' . $idProveedor);
                exit;
            }

            header("Location: ?url=proveedores&type=list");
            exit;
            
        } catch (Exception $e) {
            $_SESSION['mensaje_rapido'] = "❌ " . $e->getMessage();
            $_SESSION['tipo_rapido'] = 'danger';
            
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }
            header("Location: ?url=proveedores&type=list");
            exit;
        }
    }

    // ACTUALIZAR PROVEEDOR
    if ($type === 'update') {
        CheckPermiso::verificar($db, 'Proveedores', 'actualizar', '?url=proveedores&type=list');
        $id = $_POST['id_proveedor'] ?? 0;
        $datos = [
            'rif' => trim($_POST['rif'] ?? ''),
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'numero_contacto' => trim($_POST['numero_contacto'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'correo_electronico' => trim($_POST['correo_electronico'] ?? ''),
            'eliminado' => trim($_POST['eliminado'] ?? '')
        ];

        if (empty($datos['rif']) || empty($datos['razon_social']) || empty($datos['numero_contacto']) || empty($datos['direccion'])) {
            $error = 'RIF, Razón Social, Contacto y Dirección son obligatorios.';
        } else {
            try {
                if ($modelo->actualizarProveedor($id, $datos)) {
                    $_SESSION['mensaje'] = 'Proveedor actualizado.';
                    $_SESSION['tipo_mensaje'] = 'success';
                    header("Location: ?url=proveedores&type=list");
                    exit();
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }

    // ELIMINAR PROVEEDOR
    if ($type === 'delete') {
        CheckPermiso::verificar($db, 'Proveedores', 'eliminar', '?url=proveedores&type=list');
        $id = $_POST['id_proveedor'] ?? 0;
        try {
            if ($modelo->eliminarProveedor($id)) {
                $_SESSION['mensaje'] = 'Proveedor eliminado.';
                $_SESSION['tipo_mensaje'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'No se pudo eliminar.';
                $_SESSION['tipo_mensaje'] = 'danger';
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }
        header("Location: ?url=proveedores&type=list");
        exit();
    }
}

// VISTAS
if ($type === 'create') {
    require_once $rutaRaiz . '/app/view/proveedores/registroProveedoresView.php';
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
    require_once $rutaRaiz . '/app/view/proveedores/editarProveedoresView.php';
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
    require_once $rutaRaiz . '/app/view/proveedores/verProveedorView.php';
    exit();
}

try {
    $buscar = trim($_GET['busqueda'] ?? $_GET['buscar'] ?? '');
    $verEliminados = !empty($_GET['ver_eliminados']);
    
    if (!empty($buscar)) {
        $proveedores_full = $modelo->buscarProveedores($buscar, true);
    } else {
        $proveedores_full = $modelo->obtenerProveedores(true);
    }
    $proveedores_full = array_values(array_filter($proveedores_full, function ($proveedor) use ($verEliminados) {
        return (int) ($proveedor['eliminado'] ?? 0) === ($verEliminados ? 1 : 0);
    }));

    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    $totalRegistros = count($proveedores_full);
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    if ($por_pagina > 0) {
        $proveedores = array_slice($proveedores_full, $offset, $por_pagina);
    } else {
        $proveedores = $proveedores_full;
    }

    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
    }
} catch (Exception $e) {
    die("ERROR al listar: " . $e->getMessage());
}

require_once $rutaRaiz . '/app/view/proveedores/listProveedoresView.php';
?>