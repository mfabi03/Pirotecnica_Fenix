<?php
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        

        <!-- Contenido Principal -->
        <div class="col-md-9 col-lg-10">
            
            <!-- Barra de búsqueda -->
            <div class="card card-custom p-4 mb-4 bg-white">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 font-weight-bold text-dark">📋 Registro de Usuarios</h3>
                        <small class="text-muted"><?= isset($usuarios) ? count($usuarios) : 0 ?> usuarios registrados</small>
                    </div>
                    <div class="col-xl-6">
                            <form method="GET" class="row g-2">
                            <input type="hidden" name="url" value="usuarios">
                            <input type="hidden" name="action" value="lista">
                            <div class="col-8">
                                <input type="text" name="busqueda" class="form-control" 
                                       placeholder="Buscar por nombre o cédula..."
                                       value="<?= htmlspecialchars($busqueda ?? '') ?>">
                            </div>
                            <div class="col-4">
                                <button class="btn btn-dark w-100" type="submit">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mensajes de notificación -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?? 'info' ?> alert-dismissible fade show">
                    <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Tabla de Usuarios -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-user text-muted me-2"></i>Usuarios Registrados
                    </h5>
                    <a href="?url=usuarios&action=registrar" class="btn btn-sm btn-warning">
                        <i class="fas fa-plus me-1"></i> Registrar
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-fenix m-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cédula</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Acciones</th>
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
                                            <span class="badge <?= ($usuario['rol'] ?? 0) == 1 ? 'bg-danger' : 'bg-secondary' ?>">
                                                <?= htmlspecialchars($usuario['rol_nombre'] ?? 'Usuario') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <!-- Botón Ver -->
                                            <a href="?url=usuarios&action=ver&id=<?= $usuario['id_usuario'] ?>" 
                                               class="btn btn-sm btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- Botón Editar -->
                                            <a href="?url=usuarios&action=editar&id=<?= $usuario['id_usuario'] ?>" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <!-- Botón Eliminar -->
                                            <form method="POST" action="?url=usuarios" class="d-inline">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                                        <?= ($usuario['id_usuario'] ?? 0) == $_SESSION['id_usuario'] ? 'disabled' : '' ?>>
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No hay usuarios registrados
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