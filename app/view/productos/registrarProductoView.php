<?php
// app/view/productos/productos_create.php
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="container-fluid px-4">
    <div class="row">       
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-box text-gold me-2"></i> Registro de Productos
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Complete todos los campos para registrar un nuevo producto
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
                 FORMULARIO
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-box me-2"></i> Nuevo Producto
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="?url=productos&type=store<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>">
                        
                        <?php if (isset($_GET['return'])): ?>
                            <input type="hidden" name="registro_rapido" value="1">
                            <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return']) ?>">
                        <?php endif; ?>
                        
                        <div class="row g-3">
                            
                            <!-- ===== DATOS DEL PRODUCTO ===== -->
                            <div class="col-12">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos del Producto
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="descripcion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre del Producto *</label>
                                        <input type="text" name="descripcion" id="descripcion" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);"
                                               placeholder="Ej: Pólvora" required
                                               value="<?= htmlspecialchars($_POST['descripcion'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_categoria" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Categoría *</label>
                                        <div class="input-group">
                                            <select name="id_categoria" id="id_categoria" class="form-select" required
                                                    style="border-radius: 12px 0 0 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                                                <option value="">Seleccione una categoría...</option>
                                                <?php foreach ($categorias as $c): ?>
                                                    <option value="<?= $c['id_categoria'] ?>"
                                                        <?= (isset($_SESSION['nueva_categoria_id']) && $_SESSION['nueva_categoria_id'] == $c['id_categoria']) ? 'selected' : '' ?>
                                                        <?= (isset($_POST['id_categoria']) && $_POST['id_categoria'] == $c['id_categoria']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($c['nombre_categoria']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <a href="?url=categorias&action=registrar&return=productos" 
                                               class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 15px; display: flex; align-items: center; transition: all 0.3s ease;"
                                               title="Registrar nueva categoría"
                                               onmouseover="this.style.transform='scale(1.05)';"
                                               onmouseout="this.style.transform='scale(1)';">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                        <?php unset($_SESSION['nueva_categoria_id']); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_proveedor" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Proveedor *</label>
                                        <div class="input-group">
                                            <select name="id_proveedor" id="id_proveedor" class="form-select" required
                                                    style="border-radius: 12px 0 0 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                                                <option value="">Seleccione un proveedor...</option>
                                                <?php foreach ($proveedores as $p): ?>
                                                    <option value="<?= $p['id_proveedor'] ?>"
                                                        <?= (isset($_SESSION['nuevo_proveedor_id']) && $_SESSION['nuevo_proveedor_id'] == $p['id_proveedor']) ? 'selected' : '' ?>
                                                        <?= (isset($_POST['id_proveedor']) && $_POST['id_proveedor'] == $p['id_proveedor']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($p['razon_social']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <a href="?url=proveedores&type=create&return=productos" 
                                               class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 15px; display: flex; align-items: center; transition: all 0.3s ease;"
                                               title="Registrar nuevo proveedor"
                                               onmouseover="this.style.transform='scale(1.05)';"
                                               onmouseout="this.style.transform='scale(1)';">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </div>
                                        <?php unset($_SESSION['nuevo_proveedor_id']); ?>
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
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);"
                                               min="0" placeholder="0" required
                                               value="<?= htmlspecialchars($_POST['cantidad'] ?? 0) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="costo_unitario" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Costo Unitario *</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="border-radius: 12px 0 0 12px; border: 1.5px solid rgba(0,0,0,0.08); border-right: none; background: #f8f9fa;">$</span>
                                            <input type="number" name="costo_unitario" id="costo_unitario" class="form-control" 
                                                   style="border-radius: 0 12px 12px 0; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); border-left: none;"
                                                   min="0" step="0.01" placeholder="0.00" required
                                                   value="<?= htmlspecialchars($_POST['costo_unitario'] ?? 0) ?>">
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
                                                  placeholder="Ej: Peso: 2kg, Color: Rojo, Material: Plástico"><?= htmlspecialchars($_POST['especificaciones'] ?? '') ?></textarea>
                                        <small style="color: #6c757d; font-size: 0.7rem;">Describe las características técnicas del producto</small>
                                    </div>
                                </div>
                            </div>

                            <!-- ==========================================
                                 BOTONES DE ACCIÓN
                                 ========================================== -->
                            <div class="col-12 text-end" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px; margin-top: 10px;">
                                <?php if (isset($_GET['return'])): ?>
                                    <a href="?url=<?= $_GET['return'] ?>&type=create" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                        <i class="fas fa-times me-1"></i> Cancelar
                                    </a>
                                <?php else: ?>
                                    <a href="?url=productos&type=list" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                        <i class="fas fa-times me-1"></i> Cancelar
                                    </a>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                                    <i class="fas fa-save me-2"></i> Registrar Producto
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