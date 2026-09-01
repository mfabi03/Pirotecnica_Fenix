<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\notasalidaModel;
use App\Pirotecnicafenix\Model\ProductoModel;
use App\Pirotecnicafenix\Model\clientesModel;
use Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión para mensajes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CARGA DE MODELOS

$rutaRaiz = dirname(__DIR__, 2);

$pathNotaModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'notasalidaModel.php';
$pathProductoModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ProductoModel.php';
$pathClienteModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'clientesModel.php';

if (file_exists($pathNotaModel)) {
    require_once $pathNotaModel;
} else {
    die("ERROR CRÍTICO: No se encuentra notasalidaModel.php");
}

if (file_exists($pathProductoModel)) {
    require_once $pathProductoModel;
} else {
    die("ERROR CRÍTICO: No se encuentra ProductoModel.php");
}

if (file_exists($pathClienteModel)) {
    require_once $pathClienteModel;
} else {
    die("ERROR CRÍTICO: No se encuentra clientesModel.php");
}

try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new \App\Pirotecnicafenix\Model\notasalidaModel($db);
    $productoModel = new \App\Pirotecnicafenix\Model\ProductoModel($db);
    $clienteModel = new \App\Pirotecnicafenix\Model\clientesModel($db);
} catch (Exception $e) {
    die("ERROR de conexión: " . $e->getMessage());
}

// 2. FUNCIÓN PARA OBTENER ID USUARIO VÁLIDO

function obtenerIdUsuarioValido($db) {
    $idUsuario = $_SESSION['usuario_id'] ?? null;
    
    if ($idUsuario) {
        $stmt = $db->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        if ($stmt->fetch()) {
            return $idUsuario;
        }
    }
    
    $stmt = $db->query("SELECT id_usuario FROM usuario LIMIT 1");
    $usuario = $stmt->fetch();
    if ($usuario) {
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        return $usuario['id_usuario'];
    }
    
    throw new Exception("No hay usuarios disponibles en la base de datos.");
}

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

