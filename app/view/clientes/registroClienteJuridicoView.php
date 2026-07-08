<?php
// Vista de registro de cliente jurídico
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="col-md-8 col-lg-12">
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-building me-2"></i> Registrar Cliente Jurídico
                </h3>
                <p class="text-muted mb-0">Registra un nuevo cliente persona jurídica (empresa) en el sistema.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=clientes&type=register" class="btn btn-gold btn-sm fw-bold">
                    <i class="fas fa-user me-1"></i> Cliente Natural
                </a>
                <a href="?url=clientes&type=list" class="btn btn-secondary btn-sm fw-bold ms-1">
                    <i class="fas fa-list me-1"></i> Ver Clientes
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?> alert-custom">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4 mb-4 bg-white" style="max-width: 800px; margin: 0 auto;">
        <form method="post" action="?url=clientes&type=register_juridico" class="row g-3">
            <input type="hidden" name="accion" value="register_juridico">

            <!-- RIF - OBLIGATORIO -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    RIF <span class="text-danger">*</span>
                </label>
                <input type="text" id="rif" name="rif" class="form-control" 
                       placeholder="Ej: J-123456789" 
                       value="<?= htmlspecialchars($_POST['rif'] ?? '') ?>" required>
                <small class="text-muted">Solo números o con letra J (Ej: J-123456789)</small>
            </div>

            <!-- Teléfono -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Teléfono <span class="text-danger">*</span>
                </label>
                <input type="tel" name="telefono" class="form-control" 
                       placeholder="Ej: 0251-1234567" 
                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" required>
            </div>

            <!-- Razón Social -->
            <div class="col-12">
                <label class="form-label form-label-custom fw-bold">
                    Razón Social <span class="text-danger">*</span>
                </label>
                <input type="text" name="razon_social" class="form-control" 
                       value="<?= htmlspecialchars($_POST['razon_social'] ?? '') ?>" 
                       required placeholder="Ej: Pirotecnia Fénix C.A.">
            </div>

            <!-- Correo -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Correo Electrónico <span class="text-danger">*</span>
                </label>
                <input type="email" name="correo_electronico" class="form-control" 
                       value="<?= htmlspecialchars($_POST['correo_electronico'] ?? '') ?>" 
                       required placeholder="contacto@empresa.com">
            </div>

            <!-- Dirección -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Dirección Fiscal <span class="text-danger">*</span>
                </label>
                <textarea name="direccion" class="form-control" rows="2" required 
                          placeholder="Dirección detallada..."><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
            </div>

            <!-- Botones -->
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Registrar Cliente Jurídico
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
    // Formateo de RIF
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