<?php
// app/view/configuracion/detalleCategoriaView.php

// ✅ VERIFICAR QUE LA VARIABLE EXISTA
if (!isset($categoria) || empty($categoria)) {
    die('Categoría no encontrada');
}

require_once __DIR__ . '/../header.php';
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="mb-1 fw-bold"><i class="fas fa-tag me-2"></i> Detalle de la Categoría</h4>
                <p class="text-muted mb-0">Información completa de la categoría seleccionada.</p>
            </div>
            <a href="?url=categorias" class="btn btn-secondary btn-sm fw-bold">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <?php if (isset($mensaje) && !empty($mensaje)): ?>
            <div class="alert alert-<?= $tipo_mensaje ?? 'info' ?> alert-dismissible fade show">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card bg-light p-3 border-0 mb-4">
            <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2 mb-3">
                <i class="fas fa-info-circle me-2"></i> Datos de la Categoría
            </h6>
            <div class="mt-2">
                <p><strong>ID:</strong> <span class="badge bg-primary">#<?= htmlspecialchars($categoria['id_categoria'] ?? 'N/A') ?></span></p>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?></p>
                <p><strong>Descripción:</strong> <?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción') ?></p>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="?url=categorias" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="?url=categorias&action=editar&id=<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?>" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Editar Categoría
            </a>
            <form method="POST" action="?url=categorias&action=eliminar" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_categoria" value="<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>