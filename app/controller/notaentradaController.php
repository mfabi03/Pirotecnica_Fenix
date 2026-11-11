<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\NotaentradaModel;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\ProveedoresModel;
use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Helpers\CheckPermiso;
use Exception;

$type = $_POST['type'] ?? $_GET['type'] ?? 'list';
$id = $_POST['id'] ?? $_GET['id'] ?? null;
$esSolicitudJson = in_array($type, ['show_json', 'detalle_json', 'estado_ajax'], true);

error_reporting(E_ALL);
ini_set('display_errors', $esSolicitudJson ? '0' : '1');

// Iniciar sesión para mensajes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ((int) ($_SESSION['id_rol'] ?? 0) === 3) {
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

// 1. CARGA DE MODELOS
$rutaRaiz = dirname(__DIR__, 2);

// 🔥 BUSCAMOS LOS MODELOS EN LA CARPETA CORRECTA (Model con M mayúscula o model con m minúscula)
$pathNotaModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'NotaentradaModel.php';
$pathProductoModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'ProductoModel.php';
$pathProveedorModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'ProveedoresModel.php';

if (!file_exists($pathNotaModel)) {
    $pathNotaModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'NotaentradaModel.php';
}
if (!file_exists($pathProductoModel)) {
    $pathProductoModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ProductoModel.php';
}
if (!file_exists($pathProveedorModel)) {
    $pathProveedorModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ProveedoresModel.php';
}

if (file_exists($pathNotaModel)) {
    require_once $pathNotaModel;
} else {
    if ($esSolicitudJson) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'No se pudo cargar el modelo de notas de entrada.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    die("ERROR CRÍTICO: No se encuentra NotaentradaModel.php");
}

if (file_exists($pathProductoModel)) {
    require_once $pathProductoModel;
} else {
    if ($esSolicitudJson) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'No se pudo cargar el modelo de productos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    die("ERROR CRÍTICO: No se encuentra ProductoModel.php");
}

