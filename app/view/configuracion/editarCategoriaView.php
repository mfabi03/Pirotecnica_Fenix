<?php
// app/view/configuracion/editarCategoriaView.php

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
                <h4 class="mb-1 fw-bold"><i class="fas fa-edit me-2"></i> Editar Categoría</h4>
                <p class="text-muted mb-0">Modifique los datos de la categoría: 
                    <strong><?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?></strong>
                </p>
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

        <form action="?url=categorias&action=actualizar" method="POST">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id_categoria" value="<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre_categoria" class="form-label fw-bold">Nombre de la Categoría *</label>
                    <input type="text" name="nombre_categoria" id="nombre_categoria" class="form-control" 
                           value="<?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="descripcion" class="form-label fw-bold">Descripción</label>
                    <input type="text" name="descripcion" id="descripcion" class="form-control" 
                           value="<?= htmlspecialchars($categoria['descripcion'] ?? '') ?>"
                           placeholder="Breve descripción de la categoría">
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark">
                    <i class="fas fa-save me-2"></i> Actualizar Categoría
                </button>
                <a href="?url=categorias" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>