// 3. PROCESAMIENTO POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // REGISTRAR NOTA DE SALIDA
   
    if ($type === 'store') {
        try {
            $idUsuario = obtenerIdUsuarioValido($db);
            
            // RECIBIR DETALLES COMO ARRAYS 
            $detalles = [];
            $productos_ids = $_POST['detalle_producto'] ?? [];
            $cantidades = $_POST['detalle_cantidad'] ?? [];
            
            for ($i = 0; $i < count($productos_ids); $i++) {
                if (!empty($productos_ids[$i]) && !empty($cantidades[$i])) {
                    $detalles[] = [
                        'id_producto' => intval($productos_ids[$i]),
                        'cantidad' => intval($cantidades[$i])
                    ];
                }
            }
            
            if (empty($detalles) || count($detalles) === 0) {
                throw new Exception("Debe agregar al menos un producto.");
            }
            
            if (empty($_POST['id_cliente'])) {
                throw new Exception("Debe seleccionar un cliente.");
            }
            
            foreach ($detalles as $d) {
                if ($d['cantidad'] <= 0) {
                    throw new Exception("La cantidad debe ser mayor a 0.");
                }
            }
            
            $datos = ['id_cliente' => intval($_POST['id_cliente'])];
            $resultado = $modelo->registrarSalida($datos, $detalles, $idUsuario);
            
            if ($resultado) {
                $_SESSION['mensaje'] = '✅ Nota de Salida registrada exitosamente.';
                $_SESSION['tipo_mensaje'] = 'success';
                header("Location: ?url=notasalida&type=list");
                exit();
            }
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
    
    // REGISTRO RÁPIDO DE PRODUCTO 

    if ($type === 'store_rapido_producto') {
        try {
            // Validar campos requeridos
            if (empty($_POST['descripcion']) || empty($_POST['id_categoria']) || empty($_POST['id_proveedor'])) {
                throw new Exception("Por favor complete todos los campos requeridos (*)");
            }
            
            // Preparar datos del producto
            $datosProducto = [
                'descripcion' => trim($_POST['descripcion']),
                'id_categoria' => intval($_POST['id_categoria']),
                'id_proveedor' => intval($_POST['id_proveedor']),
                'cantidad' => intval($_POST['cantidad'] ?? 0),
                'costo_unitario' => floatval($_POST['costo_unitario'] ?? 0),
                'especificaciones' => ''
            ];
            
            // Guardar producto usando el modelo
            $id_producto = $productoModel->registrarProducto($datosProducto);
            
            if ($id_producto) {
                // GUARDAR EN SESIÓN PARA EL RETORNO
                $_SESSION['nuevo_producto_id'] = $id_producto;
                $_SESSION['nuevo_producto_nombre'] = $datosProducto['descripcion'];
                $_SESSION['mensaje_rapido'] = "✅ Producto '{$datosProducto['descripcion']}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                
                // REDIRIGIR DE VUELTA (si viene de registro rápido)
                $return = $_REQUEST['return'] ?? null;
                if ($return) {
                    header("Location: ?url=" . urlencode($return) . "&type=create");
                    exit;
                }

                header("Location: ?url=notasalida&type=create");
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
            header("Location: ?url=notasalida&type=create");
            exit;
        }
    }
    
    // REGISTRO RÁPIDO DE CLIENTE 

    if ($type === 'store_rapido_cliente') {
        try {
            $tipo = $_POST['tipo_cliente'] ?? 'natural';
            
            if ($tipo === 'juridico') {
                // Validar cliente jurídico
                if (empty($_POST['rif']) || empty($_POST['razon_social'])) {
                    throw new Exception("Por favor complete todos los campos requeridos");
                }
                
                $datos = [
                    'rif' => trim($_POST['rif']),
                    'razon_social' => trim($_POST['razon_social']),
                    'telefono' => trim($_POST['telefono_juridico'] ?? ''),
                    'correo_electronico' => trim($_POST['correo_juridico'] ?? ''),
                    'direccion' => trim($_POST['direccion_juridico'] ?? ''),
                    'tipo' => 'juridico'
                ];
                
                $id_cliente = $clienteModel->registrarClienteJuridico($datos);
                $nombre_cliente = $datos['razon_social'];
                
            } else {
                // Validar cliente natural
                if (empty($_POST['cedula']) || empty($_POST['nombre']) || empty($_POST['apellido'])) {
                    throw new Exception("Por favor complete todos los campos requeridos");
                }
                
                $datos = [
                    'cedula' => trim($_POST['cedula']),
                    'nombre' => trim($_POST['nombre']),
                    'apellido' => trim($_POST['apellido']),
                    'telefono' => trim($_POST['telefono'] ?? ''),
                    'correo_electronico' => trim($_POST['correo'] ?? ''),
                    'direccion' => trim($_POST['direccion'] ?? ''),
                    'tipo' => 'natural'
                ];
                
                $id_cliente = $clienteModel->registrarClienteNatural($datos);
                $nombre_cliente = $datos['nombre'] . ' ' . $datos['apellido'];
            }
            
            if ($id_cliente) {
                // GUARDAR EN SESIÓN PARA EL RETORNO
                $_SESSION['nuevo_cliente_id'] = $id_cliente;
                $_SESSION['nuevo_cliente_nombre'] = $nombre_cliente;
                $_SESSION['mensaje_rapido'] = "✅ Cliente '{$nombre_cliente}' registrado exitosamente";
                $_SESSION['tipo_rapido'] = 'success';
                
                // REDIRIGIR DE VUELTA (si viene de registro rápido)
                $return = $_REQUEST['return'] ?? null;
                if ($return) {
                    header("Location: ?url=" . urlencode($return) . "&type=create");
                    exit;
                }

                header("Location: ?url=notasalida&type=create");
                exit;
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = '❌ ' . $e->getMessage();
            
            $return = $_REQUEST['return'] ?? null;
            if ($return) {
                header("Location: ?url=" . urlencode($return) . "&type=create");
                exit;
            }
            header("Location: ?url=notasalida&type=create");
            exit;
        }
    }
    
    // ACTUALIZAR

    if ($type === 'update') {
        try {
            $id = $_POST['id_nota_salida'] ?? 0;
            $idUsuario = obtenerIdUsuarioValido($db);
            
            //RECIBIR DETALLES COMO ARRAYS (SIN JSON)
            $detalles = [];
            $productos_ids = $_POST['detalle_producto'] ?? [];
            $cantidades = $_POST['detalle_cantidad'] ?? [];
            
            for ($i = 0; $i < count($productos_ids); $i++) {
                if (!empty($productos_ids[$i]) && !empty($cantidades[$i])) {
                    $detalles[] = [
                        'id_producto' => intval($productos_ids[$i]),
                        'cantidad' => intval($cantidades[$i])
                    ];
                }
            }
            
            if (empty($detalles) || count($detalles) === 0) {
                throw new Exception("Debe agregar al menos un producto.");
            }
            
            if (empty($_POST['id_cliente'])) {
                throw new Exception("Debe seleccionar un cliente.");
            }
            
            foreach ($detalles as $d) {
                if ($d['cantidad'] <= 0) {
                    throw new Exception("La cantidad debe ser mayor a 0.");
                }
            }
            
            $datos = ['id_cliente' => intval($_POST['id_cliente'])];
            $resultado = $modelo->actualizarNota($id, $datos, $detalles, $idUsuario);
            
            if ($resultado) {
                $_SESSION['mensaje'] = '✅ Nota de Salida actualizada exitosamente.';
                $_SESSION['tipo_mensaje'] = 'success';
                header("Location: ?url=notasalida&type=list");
                exit();
            }
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
    
    // ELIMINAR

    if ($type === 'eliminar') {
        try {
            $idNota = $_POST['id_nota_salida'] ?? 0;
            $motivo = trim($_POST['motivo_eliminacion'] ?? '');
            $idUsuario = obtenerIdUsuarioValido($db);
            
            if (empty($motivo)) {
                throw new Exception("Debe indicar el motivo de eliminación.");
            }
            
            if (!$idNota || !is_numeric($idNota) || $idNota <= 0) {
                throw new Exception("ID de nota inválido.");
            }
            
            $resultado = $modelo->eliminarNota($idNota);
            
            if ($resultado) {
                if (!isset($_SESSION['contador_eliminaciones'])) {
                    $_SESSION['contador_eliminaciones'] = 0;
                }
                $_SESSION['contador_eliminaciones']++;
                
                $_SESSION['mensaje'] = '✅ Nota de Salida eliminada exitosamente. El stock ha sido revertido.';
                $_SESSION['tipo_mensaje'] = 'warning';
                header("Location: ?url=notasalida&type=list");
                exit();
            } else {
                throw new Exception("No se pudo eliminar la nota.");
            }
            
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
}

// 4. VISTAS

$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'nota_salida';

// CREAR
if ($type === 'create') {
    try {
        $productos = $modelo->obtenerProductosConCategoria();
        $clientes = $clienteModel->obtenerClientes();
        $categorias = $productoModel->obtenerCategorias();
    } catch (Exception $e) {
        die("ERROR al obtener datos: " . $e->getMessage());
    }
    
    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "registroNotaSalidaView.php";
    if (!file_exists($viewFile)) {
        die("ERROR: No se encuentra registroNotaSalidaView.php");
    }
    require_once $viewFile;
    exit();
}

// VER DETALLE
if ($type === 'show' && $id) {
    try {
        $nota = $modelo->obtenerNotaPorId($id);
        if (!$nota) {
            die("ERROR: Nota de salida no encontrada.");
        }
    } catch (Exception $e) {
        die("ERROR al obtener nota: " . $e->getMessage());
    }
    
    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "detalleNotasalidaView.php";
    if (!file_exists($viewFile)) {
        die("ERROR: No se encuentra detalleNotasalidaView.php");
    }
    require_once $viewFile;
    exit();
}

// La funcionalidad de edición fue eliminada intencionalmente.

// LISTAR
try {
    $notas_full = $modelo->listarNotasSalida();
    $resumen = $modelo->getResumen();

    $por_pagina = (int) ($_GET['por_pagina'] ?? 10);
    $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
    $offset = ($pagina - 1) * $por_pagina;
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

$viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "listNotasalidaView.php";
if (!file_exists($viewFile)) {
    die("ERROR: No se encuentra listNotasalidaView.php");
}
require_once $viewFile;
?>