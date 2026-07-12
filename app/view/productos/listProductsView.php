<?php
// app/view/productos/productos_lista.php
require_once dirname(__DIR__, 2) . "/view/header.php";

$jsonPath = __DIR__ . '/../../../public/uploads/products_imagenes.json';
$productosData = [];
if (file_exists($jsonPath)) {
    $productosData = json_decode(file_get_contents($jsonPath), true) ?? [];
}
?>

<div class="container-fluid px-4">
    <div class="row">       
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-cube text-gold me-2"></i> Registro de Productos
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important;">
                            <i class="fas fa-database me-2"></i> 
                            <?= isset($productos) ? count($productos) : 0 ?> productos registrados
                        </small>
                    </div>
                    <div class="col-xl-6">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="url" value="productos">
                            <input type="hidden" name="type" value="list">
                            <div class="col-8">
                                <div class="dark-search input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="buscar" class="form-control shadow-none" 
                                           placeholder="Buscar por nombre del producto..."
                                           value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-dark-search w-100" type="submit">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert dark-alert-success alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fs-4"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ==========================================
                 TARJETAS DE ESTADÍSTICAS
                 ========================================== -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card card-total-notas border-left-gold text-center p-3">
                        <h6 class="card-title">TOTAL PRODUCTOS</h6>
                        <h2 class="card-number"><?= $resumen['total_productos'] ?? 0 ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-total-notas border-left-info text-center p-3">
                        <h6 class="card-title">STOCK TOTAL</h6>
                        <h2 class="card-number"><?= $resumen['total_stock'] ?? 0 ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-total-notas border-left-danger text-center p-3">
                        <h6 class="card-title">CATEGORÍAS</h6>
                        <h2 class="card-number"><?= $resumen['total_categorias'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 TABLA DE PRODUCTOS - CABECERA OSCURA
                 ========================================== -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-cube me-2"></i> Productos Registrados
                    </h5>
                    <a href="?url=productos&type=create" class="btn btn-dark-gold">
                        <i class="fas fa-plus me-1"></i> Registrar
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">CÓDIGO</th>
                                <th class="py-3">NOMBRE</th>
                                <th class="py-3">CATEGORÍA</th>
                                <th class="py-3">ESPECIFICACIONES</th>
                                <th class="py-3">COSTO</th>
                                <th class="py-3">STOCK</th>
                                <th class="pe-4 py-3 text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($productos) && is_array($productos) && count($productos) > 0): ?>
                                <?php foreach ($productos as $p): ?>
                                    <?php 
                                    $productoKey = (string) ($p['id_producto'] ?? '');
                                    $especificaciones = $productosData[$productoKey]['especificaciones'] ?? '';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= htmlspecialchars($p['id_producto'] ?? 'N/A') ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($p['descripcion'] ?? '') ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem;">
                                                <?= htmlspecialchars($p['nombre_categoria'] ?? 'Sin categoría') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($especificaciones)): ?>
                                                <span title="<?= htmlspecialchars($especificaciones) ?>" style="cursor: help;">
                                                    <?= htmlspecialchars(substr($especificaciones, 0, 30)) ?>
                                                    <?= strlen($especificaciones) > 30 ? '...' : '' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin especificaciones</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight: 600; color: #ffffff;">
                                            $<?= number_format($p['costo_unitario'] ?? 0, 2, ',', '.') ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $stock = $p['stock'] ?? 0;
                                                if ($stock <= 5):
                                            ?>
                                                <span class="badge" style="background: #dc3545; color: #fff; padding: 4px 12px; border-radius: 50px; font-weight: 600;">
                                                    <?= $stock ?> ⚠️
                                                </span>
                                            <?php elseif ($stock <= 15): ?>
                                                <span class="badge" style="background: #ffc107; color: #1a1a2e; padding: 4px 12px; border-radius: 50px; font-weight: 600;">
                                                    <?= $stock ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #28a745; color: #fff; padding: 4px 12px; border-radius: 50px; font-weight: 600;">
                                                    <?= $stock ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="?url=productos&type=show&id=<?= $p['id_producto'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?url=productos&type=edit&id=<?= $p['id_producto'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="?url=productos&type=delete" class="d-inline">
                                                    <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-cube fa-3x d-block mb-3"></i>
                                            <p class="mb-0">No hay productos registrados</p>
                                            <small>Comienza registrando un nuevo producto</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer -->
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-cube me-1"></i> 
                        Total: <?= isset($productos) ? count($productos) : 0 ?> productos
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>