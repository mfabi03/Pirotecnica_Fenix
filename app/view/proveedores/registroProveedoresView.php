<?php
// CAMBIO: Ajuste de nombre según BD - Vista de registro de proveedor
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <!-- Tarjeta de título -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-truck me-2"></i> Registrar Proveedor
                </h3>
                <p class="text-muted mb-0">Ingresa los datos del nuevo proveedor.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=proveedores&type=list" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-list me-1"></i> Ver Proveedores
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

    <!-- Formulario -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <form method="POST" action="?url=proveedores&type=store">
            <div class="row g-3">
               
             <!-- RIF - OBLIGATORIO -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    RIF <span class="text-danger">*</span>
                </label>
                <input type="text" id="rif" name="rif" class="form-control" 
                       placeholder="Ej: J-123456789" 
                       value="<?= htmlspecialchars($_POST['rif'] ?? '') ?>" required>
                <small class="text-muted">Número de identificación fiscal</small>
            </div>
            
                <!-- Razón Social (Obligatorio) -->
                <div class="col-md-6">
                    <label class="form-label form-label-custom fw-bold">
                        Razón Social <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="razon_social" class="form-control" 
                           placeholder="Nombre de la empresa" required>
                    <small class="text-muted">Nombre legal de la empresa</small>
                </div>

                <!-- Número de Contacto (Obligatorio) -->
                <div class="col-md-6">
                    <label class="form-label form-label-custom fw-bold">
                        Número de Contacto <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="numero_contacto" class="form-control" 
                           placeholder="0412-1234567" required>
                    <small class="text-muted">Teléfono principal del proveedor</small>
                </div>

                <!-- Correo Electrónico (OPCIONAL) -->
                <div class="col-md-6">
                    <label class="form-label form-label-custom">
                        Correo Electrónico
                    </label>
                    <input type="email" name="correo_electronico" class="form-control" 
                           placeholder="correo@empresa.com">
                    <small class="text-muted">Opcional - Correo de contacto</small>
                </div>

                <!-- Dirección (Obligatorio) -->
                <div class="col-md-12">
                    <label class="form-label form-label-custom fw-bold">
                        Dirección <span class="text-danger">*</span>
                    </label>
                    <textarea name="direccion" class="form-control" rows="2" 
                              placeholder="Dirección del proveedor" required></textarea>
                    <small class="text-muted">Dirección fiscal o física del proveedor</small>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-4">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Guardar Proveedor
                </button>
                <a href="?url=proveedores&type=list" class="btn btn-secondary ms-2">
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
<?php require_once __DIR__ . '/../footer.php'; ?>