<?php
// app/view/proveedores/proveedores_create.php
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="container-fluid px-4">
    <div class="row">       
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-truck text-gold me-2"></i> Formulario de Registro
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Complete todos los campos para registrar un nuevo proveedor
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=proveedores&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ==========================================
                 FORMULARIO
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #f8f9fa !important; border-bottom: 1px solid rgba(0,0,0,0.06) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #1a1a2e !important; font-weight: 700 !important;">
                        <i class="fas fa-truck me-2"></i> Nuevo Proveedor
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="?url=proveedores&type=store<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>">
                        
                        <div class="row g-3">
                            
                            <!-- ===== DATOS DEL PROVEEDOR ===== -->
                            <div class="col-12">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-building me-2" style="color: #f39c12;"></i> Datos del proveedor
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="rif" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                                        <input type="text" id="rif" name="rif" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" 
                                               placeholder="Ej: J-123456789" 
                                               value="<?= htmlspecialchars($_POST['rif'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="razon_social" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                                        <input type="text" id="razon_social" name="razon_social" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" 
                                               placeholder="Nombre de la empresa" 
                                               value="<?= htmlspecialchars($_POST['razon_social'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="numero_contacto" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Número de Contacto *</label>
                                        <input type="text" id="numero_contacto" name="numero_contacto" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" 
                                               placeholder="0412-1234567" 
                                               value="<?= htmlspecialchars($_POST['numero_contacto'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="correo_electronico" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico</label>
                                        <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" 
                                               placeholder="correo@empresa.com" 
                                               value="<?= htmlspecialchars($_POST['correo_electronico'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="direccion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                                        <textarea id="direccion" name="direccion" class="form-control" rows="2" 
                                                  style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); resize: vertical;" 
                                                  placeholder="Dirección fiscal o física del proveedor" required><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- ==========================================
                                 BOTONES DE ACCIÓN
                                 ========================================== -->
                            <div class="col-12 text-end" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px; margin-top: 10px;">
                                <a href="?url=proveedores&type=list" class="btn btn-cancel">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-save me-2"></i> Registrar Proveedor
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     SCRIPTS
     ========================================== -->
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

        rifInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Auto mayúsculas en Razón Social
    const razonSocialInput = document.getElementById('razon_social');
    if (razonSocialInput) {
        razonSocialInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Formateo de teléfono
    const telefonoInput = document.getElementById('numero_contacto');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9-]/g, '');
        });
    }
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>