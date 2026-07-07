<?php
// app/view/configuracion/detalleCategoriaView.php

// ✅ VERIFICAR QUE LA VARIABLE EXISTA
if (!isset($categoria) || empty($categoria)) {
    die('Categoría no encontrada');
}

require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <!-- Tarjeta de título -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-tag me-2"></i> Detalle de la Categoría
                </h3>
                <p class="text-muted mb-0">Información completa de la categoría seleccionada.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <!-- ✅ CORREGIDO: Volver a la lista de categorías -->
                <a href="?url=categorias" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($mensaje) && !empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_mensaje ?? 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row g-4">
            <div class="col-md-12">
                <div class="card bg-light p-3">
                    <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i> Datos de la Categoría
                    </h6>
                    <div class="mt-2">
                        <p><strong>ID:</strong> <span class="badge bg-primary">#<?= htmlspecialchars($categoria['id_categoria'] ?? 'N/A') ?></span></p>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?></p>
                        <p><strong>Descripción:</strong> <?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="card card-custom p-4 bg-white">
        <div class="d-flex gap-2 flex-wrap">
            <!-- ✅ CORREGIDO: Volver a la lista de categorías -->
            <a href="?url=categorias" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <!-- ✅ CORREGIDO: Editar categoría -->
            <a href="?url=categorias&action=editar&id=<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?>" 
               class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Editar Categoría
            </a>
            <!-- ✅ CORREGIDO: Eliminar categoría -->
            <form method="POST" action="?url=categorias&action=eliminar" style="display:inline;" 
                  onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
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