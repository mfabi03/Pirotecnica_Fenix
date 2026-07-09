<?php
// CAMBIO: Ajuste de nombre según BD - Vista de registro de cliente natural CON BOOTSTRAP
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="col-md-8 col-lg-12">
    <!-- Tarjeta de título -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-user-plus me-2"></i> Registrar Cliente Natural
                </h3>
                <p class="text-muted mb-0">Registra un nuevo cliente persona natural en el sistema.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=clientes&type=register_juridico<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>" class="btn btn-gold btn-sm fw-bold">
                    <i class="fas fa-building me-1"></i> Cliente Jurídico
                </a>
                <a href="?url=clientes&type=list" class="btn btn-secondary btn-sm fw-bold ms-1">
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
        <form method="post" action="?url=clientes&type=register<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>" class="row g-3">
            <input type="hidden" name="accion" value="register_natural">

            <!-- Cédula -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Cédula <span class="text-danger">*</span>
                </label>
                <input type="text" id="cedula" name="cedula" class="form-control" 
                       placeholder="Ej: V-12345678" 
                       value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>" required>
                <small class="text-muted">Solo números o con letra V (Ej: V-12345678)</small>
            </div>

            <!-- Teléfono -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Teléfono <span class="text-danger">*</span>
                </label>
                <input type="tel" name="telefono" class="form-control" 
                       placeholder="Ej: 0412-5556677" 
                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" required>
            </div>

            <!-- Nombre -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Nombres <span class="text-danger">*</span>
                </label>
                <input type="text" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" 
                       required placeholder="Nombres del cliente">
            </div>

            <!-- Apellido -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Apellidos <span class="text-danger">*</span>
                </label>
                <input type="text" name="apellido" class="form-control" 
                       value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>" 
                       required placeholder="Apellidos del cliente">
            </div>

            <!-- Correo -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Correo Electrónico <span class="text-danger">*</span>
                </label>
                <input type="email" name="correo_electronico" class="form-control" 
                       value="<?= htmlspecialchars($_POST['correo_electronico'] ?? '') ?>" 
                       required placeholder="ejemplo@correo.com">
            </div>

            <!-- Fecha de Nacimiento -->
            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Fecha de Nacimiento <span class="text-danger">*</span>
                </label>
                <input type="date" name="fecha_de_nacimiento" class="form-control" 
                       value="<?= htmlspecialchars($_POST['fecha_de_nacimiento'] ?? '') ?>" required>
                <small class="text-muted">Debe ser mayor de 18 años</small>
            </div>

            <!-- Dirección -->
            <div class="col-12">
                <label class="form-label form-label-custom fw-bold">
                    Dirección <span class="text-danger">*</span>
                </label>
                <textarea name="direccion" class="form-control" rows="2" required 
                          placeholder="Dirección detallada..."><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
            </div>

            <!-- Botones -->
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Registrar Cliente
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
    // Auto-cierre de alertas
    const alertElement = document.querySelector('.alert');
    if (alertElement) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertElement);
            if (bsAlert) bsAlert.close();
        }, 5000);
    }

    // Formateo de cédula
    const cedulaInput = document.getElementById('cedula');
    if (cedulaInput) {
        cedulaInput.addEventListener('blur', function() {
            let value = this.value.trim().toUpperCase();
            if (/^\d+$/.test(value)) {
                this.value = 'V-' + value;
            } else if (/^V\d+$/.test(value)) {
                this.value = 'V-' + value.substring(1);
            }
        });
    }

    // Validación de edad
    const fechaInput = document.querySelector('input[name="fecha_de_nacimiento"]');
    if (fechaInput) {
        fechaInput.addEventListener('change', function() {
            const fechaNac = new Date(this.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fechaNac.getFullYear();
            const mes = hoy.getMonth() - fechaNac.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
                edad--;
            }
            if (edad < 18 && this.value) {
                alert('⚠️ Debes ser mayor de 18 años. Edad detectada: ' + edad + ' años.');
                this.value = '';
            }
        });
    }
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>