<?php
// ==========================================
// CONFIGURACIÓN DE ERRORES
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CARGA DE AUTOLOAD Y SESIÓN
// ==========================================
require 'vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// ENRUTADOR PRINCIPAL
// ==========================================
$url = $_GET['url'] ?? 'main';
$type = $_GET['type'] ?? 'list';

// ==========================================
// FUNCIÓN DE AYUDA PARA GENERAR URLs
// ==========================================
function url($modulo, $tipo = 'list', $params = []) {
    $url = "?url={$modulo}&type={$tipo}";
    foreach ($params as $key => $value) {
        $url .= "&{$key}={$value}";
    }
    return $url;
}

// ==========================================
// RUTAS DE LOS MÓDULOS
// ==========================================
switch ($url) {
    
    // ==========================================
    // 🏠 MAIN (PÁGINA DE INICIO)
    // ==========================================
    case 'main':
    default:
        require_once __DIR__ . '/app/view/header.php';
        ?>
        <div class="col-md-9 col-lg-10">
            <div class="p-3">
                <div class="card shadow-sm p-4">
                    <div class="text-center">
                        <h2 class="mb-3">
                            <span style="color: #DAA520;">🔥</span> 
                            Bienvenido a Pirotecnia Fénix
                        </h2>
                        <p class="text-muted">Selecciona una opción del menú lateral para comenzar.</p>
                        <hr>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <i class="fas fa-box fa-3x text-primary"></i>
                                <h5 class="mt-2">Productos</h5>
                                <p class="text-muted small">Gestiona tu catálogo de productos</p>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-truck fa-3x text-success"></i>
                                <h5 class="mt-2">Proveedores</h5>
                                <p class="text-muted small">Administra tus proveedores</p>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-file-invoice fa-3x text-warning"></i>
                                <h5 class="mt-2">Notas</h5>
                                <p class="text-muted small">Control de entradas y salidas de stock</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        require_once __DIR__ . '/app/view/footer.php';
        break;
    
    // ==========================================
    // 📦 PRODUCTOS
    // ==========================================
    case 'productos':
    require_once __DIR__ . '/app/controller/productosController.php';
    break;
    // ==========================================
    // 🚚 PROVEEDORES
    // ==========================================
    case 'proveedores':
        require_once __DIR__ . '/app/controller/proveedoresController.php';
        break;
    
    // ==========================================
    // 👥 CLIENTES
    // ==========================================
    case 'clientes':
        require_once __DIR__ . '/app/controller/clientesController.php';
        break;
    
    // ==========================================
    // 📥 NOTA DE ENTRADA
    // ==========================================
    case 'notaentrada':
        require_once __DIR__ . '/app/controller/notaentradaController.php';
        break;
    
    // ==========================================
    // 📤 NOTA DE SALIDA
    // ==========================================
    case 'notasalida':
        require_once __DIR__ . '/app/controller/notasalidaController.php';
        break;
    
    // ==========================================
    // ⚙️ CONFIGURACIÓN (CATEGORÍAS)
    // ==========================================
    case 'configuracion':
        require_once __DIR__ . '/app/controller/configuracionController.php';
        break;
    
    // ==========================================
    // 🔐 SEGURIDAD (Usuarios y Roles)
    // ==========================================
    case 'seguridad':
        require_once __DIR__ . '/app/controller/seguridadController.php';
        break;
    
    // ==========================================
    // 📊 REPORTES
    // ==========================================
    case 'reportes':
        require_once __DIR__ . '/app/controller/reportesController.php';
        break;
}
?>