<?php
// app/view/configuracion/rolListar.php
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8 col-lg-12">
            
            <!-- Header oscuro consistente -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-user-tag text-gold me-2"></i> Gestión de Roles
                        </h3>
                        <small class="text-muted">
                            <?= count($roles ?? []) ?> rol<?= count($roles ?? []) !== 1 ? 'es' : '' ?> registrado<?= count($roles ?? []) !== 1 ? 's' : '' ?>
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=roles&action=registrar" class="btn btn-warning">
                            <i class="fas fa-plus me-1"></i> Registrar Rol
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

            <!-- Barra de búsqueda -->
            <div class="card card-custom p-3 mb-4">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="url" value="roles">
                    <input type="hidden" name="action" value="lista">
                    <div class="col-md-8">
                        <input type="text" 
                               name="busqueda" 
                               class="form-control" 
                               placeholder="🔍 Buscar rol por nombre..."
                               value="<?= htmlspecialchars($busqueda ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-dark w-100" type="submit">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Roles -->
            <div class="card card-custom">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="m-0 fw-bold text-dark">
                        <i class="fas fa-list text-muted me-2"></i>Roles Registrados
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-fenix m-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nombre del Rol</th>
                                <th class="text-center">Usuarios Asignados</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($roles) && is_array($roles) && count($roles) > 0): ?>
                                <?php foreach ($roles as $rol): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-muted">#<?= htmlspecialchars($rol['id_rol'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (($rol['id_rol'] ?? 0) == 1): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-crown me-1"></i>
                                                    <?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="fw-semibold"><?= htmlspecialchars($rol['nombre_rol'] ?? '') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= ($rol['total_usuarios'] ?? 0) > 0 ? 'primary' : 'secondary' ?>">
                                                <i class="fas fa-user me-1"></i>
                                                <?= $rol['total_usuarios'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="?url=roles&action=ver&id=<?= $rol['id_rol'] ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                                                    <a href="?url=roles&action=editar&id=<?= $rol['id_rol'] ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                                                    <a href="?url=roles&action=permisos&id=<?= $rol['id_rol'] ?>" 
                                                    class="btn btn-sm btn-warning" 
                                                    title="Gestionar Permisos">
                                                    <i class="fas fa-shield-alt"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <?php if (($rol['id_rol'] ?? 0) != 1 && ($rol['total_usuarios'] ?? 0) == 0): ?>
                                                    <form method="POST" action="?url=roles" class="d-inline">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id_rol" value="<?= $rol['id_rol'] ?>">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-danger"
                                                                title="Eliminar"
                                                                onclick="return confirm('¿Estás seguro de eliminar el rol &quot;<?= htmlspecialchars($rol['nombre_rol']) ?>&quot;?')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x d-block mb-3 opacity-50"></i>
                                        <p class="mb-0">No hay roles registrados</p>
                                        <a href="?url=roles&action=registrar" class="btn btn-warning btn-sm mt-3">
                                            <i class="fas fa-plus me-1"></i> Registrar el primero
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>