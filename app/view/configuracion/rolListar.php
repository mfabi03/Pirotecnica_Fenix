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
                        <button type="button" 
                                class="btn btn-dark-gold" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalRegistrarRol"
                                style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease;">
                            <i class="fas fa-plus me-1"></i> Registrar Rol
                        </button>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
                    <input type="hidden" name="url" value="roles">
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
                    <table class="table table-hover align-middle m-0" id="tablaRoles">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Nombre del Rol</th>
                                <th class="py-3 text-center">Usuarios Asignados</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRoles">
                            <?php if (isset($roles) && is_array($roles) && count($roles) > 0): ?>
                                <?php foreach ($roles as $rol): ?>
                                    <tr data-id="<?= $rol['id_rol'] ?>" data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>">
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
                                                <?= $rol['total_usuarios'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                
                                                <!-- Ver (abre modal) -->
                                                <button type="button" 
                                                        class="btn-action-circle btn-view btn-ver-rol" 
                                                        title="Ver Detalle"
                                                        data-id="<?= $rol['id_rol'] ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <!-- Editar (AJAX + modal) -->
                                                <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                                                <button type="button" 
                                                        class="btn-action-circle btn-edit btn-editar-rol" 
                                                        title="Editar"
                                                        data-id="<?= $rol['id_rol'] ?>"
                                                        data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php endif; ?>
                                                
                                                <?php if (($rol['id_rol'] ?? 0) != 1): ?>
                                                <button type="button" 
                                                        class="btn-action-circle btn-permisos-rol" 
                                                        style="background: linear-gradient(135deg, #c0392b, #e74c3c); color: #ffffff; box-shadow: 0 2px 8px rgba(231,76,60,0.5); border: none;" 
                                                        title="Gestionar Permisos"
                                                        data-id="<?= $rol['id_rol'] ?>"
                                                        data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>">
                                                    <i class="fas fa-shield-alt"></i>
                                                </button>
                                                <?php endif; ?>
                                                
                                                <!-- Eliminar (AJAX) -->
                                                <?php if (($rol['id_rol'] ?? 0) != 1 && ($rol['total_usuarios'] ?? 0) == 0): ?>
                                                <button type="button" 
                                                        class="btn-action-circle btn-delete btn-eliminar-rol"
                                                        title="Eliminar"
                                                        data-id="<?= $rol['id_rol'] ?>"
                                                        data-nombre="<?= htmlspecialchars($rol['nombre_rol'] ?? '') ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
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
                
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-user-tag me-1"></i> 
                        Total: <?= $totalRegistros ?> roles
                    </span>
                    <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                </div>
            </div>
</div>

<!-- ========================================== -->
<!-- MODAL: REGISTRAR ROL -->
<!-- ========================================== -->
<div class="modal fade" id="modalRegistrarRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #0d0d14; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
            <div class="modal-header" style="background: rgba(243,156,18,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="fas fa-plus-circle me-2" style="color: #f39c12;"></i> Registrar Nuevo Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.3;"></button>
            </div>
            <form id="formRegistrarRol">
                <div class="modal-body" style="color: rgba(255,255,255,0.8);">
                    <div class="mb-3">
                        <label for="nombre_rol_registrar" class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">
                            Nombre del Rol <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre_rol_registrar" 
                               name="nombre_rol"
                               placeholder="Ej: Supervisor, Cajero, Almacenista..."
                               required
                               autocomplete="off"
                               style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 12px 16px;">
                        <small style="color: rgba(255,255,255,0.3); font-size: 0.75rem;">
                            El nombre debe ser único en el sistema
                        </small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.04);">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 8px 20px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 50px; padding: 8px 25px; font-weight: 600;">
                        <i class="fas fa-save me-1"></i> Registrar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: EDITAR ROL -->
<!-- ========================================== -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #0d0d14; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
            <div class="modal-header" style="background: rgba(13,110,253,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="fas fa-edit me-2" style="color: #0d6efd;"></i> Editar Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.3;"></button>
            </div>
            <form id="formEditarRol">
                <input type="hidden" id="id_rol_editar" name="id_rol">
                <div class="modal-body" style="color: rgba(255,255,255,0.8);">
                    <div class="mb-3">
                        <label for="nombre_rol_editar" class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">
                            Nombre del Rol <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre_rol_editar" 
                               name="nombre_rol"
                               required
                               autocomplete="off"
                               style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 12px 16px;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.04);">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 8px 20px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #0d6efd, #0b5ed7); border: none; color: #fff; border-radius: 50px; padding: 8px 25px; font-weight: 600;">
                        <i class="fas fa-save me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: VER DETALLE DEL ROL -->
