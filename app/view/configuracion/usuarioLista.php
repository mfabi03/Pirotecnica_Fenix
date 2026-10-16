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
                    <h5 class="m-0">
                        <i class="fas fa-user me-2"></i> Usuarios Registrados
                    </h5>
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
                                                <button type="button" 
                                                        class="btn-action-circle btn-view btn-ver-usuario" 
                                                        title="Ver Detalle"
                                                        data-id="<?= $usuario['id_usuario'] ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <button type="button" 
                                                        class="btn-action-circle btn-edit btn-editar-usuario" 
                                                        title="Editar"
                                                        data-id="<?= $usuario['id_usuario'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <?php if (($usuario['id_usuario'] ?? 0) != $_SESSION['id_usuario']): ?>
                                                <button type="button" 
                                                        class="btn-action-circle btn-delete btn-eliminar-usuario"
                                                        title="Eliminar"
                                                        data-id="<?= $usuario['id_usuario'] ?>"
                                                        data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
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
                                            <small>Comienza registrando un nuevo usuario</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-users me-1"></i> 
                        Total: <?= $totalRegistros ?> usuarios
                    </span>
                    <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                </div>
            </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 1: REGISTRAR USUARIO                                    -->
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
                    
                    <!-- ===== DATOS PERSONALES ===== -->
                    <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                        <i class="fas fa-user me-2" style="color: #f39c12;"></i> Datos personales
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre *</label>
                            <input type="text" name="nombre" id="reg-usuario-nombre" class="form-control" required
                                   data-validate="nombre">
                            <small class="feedback-validacion info" id="reg-usuario-nombre-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 2 caracteres
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Apellido *</label>
                            <input type="text" name="apellido" id="reg-usuario-apellido" class="form-control" required
                                   data-validate="nombre">
                            <small class="feedback-validacion info" id="reg-usuario-apellido-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 2 caracteres
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula *</label>
                            <input type="text" name="cedula" id="reg-usuario-cedula" class="form-control" required
                                   data-validate="cedula"
                                   data-unico-endpoint="?url=usuarios&action=check_cedula"
                                   data-unico-campo="cedula">
                            <small class="feedback-validacion info" id="reg-usuario-cedula-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: V-12345678
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                            <input type="text" name="telefono" id="reg-usuario-telefono" class="form-control" required
                                   data-validate="telefono">
                            <small class="feedback-validacion info" id="reg-usuario-telefono-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: 0412-1234567
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                            <input type="email" name="correo_electronico" id="reg-usuario-correo" class="form-control" required
                                   data-validate="correo">
                            <small class="feedback-validacion info" id="reg-usuario-correo-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección</label>
                            <input type="text" name="direccion" id="reg-usuario-direccion" class="form-control"
                                   data-validate="direccion">
                            <small class="feedback-validacion info" id="reg-usuario-direccion-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 5 caracteres (opcional)
                            </small>
                        </div>
                    </div>

                    <!-- ===== CUENTA DE USUARIO ===== -->
                    <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                        <i class="fas fa-lock me-2" style="color: #f39c12;"></i> Cuenta de usuario
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Usuario *</label>
                            <input type="text" name="usuario" id="reg-usuario-usuario" class="form-control" required
                                   data-validate="nombre"
                                   data-unico-endpoint="?url=usuarios&action=check_usuario"
                                   data-unico-campo="usuario">
                            <small class="feedback-validacion info" id="reg-usuario-usuario-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Contraseña *</label>
                            <input type="password" name="clave" id="reg-usuario-clave" class="form-control" required
                                   data-validate="razon">
                            <small class="feedback-validacion info" id="reg-usuario-clave-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Rol del usuario *</label>
                            <select name="id_rol" id="reg-usuario-rol" class="form-select" required>
                                <option value="">Seleccione un rol</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= htmlspecialchars($rol['id_rol']) ?>">
                                        <?= htmlspecialchars($rol['nombre_rol']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn"
                            style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-save me-1"></i> Registrar Usuario
                    </button>
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
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-edit me-2" style="color: #f39c12;"></i> Editar Usuario: <span id="editar-usuario-nombre-header" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <form id="formEditarUsuario">
                <input type="hidden" id="editar-usuario-id" name="id_usuario">
                <div class="modal-body" style="padding: 20px 24px;">
                    
                    <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                        <i class="fas fa-user me-2" style="color: #f39c12;"></i> Datos personales
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre *</label>
                            <input type="text" name="nombre" id="editar-usuario-nombre" class="form-control" required
                                   data-validate="nombre">
                            <small class="feedback-validacion info" id="editar-usuario-nombre-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Apellido *</label>
                            <input type="text" name="apellido" id="editar-usuario-apellido" class="form-control" required
                                   data-validate="nombre">
                            <small class="feedback-validacion info" id="editar-usuario-apellido-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula *</label>
                            <input type="text" name="cedula" id="editar-usuario-cedula" class="form-control" required
                                   data-validate="cedula"
                                   data-unico-endpoint="?url=usuarios&action=check_cedula"
                                   data-unico-campo="cedula">
                            <small class="feedback-validacion info" id="editar-usuario-cedula-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                            <input type="text" name="telefono" id="editar-usuario-telefono" class="form-control" required
                                   data-validate="telefono">
                            <small class="feedback-validacion info" id="editar-usuario-telefono-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                            <input type="email" name="correo_electronico" id="editar-usuario-correo" class="form-control" required
                                   data-validate="correo">
                            <small class="feedback-validacion info" id="editar-usuario-correo-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección</label>
                            <input type="text" name="direccion" id="editar-usuario-direccion" class="form-control"
                                   data-validate="direccion">
                            <small class="feedback-validacion info" id="editar-usuario-direccion-feedback"></small>
                        </div>
                    </div>

                    <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                        <i class="fas fa-lock me-2" style="color: #f39c12;"></i> Cuenta de usuario
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Usuario *</label>
                            <input type="text" name="usuario" id="editar-usuario-usuario" class="form-control" required
                                   data-validate="nombre"
                                   data-unico-endpoint="?url=usuarios&action=check_usuario"
                                   data-unico-campo="usuario">
                            <small class="feedback-validacion info" id="editar-usuario-usuario-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nueva Contraseña</label>
                            <input type="password" name="clave" id="editar-usuario-clave" class="form-control"
                                   placeholder="Dejar vacío para no cambiar">
                            <small style="color: #6c757d; font-size: 0.7rem;">Solo si desea cambiar la contraseña</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Rol del usuario *</label>
                            <select name="id_rol" id="editar-usuario-rol" class="form-select" required>
                                <option value="">Seleccione un rol</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= htmlspecialchars($rol['id_rol']) ?>">
                                        <?= htmlspecialchars($rol['nombre_rol']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn"
                            style="background: linear-gradient(135deg, #0d6efd, #0b5ed7); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-save me-1"></i> Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 3: VER DETALLE DEL USUARIO                              -->
<!-- ============================================================ -->
<div class="modal fade" id="modalVerUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <div id="loadingVerUsuario" class="text-center py-4">
                    <div class="spinner-border" style="color: #f39c12; width: 2.5rem; height: 2.5rem;"></div>
                    <p class="mt-3" style="color: #6c757d;">Cargando información...</p>
                </div>
                
                <div id="contenidoVerUsuario" style="display: none;">
                    <div class="text-center mb-4">
                        <div style="width: 70px; height: 70px; margin: 0 auto 15px; border-radius: 50%; background: rgba(243,156,18,0.15); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user fa-2x" style="color: #f39c12;"></i>
                        </div>
                        <h4 id="ver-usuario-nombre-completo" style="color: #1a1a2e; font-weight: 700; margin-bottom: 5px;"></h4>
                        <span id="ver-usuario-rol-badge" class="badge" style="padding: 6px 16px; border-radius: 50px; font-size: 0.75rem;"></span>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-user me-2" style="color: #f39c12;"></i> Información Personal
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Nombre Completo</label>
                                    <p id="ver-usuario-nombre" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Cédula</label>
                                    <p id="ver-usuario-cedula" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Teléfono</label>
                                    <p id="ver-usuario-telefono" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                                <div class="mb-0">
                                    <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Correo</label>
                                    <p id="ver-usuario-correo" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                    <i class="fas fa-lock me-2" style="color: #f39c12;"></i> Información de Cuenta
                                </h6>
                                <div class="mb-3">
                                    <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Usuario</label>
                                    <p id="ver-usuario-usuario" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                                <div class="mb-3">
                                    <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Rol</label>
                                    <p id="ver-usuario-rol" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                                </div>
                                <div class="mb-0">
                                    <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Dirección</label>
                                    <p id="ver-usuario-direccion" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
                <button type="button" id="btnIrAEditarDesdeVer" class="btn"
                        style="background: linear-gradient(135deg, #0d6efd, #0b5ed7); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; display: none;">
                    <i class="fas fa-edit me-1"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL 4: ELIMINAR USUARIO                                     -->
<!-- ============================================================ -->
<div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Eliminar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                <p style="margin-bottom: 12px;">¿Estás seguro de eliminar este usuario?</p>
                
                <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                    <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Usuario a eliminar</strong>
                    <span id="eliminar-usuario-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                </div>

                <div class="alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                    <i class="fas fa-info-circle me-2"></i>
                    Esta acción no se puede deshacer.
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarEliminarUsuario" class="btn"
                        style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                    <i class="fas fa-trash-alt me-1"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- JAVASCRIPT DEL MÓDULO DE USUARIOS -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // VALIDADOR - Inicializar en modales
    // ==========================================
    document.getElementById('modalRegistrarUsuario').addEventListener('shown.bs.modal', function() {
        Validador.init(document.getElementById('modalRegistrarUsuario'));
    });
    
    document.getElementById('modalEditarUsuario').addEventListener('shown.bs.modal', function() {
        Validador.init(document.getElementById('modalEditarUsuario'));
    });
    
    // ==========================================
    // 1. REGISTRAR USUARIO (AJAX)
    // ==========================================
    const formRegistrar = document.getElementById('formRegistrarUsuario');
    formRegistrar.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!Validador.validarFormulario('formRegistrarUsuario')) {
            mostrarAlerta('Revisa los campos marcados en rojo', 'warning');
            return;
        }
        
        const formData = new FormData(formRegistrar);
        formData.append('accion', 'store_ajax');
        
        const boton = formRegistrar.querySelector('button[type="submit"]');
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Registrando...';
        
        const data = await fetchAJAX('?url=usuarios', formData);
        
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
        
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistrarUsuario'));
            modal.hide();
            
            formRegistrar.reset();
            Validador.resetFormulario('formRegistrarUsuario');
            
            setTimeout(() => {
                window.location.href = '?url=usuarios&action=lista';
            }, 1000);
        } else {
            mostrarAlerta(data.mensaje || 'Error al registrar', 'error');
        }
    });
    
    // ==========================================
    // 2. EDITAR USUARIO (AJAX)
    // ==========================================
    document.querySelectorAll('.btn-editar-usuario').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            
            // Cargar datos por AJAX
            try {
                const respuesta = await fetch(`?url=usuarios&action=show_ajax&id=${id}`);
                const data = await respuesta.json();
                
                if (data.success) {
                    const u = data.data;
                    
                    document.getElementById('editar-usuario-id').value = u.id_usuario;
                    document.getElementById('editar-usuario-nombre').value = u.nombre || '';
                    document.getElementById('editar-usuario-apellido').value = u.apellido || '';
                    document.getElementById('editar-usuario-cedula').value = u.cedula || '';
                    document.getElementById('editar-usuario-telefono').value = u.telefono || '';
                    document.getElementById('editar-usuario-correo').value = u.correo_electronico || '';
                    document.getElementById('editar-usuario-direccion').value = u.direccion || '';
                    document.getElementById('editar-usuario-usuario').value = u.usuario || '';
                    document.getElementById('editar-usuario-clave').value = '';
                    document.getElementById('editar-usuario-rol').value = u.id_rol || '';
                    document.getElementById('editar-usuario-nombre-header').textContent = (u.nombre + ' ' + u.apellido).trim();
                    
                    // Guardar id_persona para el chequeo de cédula única
                    const inputCedula = document.getElementById('editar-usuario-cedula');
                    inputCedula.dataset.excluirId = u.id_usuario;
                    
                    const inputUsuario = document.getElementById('editar-usuario-usuario');
                    inputUsuario.dataset.excluirId = u.id_usuario;
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
                    modal.show();
                } else {
                    mostrarAlerta(data.mensaje || 'Error al cargar datos', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión', 'error');
            }
        });
    });
    
    const formEditar = document.getElementById('formEditarUsuario');
    formEditar.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!Validador.validarFormulario('formEditarUsuario')) {
            mostrarAlerta('Revisa los campos marcados en rojo', 'warning');
            return;
        }
        
        const formData = new FormData(formEditar);
        formData.append('accion', 'update_ajax');
        
        const boton = formEditar.querySelector('button[type="submit"]');
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Actualizando...';
        
        const data = await fetchAJAX('?url=usuarios', formData);
        
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
        
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarUsuario'));
            modal.hide();
            
            setTimeout(() => {
                window.location.href = '?url=usuarios&action=lista';
            }, 1000);
        } else {
            mostrarAlerta(data.mensaje || 'Error al actualizar', 'error');
        }
    });
    
    // ==========================================
    // 3. VER DETALLE USUARIO (AJAX)
    // ==========================================
    document.querySelectorAll('.btn-ver-usuario').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            
            document.getElementById('loadingVerUsuario').style.display = 'block';
            document.getElementById('contenidoVerUsuario').style.display = 'none';
            document.getElementById('btnIrAEditarDesdeVer').style.display = 'none';
            
            const modal = new bootstrap.Modal(document.getElementById('modalVerUsuario'));
            modal.show();
            
            try {
                const respuesta = await fetch(`?url=usuarios&action=show_ajax&id=${id}`);
                const data = await respuesta.json();
                
                if (data.success) {
                    const u = data.data;
                    const nombreCompleto = (u.nombre + ' ' + u.apellido).trim();
                    
                    document.getElementById('ver-usuario-nombre-completo').textContent = nombreCompleto;
                    document.getElementById('ver-usuario-nombre').textContent = nombreCompleto;
                    document.getElementById('ver-usuario-cedula').textContent = u.cedula || '—';
                    document.getElementById('ver-usuario-telefono').textContent = u.telefono || '—';
                    document.getElementById('ver-usuario-correo').textContent = u.correo_electronico || '—';
                    document.getElementById('ver-usuario-usuario').textContent = u.usuario || '—';
                    document.getElementById('ver-usuario-rol').textContent = u.rol_nombre || 'Usuario';
                    document.getElementById('ver-usuario-direccion').textContent = u.direccion || 'No registrada';
                    
                    const badge = document.getElementById('ver-usuario-rol-badge');
                    if (u.id_rol === 1) {
                        badge.textContent = 'Administrador';
                        badge.style.background = 'rgba(243,156,18,0.15)';
                        badge.style.color = '#f39c12';
                    } else {
                        badge.textContent = u.rol_nombre || 'Usuario';
                        badge.style.background = 'rgba(13,110,253,0.15)';
                        badge.style.color = '#0d6efd';
                    }
                    
                    // Botón Editar
                    const btnEditar = document.getElementById('btnIrAEditarDesdeVer');
                    btnEditar.style.display = 'inline-block';
                    btnEditar.onclick = function() {
                        modal.hide();
                        const btnEdit = document.querySelector(`.btn-editar-usuario[data-id="${u.id_usuario}"]`);
                        if (btnEdit) btnEdit.click();
                    };
                    
                    document.getElementById('loadingVerUsuario').style.display = 'none';
                    document.getElementById('contenidoVerUsuario').style.display = 'block';
                } else {
                    mostrarAlerta(data.mensaje || 'Error al cargar', 'error');
                    modal.hide();
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión', 'error');
                modal.hide();
            }
        });
    });
    
    // ==========================================
    // 4. ELIMINAR USUARIO (AJAX)
    // ==========================================
    let idUsuarioEliminar = null;
    
    document.querySelectorAll('.btn-eliminar-usuario').forEach(function(btn) {
        btn.addEventListener('click', function() {
            idUsuarioEliminar = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            
            document.getElementById('eliminar-usuario-nombre').textContent = nombre;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEliminarUsuario'));
            modal.show();
        });
    });
    
    document.getElementById('btnConfirmarEliminarUsuario').addEventListener('click', async function() {
        if (!idUsuarioEliminar) return;
        
        const boton = this;
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Eliminando...';
        
        const formData = new FormData();
        formData.append('accion', 'delete_ajax');
        formData.append('id_usuario', idUsuarioEliminar);
        
        const data = await fetchAJAX('?url=usuarios', formData);
        
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
        
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEliminarUsuario'));
            modal.hide();
            
            const fila = document.querySelector(`tr[data-id="${idUsuarioEliminar}"]`);
            if (fila) {
                fila.style.transition = 'opacity 0.3s ease';
                fila.style.opacity = '0';
                setTimeout(() => fila.remove(), 300);
            }
            
            idUsuarioEliminar = null;
        } else {
            mostrarAlerta(data.mensaje || 'Error al eliminar', 'error');
        }
    });
    
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>