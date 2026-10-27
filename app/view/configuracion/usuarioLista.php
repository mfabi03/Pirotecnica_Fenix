<?php
// app/view/configuracion/usuarioLista.php
require_once __DIR__ . '/../header.php';

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($usuarios) ? count($usuarios) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
            
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-users text-gold me-2"></i> Lista de Usuarios
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    Gestiona los usuarios del sistema
                </small>
            </div>
            <div class="col-auto">
                <button type="button" 
                        class="btn btn-dark-gold" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalRegistrarUsuario"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                    <i class="fas fa-plus me-1"></i> Registrar Usuario
                </button>
            </div>
        </div>
    </div>

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="usuarios">
            <input type="hidden" name="action" value="lista">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <input type="text" name="busqueda" class="form-control" 
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

    <!-- TABLA DE USUARIOS -->
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
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyUsuarios">
                    <?php if (isset($usuarios) && is_array($usuarios) && count($usuarios) > 0): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr data-id="<?= $usuario['id_usuario'] ?>" data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>">
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
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn-action-circle btn-view btn-ver-usuario" title="Ver" data-id="<?= $usuario['id_usuario'] ?>"><i class="fas fa-eye"></i></button>
                                        <button type="button" class="btn-action-circle btn-edit btn-editar-usuario" title="Editar" data-id="<?= $usuario['id_usuario'] ?>"><i class="fas fa-edit"></i></button>
                                        <?php if (($usuario['id_usuario'] ?? 0) != $_SESSION['id_usuario']): ?>
                                        <button type="button" class="btn-action-circle btn-delete btn-eliminar-usuario" title="Eliminar" data-id="<?= $usuario['id_usuario'] ?>" data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>"><i class="fas fa-trash-alt"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-users fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">No hay usuarios registrados</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small"><i class="fas fa-users me-1"></i> Total: <?= $totalRegistros ?> usuarios</span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 1: REGISTRAR USUARIO (WIZARD)                           -->
<!-- ============================================================ -->
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
                                <div class="text-end mt-4"><button type="button" class="btn btn-warning next-step" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 25px; border-radius: 50px;">Siguiente <i class="fas fa-arrow-right"></i></button></div>
                            </div>

                            <div class="tab-pane" id="step2" style="display:none;">
                                <h4 class="mb-3 text-center" style="color: #f39c12; font-weight: bold;">Paso 2: Cuenta</h4>
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label">Usuario *</label><input type="text" name="usuario" id="wiz-usuario" class="form-control" required data-validate="nombre" data-unico-endpoint="?url=usuarios&action=check_usuario" data-unico-campo="usuario"><small class="feedback-validacion info" id="wiz-usuario-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Contraseña *</label><input type="password" name="clave" id="wiz-clave" class="form-control" required data-validate="razon"><small class="feedback-validacion info" id="wiz-clave-feedback"></small></div>
                                    <div class="col-md-6"><label class="form-label">Confirmar Contraseña *</label><input type="password" id="wiz-clave-confirm" class="form-control" required><small id="wiz-clave-confirm-feedback" class="feedback-validacion info"></small></div>
                                    <div class="col-md-12"><label class="form-label">Rol *</label><select name="id_rol" id="wiz-rol" class="form-select" required data-no-search="true"><option value="">Seleccione un rol</option><?php foreach ($roles as $rol): ?><option value="<?= htmlspecialchars($rol['id_rol']) ?>"><?= htmlspecialchars($rol['nombre_rol']) ?></option><?php endforeach; ?></select></div>
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

<!-- ============================================================ -->
<!-- MODAL 2: EDITAR USUARIO                                       -->
<!-- ============================================================ -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-edit me-2" style="color: #f39c12;"></i> Editar Usuario: <span id="editar-usuario-nombre-header" style="color: #f39c12;">—</span></h5>
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
                        <div class="col-md-6"><label class="form-label">Rol *</label><select name="id_rol" id="editar-usuario-rol" class="form-select" required><?php foreach ($roles as $rol): ?><option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre_rol']) ?></option><?php endforeach; ?></select></div>
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

<!-- ============================================================ -->
<!-- MODAL 3: VER USUARIO                                          -->
<!-- ============================================================ -->
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

