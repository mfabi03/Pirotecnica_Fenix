<?php
// app/view/productos/productos_show.php
if (!isset($producto) || empty($producto)) {
    die('Producto no encontrado');
}
require_once dirname(__DIR__, 2) . "/view/header.php";

$jsonPath = __DIR__ . '/../../../public/uploads/products_imagenes.json';
$productosData = [];
if (file_exists($jsonPath)) {
    $productosData = json_decode(file_get_contents($jsonPath), true) ?? [];
}

$productoKey = (string) ($producto['id_producto'] ?? '');
$especificaciones = $productosData[$productoKey]['especificaciones'] ?? 'No hay especificaciones registradas.';
$proveedor = $productosData[$productoKey]['proveedor'] ?? 'No especificado';
?>

<div class="container-fluid px-4">
    <div class="row">
        <!-- Contenido Principal -->
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-box text-gold me-2"></i> Detalle del Producto
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Información completa del producto
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=productos&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 DETALLE DEL PRODUCTO
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-box me-2"></i> <?= htmlspecialchars($producto['descripcion'] ?? 'Producto') ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <div class="row g-4">
                        
                        <!-- ===== COLUMNA IZQUIERDA ===== -->
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Información del Producto
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">ID Producto:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0; font-weight: 700;">#<?= htmlspecialchars($producto['id_producto'] ?? 'N/A') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0; font-weight: 600;"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Categoría:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;">
                                        <span class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 4px 14px; border-radius: 50px; font-weight: 600; font-size: 0.8rem;">
                                            <?= htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría') ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ===== COLUMNA DERECHA ===== -->
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-chart-line me-2" style="color: #f39c12;"></i> Datos de Inventario
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Stock:</label>
                                    <p style="margin-bottom: 0;">
                                        <?php 
                                            $stock = $producto['stock'] ?? 0;
                                            if ($stock <= 5):
                                        ?>
                                            <span class="badge" style="background: #dc3545; color: #fff; padding: 4px 14px; border-radius: 50px; font-weight: 600;">
                                                <?= htmlspecialchars($stock) ?> ⚠️
                                            </span>
                                        <?php elseif ($stock <= 15): ?>
                                            <span class="badge" style="background: #ffc107; color: #1a1a2e; padding: 4px 14px; border-radius: 50px; font-weight: 600;">
                                                <?= htmlspecialchars($stock) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #28a745; color: #fff; padding: 4px 14px; border-radius: 50px; font-weight: 600;">
                                                <?= htmlspecialchars($stock) ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Costo Unitario:</label>
                                    <p style="color: #f39c12; margin-bottom: 0; font-weight: 700; font-size: 1.2rem;">
                                        $<?= number_format($producto['costo_unitario'] ?? 0, 2, ',', '.') ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Proveedor:</label>
                                    <p style="margin-bottom: 0;">
                                        <?php if (!empty($proveedor) && $proveedor != 'No especificado'): ?>
                                            <span class="badge" style="background: rgba(13, 202, 240, 0.15); color: #0dcaf0; padding: 4px 14px; border-radius: 50px; font-weight: 600;">
                                                <i class="fas fa-truck me-1"></i> <?= htmlspecialchars($proveedor) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #6c757d;"><?= htmlspecialchars($proveedor) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==========================================
                         ESPECIFICACIONES TÉCNICAS
                         ========================================== -->
                    <div class="mt-4">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                <i class="fas fa-list me-2" style="color: #f39c12;"></i> Especificaciones Técnicas
                            </h6>
                            <?php if (!empty($especificaciones) && $especificaciones != 'No hay especificaciones registradas.'): ?>
                                <div style="color: #1a1a2e; white-space: pre-wrap; font-family: inherit;">
                                    <?= nl2br(htmlspecialchars($especificaciones)) ?>
                                </div>
                            <?php else: ?>
                                <div style="color: #6c757d; text-align: center; padding: 20px 0;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?= htmlspecialchars($especificaciones) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- ==========================================
                         BOTONES DE ACCIÓN
                         ========================================== -->
                    <div class="text-center mt-4" style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                        <a href="?url=productos&type=edit&id=<?= htmlspecialchars($producto['id_producto'] ?? '') ?>" 
                           class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-edit me-2"></i> Editar Producto
                        </a>
                        
                        <form method="POST" action="?url=productos&type=delete" style="display: inline;">
                            <input type="hidden" name="id_producto" value="<?= htmlspecialchars($producto['id_producto'] ?? '') ?>">
                            <button type="submit" class="btn" style="background: #dc3545; color: #fff !important; font-weight: 600; padding: 10px 30px; border-radius: 50px; border: none; transition: all 0.3s ease; margin-left: 10px;"
                                    onclick="return confirm('¿Estás seguro de eliminar este producto?\n\nEsta acción no se puede deshacer.');"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(220,53,69,0.3)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <i class="fas fa-trash-alt me-2"></i> Eliminar Producto
                            </button>
                        </form>
                        
                        <a href="?url=productos&type=list" class="btn" style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.6) !important; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-left: 10px;">
                            <i class="fas fa-list me-1"></i> Ver todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>