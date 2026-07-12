<?php
// app/view/clientes/clientes_view.php
if (!isset($cliente) || empty($cliente)) {
    die('Cliente no encontrado');
}
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="container-fluid px-4">
    <div class="row">
        <!-- Contenido Principal -->
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-eye text-gold me-2"></i> Detalle del Cliente
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Información completa del cliente
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
                 DETALLE DEL CLIENTE
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-user me-2"></i> 
                        <?php if (($cliente['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                            <?= htmlspecialchars($cliente['razon_social'] ?? 'Cliente') ?>
                        <?php else: ?>
                            <?= htmlspecialchars(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')) ?>
                        <?php endif; ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <div class="row g-4">
                        
                        <!-- ===== COLUMNA IZQUIERDA ===== -->
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Información del Cliente
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula / RIF:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0; font-weight: 600;"><?= htmlspecialchars($cliente['cedula'] ?? $cliente['rif'] ?? 'N/A') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">
                                        <?= ($cliente['tipo_cliente'] ?? '') === 'Jurídico' ? 'Razón Social:' : 'Nombre Completo:' ?>
                                    </label>
                                    <p style="color: #1a1a2e; margin-bottom: 0; font-weight: 600;">
                                        <?php if (($cliente['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                            <?= htmlspecialchars($cliente['razon_social'] ?? 'N/A') ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Tipo de Cliente:</label>
                                    <p style="margin-bottom: 0;">
                                        <?php if (($cliente['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                            <span class="badge-dark-admin">
                                                <i class="fas fa-building me-1"></i> Jurídico
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-dark-user">
                                                <i class="fas fa-user me-1"></i> Natural
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php if (($cliente['tipo_cliente'] ?? '') === 'Natural'): ?>
                                    <div class="mb-3">
                                        <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Fecha de Nacimiento:</label>
                                        <p style="color: #1a1a2e; margin-bottom: 0;">
                                            <?= !empty($cliente['fecha_de_nacimiento']) ? date('d/m/Y', strtotime($cliente['fecha_de_nacimiento'])) : 'No registrada' ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- ===== COLUMNA DERECHA ===== -->
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-address-book me-2" style="color: #f39c12;"></i> Datos de Contacto
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars($cliente['telefono'] ?? 'N/A') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;">
                                        <?= htmlspecialchars($cliente['correo_electronico'] ?? $cliente['correo_electrónico'] ?? 'No registrado') ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= nl2br(htmlspecialchars($cliente['direccion'] ?? 'Sin dirección registrada')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ==========================================
                         BOTONES DE ACCIÓN
                         ========================================== -->
                    <div class="text-center mt-4" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px;">
                        <?php 
                        $editType = ($cliente['tipo_cliente'] ?? '') === 'Jurídico' ? 'edit_juridico' : 'edit';
                        ?>
                        <a href="?url=clientes&type=<?= $editType ?>&id=<?= htmlspecialchars($cliente['id_cliente'] ?? '') ?>" 
                           class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-edit me-2"></i> Editar cliente
                        </a>
                        <form method="POST" action="?url=clientes&type=delete" style="display: inline;">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($cliente['id_cliente'] ?? 0) ?>">
                            <button type="submit" class="btn" style="background: #dc3545; color: #fff !important; font-weight: 600; padding: 10px 30px; border-radius: 50px; border: none; transition: all 0.3s ease; margin-left: 10px;"
                                    onclick="return confirm('¿Estás seguro de eliminar este cliente?\n\nEsta acción no se puede deshacer.');"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(220,53,69,0.3)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <i class="fas fa-trash-alt me-2"></i> Eliminar cliente
                            </button>
                        </form>
                        <a href="?url=clientes&type=list" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-left: 10px;">
                            <i class="fas fa-list me-1"></i> Ver todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>