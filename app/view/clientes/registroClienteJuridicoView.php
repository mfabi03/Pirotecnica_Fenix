<?php
// app/view/clientes/clientes_register_juridico.php
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
                            <i class="fas fa-building text-gold me-2"></i> Registro de Cliente Jurídico
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Complete todos los campos para registrar un nuevo cliente jurídico
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=clientes&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
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

            <!-- ==========================================
                 FORMULARIO
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-building me-2"></i> Nuevo Cliente Jurídico
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="post" action="?url=clientes&type=register_juridico<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>">
                        <input type="hidden" name="accion" value="register_juridico">
                        
                        <div class="row g-3">
                            
                            <!-- ===== DATOS DE LA EMPRESA ===== -->
                            <div class="col-12">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos de la Empresa
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="rif" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                                        <input type="text" id="rif" name="rif" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" 
                                               placeholder="Ej: J-123456789" 
                                               value="<?= htmlspecialchars($_POST['rif'] ?? '') ?>" required>
                                        <small style="color: #6c757d; font-size: 0.7rem;">Solo números o con letra J (Ej: J-123456789)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telefono" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                        <input type="tel" name="telefono" id="telefono" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" 
                                               placeholder="Ej: 0251-1234567" 
                                               value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="razon_social" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                                        <input type="text" name="razon_social" id="razon_social" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" 
                                               placeholder="Ej: Pirotecnia Fénix C.A." 
                                               value="<?= htmlspecialchars($_POST['razon_social'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="correo_electronico" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                        <input type="email" name="correo_electronico" id="correo_electronico" class="form-control" 
                                               style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" 
                                               placeholder="contacto@empresa.com" 
                                               value="<?= htmlspecialchars($_POST['correo_electronico'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="direccion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección Fiscal *</label>
                                        <textarea name="direccion" id="direccion" class="form-control" rows="2" 
                                                  style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); resize: vertical;" 
                                                  placeholder="Dirección detallada..." required><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- ==========================================
                                 BOTONES DE ACCIÓN
                                 ========================================== -->
                            <div class="col-12 text-end" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px; margin-top: 10px;">
                                <a href="?url=clientes&type=list" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </a>
                                <a href="?url=clientes&type=register<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>" 
                                   class="btn" style="background: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.2); border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                    <i class="fas fa-user me-1"></i> Cliente Natural
                                </a>
                                <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                                    <i class="fas fa-save me-2"></i> Registrar Cliente Jurídico
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
    }
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>