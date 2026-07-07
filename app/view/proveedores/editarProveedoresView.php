<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-edit me-2"></i> Editar Proveedor</h4>
                <p class="text-muted mb-0">Actualiza la información del proveedor sin cambiar su funcionalidad.</p>
            </div>
            <a href="?url=proveedores&type=list" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-custom mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="?url=proveedores&type=update">
            <input type="hidden" name="id_proveedor" value="<?= $proveedor['id_proveedor'] ?>">
            
            <div class="mb-3">
                <label class="form-label">RIF</label>
                <input type="text" name="rif" class="form-control" value="<?= htmlspecialchars($proveedor['rif']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Razón Social</label>
                <input type="text" name="razon_social" class="form-control" value="<?= htmlspecialchars($proveedor['razon_social']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Contacto</label>
                <input type="text" name="numero_contacto" class="form-control" value="<?= htmlspecialchars($proveedor['numero_contacto'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="correo_electronico" class="form-control" value="<?= htmlspecialchars($proveedor['correo_electronico'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Dirección</label>
                <textarea name="direccion" class="form-control"><?= htmlspecialchars($proveedor['direccion'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-gold">Actualizar</button>
                <a href="?url=proveedores&type=list" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>