<!-- ============================================================ -->
<!-- MODAL 4: ELIMINAR USUARIO                                     -->
<!-- ============================================================ -->
<div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px;">
            <div class="modal-header" style="background: #1a1a2e;">
                <h5 class="modal-title" style="color: #ffffff;"><i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Eliminar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar a <strong id="eliminar-usuario-nombre"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarEliminarUsuario" class="btn btn-danger">Sí, eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- JAVASCRIPT DEL MÓDULO DE USUARIOS -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Inicializar Validador al abrir modal
    const modalRegistrar = document.getElementById('modalRegistrarUsuario');
    modalRegistrar.addEventListener('shown.bs.modal', function () {
        if (typeof Validador !== 'undefined') {
            Validador.init(modalRegistrar);
        }
    });

    // 2. Lógica del Wizard
    const tabs = document.querySelectorAll('#wizard-tabs li');
    const panes = document.querySelectorAll('.tab-pane');
    let currentTab = 0;

    function showTab(index) {
        tabs.forEach((tab, i) => {
            if (i === index) {
                tab.classList.add('active');
                tab.classList.remove('disabled');
            } else {
                tab.classList.remove('active');
                if (i > index) tab.classList.add('disabled');
            }
        });
        panes.forEach((pane, i) => {
            pane.classList.toggle('active', i === index);
            pane.style.display = (i === index) ? 'block' : 'none';
        });
        currentTab = index;
        if (index === 2) generarResumen();
    }

    // 3. Validaciones para avanzar de paso
    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', () => {
            const currentPane = panes[currentTab];
            const inputs = currentPane.querySelectorAll('input[required], select[required]');
            let valido = true;
            let primerError = null;
            
            inputs.forEach(input => {
                input.classList.remove('is-invalid');
                
                // Validar campo vacío
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    valido = false;
                    if (!primerError) primerError = input;
                } 
                // Validar con el Validador global
                else if (typeof Validador !== 'undefined' && !Validador.validarCampo(input)) {
                    valido = false;
                    if (!primerError) primerError = input;
                }
            });

            // Validar contraseñas en el paso 2
            if (currentTab === 1) {
                const pass = document.getElementById('wiz-clave');
                const passConfirm = document.getElementById('wiz-clave-confirm');
                const feedback = document.getElementById('wiz-clave-confirm-feedback');
                
                if (pass.value !== passConfirm.value || pass.value === '') {
                    passConfirm.classList.add('is-invalid');
                    feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i> Las contraseñas no coinciden';
                    feedback.className = 'feedback-validacion error';
                    valido = false;
                    if (!primerError) primerError = passConfirm;
                } else {
                    passConfirm.classList.remove('is-invalid');
                    passConfirm.classList.add('is-valid');
                    feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i> Las contraseñas coinciden';
                    feedback.className = 'feedback-validacion success';
                }
            }

            if (!valido) {
                mostrarAlerta('Por favor, complete todos los campos obligatorios.', 'warning');
                if (primerError) primerError.focus();
                return;
            }
            if (currentTab < 2) showTab(currentTab + 1);
        });
    });

    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentTab > 0) showTab(currentTab - 1);
        });
    });

    function generarResumen() {
        document.getElementById('resumen-datos').innerHTML = 
            `<b>Nombre:</b> ${document.getElementById('wiz-nombre').value} ${document.getElementById('wiz-apellido').value}<br>
             <b>Cédula:</b> ${document.getElementById('wiz-cedula').value}<br>
             <b>Usuario:</b> ${document.getElementById('wiz-usuario').value}`;
    }

    // Al abrir modal, resetear
    modalRegistrar.addEventListener('show.bs.modal', function () {
        document.getElementById('formRegistrarUsuario').reset();
        if (typeof Validador !== 'undefined') {
            Validador.resetFormulario('formRegistrarUsuario');
        }
        showTab(0);
    });

    showTab(0);

    // 4. Enviar Formulario
    const formRegistrar = document.getElementById('formRegistrarUsuario');
    formRegistrar.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (typeof Validador !== 'undefined' && !Validador.validarFormulario('formRegistrarUsuario')) {
            mostrarAlerta('Revisa los campos marcados', 'warning'); return;
        }
        
        const boton = formRegistrar.querySelector('button[type="submit"]');
        boton.disabled = true;
        boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        
        const formData = new FormData(formRegistrar);
        formData.append('accion', 'store_ajax');
        const data = await fetchAJAX('?url=usuarios', formData);
        
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-save"></i> Guardar Usuario';
        
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            bootstrap.Modal.getInstance(modalRegistrar).hide();
            setTimeout(() => window.location.href = '?url=usuarios&action=lista', 1000);
        } else {
            mostrarAlerta(data.mensaje, 'error');
        }
    });

    // 5. Editar, Ver y Eliminar (Igual que antes)
    document.querySelectorAll('.btn-editar-usuario').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
            modal.show();
            
            const resp = await fetch(`?url=usuarios&action=show_ajax&id=${id}`);
            const data = await resp.json();
            if(data.success) {
                const u = data.data;
                document.getElementById('editar-usuario-id').value = u.id_usuario;
                document.getElementById('editar-usuario-nombre').value = u.nombre;
                document.getElementById('editar-usuario-apellido').value = u.apellido;
                document.getElementById('editar-usuario-cedula').value = u.cedula;
                document.getElementById('editar-usuario-telefono').value = u.telefono;
                document.getElementById('editar-usuario-correo').value = u.correo_electronico;
                document.getElementById('editar-usuario-direccion').value = u.direccion;
                document.getElementById('editar-usuario-usuario').value = u.usuario;
                document.getElementById('editar-usuario-rol').value = u.id_rol;
                document.getElementById('editar-usuario-nombre-header').textContent = u.nombre + ' ' + u.apellido;
                
                document.getElementById('editar-usuario-cedula').dataset.excluirId = u.id_usuario;
                document.getElementById('editar-usuario-usuario').dataset.excluirId = u.id_usuario;
                Validador.init(document.getElementById('modalEditarUsuario'));
            }
        });
    });

    document.querySelectorAll('.btn-ver-usuario').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            const modal = new bootstrap.Modal(document.getElementById('modalVerUsuario'));
            modal.show();
            document.getElementById('loadingVerUsuario').style.display = 'block';
            document.getElementById('contenidoVerUsuario').style.display = 'none';
            
            const resp = await fetch(`?url=usuarios&action=show_ajax&id=${id}`);
            const data = await resp.json();
            if(data.success) {
                const u = data.data;
                document.getElementById('ver-usuario-nombre-completo').textContent = u.nombre + ' ' + u.apellido;
                document.getElementById('ver-usuario-cedula').textContent = u.cedula;
                document.getElementById('ver-usuario-telefono').textContent = u.telefono;
                document.getElementById('ver-usuario-correo').textContent = u.correo_electronico;
                document.getElementById('ver-usuario-usuario').textContent = u.usuario;
                document.getElementById('ver-usuario-rol').textContent = u.rol_nombre;
                document.getElementById('ver-usuario-direccion').textContent = u.direccion;
                
                document.getElementById('loadingVerUsuario').style.display = 'none';
                document.getElementById('contenidoVerUsuario').style.display = 'block';
            }
        });
    });

    let idEliminar = null;
    document.querySelectorAll('.btn-eliminar-usuario').forEach(function(btn) {
        btn.addEventListener('click', function() {
            idEliminar = this.getAttribute('data-id');
            document.getElementById('eliminar-usuario-nombre').textContent = this.getAttribute('data-nombre');
            new bootstrap.Modal(document.getElementById('modalEliminarUsuario')).show();
        });
    });

    document.getElementById('btnConfirmarEliminarUsuario').addEventListener('click', async function() {
        if (!idEliminar) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = 'Eliminando...';
        
        const fd = new FormData();
        fd.append('accion', 'delete_ajax');
        fd.append('id_usuario', idEliminar);
        
        const data = await fetchAJAX('?url=usuarios', fd);
        if(data.success) {
            mostrarAlerta(data.mensaje, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalEliminarUsuario')).hide();
            document.querySelector(`tr[data-id="${idEliminar}"]`).remove();
        } else {
            mostrarAlerta(data.mensaje, 'error');
            btn.disabled = false;
            btn.innerHTML = 'Sí, eliminar';
        }
    });

    document.getElementById('formEditarUsuario').addEventListener('submit', async function(e){
        e.preventDefault();
        if (!Validador.validarFormulario('formEditarUsuario')) return;
        
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        const fd = new FormData(this);
        fd.append('accion', 'update_ajax');
        const data = await fetchAJAX('?url=usuarios', fd);
        btn.disabled = false;
        
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalEditarUsuario')).hide();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            mostrarAlerta(data.mensaje, 'error');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>ñ