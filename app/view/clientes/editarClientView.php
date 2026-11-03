<?php
// CAMBIO: Ajuste de nombre según BD - Vista de edición de cliente natural CON BOOTSTRAP
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4 mb-4 mx-auto" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-user-edit me-2"></i> Editar Cliente Natural</h4>
                <p class="text-muted mb-0">Modifica los datos del cliente persona natural seleccionado.</p>
            </div>
            <a href="?url=clientes&type=list" class="btn btn-secondary btn-sm fw-bold">
                <i class="fas fa-list me-1"></i> Ver Clientes
            </a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?> alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="post" action="?url=clientes&type=edit" class="row g-3">
            <input type="hidden" name="accion" value="edit_natural">
            <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($cliente['id_cliente'] ?? '') ?>">

            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Cédula <span class="text-danger">*</span>
                </label>
                <input type="text" id="cedula" name="cedula" class="form-control" 
                       value="<?= htmlspecialchars($cliente['cedula'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Teléfono <span class="text-danger">*</span>
                </label>
                <input type="tel" name="telefono" class="form-control" 
                       value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Nombres <span class="text-danger">*</span>
                </label>
                <input type="text" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Apellidos <span class="text-danger">*</span>
                </label>
                <input type="text" name="apellido" class="form-control" 
                       value="<?= htmlspecialchars($cliente['apellido'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Correo Electrónico <span class="text-danger">*</span>
                </label>
                <input type="email" name="correo_electronico" class="form-control" 
                       value="<?= htmlspecialchars($cliente['correo_electrónico'] ?? $cliente['correo_electronico'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label form-label-custom fw-bold">
                    Fecha de Nacimiento <span class="text-danger">*</span>
                </label>
                <input type="date" name="fecha_de_nacimiento" class="form-control" 
                       value="<?= htmlspecialchars($cliente['fecha_de_nacimiento'] ?? '') ?>" 
                       max="<?= date('Y-m-d', strtotime('-18 years')) ?>" required>
                <small class="text-muted">Debe ser mayor de 18 años</small>
            </div>

            <div class="col-12">
                <label class="form-label form-label-custom fw-bold">
                    Dirección <span class="text-danger">*</span>
                </label>
                <textarea name="direccion" class="form-control" rows="2" required><?= htmlspecialchars($cliente['direccion'] ?? '') ?></textarea>
            </div>

            <div class="col-12 mt-3 d-flex flex-wrap gap-2">
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
                alert('⚠️ Debe ser mayor de 18 años. Edad detectada: ' + edad + ' años.');
                this.value = '';
            }
        });
    }

    const form = document.querySelector('form');
    if (form && fechaInput) {
        form.addEventListener('submit', function(e) {
            if (!fechaInput.value) {
                e.preventDefault();
                alert('⚠️ La fecha de nacimiento es obligatoria.');
                fechaInput.focus();
                return;
            }
            const fechaNac = new Date(fechaInput.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fechaNac.getFullYear();
            const mes = hoy.getMonth() - fechaNac.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
                edad--;
            }
            if (edad < 18) {
                e.preventDefault();
                alert('⚠️ Bloqueado: El cliente debe ser mayor de 18 años. Edad detectada: ' + edad + ' años.');
                fechaInput.focus();
            }
        });
    }
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>