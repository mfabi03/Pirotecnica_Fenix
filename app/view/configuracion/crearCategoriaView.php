<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-8 col-lg-12">
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-tag me-2"></i> Registrar Categoría
                </h3>
                <p class="text-muted mb-0">Ingresa los datos de la nueva categoría.</p>
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

    <div class="card card-custom p-4 mb-4 bg-white">
        <form method="POST" action="?url=configuracion&type=store<?= isset($_GET['return']) ? '&return=' . $_GET['return'] : '' ?>">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label form-label-custom fw-bold">
                        Nombre de la Categoría <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre_categoria" class="form-control" 
                           placeholder="Ej: Electrónica, Ropa, Alimentos..." required>
                    <small class="text-muted">Nombre único para la categoría</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label form-label-custom">
                        Descripción
                    </label>
                    <textarea name="descripcion" class="form-control" rows="3" 
                              placeholder="Descripción detallada de la categoría (opcional)"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Guardar Categoría
                </button>
                <?php if (isset($_GET['return'])): ?>
                    <a href="?url=<?= $_GET['return'] ?>&type=create" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Cancelar y volver
                    </a>
                <?php else: ?>
                    <a href="?url=configuracion&type=list" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>