if (file_exists($pathProveedorModel)) {
    require_once $pathProveedorModel;
} else {
    if ($esSolicitudJson) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'No se pudo cargar el modelo de proveedores.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    die("ERROR CRÍTICO: No se encuentra ProveedoresModel.php");
}

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new NotaentradaModel($db);
    $productoModel = new ProductoModel($db);
    $proveedorModel = new ProveedoresModel($db);
} catch (\Throwable $e) {
    if ($esSolicitudJson) {
        error_log('Error de conexión en solicitud JSON de nota de entrada: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Error de conexión al cargar la nota.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    die("ERROR de conexión: " . $e->getMessage());
}

$error = null;
$success = null;
$nota = null;
$notas = [];
$productos = [];
$proveedores = [];
$resumen = [];
$tipo_mensaje = '';

// 2. FUNCIÓN PARA OBTENER ID USUARIO VÁLIDO
function obtenerIdUsuarioValido($db) {
    $idUsuario = $_SESSION['id_usuario'] 
              ?? $_SESSION['usuario_id'] 
              ?? null;
    
    if (!$idUsuario) {
        throw new Exception("No hay usuario en sesión. Por favor, cierre sesión y vuelva a entrar.");
    }
    
    $stmt = $db->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? AND eliminado = 0");
    $stmt->execute([$idUsuario]);
    
    if (!$stmt->fetch()) {
        throw new Exception("El usuario en sesión no existe o está inactivo.");
    }
    
    return (int) $idUsuario;
}

if ($type === 'estado_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    $accion = $_POST['accion'] ?? '';

    try {
        if (!PermisoHelper::tienePermiso($db, $_SESSION['id_rol'] ?? 0, 'Notas de Entrada', 'eliminar')) {
            http_response_code(403);
            throw new Exception('No tienes permiso para cambiar el estado de las notas.');
        }
        if (!$id || !ctype_digit((string) $id)) {
            http_response_code(400);
            throw new Exception('ID de nota no válido.');
        }

        if ($accion === 'inactivar') {
            $motivo = trim($_POST['motivo'] ?? '');
            if ($motivo === '') {
                http_response_code(400);
                throw new Exception('Debes indicar el motivo de anulación.');
            }
            $modelo->anularNotaEntrada($id, $motivo, obtenerIdUsuarioValido($db));
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

// 3. PROCESAMIENTO POST
if (in_array($type, ['edit', 'update'], true)) {
    http_response_code(403);
    $_SESSION['mensaje'] = 'Las notas de entrada no se pueden editar.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ?url=notaentrada&type=list');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($type === 'update') {
        CheckPermiso::verificar($db, 'Notas de Entrada', 'actualizar', '?url=notaentrada&type=list');
        $idNota = $_POST['id_nota_entrada'] ?? '';

        try {
            if (!is_scalar($idNota) || !ctype_digit((string) $idNota) || (int) $idNota < 1) {
                throw new Exception('ID de nota no válido.');
            }

            $fechaEntradaPost = $_POST['fecha_ingreso'] ?? '';
            if (!is_string($fechaEntradaPost)) {
                throw new Exception('La fecha de ingreso no es válida.');
            }
            $fechaEntrada = trim($fechaEntradaPost);
            $fechaValidada = \DateTime::createFromFormat('Y-m-d\TH:i', $fechaEntrada);
            $erroresFecha = \DateTime::getLastErrors();
            if (!$fechaValidada || ($erroresFecha && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0))) {
                throw new Exception('La fecha de ingreso no es válida.');
            }

            $idProveedor = $_POST['id_proveedor'] ?? '';
            if (!is_scalar($idProveedor) || !ctype_digit((string) $idProveedor) || (int) $idProveedor < 1) {
                throw new Exception('Debe seleccionar un proveedor válido.');
            }

            $productosIds = $_POST['detalle_producto'] ?? [];
            $cantidades = $_POST['detalle_cantidad'] ?? [];
            $costos = $_POST['detalle_costo'] ?? [];
            if (!is_array($productosIds) || !is_array($cantidades) || !is_array($costos)
                || count($productosIds) !== count($cantidades) || count($productosIds) !== count($costos)) {
                throw new Exception('Los detalles de productos enviados no son válidos.');
            }

            $detalles = [];
            foreach ($productosIds as $indice => $productoId) {
                $cantidad = $cantidades[$indice] ?? '';
                $costo = $costos[$indice] ?? '';
                if (!is_scalar($productoId) || !ctype_digit((string) $productoId) || (int) $productoId < 1
                    || !is_scalar($cantidad) || !ctype_digit((string) $cantidad) || (int) $cantidad < 1
                    || !is_numeric($costo) || !is_finite((float) $costo) || (float) $costo < 0) {
                    throw new Exception('Uno de los productos, cantidades o costos no es válido.');
                }
                $detalles[] = [
                    'id_producto' => (int) $productoId,
                    'cantidad' => (int) $cantidad,
                    'costo_unitario' => (float) $costo
                ];
            }

            $descripcion = $_POST['descripcion'] ?? '';
            if (!is_string($descripcion)) {
                throw new Exception('La descripción no es válida.');
            }

            $modelo->actualizarNotaEntradaCompleta((int) $idNota, [
                'fecha_ingreso' => $fechaValidada->format('Y-m-d H:i:s'),
                'id_proveedor' => (int) $idProveedor,
                'descripcion' => trim($descripcion)
            ], $detalles);

            $_SESSION['mensaje'] = 'Nota de Entrada actualizada exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: ?url=notaentrada&type=list');
            exit();
        } catch (Exception $e) {
            $error = 'No se pudo actualizar la nota: ' . $e->getMessage();
            $type = 'edit';
            $id = is_scalar($idNota) ? (string) $idNota : '';
        }
    }
    
    // REGISTRAR NOTA DE ENTRADA 
    if ($type === 'store') {
        CheckPermiso::verificar($db, 'Notas de Entrada', 'crear', '?url=notaentrada&type=list');
        try {
            $idUsuario = obtenerIdUsuarioValido($db);
            
            $detalles = [];
            $productos_ids = $_POST['detalle_producto'] ?? [];
            $cantidades = $_POST['detalle_cantidad'] ?? [];
            $costos = $_POST['detalle_costo'] ?? [];
            
            for ($i = 0; $i < count($productos_ids); $i++) {
                if (!empty($productos_ids[$i]) && !empty($cantidades[$i])) {
                    $detalles[] = [
                        'id_producto' => intval($productos_ids[$i]),
                        'cantidad' => intval($cantidades[$i]),
                        'costo_unitario' => floatval($costos[$i] ?? 0)
                    ];
                }
            }
            
            if (empty($detalles) || count($detalles) === 0) {
                throw new Exception("Debe agregar al menos un producto.");
            }
            
            if (empty($_POST['id_proveedor'])) {
                throw new Exception("Debe seleccionar un proveedor.");
            }
            
            foreach ($detalles as $d) {
                if ($d['cantidad'] <= 0) {
                    throw new Exception("La cantidad debe ser mayor a 0.");
                }
            }
            
            $datos = [
                'fecha_ingreso' => $_POST['fecha_ingreso'],
                'id_proveedor' => intval($_POST['id_proveedor']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];
            
            $resultado = $modelo->guardarNotaEntradaCompleta($datos, $detalles, $idUsuario);
            
            if ($resultado) {
                $_SESSION['mensaje'] = '✅ Nota de Entrada registrada exitosamente.';
                $_SESSION['tipo_mensaje'] = 'success';
                header("Location: ?url=notaentrada&type=list");
                exit();
            }
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
    
    // REGISTRO RÁPIDO DE PRODUCTO
    if ($type === 'store_rapido_producto') {
        CheckPermiso::verificar($db, 'Notas de Entrada', 'crear', '?url=notaentrada&type=list');
        try {
            if (empty($_POST['descripcion']) || empty($_POST['id_categoria']) || empty($_POST['id_proveedor'])) {
                throw new Exception("Por favor complete todos los campos requeridos (*)");
            }
            
            $cantidadOriginal = intval($_POST['cantidad'] ?? 1);
            $costoOriginal = floatval($_POST['costo_unitario'] ?? 0);
            $idProveedorOriginal = intval($_POST['id_proveedor'] ?? 0);

            $datosProducto = [
                'descripcion' => trim($_POST['descripcion']),
                'id_categoria' => intval($_POST['id_categoria']),
                'id_proveedor' => $idProveedorOriginal,
                'cantidad' => 0,
                'costo_unitario' => $costoOriginal,
                'especificaciones' => ''
            ];
            
            $id_producto = $productoModel->registrarProducto($datosProducto);
            
            if ($id_producto) {
                $_SESSION['nuevo_producto_id'] = $id_producto;
                $_SESSION['nuevo_producto_nombre'] = $datosProducto['descripcion'];
                $_SESSION['nuevo_producto_costo'] = $costoOriginal;
                $_SESSION['mensaje_rapido'] = "✅ Producto '{$datosProducto['descripcion']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                
                $return = $_REQUEST['return'] ?? 'notaentrada';
                $params = [
                    'url' => $return,
                    'type' => 'create',
                    'id_producto' => $id_producto,
                    'id_proveedor' => $idProveedorOriginal,
                    'cantidad' => $cantidadOriginal,
                    'costo' => $costoOriginal
                ];
                header("Location: ?" . http_build_query($params));
                exit;
            } else {
                throw new Exception("Error al guardar el producto");
            }
            
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
        CheckPermiso::verificar($db, 'Notas de Entrada', 'crear', '?url=notaentrada&type=list');
        try {
            if (empty($_POST['rif']) || empty($_POST['razon_social']) || empty($_POST['numero_contacto']) || empty($_POST['direccion'])) {
                throw new Exception("Por favor complete todos los campos requeridos (*)");
            }
            
            $datosProveedor = [
                'rif' => trim($_POST['rif']),
                'razon_social' => trim($_POST['razon_social']),
                'numero_contacto' => trim($_POST['numero_contacto']),
                'direccion' => trim($_POST['direccion']),
                'correo_electronico' => trim($_POST['correo_electronico'] ?? '')
            ];
            
            $id_proveedor = $proveedorModel->registrarProveedor($datosProveedor);
            
            if ($id_proveedor) {
                $_SESSION['nuevo_proveedor_id'] = (int) $id_proveedor;
                $_SESSION['nuevo_proveedor_nombre'] = $datosProveedor['razon_social'];
                $_SESSION['mensaje_rapido'] = "✅ Proveedor '{$datosProveedor['razon_social']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                
                $return = $_REQUEST['return'] ?? 'notaentrada';
                header("Location: ?url=" . urlencode($return) . "&type=create&id_proveedor=" . (int) $id_proveedor);
                exit;
            } else {
                throw new Exception("Error al guardar el proveedor");
            }
            
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
    
    // ANULAR (CON GUARDADO DE MOTIVO)
    if ($type === 'anular') {
        CheckPermiso::verificar($db, 'Notas de Entrada', 'eliminar', '?url=notaentrada&type=list');
        try {
            $id = $_POST['id_nota_entrada'] ?? 0;
            
            $motivo = trim($_POST['motivo_eliminacion'] ?? $_POST['motivo_anulacion'] ?? '');
            
            $idUsuario = obtenerIdUsuarioValido($db);
            
            if (empty($motivo)) {
                throw new Exception("Debe indicar el motivo de anulación.");
            }
            
            if (!$id || !is_numeric($id) || $id <= 0) {
                throw new Exception("ID de nota inválido.");
            }
            
            // 1. Anular la nota (esto hace el UPDATE de eliminado=1)
            $resultado = $modelo->anularNotaEntrada($id, $motivo, $idUsuario);
            
            if ($resultado) {
                // 2. GUARDAR EL MOTIVO EN LA BASE DE DATOS
                $stmtMotivo = $db->prepare("UPDATE nota_de_entrada SET motivo_anulacion = :motivo WHERE id_nota_entrada = :id");
                $stmtMotivo->execute([':motivo' => $motivo, ':id' => $id]);

                $_SESSION['contador_anulaciones_notaentrada'] = (int) ($modelo->getResumen()['total_anuladas'] ?? 0);

                $_SESSION['mensaje'] = '✅ Nota de Entrada anulada exitosamente. El stock ha sido revertido.';
                $_SESSION['tipo_mensaje'] = 'warning';
                header("Location: ?url=notaentrada&type=list");
                exit();
            } else {
                throw new Exception("No se pudo anular la nota.");
            }
            
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
}

// ============================================================
// ENDPOINT AJAX: DEVOLVER DETALLE DE LA NOTA DE ENTRADA EN JSON
// ============================================================
if ($esSolicitudJson) {
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', '0');

    if (!$id || !ctype_digit((string) $id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de nota no válido'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    try {
        $notaJson = $modelo->obtenerNotaEntradaPorId($id);
        if (!$notaJson) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Nota no encontrada'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
        }
        echo json_encode(['success' => true, 'nota' => $notaJson], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (\Throwable $e) {
        error_log('Error al obtener detalle JSON de nota de entrada: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo cargar el detalle de la nota'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    exit;
}

// 4. VISTAS
$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'nota_entrada';

// ==========================================
// 🔥 RUTA PARA GENERAR EL PDF DE LA NOTA DE ENTRADA
// ==========================================
if ($type === 'generar_pdf' && $id) {
    $archivoPdf = $baseViewPath . DIRECTORY_SEPARATOR . 'generar_pdf_entrada.php';
    
    // Si no existe con ese nombre, intentamos con generar_pdf.php
    if (!file_exists($archivoPdf)) {
        $archivoPdf = $baseViewPath . DIRECTORY_SEPARATOR . 'generar_pdf.php';
    }
    
    if (file_exists($archivoPdf)) {
        require_once $archivoPdf;
        exit();
    } else {
        die("ERROR: No se encuentra el archivo generar_pdf_entrada.php ni generar_pdf.php en: " . $baseViewPath);
    }
}

// CREAR
if ($type === 'create') {
    try {
        $productos = $modelo->obtenerProductos();
        $proveedores = $modelo->obtenerProveedores();
        $categorias = $productoModel->obtenerCategorias();
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

if ($type === 'edit' && $id) {
    CheckPermiso::verificar($db, 'Notas de Entrada', 'actualizar', '?url=notaentrada&type=list');
    try {
        $nota = $modelo->obtenerNotaEntradaPorId($id);
        if (!$nota || (int) $nota['eliminado'] === 1) {
            throw new Exception('La nota no existe o ya fue anulada.');
        }
        $productos = $modelo->obtenerProductos();
        $proveedores = $modelo->obtenerProveedores();
    } catch (Exception $e) {
        if ($type === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $buscar = trim((string) ($_GET['busqueda'] ?? $_GET['buscar'] ?? ''));
    $estado_nota = !empty($_GET['ver_anuladas']) ? 'inactivos' : 'activos';
    $notas_full = !empty($buscar)
        ? $modelo->buscarNotasEntrada($buscar, $estado_nota)
        : $modelo->obtenerNotasEntrada($estado_nota);
    $resumen = $modelo->getResumen();

    $por_pagina    = (int) ($_GET['por_pagina'] ?? 10);
    $pagina_actual = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;
    
    $totalRegistros = is_array($notas_full) ? count($notas_full) : 0;
    $totalPaginas = $por_pagina > 0 ? (int)ceil($totalRegistros / $por_pagina) : 1;
    
    if ($por_pagina > 0) {
        $notas = array_slice($notas_full, $offset, $por_pagina);
    } else {
        $notas = $notas_full;
    }
    
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
?>