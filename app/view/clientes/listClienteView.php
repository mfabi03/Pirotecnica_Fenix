<?php
// app/view/clientes/listClienteView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

// Crear conexión si no existe
if (!isset($db) || $db === null) {
    try {
        $db = (new ConnectDB())->getConnection();
    } catch (Exception $e) {
        $db = null;
    }
}

// Obtener permisos del usuario actual
$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$puede_crear_cliente = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Clientes', 'crear') : false;
$puede_editar_cliente = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Clientes', 'actualizar') : false;
$puede_eliminar_cliente = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Clientes', 'eliminar') : false;

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($clientes) ? count($clientes) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
    
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-users text-gold me-2"></i> Lista de Clientes
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    Gestiona los clientes registrados en el sistema
                </small>
            </div>
            <div class="col-auto">
                <?php if ($puede_crear_cliente): ?>
                <button type="button" class="btn btn-dark-gold" 
                        data-bs-toggle="modal" data-bs-target="#modalRegistrarCliente"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                    <i class="fas fa-plus me-1"></i> Registrar Cliente
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="clientes">
            <input type="hidden" name="type" value="list">
            
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
                       list="listaClientes"
                       placeholder="Buscar por nombre, cédula, RIF o razón social..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaClientes">
                    <?php
                    $sugerencias = [];
                    if (!empty($clientes) && is_array($clientes)):
                        foreach ($clientes as $c):
                            $razon  = trim($c['razon_social'] ?? '');
                            $nombre = trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                            $doc    = trim($c['cedula'] ?? $c['rif'] ?? '');
                            if ($razon  !== '') $sugerencias[$razon]  = 1;
                            if ($nombre !== '') $sugerencias[$nombre] = 1;
                            if ($doc    !== '') $sugerencias[$doc]    = 1;
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
                <a href="?url=clientes&type=list" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- MENSAJES -->
    <?php
    $mapaTipos = [
        'success' => ['clase' => 'dark-alert-success', 'icono' => 'fa-check-circle'],
        'danger'  => ['clase' => 'dark-alert-danger',  'icono' => 'fa-exclamation-circle'],
        'error'   => ['clase' => 'dark-alert-danger',  'icono' => 'fa-exclamation-circle'],
        'warning' => ['clase' => 'dark-alert-warning', 'icono' => 'fa-exclamation-triangle'],
        'info'    => ['clase' => 'dark-alert-info',    'icono' => 'fa-info-circle'],
    ];
    $estiloMsg = $mapaTipos[$tipo_mensaje ?? 'info'] ?? $mapaTipos['info'];
    ?>
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?= $estiloMsg['clase'] ?> alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas <?= $estiloMsg['icono'] ?> me-3"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- TABLA DE CLIENTES -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-users me-2"></i> Clientes Registrados
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Cédula / RIF</th>
                        <th class="py-3">Nombre / Razón Social</th>
                        <th class="py-3">Tipo</th>
                        <th class="py-3">Teléfono</th>
                        <th class="py-3">Correo</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($clientes) && is_array($clientes) && count($clientes) > 0): ?>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($c['cedula'] ?? $c['rif'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (($c['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                        <strong><?= htmlspecialchars($c['razon_social'] ?? 'N/A') ?></strong>
                                    <?php else: ?>
                                        <?= htmlspecialchars(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($c['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                        <span class="badge" style="background: rgba(13,110,253,0.15); color: #0d6efd; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-building me-1"></i> Jurídico
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.15); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-user me-1"></i> Natural
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($c['telefono'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($c['correo_electronico'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                onclick="verDetalleCliente(<?= (int) $c['id_cliente'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($puede_editar_cliente): ?>
                                        <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                onclick="editarCliente(<?= (int) $c['id_cliente'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($puede_eliminar_cliente): ?>
                                        <button type="button" class="btn-action-circle btn-delete" title="Eliminar"
                                                onclick="confirmarEliminarCliente(<?= (int) $c['id_cliente'] ?>, '<?= htmlspecialchars(addslashes($c['tipo_cliente'] === 'Jurídico' ? ($c['razon_social'] ?? '') : (($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')))) ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-users fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">No hay clientes registrados</p>
                                    <small>Comienza registrando un nuevo cliente</small>
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
                Total: <?= $totalRegistros ?> clientes
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODAL 1: REGISTRAR CLIENTE (HEADER OSCURO)                   -->
<!-- ============================================================ -->
<?php if ($puede_crear_cliente): ?>
<div class="modal fade" id="modalRegistrarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-user-plus me-2" style="color: #f39c12;"></i> Registrar Nuevo Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <ul class="nav nav-tabs mb-4" style="border-bottom: 1px solid #e9ecef;">
                    <li class="nav-item">
                        <button class="nav-link active" id="tab-natural-reg" data-bs-toggle="tab" data-bs-target="#pane-natural-reg" type="button"
                                style="color: #f39c12; background: transparent; border: none; border-bottom: 2px solid #f39c12; font-weight: 600;">
                            <i class="fas fa-user me-1"></i> Cliente Natural
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-juridico-reg" data-bs-toggle="tab" data-bs-target="#pane-juridico-reg" type="button"
                                style="color: #6c757d; background: transparent; border: none; font-weight: 600;">
                            <i class="fas fa-building me-1"></i> Cliente Jurídico
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- PESTAÑA NATURAL -->
                    <div class="tab-pane fade show active" id="pane-natural-reg">
                        <form method="POST" action="?url=clientes&type=register" id="formRegistrarNatural">
                            <input type="hidden" name="accion" value="register_natural">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula *</label>
                                    <input type="text" id="reg-cedula" name="cedula" class="form-control" required
                                           placeholder="Ej: V-12345678"
                                           data-validate="cedula"
                                           data-unico-endpoint="?url=clientes&type=check_duplicado"
                                           data-unico-campo="cedula">
                                    <small class="feedback-validacion info" id="reg-cedula-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: V-12345678
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                    <input type="tel" id="reg-telefono" name="telefono" class="form-control" required
                                           placeholder="Ej: 0412-5556677" data-validate="telefono">
                                    <small class="feedback-validacion info" id="reg-telefono-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: 0412-1234567
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombres *</label>
                                    <input type="text" id="reg-nombre" name="nombre" class="form-control" required
                                           placeholder="Solo letras" data-validate="nombre">
                                    <small class="feedback-validacion info" id="reg-nombre-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 2 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Apellidos *</label>
                                    <input type="text" id="reg-apellido" name="apellido" class="form-control" required
                                           placeholder="Solo letras" data-validate="nombre">
                                    <small class="feedback-validacion info" id="reg-apellido-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 2 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                    <input type="email" id="reg-correo" name="correo_electronico" class="form-control" required
                                           placeholder="ejemplo@correo.com" data-validate="correo">
                                    <small class="feedback-validacion info" id="reg-correo-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Fecha de Nacimiento *</label>
                                    <input type="date" id="reg-fecha-nac" name="fecha_de_nacimiento" class="form-control" required data-validate="fecha">
                                    <small class="feedback-validacion info" id="reg-fecha-nac-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Debe ser mayor de 18 años
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                                    <textarea id="reg-direccion" name="direccion" class="form-control" rows="2" required
                                              style="resize: vertical;" placeholder="Dirección completa" data-validate="direccion"></textarea>
                                    <small class="feedback-validacion info" id="reg-direccion-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 5 caracteres
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn"
                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                                    <i class="fas fa-save me-1"></i> Registrar Cliente Natural
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- PESTAÑA JURÍDICO -->
                    <div class="tab-pane fade" id="pane-juridico-reg">
                        <form method="POST" action="?url=clientes&type=register_juridico" id="formRegistrarJuridico">
                            <input type="hidden" name="accion" value="register_juridico">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                                    <input type="text" id="reg-rif" name="rif" class="form-control" required
                                           placeholder="Ej: J-123456789"
                                           data-validate="rif"
                                           data-unico-endpoint="?url=clientes&type=check_duplicado"
                                           data-unico-campo="rif">
                                    <small class="feedback-validacion info" id="reg-rif-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: J-123456789
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                    <input type="tel" id="reg-jur-telefono" name="telefono" class="form-control" required
                                           placeholder="Ej: 0251-1234567" data-validate="telefono">
                                    <small class="feedback-validacion info" id="reg-jur-telefono-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: 0251-1234567
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                                    <input type="text" id="reg-jur-razon" name="razon_social" class="form-control" required
                                           placeholder="Nombre de la empresa" data-validate="razon">
                                    <small class="feedback-validacion info" id="reg-jur-razon-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                    <input type="email" id="reg-jur-correo" name="correo_electronico" class="form-control" required
                                           placeholder="empresa@correo.com" data-validate="correo">
                                    <small class="feedback-validacion info" id="reg-jur-correo-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección Fiscal *</label>
                                    <input type="text" id="reg-jur-direccion" name="direccion" class="form-control" required
                                           placeholder="Dirección fiscal" data-validate="direccion">
                                    <small class="feedback-validacion info" id="reg-jur-direccion-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 5 caracteres
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn"
                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                                    <i class="fas fa-save me-1"></i> Registrar Cliente Jurídico
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ============================================================ -->
<!-- MODAL 2: VER DETALLE DEL CLIENTE                             -->
<!-- ============================================================ -->
<div class="modal fade" id="modalVerCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <div id="ver-cliente-titulo" class="text-center mb-4">
                    <h4 style="color: #f39c12; font-weight: 700; margin-bottom: 6px;">—</h4>
                    <span id="ver-cliente-tipo-badge" class="badge" style="padding: 6px 16px; border-radius: 50px; font-size: 0.75rem;">—</span>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Información del Cliente
                            </h6>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Cédula / RIF</label>
                                <p id="ver-cliente-documento" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                            </div>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Nombre / Razón Social</label>
                                <p id="ver-cliente-nombre" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                            </div>
                            <div class="mb-0" id="ver-cliente-fecha-wrap">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Fecha de Nacimiento</label>
                                <p id="ver-cliente-fecha" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                <i class="fas fa-address-book me-2" style="color: #f39c12;"></i> Datos de Contacto
                            </h6>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Teléfono</label>
                                <p id="ver-cliente-telefono" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Correo Electrónico</label>
                                <p id="ver-cliente-correo" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                            <div class="mb-0">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Dirección</label>
                                <p id="ver-cliente-direccion" style="color: #1a1a2e; margin: 0;">—</p>
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
            </div>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODAL 3: EDITAR CLIENTE                                      -->
<!-- ============================================================ -->
<?php if ($puede_editar_cliente): ?>
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-edit me-2" style="color: #f39c12;"></i> Editar Cliente: <span id="editar-cliente-nombre-header" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <!-- FORMULARIO NATURAL -->
                <div id="editar-bloque-natural" style="display: none;">
                    <form method="POST" action="?url=clientes&type=edit" id="formEditarNatural">
                        <input type="hidden" name="accion" value="edit_natural">
                        <input type="hidden" name="id_cliente" id="editar-natural-id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula *</label>
                                <input type="text" id="editar-natural-cedula" name="cedula" class="form-control" required
                                       data-validate="cedula"
                                       data-unico-endpoint="?url=clientes&type=check_duplicado"
                                       data-unico-campo="cedula">
                                <small class="feedback-validacion info" id="editar-natural-cedula-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                <input type="tel" id="editar-natural-telefono" name="telefono" class="form-control" required data-validate="telefono">
                                <small class="feedback-validacion info" id="editar-natural-telefono-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombres *</label>
                                <input type="text" id="editar-natural-nombre" name="nombre" class="form-control" required data-validate="nombre">
                                <small class="feedback-validacion info" id="editar-natural-nombre-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Apellidos *</label>
                                <input type="text" id="editar-natural-apellido" name="apellido" class="form-control" required data-validate="nombre">
                                <small class="feedback-validacion info" id="editar-natural-apellido-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                <input type="email" id="editar-natural-correo" name="correo_electronico" class="form-control" required data-validate="correo">
                                <small class="feedback-validacion info" id="editar-natural-correo-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Fecha de Nacimiento *</label>
                                <input type="date" id="editar-natural-fecha" name="fecha_de_nacimiento" class="form-control" required data-validate="fecha">
                                <small class="feedback-validacion info" id="editar-natural-fecha-feedback"></small>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                                <textarea id="editar-natural-direccion" name="direccion" class="form-control" rows="2" required style="resize: vertical;" data-validate="direccion"></textarea>
                                <small class="feedback-validacion info" id="editar-natural-direccion-feedback"></small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                    style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                                Cancelar
                            </button>
                            <button type="submit" class="btn"
                                    style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                                <i class="fas fa-save me-1"></i> Actualizar Cliente
                            </button>
                        </div>
                    </form>
                </div>

                <!-- FORMULARIO JURÍDICO -->
                <div id="editar-bloque-juridico" style="display: none;">
                    <form method="POST" action="?url=clientes&type=edit_juridico" id="formEditarJuridico">
                        <input type="hidden" name="accion" value="edit_juridico">
                        <input type="hidden" name="id_cliente" id="editar-juridico-id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                                <input type="text" id="editar-juridico-rif" name="rif" class="form-control" required
                                       data-validate="rif"
                                       data-unico-endpoint="?url=clientes&type=check_duplicado"
                                       data-unico-campo="rif">
                                <small class="feedback-validacion info" id="editar-juridico-rif-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                <input type="tel" id="editar-juridico-telefono" name="telefono" class="form-control" required data-validate="telefono">
                                <small class="feedback-validacion info" id="editar-juridico-telefono-feedback"></small>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                                <input type="text" id="editar-juridico-razon" name="razon_social" class="form-control" required data-validate="razon">
                                <small class="feedback-validacion info" id="editar-juridico-razon-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                <input type="email" id="editar-juridico-correo" name="correo_electronico" class="form-control" required data-validate="correo">
                                <small class="feedback-validacion info" id="editar-juridico-correo-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección Fiscal *</label>
                                <input type="text" id="editar-juridico-direccion" name="direccion" class="form-control" required data-validate="direccion">
                                <small class="feedback-validacion info" id="editar-juridico-direccion-feedback"></small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                    style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                                Cancelar
                            </button>
                            <button type="submit" class="btn"
                                    style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                                <i class="fas fa-save me-1"></i> Actualizar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ============================================================ -->
<!-- MODAL 4: ELIMINAR CLIENTE                                    -->
<!-- ============================================================ -->
<?php if ($puede_eliminar_cliente): ?>
<div class="modal fade" id="modalEliminarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Eliminar Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <form method="POST" action="?url=clientes&type=delete">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_cliente" id="eliminar-cliente-id">
                
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px;">¿Estás seguro de eliminar este cliente?</p>
                    
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Cliente a eliminar</strong>
                        <span id="eliminar-cliente-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>

                    <div class="alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                        <i class="fas fa-info-circle me-2"></i>
                        Esta acción no se puede deshacer. Si el cliente tiene notas de salida asociadas, el sistema bloqueará la eliminación.
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn"
                            style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-trash-alt me-1"></i> Sí, eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ============================================================ -->
<!-- MODAL 5: ALERTA DE MENOR DE EDAD                             -->
<!-- ============================================================ -->
<div class="modal fade" id="modalAlertaMenorEdad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-user-slash me-2" style="color: #dc3545;"></i> Cliente Menor de Edad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 24px; text-align: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #dc3545; margin-bottom: 16px;"></i>
                
                <h4 style="color: #1a1a2e; font-weight: 700; margin-bottom: 12px;">
                    ¡No se puede registrar!
                </h4>
                
                <p style="color: #6c757d; margin-bottom: 20px;">
                    El cliente es <strong>menor de 18 años</strong>. Por normativa de seguridad, no se permite registrar menores de edad en el sistema.
                </p>

                <div class="p-3 mb-0" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px; text-align: left;">
                    <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Edad detectada</strong>
                    <span id="alerta-edad-detectada" style="color: #dc3545; font-weight: 700; font-size: 1.2rem;">—</span>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                        style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; width: 100%;">
                    <i class="fas fa-check me-1"></i> Entendido
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- REAPERTURA DE MODAL TRAS POST FALLIDO POR DUPLICADO         -->
<!-- Reabre el modal correspondiente, repuebla los campos y pinta -->
<!-- el error inline en el campo que falló.                      -->
<!-- ============================================================ -->
<?php if (!empty($_SESSION['errores_campo']) && !empty($_SESSION['form_modal'])): 
    $erroresCampo = $_SESSION['errores_campo'];
    $formModal    = $_SESSION['form_modal'];
    $formData     = $_SESSION['form_data'] ?? [];
    unset($_SESSION['errores_campo'], $_SESSION['form_modal'], $_SESSION['form_data']);
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var formModal = <?= json_encode($formModal, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
    var formData  = <?= json_encode($formData,  JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;
    var errores   = <?= json_encode($erroresCampo, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;

    function setField(id, value) {
        var el = document.getElementById(id);
        if (el && value !== undefined && value !== null) el.value = value;
    }

    function pintarError(inputId, mensaje) {
        var input    = document.getElementById(inputId);
        var feedback = document.getElementById(inputId + '-feedback');
        if (input) {
            input.classList.remove('campo-valido', 'campo-verificando');
            input.classList.add('campo-invalido');
            input.dataset.unicoEstado = 'duplicado';
        }
        if (feedback) {
            feedback.classList.remove('info', 'success', 'checking');
            feedback.classList.add('error');
            feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i> ' + mensaje;
        }
    }

    // ============================================================
    // REGISTRAR (NATURAL o JURÍDICO)
    // ============================================================
    if (formModal === 'registrar_natural' || formModal === 'registrar_juridico') {
        var modalEl = document.getElementById('modalRegistrarCliente');
        if (!modalEl) return;

        modalEl.addEventListener('shown.bs.modal', function onShown() {
            modalEl.removeEventListener('shown.bs.modal', onShown);

            var tabId = (formModal === 'registrar_natural') ? 'tab-natural-reg' : 'tab-juridico-reg';
            var tabEl = document.getElementById(tabId);
            if (tabEl) new bootstrap.Tab(tabEl).show();

            if (formModal === 'registrar_natural') {
                setField('reg-cedula',          formData.cedula);
                setField('reg-nombre',          formData.nombre);
                setField('reg-apellido',        formData.apellido);
                setField('reg-telefono',        formData.telefono);
                setField('reg-correo',          formData.correo_electronico);
                setField('reg-fecha-nac',       formData.fecha_de_nacimiento);
                setField('reg-direccion',       formData.direccion);
                if (errores.cedula) pintarError('reg-cedula', errores.cedula);
            } else {
                setField('reg-rif',             formData.rif);
                setField('reg-jur-razon',       formData.razon_social);
                setField('reg-jur-telefono',    formData.telefono);
                setField('reg-jur-correo',      formData.correo_electronico);
                setField('reg-jur-direccion',   formData.direccion);
                if (errores.rif) pintarError('reg-rif', errores.rif);
            }
        }, { once: true });

        new bootstrap.Modal(modalEl).show();
    }

    // ============================================================
    // EDITAR (NATURAL o JURÍDICO)
    // ============================================================
    else if (formModal === 'editar_natural' || formModal === 'editar_juridico') {
        var modalEl = document.getElementById('modalEditarCliente');
        if (!modalEl) return;

        var bloqueNat = document.getElementById('editar-bloque-natural');
        var bloqueJur = document.getElementById('editar-bloque-juridico');

        modalEl.addEventListener('shown.bs.modal', function onShown() {
            modalEl.removeEventListener('shown.bs.modal', onShown);

            if (formModal === 'editar_natural') {
                bloqueNat.style.display = 'block';
                bloqueJur.style.display = 'none';

                setField('editar-natural-id',        formData.id_cliente);
                setField('editar-natural-cedula',    formData.cedula);
                setField('editar-natural-nombre',    formData.nombre);
                setField('editar-natural-apellido',  formData.apellido);
                setField('editar-natural-telefono',  formData.telefono);
                setField('editar-natural-correo',    formData.correo_electronico);
                setField('editar-natural-fecha',     formData.fecha_de_nacimiento);
                setField('editar-natural-direccion', formData.direccion);

                var hdr = document.getElementById('editar-cliente-nombre-header');
                if (hdr) hdr.textContent = ((formData.nombre || '') + ' ' + (formData.apellido || '')).trim();

                if (errores.cedula) pintarError('editar-natural-cedula', errores.cedula);
            } else {
                bloqueNat.style.display = 'none';
                bloqueJur.style.display = 'block';

                setField('editar-juridico-id',        formData.id_cliente);
                setField('editar-juridico-rif',       formData.rif);
                setField('editar-juridico-razon',     formData.razon_social);
                setField('editar-juridico-telefono',  formData.telefono);
                setField('editar-juridico-correo',    formData.correo_electronico);
                setField('editar-juridico-direccion', formData.direccion);

                var hdr2 = document.getElementById('editar-cliente-nombre-header');
                if (hdr2) hdr2.textContent = formData.razon_social || '';

                if (errores.rif) pintarError('editar-juridico-rif', errores.rif);
            }
        }, { once: true });

        new bootstrap.Modal(modalEl).show();
    }
});
</script>
<?php endif; ?>


<!-- ============================================================ -->
<!-- DATOS DE CLIENTES EN JSON + JAVASCRIPT ESPECÍFICO            -->
<!-- ============================================================ -->
<script id="clientesData" type="application/json">
<?= json_encode(
    isset($clientes) && is_array($clientes) ? array_values($clientes) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ============================================================
    // DATOS DE CLIENTES
    // ============================================================
    const clientesData = JSON.parse(document.getElementById('clientesData').textContent);
    const clientesPorId = {};
    clientesData.forEach(function(c) {
        clientesPorId[parseInt(c.id_cliente)] = c;
    });

    // ============================================================
    // CALLBACK DE MENOR DE EDAD (usa el modal del sistema)
    // ============================================================
    let modalMenorEdadMostrado = false;
    Validador.onMenorEdad(function(edad) {
        if (modalMenorEdadMostrado) return;
        modalMenorEdadMostrado = true;
        document.getElementById('alerta-edad-detectada').textContent = edad + ' años';
        new bootstrap.Modal(document.getElementById('modalAlertaMenorEdad')).show();
        
        document.getElementById('modalAlertaMenorEdad').addEventListener('hidden.bs.modal', function onHidden() {
            modalMenorEdadMostrado = false;
            this.removeEventListener('hidden.bs.modal', onHidden);
        });
    });

    // ============================================================
    // INICIALIZAR VALIDADOR EN LOS MODALES
    // ============================================================
    Validador.init(document.getElementById('modalRegistrarCliente'));
    Validador.init(document.getElementById('modalEditarCliente'));

    // ============================================================
    // RESET AL ABRIR MODAL REGISTRAR
    // ============================================================
    document.getElementById('modalRegistrarCliente').addEventListener('show.bs.modal', function() {
        Validador.resetFormulario('formRegistrarNatural');
        Validador.resetFormulario('formRegistrarJuridico');
    });

    // ============================================================
    // VALIDAR AL ENVIAR FORMULARIOS
    // ============================================================
    ['formRegistrarNatural', 'formRegistrarJuridico', 'formEditarNatural', 'formEditarJuridico'].forEach(function(id) {
        const form = document.getElementById(id);
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!Validador.validarFormulario(id)) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });

    // ============================================================
    // VER DETALLE
    // ============================================================
    window.verDetalleCliente = function(id) {
        const c = clientesPorId[id];
        if (!c) return;

        const esJuridico = (c.tipo_cliente === 'Jurídico');
        const nombre = esJuridico 
            ? (c.razon_social || 'Sin razón social') 
            : ((c.nombre || '') + ' ' + (c.apellido || '')).trim();
        const documento = esJuridico ? (c.rif || 'N/A') : (c.cedula || 'N/A');

        document.getElementById('ver-cliente-titulo').querySelector('h4').textContent = nombre || 'Sin nombre';
        
        const badge = document.getElementById('ver-cliente-tipo-badge');
        if (esJuridico) {
            badge.textContent = 'Jurídico';
            badge.style.background = 'rgba(13,110,253,0.15)';
            badge.style.color = '#0d6efd';
        } else {
            badge.textContent = 'Natural';
            badge.style.background = 'rgba(40,167,69,0.15)';
            badge.style.color = '#28a745';
        }

        document.getElementById('ver-cliente-documento').textContent = documento;
        document.getElementById('ver-cliente-nombre').textContent = nombre;
        document.getElementById('ver-cliente-telefono').textContent = c.telefono || 'N/A';
        document.getElementById('ver-cliente-correo').textContent = c.correo_electronico || 'N/A';
        document.getElementById('ver-cliente-direccion').textContent = c.direccion || 'Sin dirección registrada';

        const fechaWrap = document.getElementById('ver-cliente-fecha-wrap');
        if (esJuridico || !c.fecha_de_nacimiento) {
            fechaWrap.style.display = 'none';
        } else {
            fechaWrap.style.display = 'block';
            document.getElementById('ver-cliente-fecha').textContent = Validador.formatearFecha(c.fecha_de_nacimiento);
        }

        new bootstrap.Modal(document.getElementById('modalVerCliente')).show();
    };

    // ============================================================
    // EDITAR
    // ============================================================
    window.editarCliente = function(id) {
        const c = clientesPorId[id];
        if (!c) return;

        const esJuridico = (c.tipo_cliente === 'Jurídico');
        const nombre = esJuridico 
            ? (c.razon_social || '') 
            : ((c.nombre || '') + ' ' + (c.apellido || '')).trim();
        
        document.getElementById('editar-cliente-nombre-header').textContent = nombre || 'Cliente';

        const bloqueNatural = document.getElementById('editar-bloque-natural');
        const bloqueJuridico = document.getElementById('editar-bloque-juridico');

        if (esJuridico) {
            bloqueNatural.style.display = 'none';
            bloqueJuridico.style.display = 'block';

            document.getElementById('editar-juridico-id').value = c.id_cliente;
            document.getElementById('editar-juridico-rif').value = c.rif || '';
            document.getElementById('editar-juridico-telefono').value = c.telefono || '';
            document.getElementById('editar-juridico-razon').value = c.razon_social || '';
            document.getElementById('editar-juridico-correo').value = c.correo_electronico || '';
            document.getElementById('editar-juridico-direccion').value = c.direccion || '';
            
            setTimeout(function() {
                document.querySelectorAll('#editar-bloque-juridico input[data-validate]').forEach(function(input) {
                    Validador.validarCampo(input);
                });
            }, 100);
        } else {
            bloqueNatural.style.display = 'block';
            bloqueJuridico.style.display = 'none';

            document.getElementById('editar-natural-id').value = c.id_cliente;
            document.getElementById('editar-natural-cedula').value = c.cedula || '';
            document.getElementById('editar-natural-telefono').value = c.telefono || '';
            document.getElementById('editar-natural-nombre').value = c.nombre || '';
            document.getElementById('editar-natural-apellido').value = c.apellido || '';
            document.getElementById('editar-natural-correo').value = c.correo_electronico || '';
            document.getElementById('editar-natural-fecha').value = c.fecha_de_nacimiento || '';
            document.getElementById('editar-natural-direccion').value = c.direccion || '';
            
            setTimeout(function() {
                document.querySelectorAll('#editar-bloque-natural input[data-validate], #editar-bloque-natural textarea[data-validate]').forEach(function(input) {
                    Validador.validarCampo(input);
                });
            }, 100);
        }

        new bootstrap.Modal(document.getElementById('modalEditarCliente')).show();
    };

    // ============================================================
    // ELIMINAR
    // ============================================================
    window.confirmarEliminarCliente = function(id, nombre) {
        document.getElementById('eliminar-cliente-id').value = id;
        document.getElementById('eliminar-cliente-nombre').textContent = nombre || 'Cliente #' + id;
        new bootstrap.Modal(document.getElementById('modalEliminarCliente')).show();
    };

    // ============================================================
    // ESTILO DE PESTAÑAS
    // ============================================================
    document.querySelectorAll('#modalRegistrarCliente .nav-link').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function(e) {
            document.querySelectorAll('#modalRegistrarCliente .nav-link').forEach(function(t) {
                t.style.color = '#6c757d';
                t.style.borderBottom = 'none';
            });
            e.target.style.color = '#f39c12';
            e.target.style.borderBottom = '2px solid #f39c12';
        });
    });
});
</script>

<!-- ============================================================ -->
<!-- AUTO-CIERRE DE ALERTAS                                       -->
<!-- ============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
        var ms = parseInt(el.dataset.autoDismiss, 10) || 5000;
        setTimeout(function () {
            if (!el.parentNode) return;
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function () { el.remove(); }, 400);
        }, ms);
    });
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>