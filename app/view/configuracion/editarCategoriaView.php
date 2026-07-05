<?php
// CAMBIO: Ajuste de nombre según BD - Vista de edición de categoría CON BOOTSTRAP
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <!-- Tarjeta de título -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-edit me-2"></i> Editar Categoría
                </h3>
                <p class="text-muted mb-0">Modifica los datos de la categoría seleccionada.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=configuracion&type=list" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-list me-1"></i> Ver Categorías
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Información de la categoría -->
    <div class="card card-custom p-3 mb-4 bg-light">
        <div class="row align-items-center">
            <div class="col-md-6">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong>Categoría #<?= $categoria['id_categoria'] ?></strong>
                <span class="text-muted ms-2">
                    <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                </span>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted">
                    <i class="fas fa-tag me-1"></i>
                    ID: <?= $categoria['id_categoria'] ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card card-custom p-4 mb-4 bg-white">
        <form method="POST" action="?url=configuracion&type=update">
            <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label form-label-custom fw-bold">
                        Nombre de la Categoría <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre_categoria" class="form-control" 
                           value="<?= htmlspecialchars($categoria['nombre_categoria']) ?>" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label form-label-custom">
                        Descripción
                    </label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Actualizar Categoría
                </button>
                <a href="?url=configuracion&type=list" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>