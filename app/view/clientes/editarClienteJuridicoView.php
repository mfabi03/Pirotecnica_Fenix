<?php
// CAMBIO: Ajuste de nombre según BD - Vista de edición de cliente jurídico CON BOOTSTRAP
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="col-md-9 col-lg-10">
    <!-- Tarjeta de título -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-building me-2"></i> Editar Cliente Jurídico
                </h3>
                <p class="text-muted mb-0">Modifica los datos del cliente persona jurídica (empresa) seleccionado.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=clientes&type=list" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-list me-1"></i> Ver Clientes
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?> alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4 mb-4 bg-white" style="max-width: 800px; margin: 0 auto;">
        <form method="post" action="?url=clientes&type=edit_juridico" class="row g-3">
            <input type="hidden" name="accion" value="edit_juridico">
            <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($cliente['id_cliente'] ?? '') ?>">

            <!-- RIF -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    RIF <span class="text-danger">*</span>
                </label>
                <input type="text" id="rif" name="rif" class="form-control" 
                       value="<?= htmlspecialchars($cliente['rif'] ?? '') ?>" required>
            </div>

            <!-- Teléfono -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Teléfono <span class="text-danger">*</span>
                </label>
                <input type="tel" name="telefono" class="form-control" 
                       value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>" required>
            </div>

            <!-- Razón Social -->
            <div class="col-12">
                <label class="form-label form-label-custom fw-bold">
                    Razón Social <span class="text-danger">*</span>
                </label>
                <input type="text" name="razon_social" class="form-control" 
                       value="<?= htmlspecialchars($cliente['razon_social'] ?? '') ?>" required>
            </div>

            <!-- Correo -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Correo Electrónico <span class="text-danger">*</span>
                </label>
                <input type="email" name="correo_electronico" class="form-control" 
                       value="<?= htmlspecialchars($cliente['correo_electrónico'] ?? $cliente['correo_electronico'] ?? '') ?>" required>
            </div>

            <!-- Dirección -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Dirección Fiscal <span class="text-danger">*</span>
                </label>
                <textarea name="direccion" class="form-control" rows="2" required><?= htmlspecialchars($cliente['direccion'] ?? '') ?></textarea>
            </div>

            <!-- Botones -->
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
                <a href="?url=clientes&type=list" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alertElement = document.querySelector('.alert');
    if (alertElement) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertElement);
            if (bsAlert) bsAlert.close();
        }, 5000);
    }

    const rifInput = document.getElementById('rif');
    if (rifInput) {
        rifInput.addEventListener('blur', function() {
            let value = this.value.trim().toUpperCase();
            if (/^\d+$/.test(value)) {
                this.value = 'J-' + value;
            } else if (/^J\d+$/.test(value)) {
                this.value = 'J-' + value.substring(1);
            }
        });
    }
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>