<?php
// CAMBIO: Ajuste de nombre según BD - Vista de registro de cliente natural CON BOOTSTRAP
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8 col-lg-12">
    <!-- Tarjeta de título -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-user-plus text-gold me-2"></i> Registror de Cliente Natural
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    Registra un nuevo cliente persona natural en el sistema.
                </small>
            </div>
            <div class="col-auto">
                <a href="?url=clientes&type=register_juridico<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>" class="btn" style="background: rgba(207, 181, 10, 0.08); color: rgba(255,255,255,0.6); border: 2px solid rgba(255, 217, 26, 0.58); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                    <i class="fas fa-building me-1"></i> Cliente Jurídico
                </a>
                
                <a href="?url=clientes&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                    <i class="fas fa-list me-1"></i> Ver Clientes
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?= ($tipo_mensaje ?? '') === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-3 fs-4"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="dark-card card shadow-sm">
        <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
            <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                <i class="fas fa-user-plus me-2"></i> Nuevo Cliente Natural
            </h5>
        </div>
        <div class="card-body">
        <form method="post" action="?url=clientes&type=register<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>" class="row g-3">
            <?php if (isset($_GET['return'])): ?>
                <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return']) ?>">
            <?php endif; ?>
            <input type="hidden" name="accion" value="register_natural">

            <!-- Cédula -->
            <div class="col-md-6">
                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">
                    Cédula <span class="text-danger">*</span>
                </label>
                <input type="text" id="cedula" name="cedula" class="form-control" 
                       placeholder="Ej: V-12345678" 
                       value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>" required>
                <small class="text-muted">Solo números o con letra V (Ej: V-12345678)</small>
            </div>

            <!-- Teléfono -->
            <div class="col-md-6">
                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">
                    Teléfono <span class="text-danger">*</span>
                </label>
                <input type="tel" name="telefono" class="form-control" 
                       placeholder="Ej: 0412-5556677" 
                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" required>
            </div>

            <!-- Nombre -->
            <div class="col-md-6">
                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">
                    Nombres <span class="text-danger">*</span>
                </label>
                <input type="text" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" 
                       required placeholder="Nombres del cliente">
            </div>

            <!-- Apellido -->
            <div class="col-md-6">
                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">
                    Apellidos <span class="text-danger">*</span>
                </label>
                <input type="text" name="apellido" class="form-control" 
                       value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>" 
                       required placeholder="Apellidos del cliente">
            </div>

            <!-- Correo -->
            <div class="col-md-6">
                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">
                    Correo Electrónico <span class="text-danger">*</span>
                </label>
                <input type="email" name="correo_electronico" class="form-control" 
                       value="<?= htmlspecialchars($_POST['correo_electronico'] ?? '') ?>" 
                       required placeholder="ejemplo@correo.com">
            </div>

            <!-- Fecha de Nacimiento -->
            <div class="col-md-6">
                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">
                    Fecha de Nacimiento <span class="text-danger">*</span>
                </label>
                <input type="date" name="fecha_de_nacimiento" class="form-control" 
                       value="<?= htmlspecialchars($_POST['fecha_de_nacimiento'] ?? '') ?>" required>
                <small class="text-muted">Debe ser mayor de 18 años</small>
            </div>

            <!-- Dirección -->
            <div class="col-12">
                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">
                    Dirección <span class="text-danger">*</span>
                </label>
                <textarea name="direccion" class="form-control" rows="2" required 
                          placeholder="Dirección detallada..."><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
            </div>

            <!-- Botones -->
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                    <i class="fas fa-save me-1"></i> Registrar Cliente
                </button>
                <a href="?url=clientes&type=list" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
        </div>
    </div>
</div>
        </div>
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