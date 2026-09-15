<?php
// app/view/configuracion/rolListar.php
require_once __DIR__ . '/../header.php';

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($roles) ? count($roles) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
            
            <!-- TARJETA DE TÍTULO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-user-tag text-gold me-2"></i> Lista de Roles
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona los roles del sistema
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=roles&action=registrar" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Rol
                        </a>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
                    <input type="hidden" name="url" value="roles">
                    <input type="hidden" name="action" value="lista">
                    
                    <!-- ✅ SELECT "MOSTRAR" ARRIBA -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                        <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ([5, 10, 25, 50] as $o): ?>
                                <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <input type="text" 
                               name="busqueda" 
                               class="form-control" 
                               placeholder="Buscar rol por nombre..."
                               value="<?= htmlspecialchars($busqueda ?? '') ?>">
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
                        <i class="fas fa-user-tag me-2"></i> Roles Registrados
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Nombre del Rol</th>
                                <th class="py-3 text-center">Usuarios Asignados</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($roles) && is_array($roles) && count($roles) > 0): ?>
                                <?php foreach ($roles as $rol): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= htmlspecialchars($rol['id_rol'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (($rol['id_rol'] ?? 0) == 1): ?>
                                                <span class="badge" style="background: rgba(220,53,69,0.15); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem;">
                                                    <i class="fas fa-crown me-1"></i>
                                                    <?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="fw-semibold"><?= htmlspecialchars($rol['nombre_rol'] ?? '') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" style="background: rgba(13,110,253,0.15); color: #0d6efd; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                <i class="fas fa-user me-1"></i>
                                                <?= $rol['total_usuarios'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <!-- Ver -->
                                                <a href="?url=roles&action=ver&id=<?= $rol['id_rol'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <!-- Editar -->
                                                <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                                                <a href="?url=roles&action=editar&id=<?= $rol['id_rol'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <!-- Permisos (ROJO LLAMATIVO) -->
                                                <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                                                <a href="?url=roles&action=permisos&id=<?= $rol['id_rol'] ?>" 
                                                   class="btn-action-circle" 
                                                   style="background: linear-gradient(135deg, #c0392b, #e74c3c); color: #ffffff; box-shadow: 0 2px 8px rgba(231,76,60,0.5); border: none;" 
                                                   title="Gestionar Permisos">
                                                    <i class="fas fa-shield-alt"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <!-- Eliminar -->
                                                <?php if (($rol['id_rol'] ?? 0) != 1 && ($rol['total_usuarios'] ?? 0) == 0): ?>
                                                <form method="POST" action="?url=roles" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_rol" value="<?= $rol['id_rol'] ?>">
                                                    <button type="submit" 
                                                            class="btn-action-circle btn-delete"
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
                                    <td colspan="4" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-user-tag fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay roles registrados</p>
                                            <small>Comienza registrando un nuevo rol</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- ✅ PAGINACIÓN: TOTAL IZQ + BOTONES DER -->
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-user-tag me-1"></i> 
                        Total: <?= $totalRegistros ?> roles
                    </span>
                    <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                </div>
            </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>