<!-- ========================================== -->
<div class="modal fade" id="modalVerRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #0d0d14; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
            <div class="modal-header" style="background: rgba(243,156,18,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.3;"></button>
            </div>
            <div class="modal-body" style="color: rgba(255,255,255,0.8);">
                
                <!-- Loading -->
                <div id="loadingVerRol" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x" style="color: #f39c12;"></i>
                    <p class="mt-2" style="color: rgba(255,255,255,0.5);">Cargando información...</p>
                </div>
                
                <!-- Contenido -->
                <div id="contenidoVerRol" style="display: none;">
                    
                    <!-- Icono + Nombre -->
                    <div class="mb-4 text-center">
                        <div style="width: 70px; height: 70px; margin: 0 auto 15px; border-radius: 50%; background: rgba(243,156,18,0.15); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-tag fa-2x" style="color: #f39c12;"></i>
                        </div>
                        <h4 id="ver_nombre_rol" style="color: #ffffff; font-weight: 700; margin-bottom: 5px;"></h4>
                        <p style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">
                            ID: <span id="ver_id_rol" style="color: #f39c12; font-weight: 600;"></span>
                        </p>
                    </div>
                    
                    <!-- Información del rol -->
                    <div class="p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px;">
                        <h6 style="color: rgba(255,255,255,0.5); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                            <i class="fas fa-info-circle me-1"></i> Información del Rol
                        </h6>
                        
                        <div class="row g-3">
                            
                            <div class="col-12">
                                <label style="color: rgba(255,255,255,0.5); font-size: 0.75rem;">Nombre del Rol:</label>
                                <p id="ver_nombre_detalle" style="color: #ffffff; margin-bottom: 0; font-weight: 600;"></p>
                            </div>
                            
                            <div class="col-12">
                                <label style="color: rgba(255,255,255,0.5); font-size: 0.75rem;">Usuarios Asignados:</label>
                                <p style="margin-bottom: 0;">
                                    <span id="ver_total_usuarios" class="badge" style="background: rgba(13,110,253,0.15); color: #0d6efd; padding: 6px 14px; border-radius: 50px; font-weight: 600;">
                                        0
                                    </span>
                                </p>
                            </div>
                            
                            <div class="col-12">
                                <label style="color: rgba(255,255,255,0.5); font-size: 0.75rem;">Estado:</label>
                                <p style="margin-bottom: 0;">
                                    <span class="badge" style="background: rgba(40,167,69,0.15); color: #28a745; padding: 6px 14px; border-radius: 50px; font-weight: 600;">
                                        <i class="fas fa-check-circle me-1"></i> Activo
                                    </span>
                                </p>
                            </div>
                            
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.04);">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 8px 20px; font-weight: 600;">
                    Cerrar
                </button>
                <button type="button" id="btnIrAEditar" class="btn" style="background: linear-gradient(135deg, #0d6efd, #0b5ed7); border: none; color: #fff; border-radius: 50px; padding: 8px 25px; font-weight: 600; display: none;">
                    <i class="fas fa-edit me-1"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: GESTIONAR PERMISOS DEL ROL - Opción 3 -->
<!-- ========================================== -->
<div class="modal fade" id="modalPermisosRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            
            <!-- Header limpio -->
            <div class="modal-header" style="background: #ffffff; border-bottom: 1px solid #e9ecef; border-radius: 12px 12px 0 0; padding: 24px 32px;">
                <div>
                    <h4 class="modal-title" style="color: #212529; font-weight: 600; margin: 0; font-size: 1.25rem;">
                        Permisos del Rol
                    </h4>
                    <p style="color: #6c757d; margin: 4px 0 0 0; font-size: 0.875rem;">
                        Configurando: <strong id="permisos_nombre_rol" style="color: #212529;"></strong>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body" style="padding: 32px;">
                
                <!-- Loading -->
                <div id="loadingPermisos" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
                    <p class="mt-3" style="color: #6c757d;">Cargando permisos...</p>
                </div>
                
                <!-- Contenido -->
                <div id="contenidoPermisos" style="display: none;">
                    
                    <div class="alert alert-light border mb-4" style="border-radius: 8px; padding: 16px 20px;">
                        <div class="d-flex align-items-start gap-3">
                            <i class="fas fa-info-circle text-primary mt-1"></i>
                            <div style="font-size: 0.875rem; color: #495057;">
                                <strong class="d-block mb-1" style="color: #212529;">¿Cómo funciona?</strong>
                                Marca las casillas para otorgar permisos. Los cambios se guardan al hacer clic en "Guardar Cambios".
                            </div>
                        </div>
                    </div>
                    
                    <form id="formPermisosRol">
                        <input type="hidden" id="permisos_id_rol" name="id_rol">
                        
                        <div class="table-responsive">
                            <table class="table align-middle m-0">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th class="ps-4 py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; border-bottom: 2px solid #dee2e6;">
                                            Módulo
                                        </th>
                                        <th class="text-center py-3" style="border-bottom: 2px solid #dee2e6;">
                                            <div style="color: #198754; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                                <i class="fas fa-plus-circle me-1"></i> Crear
                                            </div>
                                        </th>
                                        <th class="text-center py-3" style="border-bottom: 2px solid #dee2e6;">
                                            <div style="color: #0dcaf0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                                <i class="fas fa-eye me-1"></i> Leer
                                            </div>
                                        </th>
                                        <th class="text-center py-3" style="border-bottom: 2px solid #dee2e6;">
                                            <div style="color: #0d6efd; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                                <i class="fas fa-edit me-1"></i> Actualizar
                                            </div>
                                        </th>
                                        <th class="text-center py-3" style="border-bottom: 2px solid #dee2e6;">
                                            <div style="color: #dc3545; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                                <i class="fas fa-trash me-1"></i> Eliminar
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPermisos">
                                    <!-- JS lo llena -->
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                </div>
            </div>
            
            <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #e9ecef; border-radius: 0 0 12px 12px; padding: 16px 32px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" 
                        style="border: 1px solid #dee2e6; border-radius: 8px; padding: 10px 24px; font-weight: 500; color: #495057;">
                    Cancelar
                </button>
                <button type="button" id="btnGuardarPermisos" class="btn btn-primary" 
                        style="background: #0d6efd; border: none; border-radius: 8px; padding: 10px 28px; font-weight: 600;">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
            
        </div>
    </div>
