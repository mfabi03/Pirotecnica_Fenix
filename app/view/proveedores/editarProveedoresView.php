<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card shadow-sm p-4">
        <h4><i class="fas fa-edit me-2"></i> Editar Proveedor</h4>
        <hr>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
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

            <button type="submit" class="btn btn-gold">Actualizar</button>
            <a href="?url=proveedores&type=list" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>