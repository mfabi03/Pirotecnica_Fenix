<?php
require_once __DIR__ . '/../header.php';

$jsonPath = __DIR__ . '/../../../public/uploads/products_imagenes.json';
$productosData = [];
if (file_exists($jsonPath)) {
    $productosData = json_decode(file_get_contents($jsonPath), true) ?? [];
}
?>

<div class="col-md-9 col-lg-10">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-cube me-2"></i> Productos
            </h4>
            <small class="text-muted">Administra búsqueda, edición y eliminación en un solo lugar.</small>
        </div>
        <a href="?url=productos&type=create" class="btn btn-gold">
            <i class="fas fa-plus me-1"></i> Nuevo Producto
        </a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6>TOTAL PRODUCTOS</h6>
                <h2><?= $resumen['total_productos'] ?? 0 ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6>STOCK TOTAL</h6>
                <h2><?= $resumen['total_stock'] ?? 0 ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6>CATEGORÍAS</h6>
                <h2><?= $resumen['total_categorias'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" action="?url=productos" class="d-flex">
                <input type="hidden" name="url" value="productos">
                <input type="hidden" name="type" value="list">
                <input type="text" name="buscar" class="form-control" 
                       placeholder="Buscar por nombre..." 
                       value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                <button type="submit" class="btn btn-gold ms-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <?php if (isset($_GET['buscar']) && !empty($_GET['buscar'])): ?>
                <a href="?url=productos&type=list" class="btn btn-secondary">Limpiar</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>CÓDIGO</th>
                    <th>NOMBRE</th>
                    <th>CATEGORÍA</th>
                    <th>ESPECIFICACIONES</th>
                    <th>COSTO</th>
                    <th>STOCK</th>
                    <th class="text-end">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-database mb-2 d-block text-muted"></i>
                            No hay productos registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($productos as $p): ?>
                        <?php 
                        $productoKey = (string) ($p['id_producto'] ?? '');
                        $especificaciones = $productosData[$productoKey]['especificaciones'] ?? '';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($p['id_producto']) ?></td>
                            <td><strong><?= htmlspecialchars($p['descripcion']) ?></strong></td>
                            <td><?= htmlspecialchars($p['nombre_categoria'] ?? 'Sin categoría') ?></td>
                            <td>
                                <?php if (!empty($especificaciones)): ?>
                                    <span title="<?= htmlspecialchars($especificaciones) ?>">
                                        <?= nl2br(htmlspecialchars($especificaciones)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Sin especificaciones</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($p['costo_unitario'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($p['stock'] <= 5): ?>
                                    <span class="badge bg-danger"><?= htmlspecialchars($p['stock']) ?></span>
                                <?php elseif ($p['stock'] <= 15): ?>
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($p['stock']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= htmlspecialchars($p['stock']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="?url=productos&type=show&id=<?= $p['id_producto'] ?>" 
                                       class="btn btn-outline-info" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?url=productos&type=edit&id=<?= $p['id_producto'] ?>" 
                                       class="btn btn-outline-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="?url=productos&type=delete" 
                                          style="display:inline;" 
                                          onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                        <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>