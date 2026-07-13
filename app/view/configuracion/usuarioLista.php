<?php
// app/view/configuracion/usuarioLista.php
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
      
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-users text-gold me-2"></i> Lista de Usuarios
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important;">
                            <i class="fas fa-database me-2"></i> 
                            <?= isset($usuarios) ? count($usuarios) : 0 ?> usuarios registrados
                        </small>
                    </div>
                    <div class="col-xl-6">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="url" value="usuarios">
                            <input type="hidden" name="action" value="lista">
                            <div class="col-8">
                                <div class="dark-search input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="busqueda" class="form-control shadow-none" 
                                           placeholder="Buscar por nombre o cédula..."
                                           value="<?= htmlspecialchars($busqueda ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-dark-search w-100" type="submit">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                        </form>
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
                 TABLA DE USUARIOS - CABECERA OSCURA
                 ========================================== -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-user me-2"></i> Usuarios Registrados
                    </h5>
                    <a href="?url=usuarios&action=registrar" class="btn btn-dark-gold">
                        <i class="fas fa-plus me-1"></i> Registrar
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Nombre</th>
                                <th class="py-3">Apellido</th>
                                <th class="py-3">Cédula</th>
                                <th class="py-3">Teléfono</th>
                                <th class="py-3">Correo</th>
                                <th class="py-3">Rol</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($usuarios) && is_array($usuarios) && count($usuarios) > 0): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($usuario['id_usuario'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($usuario['nombre'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['apellido'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['cedula'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['telefono'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['correo_electronico'] ?? '') ?></td>
                                        <td>
                                            <?php 
                                                $rol = $usuario['rol'] ?? 0;
                                                if ($rol == 1):
                                            ?>
                                                <span class="badge-dark-admin">
                                                    <i class="fas fa-crown me-1"></i> Administrador
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-dark-user">
                                                    <i class="fas fa-user me-1"></i> Usuario
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="?url=usuarios&action=ver&id=<?= $usuario['id_usuario'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?url=usuarios&action=editar&id=<?= $usuario['id_usuario'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="?url=usuarios" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                                            <?= ($usuario['id_usuario'] ?? 0) == $_SESSION['id_usuario'] ? 'disabled' : '' ?>>
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                            <p class="mb-0">No hay usuarios registrados</p>
                                            <small>Comienza registrando un nuevo usuario</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer -->
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-users me-1"></i> 
                        Total: <?= isset($usuarios) ? count($usuarios) : 0 ?> usuarios
                    </span>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>