<?php
// app/view/configuracion/editarCategoriaView.php

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
                            <i class="fas fa-edit text-gold me-2"></i> Editar Categoría
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Modifique los datos de la categoría: 
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
                 FORMULARIO
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-edit me-2"></i> Editando: <?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="?url=categorias&action=actualizar" method="POST">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="id_categoria" value="<?= htmlspecialchars($categoria['id_categoria'] ?? '') ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre_categoria" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre de la Categoría *</label>
                                <input type="text" name="nombre_categoria" id="nombre_categoria" class="form-control" 
                                       value="<?= htmlspecialchars($categoria['nombre_categoria'] ?? '') ?>"
                                       style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);" required>
                            </div>
                            <div class="col-md-6">
                                <label for="descripcion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Descripción</label>
                                <input type="text" name="descripcion" id="descripcion" class="form-control" 
                                       value="<?= htmlspecialchars($categoria['descripcion'] ?? '') ?>"
                                       placeholder="Breve descripción de la categoría"
                                       style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                            </div>
                        </div>

                        <!-- ==========================================
                             BOTONES
                             ========================================== -->
                        <div class="mt-4 text-end" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px;">
                            <a href="?url=categorias" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <!-- ✅ BOTÓN DORADO -->
                            <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                                <i class="fas fa-save me-2"></i> Actualizar Categoría
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>