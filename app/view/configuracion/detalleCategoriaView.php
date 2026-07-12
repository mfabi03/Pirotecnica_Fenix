<?php
// app/view/configuracion/detalleCategoriaView.php

// ✅ VERIFICAR QUE LA VARIABLE EXISTA
if (!isset($categoria) || empty($categoria)) {
    die('Categoría no encontrada');
}

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
                            <i class="fas fa-tag text-gold me-2"></i> Detalle de la Categoría
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Información completa de la categoría: 
                            <strong style="color: rgba(255, 255, 255, 0.8);"><?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?></strong>
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=categorias" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
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
                 DETALLE DE LA CATEGORÍA
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-tag me-2"></i> <?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <!-- Información de la categoría -->
                    <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                        <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.15); padding-bottom: 8px; margin-bottom: 16px;">
                            <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos de la Categoría
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">ID:</label>
                                <p style="color: #1a1a2e; font-weight: 600; margin-bottom: 0;">
                                    <span class="badge" style="background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; padding: 4px 12px; border-radius: 50px;">#<?= htmlspecialchars($categoria['id_categoria'] ?? 'N/A') ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre:</label>
                                <p style="color: #1a1a2e; margin-bottom: 0; font-weight: 500;"><?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?></p>
                            </div>
                            <div class="col-12">
                                <label style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Descripción:</label>
                                <p style="color: #1a1a2e; margin-bottom: 0;"><?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción') ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ==========================================
                         BOTONES DE ACCIÓN
                         ========================================== -->
                    <div class="mt-4 text-center" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px;">
                        <a href="?url=categorias" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                        <a href="?url=categorias&action=editar&id=<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?>" 
                           class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block; margin-right: 10px;">
                            <i class="fas fa-edit me-2"></i> Editar Categoría
                        </a>
                        <form method="POST" action="?url=categorias&action=eliminar" style="display:inline;" 
                              onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_categoria" value="<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?>">
                            <button type="submit" class="btn" style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 25px; border-radius: 50px; transition: all 0.3s ease;">
                                <i class="fas fa-trash me-1"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>