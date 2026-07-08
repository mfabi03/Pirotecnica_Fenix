<?php
require_once __DIR__ . '/../header.php';

$jsonPath = __DIR__ . '/../../../public/uploads/products_imagenes.json';
$productosData = [];
if (file_exists($jsonPath)) {
    $productosData = json_decode(file_get_contents($jsonPath), true) ?? [];
}

$productoKey = (string) ($producto['id_producto'] ?? '');
$especificacionesActual = $productosData[$productoKey]['especificaciones'] ?? '';
$idProveedorActual = $productosData[$productoKey]['id_proveedor'] ?? 0;
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-edit me-2"></i> Editar Producto</h4>
            <a href="?url=productos&type=list" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="?url=productos&type=update">
            <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
            
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                    <input type="text" name="descripcion" class="form-control" required 
                           value="<?= htmlspecialchars($producto['descripcion']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Categoría <span class="text-danger">*</span></label>
                    <select name="id_categoria" class="form-select" required>
                        <option value="">Seleccione una categoría...</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id_categoria'] ?>" 
                                <?= ($c['id_categoria'] == $producto['id_categoria']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nombre_categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                    <select name="id_proveedor" class="form-select" required>
                        <option value="">Seleccione un proveedor...</option>
                        <?php foreach ($proveedores as $p): ?>
                            <option value="<?= $p['id_proveedor'] ?>" 
                                <?= ($idProveedorActual > 0 && $p['id_proveedor'] == $idProveedorActual) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['razon_social']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cantidad (Stock) <span class="text-danger">*</span></label>
                    <input type="number" name="cantidad" class="form-control" required 
                           min="0" value="<?= htmlspecialchars($producto['stock'] ?? 0) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Costo Unitario <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="costo_unitario" class="form-control" required 
                               min="0" step="0.01" value="<?= htmlspecialchars($producto['costo_unitario'] ?? 0) ?>">
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Especificaciones Técnicas</label>
                    <textarea name="especificaciones" class="form-control" rows="4" 
                              placeholder="Ej: Peso: 2kg, Color: Rojo, Material: Plástico"><?= htmlspecialchars($especificacionesActual) ?></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-save me-1"></i> Actualizar Producto
                </button>
                <a href="?url=productos&type=list" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
                <a href="?url=productos&type=show&id=<?= $producto['id_producto'] ?>" class="btn btn-info ms-2">
                    <i class="fas fa-eye me-1"></i> Ver Detalle
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>