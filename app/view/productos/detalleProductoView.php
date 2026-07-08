<?php
require_once __DIR__ . '/../header.php';

$jsonPath = __DIR__ . '/../../../public/uploads/products_imagenes.json';
$productosData = [];
if (file_exists($jsonPath)) {
    $productosData = json_decode(file_get_contents($jsonPath), true) ?? [];
}

$productoKey = (string) ($producto['id_producto'] ?? '');
$especificaciones = $productosData[$productoKey]['especificaciones'] ?? 'No hay especificaciones registradas.';
$proveedor = $productosData[$productoKey]['proveedor'] ?? 'No especificado';
?>

<div class="col-md-8 col-lg-12">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-box me-2"></i> Detalle del Producto</h4>
            <a href="?url=productos&type=list" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
        <hr>

        <?php if (isset($producto) && $producto): ?>
            <div class="row g-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Información del Producto</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">ID Producto:</th>
                                            <td><?= htmlspecialchars($producto['id_producto']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nombre:</th>
                                            <td><strong><?= htmlspecialchars($producto['descripcion']) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Categoría:</th>
                                            <td><?= htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Stock:</th>
                                            <td>
                                                <?php if ($producto['stock'] <= 5): ?>
                                                    <span class="badge bg-danger"><?= htmlspecialchars($producto['stock']) ?></span>
                                                <?php elseif ($producto['stock'] <= 15): ?>
                                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($producto['stock']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?= htmlspecialchars($producto['stock']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Precio Unitario:</th>
                                            <td><strong>$<?= number_format($producto['costo_unitario'], 2, ',', '.') ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Proveedor:</th>
                                            <td>
                                                <?php if (!empty($proveedor) && $proveedor != 'No especificado'): ?>
                                                    <span class="badge bg-info"><?= htmlspecialchars($proveedor) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= htmlspecialchars($proveedor) ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Especificaciones Técnicas</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($especificaciones) && $especificaciones != 'No hay especificaciones registradas.'): ?>
                                <div class="p-3 bg-light rounded" style="white-space: pre-wrap; font-family: inherit;">
                                    <?= nl2br(htmlspecialchars($especificaciones)) ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?= htmlspecialchars($especificaciones) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="?url=productos&type=edit&id=<?= $producto['id_producto'] ?>" class="btn btn-gold">
                    <i class="fas fa-edit me-1"></i> Editar
                </a>
                <form method="POST" action="?url=productos&type=delete" style="display:inline;" 
                      onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                    <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> Producto no encontrado.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>