<?php
// app/view/configuracion/listCategoriaView.php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-tags me-2"></i> Categorías
                </h3>
                <p class="text-muted mb-0">Administra las categorías para tus productos.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <!-- ✅ CORREGIDO: usar ?url=categorias&action=registrar -->
                <a href="?url=categorias&action=registrar" class="btn btn-warning btn-sm fw-bold text-dark">
                    <i class="fas fa-plus me-1"></i> Registrar Categoría
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($mensaje) && !empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_mensaje ?? 'info' ?> alert-dismissible fade show">
            <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom mb-4">
        <div class="table-responsive">
            <table class="table table-fenix table-hover m-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-database mb-2" style="font-size: 2rem; display: block;"></i>
                                No hay categorías registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td class="ps-4"><?= $cat['id_categoria'] ?></td>
                                <td><?= htmlspecialchars($cat['nombre_categoria']) ?></td>
                                <td><?= htmlspecialchars($cat['descripcion'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <!-- ✅ CORREGIDO -->
                                        <a href="?url=categorias&action=ver&id=<?= $cat['id_categoria'] ?>" 
                                           class="btn btn-outline-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- ✅ CORREGIDO -->
                                        <a href="?url=categorias&action=editar&id=<?= $cat['id_categoria'] ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- ✅ CORREGIDO -->
                                        <form method="POST" action="?url=categorias&action=eliminar" style="display:inline;" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_categoria" value="<?= $cat['id_categoria'] ?>">
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
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>