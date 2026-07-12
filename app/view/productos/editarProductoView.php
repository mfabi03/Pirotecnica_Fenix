<?php
// app/view/productos/productos_edit.php
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
$especificacionesActual = $productosData[$productoKey]['especificaciones'] ?? '';
$idProveedorActual = $productosData[$productoKey]['id_proveedor'] ?? 0;
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
                            <i class="fas fa-edit text-gold me-2"></i> Editar Producto
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Modifique los datos del producto: <?= htmlspecialchars($producto['descripcion'] ?? '') ?>
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
                 FORMULARIO DE EDICIÓN
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-box me-2"></i> Datos del Producto
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="?url=productos&type=update">
                        <input type="hidden" name="id_producto" value="<?= htmlspecialchars($producto['id_producto'] ?? '') ?>">
                        
                        <div class="row g-3">
                            
                            <!-- ===== DATOS DEL PRODUCTO ===== -->
                            <div class="col-12">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Información del Producto
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="descripcion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre del Producto *</label>
                                        <input type="text" name="descripcion" id="descripcion" class="form-control" 
                                               value="<?= htmlspecialchars($producto['descripcion'] ?? '') ?>"
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);"
                                               required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_categoria" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Categoría *</label>
                                        <select name="id_categoria" id="id_categoria" class="form-select" required
                                                style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                                            <option value="">Seleccione una categoría</option>
                                            <?php if (!empty($categorias)): ?>
                                                <?php foreach ($categorias as $c): ?>
                                                    <option value="<?= htmlspecialchars($c['id_categoria']) ?>"
                                                        <?= ($c['id_categoria'] == ($producto['id_categoria'] ?? 0)) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($c['nombre_categoria']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="">No hay categorías disponibles</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_proveedor" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Proveedor *</label>
                                        <select name="id_proveedor" id="id_proveedor" class="form-select" required
                                                style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                                            <option value="">Seleccione un proveedor</option>
                                            <?php if (!empty($proveedores)): ?>
                                                <?php foreach ($proveedores as $p): ?>
                                                    <option value="<?= htmlspecialchars($p['id_proveedor']) ?>"
                                                        <?= ($idProveedorActual > 0 && $p['id_proveedor'] == $idProveedorActual) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($p['razon_social']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="">No hay proveedores disponibles</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== DATOS DE INVENTARIO ===== -->
                            <div class="col-12">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-chart-line me-2" style="color: #f39c12;"></i> Datos de Inventario
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="cantidad" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cantidad (Stock) *</label>
                                        <input type="number" name="cantidad" id="cantidad" class="form-control" 
                                               value="<?= htmlspecialchars($producto['stock'] ?? 0) ?>"
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);"
                                               min="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="costo_unitario" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Costo Unitario *</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="border-radius: 12px 0 0 12px; border: 1.5px solid rgba(0,0,0,0.08); border-right: none; background: #f8f9fa;">$</span>
                                            <input type="number" name="costo_unitario" id="costo_unitario" class="form-control" 
                                                   value="<?= htmlspecialchars($producto['costo_unitario'] ?? 0) ?>"
                                                   style="border-radius: 0 12px 12px 0; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); border-left: none;"
                                                   min="0" step="0.01" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== ESPECIFICACIONES ===== -->
                            <div class="col-12">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-list me-2" style="color: #f39c12;"></i> Especificaciones Técnicas
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="especificaciones" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Especificaciones</label>
                                        <textarea name="especificaciones" id="especificaciones" class="form-control" rows="4"
                                                  style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); resize: vertical;"
                                                  placeholder="Ej: Peso: 2kg, Color: Rojo, Material: Plástico"><?= htmlspecialchars($especificacionesActual) ?></textarea>
                                        <small style="color: #6c757d; font-size: 0.7rem;">Información adicional del producto</small>
                                    </div>
                                </div>
                            </div>

                            <!-- ==========================================
                                 BOTONES DE ACCIÓN
                                 ========================================== -->
                            <div class="col-12 text-end" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px; margin-top: 10px;">
                                <a href="?url=productos&type=list" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </a>
                                
                                <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                                    <i class="fas fa-save me-2"></i> Actualizar producto
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>