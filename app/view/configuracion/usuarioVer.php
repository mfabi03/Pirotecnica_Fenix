<?php 
// app/view/configuracion/usuario_ver.php
if (!isset($usuario) || empty($usuario)) {
    die('Usuario no encontrado');
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
                            <i class="fas fa-eye text-gold me-2"></i> Detalle del Usuario
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Información completa del usuario
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=usuarios" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 DETALLE DEL USUARIO
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-user me-2"></i> <?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <div class="row g-4">
                        
                        <!-- ===== COLUMNA IZQUIERDA ===== -->
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-user me-2" style="color: #f39c12;"></i> Información Personal
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre completo:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars($usuario['cedula'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars($usuario['telefono'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo electrónico:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars($usuario['correo_electronico'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ===== COLUMNA DERECHA ===== -->
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-lock me-2" style="color: #f39c12;"></i> Información de Cuenta
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Usuario:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars($usuario['usuario'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Rol:</label>
                                    <p style="margin-bottom: 0;">
                                        <?php if (($usuario['id_rol'] ?? 0) == 1): ?>
                                            <span class="badge-dark-admin">
                                                <i class="fas fa-crown me-1"></i> Administrador
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-dark-user">
                                                <i class="fas fa-user me-1"></i> Usuario
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    
                    <!-- ==========================================
                         BOTONES DE ACCIÓN
                         ========================================== -->
                    <div class="text-center mt-4" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px;">
                        <a href="?url=usuarios&action=editar&id=<?= htmlspecialchars($usuario['id_usuario'] ?? '') ?>" 
                           class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-edit me-2"></i> Editar usuario
                        </a>
                        <a href="?url=usuarios" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-left: 10px;">
                            <i class="fas fa-list me-1"></i> Ver todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>