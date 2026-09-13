<?php
// app/view/configuracion/rolEditar.php
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <!-- Header oscuro consistente con el dashboard -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-edit text-gold me-2"></i> Editar Rol
                        </h3>
                        <small class="text-muted">Modifique el nombre del rol</small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=roles" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?? 'info' ?> alert-dismissible fade show">
                    <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <div class="card card-custom p-4 bg-white">
                <form action="?url=roles" method="POST">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="id_rol" value="<?= htmlspecialchars($rol['id_rol'] ?? '') ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nombre_rol" class="form-label fw-bold">
                                Nombre del Rol <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre_rol" 
                                   id="nombre_rol" 
                                   class="form-control form-control-lg" 
                                   value="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>"
                                   required>
                            <small class="text-muted">Modifique el nombre y guarde los cambios</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="?url=roles" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>