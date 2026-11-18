<?php
// app/view/configuracion/rolListar.php
require_once __DIR__ . '/../header.php';

// El acceso a este módulo está restringido al administrador desde RolController.

// Paginación
$por_pagina     = $por_pagina     ?? (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = $totalRegistros ?? (isset($roles) ? count($roles) : 0);
$pagina_actual  = $pagina_actual  ?? (int)($_GET['pagina'] ?? 1);
$totalPaginas   = $totalPaginas   ?? 1;
$ver_papelera   = $ver_papelera   ?? (isset($_GET['papelera']) && $_GET['papelera'] == '1');
?>

<div class="col-md-8 col-lg-12">
            
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-user-tag text-gold me-2"></i> 
                    <?= htmlspecialchars((string) ($ver_papelera ? 'Papelera de Roles' : 'Lista de Roles'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= htmlspecialchars((string) ($ver_papelera ? 'Roles inactivos. Puedes restaurarlos.' : 'Gestiona los roles del sistema'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if (!$ver_papelera): ?>
                <button type="button" 
                        class="btn btn-dark-gold" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalRegistrarRol"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                    <i class="fas fa-plus me-1"></i> Registrar Rol
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="roles">
            <input type="hidden" name="action" value="lista">
            <?php if ($ver_papelera): ?><input type="hidden" name="papelera" value="1"><?php endif; ?>
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-no-search="true" onchange="this.form.submit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= htmlspecialchars((string) (($por_pagina == $o) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>><?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small text-dark mb-0">Buscar</label>
                <input type="text" 
                       name="busqueda" 
                       class="form-control form-control-sm" 
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

            <div class="col-md-2">
                <?php if ($ver_papelera): ?>
                    <a href="?url=roles&action=lista" class="btn btn-success w-100" style="background: #28a745; border: none; color: #fff;">
                        <i class="fas fa-undo-alt me-1"></i> Ver Activos
                    </a>
                <?php else: ?>
                    <a href="?url=roles&action=lista&papelera=1" class="btn btn-danger w-100" style="background: #dc3545; border: none; color: #fff;">
                        <i class="fas fa-trash-alt me-1"></i> Ver Eliminados
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- MENSAJES -->
    <?php if (isset($mensaje) && !empty($mensaje)): ?>
        <div class="alert <?= htmlspecialchars((string) (($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> alert-dismissible fade show shadow-sm border-0">
            <div class="d-flex align-items-center">
                <i class="fas <?= htmlspecialchars((string) (($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> me-3 fs-4"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- TABLA DE ROLES -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-user-tag me-2"></i> <?= htmlspecialchars((string) ($ver_papelera ? 'Roles Eliminados' : 'Roles Registrados'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="tablaRoles">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">ID</th>
                        <th class="py-3">Nombre del Rol</th>
                        <th class="py-3 text-center">Usuarios Asignados</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyRoles">
                    <?php if (isset($roles) && is_array($roles) && count($roles) > 0): ?>
                        <?php foreach ($roles as $rol): ?>
                            <tr data-id="<?= htmlspecialchars((string) ((int)$rol['id_rol']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>">
                                <td class="ps-4 fw-bold">#<?= htmlspecialchars($rol['id_rol'] ?? 'N/A') ?></td>
                                <td class="nombre-rol">
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
                                        <?= htmlspecialchars((string) ((int)($rol['total_usuarios'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if (($rol['eliminado'] ?? 0) == 1): ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-trash-alt me-1"></i> En Papelera
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        
                                        <!-- Ver -->
                                        <button type="button" 
                                                class="btn-action-circle btn-view btn-ver-rol" 
                                                title="Ver Detalle"
                                                data-id="<?= htmlspecialchars((string) ((int)$rol['id_rol']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                                            <?php if ($ver_papelera): ?>
                                                <!-- Restaurar -->
                                                <button type="button" 
                                                        class="btn-action-circle btn-reactivar-rol" 
                                                        title="Restaurar Rol"
                                                        style="background: #10b981; color: #ffffff; border: none; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);"
                                                        data-id="<?= htmlspecialchars((string) ((int)$rol['id_rol']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                        data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- Editar -->
                                                <button type="button" 
                                                        class="btn-action-circle btn-edit btn-editar-rol" 
                                                        title="Editar"
                                                        data-id="<?= htmlspecialchars((string) ((int)$rol['id_rol']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                        data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <!-- Permisos -->
                                                <button type="button" 
                                                        class="btn-action-circle btn-permisos-rol" 
                                                        style="background: linear-gradient(135deg, #c0392b, #e74c3c); color: #ffffff; box-shadow: 0 2px 8px rgba(231,76,60,0.5); border: none;" 
                                                        title="Gestionar Permisos"
                                                        data-id="<?= htmlspecialchars((string) ((int)$rol['id_rol']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                        data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>">
                                                    <i class="fas fa-shield-alt"></i>
                                                </button>
                                                
                                                <!-- Inactivar -->
                                                <button type="button" 
                                                        class="btn-action-circle btn-delete btn-eliminar-rol"
                                                        title="Inactivar"
                                                        data-id="<?= htmlspecialchars((string) ((int)$rol['id_rol']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                        data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>"
                                                        data-usuarios="<?= htmlspecialchars((string) ((int)($rol['total_usuarios'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <?php if ($ver_papelera): ?>
                                        <i class="fas fa-trash-alt fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                        <p class="mb-0">La papelera está vacía</p>
                                        <small>No hay roles eliminados</small>
                                    <?php else: ?>
                                        <i class="fas fa-user-tag fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                        <p class="mb-0">No hay roles registrados</p>
                                        <small>Comienza registrando un nuevo rol</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-user-tag me-1"></i> 
                Total: <?= htmlspecialchars((string) ($totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> roles
            </span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 1: REGISTRAR ROL                                       -->
<!-- ============================================================ -->
<div class="modal fade" id="modalRegistrarRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-plus-circle me-2" style="color: #f39c12;"></i> Registrar Nuevo Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>

            <form id="formRegistrarRol">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="mb-3">
                        <label for="nombre_rol_registrar" class="form-label">Nombre del Rol *</label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre_rol_registrar" 
                               name="nombre_rol"
                               placeholder="Ej: Supervisor, Cajero, Almacenista..."
                               required
                               autocomplete="off"
                               data-validate="nombre"
                               data-unico-endpoint="?url=roles&action=check_nombre"
                               data-unico-campo="nombre_rol">
                        <small class="feedback-validacion info" id="nombre_rol_registrar-feedback">
                            <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600;">
                        <i class="fas fa-save me-1"></i> Registrar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 2: EDITAR ROL                                          -->
<!-- ============================================================ -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-edit me-2" style="color: #f39c12;"></i> Editar Rol: <span id="editar-rol-nombre-header" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>

            <form id="formEditarRol">
                <input type="hidden" id="id_rol_editar" name="id_rol">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="mb-3">
                        <label for="nombre_rol_editar" class="form-label">Nombre del Rol *</label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre_rol_editar" 
                               name="nombre_rol"
                               required
                               autocomplete="off"
                               data-validate="nombre"
                               data-unico-endpoint="?url=roles&action=check_nombre"
                               data-unico-campo="nombre_rol">
                        <small class="feedback-validacion info" id="nombre_rol_editar-feedback"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Actualizar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 3: VER DETALLE DEL ROL                                 -->
<!-- ============================================================ -->
<div class="modal fade" id="modalVerRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <div id="loadingVerRol" class="text-center py-4">
                    <div class="spinner-border" style="color: #f39c12;"></div>
                    <p class="mt-3">Cargando información...</p>
                </div>
                
                <div id="contenidoVerRol" style="display: none;">
                    <div class="text-center mb-4">
                        <div style="width: 70px; height: 70px; margin: 0 auto 15px; border-radius: 50%; background: rgba(243,156,18,0.15); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-tag fa-2x" style="color: #f39c12;"></i>
                        </div>
                        <h4 id="ver_nombre_rol" style="color: #1a1a2e; font-weight: 700; margin-bottom: 5px;"></h4>
                        <p style="color: #6c757d; font-size: 0.85rem;">ID: <span id="ver_id_rol" style="color: #f39c12; font-weight: 600;"></span></p>
                    </div>
                    
                    <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                        <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                            <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Información del Rol
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Nombre del Rol</label>
                                <p id="ver_nombre_detalle" style="color: #1a1a2e; margin-bottom: 0; font-weight: 600;"></p>
                            </div>
                            <div class="col-12">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Usuarios Asignados</label>
                                <p style="margin-bottom: 0;"><span id="ver_total_usuarios" class="badge" style="background: rgba(13,110,253,0.15); color: #0d6efd; padding: 6px 14px; border-radius: 50px; font-weight: 600;">0</span></p>
                            </div>
                            <div class="col-12">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Estado</label>
                                <div id="ver_estado_rol"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
                <button type="button" id="btnIrAEditar" class="btn btn-primary" style="display: none;">
                    <i class="fas fa-edit me-1"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 4: GESTIONAR PERMISOS DEL ROL                          -->
<!-- ============================================================ -->
<div class="modal fade" id="modalPermisosRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px;">
            <div class="modal-header" style="background: #1a1a2e; padding: 20px 32px;">
                <div>
                    <h4 class="modal-title" style="color: #ffffff; font-weight: 700; margin: 0; font-size: 1.25rem;">
                        <i class="fas fa-shield-alt me-2" style="color: #f39c12;"></i> Permisos del Rol
                    </h4>
                    <p style="color: rgba(255,255,255,0.6); margin: 4px 0 0 0; font-size: 0.875rem;">
                        Configurando: <strong id="permisos_nombre_rol" style="color: #f39c12;"></strong>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>
            
            <div class="modal-body" style="padding: 24px 32px;">
                <div id="loadingPermisos" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">Cargando permisos...</p>
                </div>
                
                <div id="contenidoPermisos" style="display: none;">
                    <form id="formPermisosRol">
                        <input type="hidden" id="permisos_id_rol" name="id_rol">
                        <div class="table-responsive">
                            <table class="table align-middle m-0">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th class="ps-4 py-3">Módulo</th>
                                        <th class="text-center py-3"><i class="fas fa-plus-circle"></i> Crear</th>
                                        <th class="text-center py-3"><i class="fas fa-eye"></i> Leer</th>
                                        <th class="text-center py-3"><i class="fas fa-edit"></i> Actualizar</th>
                                        <th class="text-center py-3"><i class="fas fa-trash"></i> Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPermisos"></tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarPermisos" class="btn" style="background: linear-gradient(135deg, #c0392b, #e74c3c); border: none; color: #fff; font-weight: 600;">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<?php $scriptsModulo = ['roles.js']; ?>
<?php require_once __DIR__ . '/../footer.php'; ?>