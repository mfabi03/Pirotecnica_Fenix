<?php
// app/view/configuracion/rolPermisos.php
require_once __DIR__ . '/../header.php';

// Helper para verificar si un permiso está activo
$tieneAccion = function($id_modulo, $accion) use ($permisosActuales) {
    return isset($permisosActuales[$id_modulo][$accion]) 
        && $permisosActuales[$id_modulo][$accion] == 1;
};
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8 col-lg-12">

            <!-- Header oscuro -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-shield-alt text-gold me-2"></i> 
                            Permisos de: <?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>
                        </h3>
                        <small class="text-muted">
                            Configure qué puede hacer este rol en cada módulo del sistema
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=roles" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver a Roles
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

            <!-- Aviso si es Administrador -->
            <?php if (($rol['id_rol'] ?? 0) == 1): ?>
                <div class="alert alert-warning border-0 mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>El rol Administrador tiene acceso total.</strong> 
                    Sus permisos no son editables para garantizar el control del sistema.
                </div>
            <?php endif; ?>

            <!-- Matriz de permisos -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="m-0 fw-bold text-dark">
                        <i class="fas fa-table text-muted me-2"></i>Matriz de Permisos
                    </h5>
                </div>

                <form method="POST" action="?url=roles">
                    <input type="hidden" name="accion" value="guardar_permisos">
                    <input type="hidden" name="id_rol" value="<?= $rol['id_rol'] ?>">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-fenix m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Módulo</th>
                                    <th class="text-center">
                                        <i class="fas fa-plus-circle text-success"></i> Crear
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-eye text-info"></i> Leer
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-edit text-primary"></i> Actualizar
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-trash text-danger"></i> Eliminar
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modulos as $mod): ?>
                                    <?php $idMod = $mod['id_modulo']; ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold">
                                            <?= htmlspecialchars($mod['nombre_modulo']) ?>
                                        </td>

                                        <?php foreach (['crear', 'leer', 'actualizar', 'eliminar'] as $accion): ?>
                                            <td class="text-center">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="permisos[<?= $idMod ?>][<?= $accion ?>]" 
                                                           value="1"
                                                           <?= $tieneAccion($idMod, $accion) ? 'checked' : '' ?>
                                                           <?= (($rol['id_rol'] ?? 0) == 1) ? 'disabled' : '' ?>
                                                           style="width: 1.5em; height: 1.5em; cursor: pointer;">
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                        <div class="card-footer bg-white py-3 d-flex justify-content-end gap-2">
                            <a href="?url=roles" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save me-2"></i> Guardar Permisos
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Leyenda -->
            <div class="card card-custom p-3 mb-4">
                <div class="d-flex flex-wrap gap-4 small text-muted">
                    <div><i class="fas fa-plus-circle text-success me-1"></i> <strong>Crear:</strong> Registrar nuevos elementos</div>
                    <div><i class="fas fa-eye text-info me-1"></i> <strong>Leer:</strong> Ver listados y detalles</div>
                    <div><i class="fas fa-edit text-primary me-1"></i> <strong>Actualizar:</strong> Editar elementos existentes</div>
                    <div><i class="fas fa-trash text-danger me-1"></i> <strong>Eliminar:</strong> Deshabilitar elementos</div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>