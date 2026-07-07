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
            
            <div class="card card-custom p-4 mb-4 bg-white">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 font-weight-bold text-dark">👁️ Detalle del Usuario</h3>
                        <small class="text-muted">Información completa del usuario</small>
                    </div>
                    <div class="col-xl-6 text-end">
                        <a href="?url=usuarios" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Detalle -->
            <div class="card card-custom">
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Columna Izquierda -->
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted text-uppercase small">Información Personal</h6>
                                <hr>
                                <div class="mb-3">
                                    <label class="fw-bold">Nombre completo:</label>
                                    <p><?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Cédula:</label>
                                    <p><?= htmlspecialchars($usuario['cedula'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Teléfono:</label>
                                    <p><?= htmlspecialchars($usuario['telefono'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Correo electrónico:</label>
                                    <p><?= htmlspecialchars($usuario['correo_electronico'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Columna Derecha -->
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted text-uppercase small">Información de Cuenta</h6>
                                <hr>
                                <div class="mb-3">
                                    <label class="fw-bold">Usuario:</label>
                                    <p><?= htmlspecialchars($usuario['usuario'] ?? '') ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Rol:</label>
                                    <p>
                                        <span class="badge <?= ($usuario['id_rol'] ?? 0) == 1 ? 'bg-danger' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($usuario['rol_nombre'] ?? 'Usuario') ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Fecha de registro:</label>
                                    <p><?= isset($usuario['fecha_registro']) ? date('d/m/Y H:i', strtotime($usuario['fecha_registro'])) : '—' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="text-center mt-4">
                        <a href="?url=usuarios&action=editar&id=<?= htmlspecialchars($usuario['id_usuario'] ?? '') ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Editar usuario
                        </a>
                        <a href="?url=usuarios" class="btn btn-secondary">
                            <i class="fas fa-list me-1"></i> Ver todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>