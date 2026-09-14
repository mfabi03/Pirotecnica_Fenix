<?php
// app/view/proveedores/proveedores_show.php
if (!isset($proveedor) || empty($proveedor)) {
    die('Proveedor no encontrado');
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
                            <i class="fas fa-eye text-gold me-2"></i> Detalle del Proveedor
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Información completa del proveedor
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
                 DETALLE DEL PROVEEDOR
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-truck me-2"></i> <?= htmlspecialchars($proveedor['razon_social'] ?? 'Proveedor') ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <div class="row g-4">
                        
                        <!-- ===== COLUMNA IZQUIERDA ===== -->
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-building me-2" style="color: #f39c12;"></i> Información del Proveedor
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">ID:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0; font-weight: 700;">#<?= htmlspecialchars($proveedor['id_proveedor'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;">
                                        <span class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.8rem;">
                                            <?= htmlspecialchars($proveedor['rif'] ?? '') ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0; font-weight: 600;"><?= htmlspecialchars($proveedor['razon_social'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars($proveedor['direccion'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ===== COLUMNA DERECHA ===== -->
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-phone me-2" style="color: #f39c12;"></i> Información de Contacto
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Número de Contacto:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars($proveedor['numero_contacto'] ?? 'N/A') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;">
                                        <?php if (!empty($proveedor['correo_electronico'] ?? '')): ?>
                                            <a href="mailto:<?= htmlspecialchars($proveedor['correo_electronico']) ?>" 
                                               style="color: #0dcaf0; text-decoration: none;">
                                                <?= htmlspecialchars($proveedor['correo_electronico']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">N/A</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Fecha de Registro:</label>
                                    <p style="color: #1a1a2e; margin-bottom: 0;">
                                        <?= isset($proveedor['fecha_creacion']) ? date('d/m/Y H:i', strtotime($proveedor['fecha_creacion'])) : 'N/A' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ==========================================
                         BOTONES DE ACCIÓN - ESTILO USUARIO VER
                         ========================================== -->
                    <div class="text-center mt-4" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px;">
                        <a href="?url=proveedores&type=edit&id=<?= htmlspecialchars($proveedor['id_proveedor'] ?? '') ?>" 
                           class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-edit me-2"></i> Editar proveedor
                        </a>
                        
                        <form method="POST" action="?url=proveedores&type=delete" style="display: inline;">
                            <input type="hidden" name="id_proveedor" value="<?= htmlspecialchars($proveedor['id_proveedor'] ?? '') ?>">
                            <button type="submit" class="btn" style="background: #dc3545; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; border: none; transition: all 0.3s ease; margin-left: 10px;"
                                    onclick="return confirm('¿Estás seguro de eliminar este proveedor?\n\nEsta acción no se puede deshacer.');"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(220,53,69,0.3)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <i class="fas fa-trash-alt me-2"></i> Eliminar proveedor
                            </button>
                        </form>
                        
                        <a href="?url=proveedores&type=list" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-left: 10px;">
                            <i class="fas fa-list me-1"></i> Ver todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>