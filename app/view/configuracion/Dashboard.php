<?php 
// app/view/configuracion/dashboard.php
require_once dirname(__DIR__, 2) . "/view/header.php"; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mensajes de error de permisos
if (!empty($_SESSION['error'])): ?>
    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-md-8 col-lg-12">
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8 col-lg-12">
            
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-chart-pie text-gold me-2"></i> Panel de Control
                        </h3>
                    
                    </div>
                    <div class="col-auto">
                        <span class="badge" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); padding: 6px 14px; border-radius: 50px;">
                            <i class="fas fa-database me-1"></i> Resumen General
                        </span>
                    </div>
                </div>
            </div>
                       
                <?php if (($stats['productos_criticos'] ?? 0) > 0): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert dark-alert-warning alert-dismissible fade show shadow-sm border-0 mb-0" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-3 fs-4 text-warning"></i>
                                    <div class="flex-grow-1">
                                        <strong>Alerta de Stock Mínimo:</strong>
                                        Hay <?= (int)($stats['productos_criticos'] ?? 0) ?> 
                                        producto<?= (($stats['productos_criticos'] ?? 0) > 1) ? 's' : '' ?> 
                                        con existencias críticas. 
                                        <span class="text-warning">Revise el módulo de Productos.</span>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="alert"></button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            
            <div class="row g-4 mb-4">
              
                <div class="col-12 col-md-3">
                    <div class="stat-card stat-card-primary dashboard-stat-interactive" role="button" tabindex="0" aria-haspopup="dialog" aria-label="Ver inventario total" data-bs-toggle="modal" data-bs-target="#modalInventarioTotal" title="Ver detalle del inventario">
                        <div class="stat-card-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Inventario Total</span>
                            <span class="stat-card-number"><?= number_format($stats['total_productos'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Productos registrados
                            </span>
                        </div>
                    </div>
                </div>
               
                <div class="col-12 col-md-3">
                    <div class="stat-card stat-card-success dashboard-stat-interactive" role="button" tabindex="0" aria-haspopup="dialog" aria-label="Ver usuarios registrados" data-bs-toggle="modal" data-bs-target="#modalUsuarios" title="Ver usuarios">
                        <div class="stat-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Usuarios</span>
                            <span class="stat-card-number"><?= number_format($stats['total_usuarios'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Usuarios registrados
                            </span>
                        </div>
                    </div>
                </div>
                
                
                <div class="col-12 col-md-3">
                    <div class="stat-card stat-card-warning dashboard-stat-interactive" role="button" tabindex="0" aria-haspopup="dialog" aria-label="Ver productos con stock crítico" data-bs-toggle="modal" data-bs-target="#modalStockCritico" title="Ver stock crítico">
                        <div class="stat-card-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Stock Crítico</span>
                            <span class="stat-card-number"><?= number_format($stats['productos_criticos'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-down">
                                <i class="fas fa-arrow-down"></i> Productos con stock bajo
                            </span>
                        </div>
                    </div>
                </div>
          
                <div class="col-12 col-md-3">
                    <div class="stat-card stat-card-info dashboard-stat-interactive" role="button" tabindex="0" aria-haspopup="dialog" aria-label="Ver clientes registrados" data-bs-toggle="modal" data-bs-target="#modalClientes" title="Ver clientes">
                        <div class="stat-card-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Clientes</span>
                            <span class="stat-card-number"><?= number_format($stats['total_clientes'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Clientes registrados
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="row g-4 mb-4">
             
                <div class="col-12 col-md-6">
                    <div class="stat-card stat-card-purple dashboard-stat-interactive" role="button" tabindex="0" aria-haspopup="dialog" aria-label="Ver últimas notas de salida" data-bs-toggle="modal" data-bs-target="#modalNotasSalida" title="Ver notas de salida">
                        <div class="stat-card-icon">
                            <i class="fas fa-sign-out-alt"></i> <!-- Ícono corregido -->
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Notas de Salida</span>
                            <span class="stat-card-number"><?= number_format($stats['total_nota_salida'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Salidas registradas
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-md-6">
                    <div class="stat-card dashboard-stat-interactive" role="button" tabindex="0" aria-haspopup="dialog" aria-label="Ver últimas notas de entrada" data-bs-toggle="modal" data-bs-target="#modalNotasEntrada" title="Ver notas de entrada" style="border-color: rgba(108, 117, 125, 0.3);">
                        <div class="stat-card-icon" style="background: rgba(108, 117, 125, 0.12); color: #adb5bd;">
                            <i class="fas fa-sign-in-alt"></i> 
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Notas de Entrada</span>
                            <span class="stat-card-number"><?= number_format($stats['total_nota_entrada'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Entradas registradas
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$modalesDashboard = [
    'usuarios' => [
        'id' => 'modalUsuarios', 'titulo' => 'Usuarios Registrados', 'icono' => 'fa-users',
        'columnas' => ['ID', 'Nombre completo', 'Correo electrónico', 'Rol', 'Estado'],
        'filas' => array_map(static function ($usuario) {
            return [
                $usuario['id_usuario'] ?? '',
                trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')),
                $usuario['correo_electronico'] ?? '',
                $usuario['rol'] ?? 'Sin rol',
                $usuario['estado'] ?? 'Activo'
            ];
        }, $usuariosDetalle ?? [])
    ],
    'stock' => [
        'id' => 'modalStockCritico', 'titulo' => 'Productos con Stock Crítico', 'icono' => 'fa-exclamation-triangle',
        'columnas' => ['Código', 'Producto', 'Stock actual', 'Stock mínimo', 'Proveedor'],
        'filas' => array_map(static function ($producto) {
            return [
                $producto['id_producto'] ?? '',
                $producto['descripcion'] ?? '',
                $producto['cantidad'] ?? 0,
                $producto['stock_minimo'] ?? 0,
                $producto['proveedor'] ?? 'Sin proveedor'
            ];
        }, $productosCriticosDetalle ?? [])
    ],
    'clientes' => [
        'id' => 'modalClientes', 'titulo' => 'Clientes Registrados', 'icono' => 'fa-user-friends',
        'columnas' => ['Cédula / RIF', 'Nombre / Razón Social', 'Tipo', 'Teléfono', 'Correo'],
        'filas' => array_map(static function ($cliente) {
            return [
                $cliente['documento'] ?? '',
                $cliente['nombre'] ?? '',
                $cliente['tipo_cliente'] ?? '',
                $cliente['telefono'] ?? '',
                $cliente['correo_electronico'] ?? ''
            ];
        }, $clientesDetalle ?? [])
    ],
    'entradas' => [
        'id' => 'modalNotasEntrada', 'titulo' => 'Últimas Notas de Entrada', 'icono' => 'fa-sign-in-alt',
        'columnas' => ['Número', 'Proveedor', 'Fecha', 'Total', 'Estado'],
        'filas' => array_map(static function ($nota) {
            return [
                $nota['id_nota_entrada'] ?? '',
                $nota['proveedor'] ?? 'Sin proveedor',
                $nota['fecha_ingreso'] ?? '',
                '$' . number_format((float) ($nota['costo_total'] ?? 0), 2, ',', '.'),
                $nota['estado'] ?? 'Activa'
            ];
        }, $notasEntradaDetalle ?? [])
    ],
    'salidas' => [
        'id' => 'modalNotasSalida', 'titulo' => 'Últimas Notas de Salida', 'icono' => 'fa-sign-out-alt',
        'columnas' => ['Número', 'Cliente', 'Fecha', 'Total', 'Estado'],
        'filas' => array_map(static function ($nota) {
            return [
                $nota['id_nota_salida'] ?? '',
                $nota['cliente'] ?? 'Sin cliente',
                $nota['fecha'] ?? '',
                number_format((int) ($nota['total'] ?? 0)) . ' unidades',
                $nota['estado'] ?? 'Activa'
            ];
        }, $notasSalidaDetalle ?? [])
    ],
    'inventario' => [
        'id' => 'modalInventarioTotal', 'titulo' => 'Inventario Total', 'icono' => 'fa-boxes',
        'columnas' => ['Código', 'Producto', 'Categoría', 'Stock actual', 'Precio'],
        'filas' => array_map(static function ($producto) {
            return [
                $producto['id_producto'] ?? '',
                $producto['descripcion'] ?? '',
                $producto['nombre_categoria'] ?? 'Sin categoría',
                $producto['cantidad'] ?? 0,
                '$' . number_format((float) ($producto['precio'] ?? 0), 2, ',', '.')
            ];
        }, $inventarioDetalle ?? [])
    ]
];
?>

<?php foreach ($modalesDashboard as $modal): ?>
<div class="modal fade" id="<?= htmlspecialchars($modal['id']) ?>" tabindex="-1" aria-hidden="true" aria-labelledby="<?= htmlspecialchars($modal['id']) ?>Label">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" id="<?= htmlspecialchars($modal['id']) ?>Label" style="color: #ffffff; font-weight: 700;">
                    <i class="fas <?= htmlspecialchars($modal['icono']) ?> me-2" style="color: #f39c12;"></i><?= htmlspecialchars($modal['titulo']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <div class="modal-body" style="padding: 20px 24px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <?php foreach ($modal['columnas'] as $columna): ?>
                                    <th scope="col" class="text-nowrap"><?= htmlspecialchars($columna) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($modal['filas'])): ?>
                                <?php foreach ($modal['filas'] as $fila): ?>
                                    <tr>
                                        <?php foreach ($fila as $valor): ?>
                                            <td><?= htmlspecialchars((string) $valor) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= count($modal['columnas']) ?>" class="text-center text-muted py-4">No hay registros para mostrar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.dashboard-stat-interactive {
    cursor: pointer;
}

.dashboard-stat-interactive:focus-visible {
    outline: 3px solid #f39c12;
    outline-offset: 3px;
}
</style>

<script>
document.querySelectorAll('.dashboard-stat-interactive').forEach(function(card) {
    card.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        event.preventDefault();
        const modalElement = document.querySelector(card.getAttribute('data-bs-target'));
        if (modalElement) bootstrap.Modal.getOrCreateInstance(modalElement).show();
    });
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>