</div>
<!-- ========================================== -->
<!-- JAVASCRIPT DEL MÓDULO DE ROLES -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // 1. VALIDACIÓN EN TIEMPO REAL (nombre único)
    // ==========================================
    validarCampoUnico(
        '#nombre_rol_registrar',
        '?url=roles&action=check_nombre',
        'nombre del rol'
    );
    
    // ==========================================
    // 2. REGISTRAR ROL (AJAX)
    // ==========================================
    const formRegistrar = document.getElementById('formRegistrarRol');
    formRegistrar.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(formRegistrar);
        formData.append('accion', 'store_ajax');
        
        const boton = formRegistrar.querySelector('button[type="submit"]');
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Registrando...';
        
        const data = await fetchAJAX('?url=roles', formData);
        
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
        
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalRegistrarRol'));
            modal.hide();
            
            formRegistrar.reset();
            document.getElementById('nombre_rol_registrar').classList.remove('is-valid', 'is-invalid');
            
            setTimeout(() => {
                window.location.href = '?url=roles&action=lista';
            }, 1000);
        } else {
            mostrarAlerta(data.mensaje || 'Error al registrar', 'error');
        }
    });
    
    // ==========================================
    // 3. EDITAR ROL (AJAX)
    // ==========================================
    document.querySelectorAll('.btn-editar-rol').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            
            document.getElementById('id_rol_editar').value = id;
            document.getElementById('nombre_rol_editar').value = nombre;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarRol'));
            modal.show();
        });
    });
    
    const formEditar = document.getElementById('formEditarRol');
    formEditar.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(formEditar);
        formData.append('accion', 'update_ajax');
        
        const boton = formEditar.querySelector('button[type="submit"]');
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Actualizando...';
        
        const data = await fetchAJAX('?url=roles', formData);
        
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
        
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarRol'));
            modal.hide();
            
            const fila = document.querySelector(`tr[data-id="${data.data.id}"]`);
            if (fila) {
                fila.querySelector('.nombre-rol').innerHTML = `<span class="fw-semibold">${data.data.nombre}</span>`;
                fila.setAttribute('data-nombre', data.data.nombre);
            }
        } else {
            mostrarAlerta(data.mensaje || 'Error al actualizar', 'error');
        }
    });
    
    // ==========================================
    // 4. ELIMINAR ROL (AJAX)
    // ==========================================
    document.querySelectorAll('.btn-eliminar-rol').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            
            if (!confirm(`¿Estás seguro de eliminar el rol "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('accion', 'delete_ajax');
            formData.append('id_rol', id);
            
            fetchAJAX('?url=roles', formData).then(function(data) {
                if (data.success) {
                    mostrarAlerta(data.mensaje, 'success');
                    
                    const fila = document.querySelector(`tr[data-id="${id}"]`);
                    if (fila) {
                        fila.style.transition = 'opacity 0.3s ease';
                        fila.style.opacity = '0';
                        setTimeout(() => fila.remove(), 300);
                    }
                } else {
                    mostrarAlerta(data.mensaje || 'Error al eliminar', 'error');
                }
            });
        });
    });
    
    // ==========================================
    // 5. VER DETALLE DEL ROL (AJAX + modal)
    // ==========================================
    document.querySelectorAll('.btn-ver-rol').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            
            // Resetear el modal (mostrar loading)
            document.getElementById('loadingVerRol').style.display = 'block';
            document.getElementById('contenidoVerRol').style.display = 'none';
            document.getElementById('btnIrAEditar').style.display = 'none';
            
            // Abrir modal
            const modal = new bootstrap.Modal(document.getElementById('modalVerRol'));
            modal.show();
            
            // Consultar datos por AJAX
            try {
                const respuesta = await fetch(`?url=roles&action=show_ajax&id=${id}`);
                const data = await respuesta.json();
                
                if (data.success) {
                    // Llenar el modal con los datos
                    document.getElementById('ver_nombre_rol').textContent = data.data.nombre_rol;
                    document.getElementById('ver_id_rol').textContent = data.data.id_rol;
                    document.getElementById('ver_nombre_detalle').textContent = data.data.nombre_rol;
                    document.getElementById('ver_total_usuarios').textContent = data.data.total_usuarios;
                    
                    // Mostrar botón "Editar" SOLO si no es Administrador
                    if (data.data.id_rol !== 1) {
                        const btnEditar = document.getElementById('btnIrAEditar');
                        btnEditar.style.display = 'inline-block';
                        btnEditar.onclick = function() {
                            modal.hide();
                            // Abrir el modal de editar con los datos
                            const btnEdit = document.querySelector(`.btn-editar-rol[data-id="${data.data.id_rol}"]`);
                            if (btnEdit) btnEdit.click();
                        };
                    }
                    
                    // Ocultar loading, mostrar contenido
                    document.getElementById('loadingVerRol').style.display = 'none';
                    document.getElementById('contenidoVerRol').style.display = 'block';
                } else {
                    mostrarAlerta(data.mensaje || 'Error al cargar el rol', 'error');
                    modal.hide();
                }
            } catch (error) {
                console.error('Error al ver rol:', error);
                mostrarAlerta('Error de conexión. Intenta nuevamente.', 'error');
                modal.hide();
            }
        });
    });
    
});

    // ==========================================
    // 6. GESTIONAR PERMISOS DEL ROL (AJAX + modal)
    // ==========================================
    document.querySelectorAll('.btn-permisos-rol').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            
            // Resetear modal
            document.getElementById('loadingPermisos').style.display = 'block';
            document.getElementById('contenidoPermisos').style.display = 'none';
            document.getElementById('permisos_nombre_rol').textContent = nombre;
            document.getElementById('permisos_id_rol').value = id;
            
            // Abrir modal
            const modal = new bootstrap.Modal(document.getElementById('modalPermisosRol'));
            modal.show();
            
            // Consultar datos
            try {
                const respuesta = await fetch(`?url=roles&action=permisos_ajax&id_rol=${id}`);
                const data = await respuesta.json();
                
                if (data.success) {
                    // Construir la tabla de permisos
                    const tbody = document.getElementById('tbodyPermisos');
                    let html = '';
                    
                    data.data.modulos.forEach(function(mod) {
                        const idMod = mod.id_modulo;
                        const permisosMod = data.data.permisos[idMod] || {};
                        
                        // ✅ CAMBIO: color oscuro para el nombre del módulo
                        html += `<tr style="border-bottom: 1px solid #dee2e6;">
                            <td class="ps-4 fw-semibold" style="color: #212529;">${mod.nombre_modulo}</td>`;
                        
                        ['crear', 'leer', 'actualizar', 'eliminar'].forEach(function(accion) {
                            const checked = (permisosMod[accion] == 1) ? 'checked' : '';
                            html += `
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            name="permisos[${idMod}][${accion}]" 
                                            value="1"
                                            ${checked}
                                            style="width: 1.5em; height: 1.5em; cursor: pointer;">
                                    </div>
                                </td>`;
                        });
                        
                        html += `</tr>`;
                    });
                    
                    tbody.innerHTML = html;
                    
                    // Ocultar loading, mostrar contenido
                    document.getElementById('loadingPermisos').style.display = 'none';
                    document.getElementById('contenidoPermisos').style.display = 'block';
                } else {
                    mostrarAlerta(data.mensaje || 'Error al cargar permisos', 'error');
                    modal.hide();
                }
            } catch (error) {
                console.error('Error al cargar permisos:', error);
                mostrarAlerta('Error de conexión. Intenta nuevamente.', 'error');
                modal.hide();
            }
        });
    });
    
    // Guardar permisos
    document.getElementById('btnGuardarPermisos').addEventListener('click', async function() {
        const form = document.getElementById('formPermisosRol');
        const formData = new FormData(form);
        formData.append('accion', 'save_permisos_ajax');
        
        const boton = this;
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        
        const data = await fetchAJAX('?url=roles', formData);
        
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
        
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalPermisosRol'));
            modal.hide();
        } else {
            mostrarAlerta(data.mensaje || 'Error al guardar', 'error');
        }
    });

</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
