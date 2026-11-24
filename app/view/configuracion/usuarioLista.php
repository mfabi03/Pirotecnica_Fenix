<?php
// app/view/configuracion/usuarioLista.php
require_once __DIR__ . '/../header.php';

$ver_papelera   = $ver_papelera   ?? (isset($_GET['papelera']) && $_GET['papelera'] == '1');
?>

<div class="col-md-8 col-lg-12">
            
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-users text-gold me-2"></i> 
                    <?= htmlspecialchars((string) ($ver_papelera ? 'Papelera de Usuarios' : 'Lista de Usuarios'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= htmlspecialchars((string) ($ver_papelera ? 'Usuarios inactivos. Puedes restaurarlos.' : 'Gestiona los usuarios del sistema'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if (!$ver_papelera && $puede_crear): ?>
                <button type="button" 
                        class="btn btn-dark-gold" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalRegistrarUsuario"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                    <i class="fas fa-plus me-1"></i> Registrar Usuario
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="usuarios">
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
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       list="listaUsuarios"
                       placeholder="Buscar por nombre, apellido, cédula o correo..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaUsuarios">
                    <?php
                    $sugerencias = [];
                    if (!empty($usuarios) && is_array($usuarios)):
                        foreach ($usuarios as $u):
                            $nombre  = trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''));
                            $cedula  = trim($u['cedula'] ?? '');
                            $correo  = trim($u['correo_electronico'] ?? '');
                            if ($nombre !== '') $sugerencias[$nombre] = 1;
                            if ($cedula !== '') $sugerencias[$cedula] = 1;
                            if ($correo !== '') $sugerencias[$correo] = 1;
                        endforeach;
                    endif;

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
                <a href="?url=usuarios&action=lista" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>

            <div class="col-md-2">
                <?php if ($ver_papelera): ?>
                    <a href="?url=usuarios&action=lista" class="btn btn-success w-100" style="background: #28a745; border: none; color: #fff;">
                        <i class="fas fa-undo-alt me-1"></i> Ver Activos
                    </a>
                <?php else: ?>
                    <a href="?url=usuarios&action=lista&papelera=1" class="btn btn-danger w-100" style="background: #dc3545; border: none; color: #fff;">
                        <i class="fas fa-trash-alt me-1"></i> Ver Eliminados
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <?php if (isset($mensaje) && !empty($mensaje)): ?>
        <div class="alert <?= htmlspecialchars((string) (($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> alert-dismissible fade show shadow-sm border-0">
            <div class="d-flex align-items-center">
                <i class="fas <?= htmlspecialchars((string) (($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> me-3 fs-4"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0"><i class="fas fa-user me-2"></i> Usuarios Registrados</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">ID</th>
                        <th class="py-3">Nombre</th>
                        <th class="py-3">Apellido</th>
                        <th class="py-3">Cédula</th>
                        <th class="py-3">Teléfono</th>
                        <th class="py-3">Correo</th>
                        <th class="py-3">Rol</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyUsuarios">
                    <?php if (isset($usuarios) && is_array($usuarios) && count($usuarios) > 0): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr data-id="<?= htmlspecialchars((string) ((int)$usuario['id_usuario']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>">
                                <td class="ps-4 fw-bold">#<?= htmlspecialchars($usuario['id_usuario'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($usuario['nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($usuario['apellido'] ?? '') ?></td>
                                <td><?= htmlspecialchars($usuario['cedula'] ?? '') ?></td>
                                <td><?= htmlspecialchars($usuario['telefono'] ?? '') ?></td>
                                <td><?= htmlspecialchars($usuario['correo_electronico'] ?? '') ?></td>
                                <td>
                                    <?php if (($usuario['id_rol'] ?? 0) == 1): ?>
                                        <span class="badge" style="background: rgba(243,156,18,0.15); color: #f39c12; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-crown me-1"></i> Administrador
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(13,110,253,0.15); color: #0d6efd; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-user me-1"></i> <?= htmlspecialchars($usuario['rol_nombre'] ?? 'Usuario') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center">
                                    <?php if (($usuario['eliminado'] ?? 0) == 0): ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.15); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.15); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-trash-alt me-1"></i> En Papelera
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn-action-circle btn-view btn-ver-usuario" title="Ver" data-id="<?= htmlspecialchars((string) ((int)$usuario['id_usuario']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><i class="fas fa-eye"></i></button>

                                         <?php if ($ver_papelera): ?>
                                            <?php if ($puede_editar): ?>
                                            <button type="button" class="btn-action-circle btn-reactivar-usuario" title="Restaurar Usuario"
                                                    style="background: #07e49a; color: #ffffff; border: none; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);"
                                                    data-id="<?= htmlspecialchars((string) ((int)$usuario['id_usuario']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" 
                                                    data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($puede_editar): ?>
                                            <button type="button" class="btn-action-circle btn-edit btn-editar-usuario" title="Editar" data-id="<?= htmlspecialchars((string) ((int)$usuario['id_usuario']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><i class="fas fa-edit"></i></button>
                                            <?php endif; ?>
                                            
                                            <?php if ($puede_eliminar && ((int)$usuario['id_usuario'] !== (int)($_SESSION['id_usuario'] ?? 0))): ?>
                                                <button type="button" class="btn-action-circle btn-delete btn-eliminar-usuario" title="Mover a Papelera" data-id="<?= htmlspecialchars((string) ((int)$usuario['id_usuario']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>"><i class="fas fa-trash-alt"></i></button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <?php if ($ver_papelera): ?>
                                        <i class="fas fa-trash-alt fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                        <p class="mb-0">La papelera está vacía</p>
                                        <small>No hay usuarios eliminados</small>
                                    <?php else: ?>
                                        <i class="fas fa-users fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                        <p class="mb-0">No hay usuarios registrados</p>
                                        <small>Comienza registrando un nuevo usuario</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small"><i class="fas fa-users me-1"></i> Total: <?= htmlspecialchars((string) ($totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> usuarios</span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>
</div>

<!-- MODAL REGISTRAR USUARIO: ASISTENTE DE TRES PASOS -->
<div class="modal fade" id="modalRegistrarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-user-plus me-2" style="color: #f39c12;"></i> Registrar Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <form id="formRegistrarUsuario">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="wizard">
                        <div class="wizard-inner">
                            <div class="connecting-line"></div>
                            <ul class="nav nav-tabs" role="tablist" id="wizard-tabs">
                                <li role="presentation" class="active"><span class="round-tab"><i class="fas fa-user"></i></span></li>
                                <li role="presentation" class="disabled"><span class="round-tab"><i class="fas fa-lock"></i></span></li>
                                <li role="presentation" class="disabled"><span class="round-tab"><i class="fas fa-check"></i></span></li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane active" id="step1">
                                <h4 class="mb-3 text-center" style="color: #f39c12; font-weight: bold;">Paso 1: Datos Personales</h4>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="wiz-nombre" class="form-control" required data-validate="nombre"><small class="feedback-validacion info" id="wiz-nombre-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Apellido *</label><input type="text" name="apellido" id="wiz-apellido" class="form-control" required data-validate="nombre"><small class="feedback-validacion info" id="wiz-apellido-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Cédula *</label><input type="text" name="cedula" id="wiz-cedula" class="form-control" required data-validate="cedula" data-unico-endpoint="?url=usuarios&action=check_cedula" data-unico-campo="cedula"><small class="feedback-validacion info" id="wiz-cedula-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Teléfono *</label><input type="text" name="telefono" id="wiz-telefono" class="form-control" required data-validate="telefono"><small class="feedback-validacion info" id="wiz-telefono-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Correo *</label><input type="email" name="correo_electronico" id="wiz-correo" class="form-control" required data-validate="correo"><small class="feedback-validacion info" id="wiz-correo-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Dirección *</label><input type="text" name="direccion" id="wiz-direccion" class="form-control" required data-validate="direccion"><small class="feedback-validacion info" id="wiz-direccion-feedback"></small></div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="button" class="btn btn-warning next-step" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 25px; border-radius: 50px;">Siguiente <i class="fas fa-arrow-right"></i></button>
                                </div>
                            </div>

                            <div class="tab-pane" id="step2" style="display:none;">
                                <h4 class="mb-3 text-center" style="color: #f39c12; font-weight: bold;">Paso 2: Cuenta</h4>
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label">Usuario *</label><input type="text" name="usuario" id="wiz-usuario" class="form-control" required data-validate="nombre" data-unico-endpoint="?url=usuarios&action=check_usuario" data-unico-campo="usuario"><small class="feedback-validacion info" id="wiz-usuario-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Contraseña *</label><input type="password" name="clave" id="wiz-clave" class="form-control" required data-validate="razon"><small class="feedback-validacion info" id="wiz-clave-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Confirmar Contraseña *</label><input type="password" id="wiz-clave-confirm" class="form-control" required><small id="wiz-clave-confirm-feedback" class="feedback-validacion info"></small></div>
                                    <div class="col-md-12">
                                        <label class="form-label">Rol *</label>
                                        <select name="id_rol" id="wiz-rol" class="form-select" required data-no-search="true">
                                            <option value="">Seleccione un rol</option>
                                            <?php foreach (($roles ?? []) as $rol): ?>
                                                <option value="<?= htmlspecialchars((string) ((int)$rol['id_rol']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($rol['nombre_rol']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary prev-step" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;"><i class="fas fa-arrow-left"></i> Atrás</button>
                                    <button type="button" class="btn btn-warning next-step" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 25px; border-radius: 50px;">Revisar <i class="fas fa-check"></i></button>
                                </div>
                            </div>

                            <div class="tab-pane" id="complete" style="display:none;">
                                <h4 class="mb-3 text-center text-success" style="font-weight: bold;"><i class="fas fa-check-circle"></i> Confirmar</h4>
                                <div class="alert alert-info"><span id="resumen-datos"></span></div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary prev-step" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;"><i class="fas fa-arrow-left"></i> Atrás</button>
                                    <button type="submit" class="btn btn-warning" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 25px; border-radius: 50px;"><i class="fas fa-save"></i> Guardar Usuario</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDITAR USUARIO -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-edit me-2" style="color: #f39c12;"></i> Editar Usuario: <span id="editar-usuario-nombre-header" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>
            <form id="formEditarUsuario">
                <input type="hidden" id="editar-usuario-id" name="id_usuario">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="editar-usuario-nombre" class="form-control" required data-validate="nombre"></div>
                        <div class="col-md-6"><label class="form-label">Apellido *</label><input type="text" name="apellido" id="editar-usuario-apellido" class="form-control" required data-validate="nombre"></div>
                        <div class="col-md-6"><label class="form-label">Cédula *</label><input type="text" name="cedula" id="editar-usuario-cedula" class="form-control" required data-validate="cedula" data-unico-endpoint="?url=usuarios&action=check_cedula" data-unico-campo="cedula"></div>
                        <div class="col-md-6"><label class="form-label">Teléfono *</label><input type="text" name="telefono" id="editar-usuario-telefono" class="form-control" required data-validate="telefono"></div>
                        <div class="col-md-6"><label class="form-label">Correo *</label><input type="email" name="correo_electronico" id="editar-usuario-correo" class="form-control" required data-validate="correo"></div>
                        <div class="col-md-6"><label class="form-label">Dirección</label><input type="text" name="direccion" id="editar-usuario-direccion" class="form-control" data-validate="direccion"></div>
                        <div class="col-md-6"><label class="form-label">Usuario *</label><input type="text" name="usuario" id="editar-usuario-usuario" class="form-control" required data-validate="nombre" data-unico-endpoint="?url=usuarios&action=check_usuario" data-unico-campo="usuario"></div>
                        <div class="col-md-6"><label class="form-label">Nueva Clave</label><input type="password" name="clave" id="editar-usuario-clave" class="form-control" placeholder="Dejar vacío para no cambiar"></div>
                        <div class="col-md-12">
                            <label class="form-label">Rol *</label>
                            <select name="id_rol" id="editar-usuario-rol" class="form-select" required data-no-search="true">
                                <?php foreach (($roles ?? []) as $rol): ?>
                                    <option value="<?= htmlspecialchars((string) ((int)$rol['id_rol']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($rol['nombre_rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL VER USUARIO -->
<div class="modal fade" id="modalVerUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body" style="padding: 20px 24px;">
                <div id="loadingVerUsuario" class="text-center py-4">
                    <div class="spinner-border" style="color: #f39c12;"></div>
                    <p class="mt-3">Cargando...</p>
                </div>
                <div id="contenidoVerUsuario" style="display: none;">
                    <h4 id="ver-usuario-nombre-completo" class="text-center mb-3"></h4>
                    <div class="row g-3">
                        <div class="col-md-6"><b>Cédula:</b> <span id="ver-usuario-cedula"></span></div>
                        <div class="col-md-6"><b>Teléfono:</b> <span id="ver-usuario-telefono"></span></div>
                        <div class="col-md-6"><b>Correo:</b> <span id="ver-usuario-correo"></span></div>
                        <div class="col-md-6"><b>Usuario:</b> <span id="ver-usuario-usuario"></span></div>
                        <div class="col-md-6"><b>Rol:</b> <span id="ver-usuario-rol"></span></div>
                        <div class="col-md-6"><b>Dirección:</b> <span id="ver-usuario-direccion"></span></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- JS DEL WIZARD (delegación, funciona aunque el modal se abra después) -->
<script>
(function () {
    // ---------- SIGUIENTE / ATRÁS ----------
    document.addEventListener('click', function (e) {
        // SIGUIENTE
        const btnNext = e.target.closest('#formRegistrarUsuario .next-step');
        if (btnNext) {
            e.preventDefault();
            const form = document.getElementById('formRegistrarUsuario');
            if (!form) return;

            const tabs  = form.querySelectorAll('.tab-pane');
            const steps = form.querySelectorAll('#wizard-tabs li');
            let current = 0;
            tabs.forEach((tab, i) => { if (tab.style.display !== 'none') current = i; });

            // Validar paso actual
            const tabActual = tabs[current];
            const campos = tabActual.querySelectorAll('input[required], select[required], textarea[required]');
            for (const campo of campos) {
                if (!campo.checkValidity()) { campo.reportValidity(); return; }
            }

            if (current < tabs.length - 1) {
                const next = current + 1;
                tabs.forEach((tab, i) => {
                    tab.style.display = (i === next) ? 'block' : 'none';
                    tab.classList.toggle('active', i === next);
                });
                steps.forEach((li, i) => {
                    li.classList.toggle('active', i === next);
                    li.classList.toggle('disabled', i > next);
                });
                if (next === tabs.length - 1) actualizarResumen(form);
            }
            return;
        }

        // ATRÁS
        const btnPrev = e.target.closest('#formRegistrarUsuario .prev-step');
        if (btnPrev) {
            e.preventDefault();
            const form = document.getElementById('formRegistrarUsuario');
            if (!form) return;

            const tabs  = form.querySelectorAll('.tab-pane');
            const steps = form.querySelectorAll('#wizard-tabs li');
            let current = 0;
            tabs.forEach((tab, i) => { if (tab.style.display !== 'none') current = i; });

            if (current > 0) {
                const prev = current - 1;
                tabs.forEach((tab, i) => {
                    tab.style.display = (i === prev) ? 'block' : 'none';
                    tab.classList.toggle('active', i === prev);
                });
                steps.forEach((li, i) => {
                    li.classList.toggle('active', i === prev);
                    li.classList.toggle('disabled', i > prev);
                });
            }
            return;
        }
    });

    function actualizarResumen(form) {
        const resumen = document.getElementById('resumen-datos');
        if (!resumen) return;
        const nombre   = form.querySelector('[name="nombre"]')?.value || '';
        const apellido = form.querySelector('[name="apellido"]')?.value || '';
        const cedula   = form.querySelector('[name="cedula"]')?.value || '';
        const usuario  = form.querySelector('[name="usuario"]')?.value || '';
        const rolSel   = form.querySelector('[name="id_rol"]');
        const rol      = rolSel ? (rolSel.options[rolSel.selectedIndex]?.text || '') : '';
        resumen.innerHTML = `<b>${nombre} ${apellido}</b><br>Cédula: ${cedula}<br>Usuario: ${usuario}<br>Rol: ${rol}`;
    }

    // ---------- CONFIRMAR CONTRASEÑA ----------
    document.addEventListener('input', function (e) {
        if (e.target && e.target.id === 'wiz-clave-confirm') {
            const clave = document.getElementById('wiz-clave');
            const fb = document.getElementById('wiz-clave-confirm-feedback');
            if (!clave || !fb) return;
            if (e.target.value === '') { fb.textContent = ''; return; }
            if (clave.value === e.target.value) {
                fb.textContent = '✅ Las contraseñas coinciden';
                fb.style.color = '#28a745';
            } else {
                fb.textContent = '❌ Las contraseñas no coinciden';
                fb.style.color = '#dc3545';
            }
        }
    });

    // ---------- SUBMIT FINAL ----------
    document.addEventListener('submit', function (e) {
        if (!e.target || e.target.id !== 'formRegistrarUsuario') return;
        e.preventDefault();

        const form = e.target;
        const clave   = document.getElementById('wiz-clave');
        const confirm = document.getElementById('wiz-clave-confirm');
        if (clave && confirm && clave.value !== confirm.value) {
            alert('Las contraseñas no coinciden');
            return;
        }

        const formData = new FormData(form);
        formData.append('accion', 'store_ajax');

        const btnSubmit = form.querySelector('button[type="submit"]');
        const originalHTML = btnSubmit ? btnSubmit.innerHTML : '';
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        }

        fetch('?url=usuarios', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('modalRegistrarUsuario');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                    }
                    mostrarAlerta(data.mensaje || 'Usuario registrado exitosamente', 'success');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    mostrarAlerta(data.mensaje || 'Error al registrar usuario', 'danger');
                    if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = originalHTML; }
                }
            })
            .catch(err => {
                mostrarAlerta('Error: ' + err.message, 'danger');
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = originalHTML; }
            });
    });

    function mostrarAlerta(texto, tipo) {
        const alerta = document.createElement('div');
        alerta.className = 'alert ' + (tipo === 'success' ? 'dark-alert-success' : 'dark-alert-danger') + ' alert-dismissible fade show shadow-sm border-0';
        alerta.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 320px;';
        alerta.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-3 fs-4"></i>
                <span>${texto}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>`;
        document.body.appendChild(alerta);
        setTimeout(() => alerta.remove(), 4000);
    }
})();
</script>

<?php $scriptsModulo = ['usuarios.js?v=' . filemtime(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'usuarios.js')]; ?>
<?php require_once __DIR__ . '/../footer.php'; ?>