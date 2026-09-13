<?php
// app/view/configuracion/rolRegistro.php
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-plus-circle text-gold me-2"></i> Registrar Nuevo Rol
                        </h3>
                        <small class="text-muted">Complete los campos para registrar un nuevo rol</small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=roles" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?? 'info' ?> alert-dismissible fade show">
                    <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card card-custom p-4 bg-white">
                <form action="?url=roles" method="POST">
                    <input type="hidden" name="accion" value="guardar">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nombre_rol" class="form-label fw-bold">
                                Nombre del Rol <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre_rol" 
                                   id="nombre_rol" 
                                   class="form-control form-control-lg" 
                                   placeholder="Ej: Supervisor, Cajero, Almacenista..."
                                   required
                                   autofocus>
                            <small class="text-muted">El nombre debe ser único en el sistema</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="?url=roles" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-save me-2"></i> Registrar Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>