<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-box me-2"></i> Registrar Producto</h4>
            <a href="?url=productos&type=list" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <!-- Mostrar mensajes de éxito/error -->
        <?php if (isset($_SESSION['mensaje_rapido'])): ?>
            <div class="alert alert-<?= $_SESSION['tipo_rapido'] ?? 'success' ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= ($_SESSION['tipo_rapido'] ?? 'success') == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($_SESSION['mensaje_rapido']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje_rapido'], $_SESSION['tipo_rapido']); ?>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!--  FORMULARIO CON RETORNO -->
        <form method="POST" action="?url=productos&type=store<?= isset($_GET['return']) ? '&return=' . $_GET['return'] : '' ?>">
            
            <?php if (isset($_GET['return'])): ?>
                <input type="hidden" name="registro_rapido" value="1">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                    <input type="text" name="descripcion" class="form-control" required 
                           placeholder="Ej:polvora "
                           value="<?= htmlspecialchars($_POST['descripcion'] ?? '') ?>">
                </div>

                <!--  CATEGORÍA CON BOTÓN DE REGISTRO RÁPIDO (REDIRECCIÓN) -->
                <div class="col-md-6">
                    <label class="form-label">Categoría <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="id_categoria" class="form-select" required>
                            <option value="">Seleccione una categoría...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id_categoria'] ?>"
                                    <?= (isset($_SESSION['nueva_categoria_id']) && $_SESSION['nueva_categoria_id'] == $c['id_categoria']) ? 'selected' : '' ?>
                                    <?= (isset($_POST['id_categoria']) && $_POST['id_categoria'] == $c['id_categoria']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre_categoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <a href="?url=configuracion&type=create&return=productos" 
                           class="btn btn-gold" 
                           title="Registrar nueva categoría">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                    <?php unset($_SESSION['nueva_categoria_id']); ?>
                </div>

                <!-- PROVEEDOR CON BOTÓN DE REGISTRO RÁPIDO (REDIRECCIÓN) -->
                <div class="col-md-6">
                    <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="id_proveedor" class="form-select" required>
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
                           class="btn btn-gold" 
                           title="Registrar nuevo proveedor">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                    <?php unset($_SESSION['nuevo_proveedor_id']); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cantidad (Stock) <span class="text-danger">*</span></label>
                    <input type="number" name="cantidad" class="form-control" required 
                           min="0" placeholder="0"
                           value="<?= htmlspecialchars($_POST['cantidad'] ?? 0) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Costo Unitario <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="costo_unitario" class="form-control" required 
                               min="0" step="0.01" placeholder="0.00"
                               value="<?= htmlspecialchars($_POST['costo_unitario'] ?? 0) ?>">
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Especificaciones Técnicas</label>
                    <textarea name="especificaciones" class="form-control" rows="4" 
                              placeholder="Ej: Peso: 2kg, Color: Rojo, Material: Plástico"><?= htmlspecialchars($_POST['especificaciones'] ?? '') ?></textarea>
                    <small class="text-muted">Describe las características técnicas del producto</small>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-save me-1"></i> Guardar Producto
                </button>
                
                <?php if (isset($_GET['return'])): ?>
                    <a href="?url=<?= $_GET['return'] ?>&type=create" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Cancelar y volver
                    </a>
                <?php else: ?>
                    <a href="?url=productos&type=list" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>