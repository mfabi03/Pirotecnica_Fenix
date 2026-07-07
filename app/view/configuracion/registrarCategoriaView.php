<?php
// app/view/configuracion/registrarCategoriaView.php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="mb-1 fw-bold"><i class="fas fa-plus-circle me-2"></i> Registrar Categoría</h4>
                <p class="text-muted mb-0">Complete los campos para registrar una nueva categoría.</p>
            </div>
            <a href="?url=categorias" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <?php if (isset($mensaje) && !empty($mensaje)): ?>
            <div class="alert alert-<?= $tipo_mensaje ?? 'info' ?> alert-dismissible fade show">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="?url=categorias&action=guardar" method="POST">
            <input type="hidden" name="accion" value="guardar">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre_categoria" class="form-label fw-bold">Nombre de la Categoría *</label>
                    <input type="text" name="nombre_categoria" id="nombre_categoria" class="form-control" 
                           placeholder="Ej: Electrónicos, Ropa, Alimentos" required>
                </div>
                <div class="col-md-6">
                    <label for="descripcion" class="form-label fw-bold">Descripción</label>
                    <input type="text" name="descripcion" id="descripcion" class="form-control" 
                           placeholder="Breve descripción de la categoría">
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark">
                    <i class="fas fa-save me-2"></i> Guardar Categoría
                </button>
                <a href="?url=categorias" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>