<?php
// app/view/configuracion/rolVer.php
if (!isset($rol) || empty($rol)) {
    die('Rol no encontrado');
}
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8 col-lg-12">
            
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-eye text-gold me-2"></i> Detalle del Rol
                        </h3>
                        <small class="text-muted">Información completa del rol</small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=roles" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <div class="card card-custom">
                <div class="card-body p-4">
                    <div class="row g-4">
                        
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded">
                                <h6 class="text-muted text-uppercase small mb-3">
                                    <i class="fas fa-info-circle me-1"></i> Información del Rol
                                </h6>
                                <hr>
                                <div class="mb-3">
                                    <label class="fw-bold text-muted small">ID:</label>
                                    <p class="mb-0 fs-5">#<?= htmlspecialchars($rol['id_rol'] ?? 'N/A') ?></p>
                                </div>
                                <div class="mb-0">
                                    <label class="fw-bold text-muted small">Nombre del Rol:</label>
                                    <p class="mb-0 fs-5">
                                        <?php if (($rol['id_rol'] ?? 0) == 1): ?>
                                            <span class="badge bg-danger fs-6">
                                                <i class="fas fa-crown me-1"></i>
                                                <?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="fw-semibold"><?= htmlspecialchars($rol['nombre_rol'] ?? '') ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded">
                                <h6 class="text-muted text-uppercase small mb-3">
                                    <i class="fas fa-users me-1"></i> Usuarios Asignados
                                </h6>
                                <hr>
                                <div class="text-center py-3">
                                    <?php if (($rol['total_usuarios'] ?? 0) > 0): ?>
                                        <div class="display-4 text-primary fw-bold">
                                            <?= $rol['total_usuarios'] ?>
                                        </div>
                                        <p class="text-muted mb-0">
                                            usuario<?= $rol['total_usuarios'] > 1 ? 's' : '' ?> con este rol
                                        </p>
                                    <?php else: ?>
                                        <i class="fas fa-user-slash fa-3x text-muted opacity-50 mb-2"></i>
                                        <p class="text-muted mb-0">Sin usuarios asignados</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">

                    <div class="d-flex justify-content-center gap-2">
                        <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                            <a href="?url=roles&action=editar&id=<?= htmlspecialchars($rol['id_rol'] ?? '') ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Editar Rol
                            </a>
                        <?php endif; ?>
                        <a href="?url=roles" class="btn btn-secondary">
                            <i class="fas fa-list me-1"></i> Ver todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>