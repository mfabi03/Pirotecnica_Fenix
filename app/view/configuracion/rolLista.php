<?php
// app/view/configuracion/rolLista.php
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <!-- TARJETA DE TÍTULO - FONDO OSCURO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-user-shield text-gold me-2"></i> lista de Roles
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona los roles del sistema
                        </small>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <form method="GET" class="me-3">
                            <input type="hidden" name="url" value="roles">
                            <input type="hidden" name="action" value="lista">
                            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                        </form>
                        <a href="?url=roles&action=registrar" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Rol
                        </a>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-center" autocomplete="off">
                    <input type="hidden" name="url" value="roles">
                    <input type="hidden" name="action" value="lista">
                    
                    <div class="col-md-8">
                        <input type="text" name="busqueda" class="form-control" 
                               list="listaRoles"
                               placeholder="Buscar por nombre del rol o ID..."
                               value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        <datalist id="listaRoles">
                            <?php
                            // Recolectar sugerencias únicas
                            $sugerencias = [];
                            if (!empty($roles) && is_array($roles)):
                                foreach ($roles as $rol):
                                    $nombre = trim((string)($rol['nombre_rol'] ?? ''));
                                    $id     = trim((string)($rol['id_rol'] ?? ''));
                                    if ($nombre !== '') $sugerencias[$nombre]    = 1;
                                    if ($id     !== '') $sugerencias['#' . $id]  = 1;
                                endforeach;
                            endif;

                            // Pintar opciones
                            foreach (array_keys($sugerencias) as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gold w-100">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                    
                    <div class="col-md-2">
                        <a href="?url=roles&action=lista" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- MENSAJES -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABLA DE ROLES -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-user-shield me-2"></i> Roles Registrados
                    </h5>
                    <span class="text-muted small" style="color: rgba(255,255,255,0.3) !important; font-size: 0.75rem;">
                        <i class="fas fa-database me-1"></i> 
                        <?= isset($roles) ? count($roles) : 0 ?> registros
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Nombre del Rol</th>
                                <th class="py-3">Usuarios</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($roles) && is_array($roles) && count($roles) > 0): ?>
                                <?php foreach ($roles as $rol): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($rol['id_rol'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($rol['nombre_rol'] ?? '') ?></td>
                                        <td>
                                            <span class="badge" style="background: rgba(108,117,125,0.15); color: #6c757d; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                <?= htmlspecialchars($rol['total_usuarios'] ?? 0) ?> usuarios
                                            </span>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="?url=roles&action=ver&id=<?= $rol['id_rol'] ?? 0 ?>"
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?url=roles&action=editar&id=<?= $rol['id_rol'] ?? 0 ?>"
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="?url=roles&action=lista" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_rol" value="<?= $rol['id_rol'] ?? 0 ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este rol?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-user-shield fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay roles registrados</p>
                                            <small>Comienza registrando un nuevo rol</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-2 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-user-shield me-1"></i>
                        Total: <?= isset($roles) ? count($roles) : 0 ?> roles
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>