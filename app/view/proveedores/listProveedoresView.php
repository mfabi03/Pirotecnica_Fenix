<?php
// app/view/proveedores/listProveedoresView.php
require_once __DIR__ . '/../header.php';

$verEliminados = !empty($_GET['ver_eliminados']);
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-<?= htmlspecialchars($tipo_mensaje ?? 'success', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 12px; font-weight: 600;">
        <i class="fas fa-<?= ($tipo_mensaje ?? 'success') === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
        <?= htmlspecialchars((string) $success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="col-md-8 col-lg-12">
            
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-truck text-gold me-2"></i> 
                    <?= htmlspecialchars((string) ($verEliminados ? 'Proveedores Eliminados' : 'Lista de Proveedores'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </h3>
            </div>
            <div class="col-auto d-flex align-items-center">
                <?php if ($puede_crear && !$verEliminados): ?>
                <button type="button" class="btn btn-dark-gold" data-bs-toggle="modal" data-bs-target="#modalRegistrarProveedor" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                    <i class="fas fa-plus me-1"></i> Registrar Proveedor
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-center" autocomplete="off">
            <input type="hidden" name="url" value="proveedores">
            <input type="hidden" name="type" value="list">
            <?php if ($verEliminados): ?><input type="hidden" name="ver_eliminados" value="1"><?php endif; ?>
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-auto-submit>
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= htmlspecialchars((string) ((isset($por_pagina) && $por_pagina == $o) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>><?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <input type="text" name="busqueda" class="form-control" 
                       list="listaProveedores"
                       placeholder="Buscar por RIF, razón social..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaProveedores">
                    <?php
                    $sugerencias = [];
                    if (!empty($proveedores) && is_array($proveedores)):
                        foreach ($proveedores as $p):
                            $rif     = trim($p['rif'] ?? '');
                            $razon   = trim($p['razon_social'] ?? '');
                            if ($rif !== '') $sugerencias[$rif] = 1;
                            if ($razon !== '') $sugerencias[$razon] = 1;
                        endforeach;
                    endif;
                    foreach (array_keys($sugerencias) as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            
            <div class="col-md-7 d-flex flex-column flex-sm-row gap-2">
                <button type="submit" class="btn btn-gold flex-fill"><i class="fas fa-search me-1"></i> Buscar</button>
                <a href="?url=proveedores&amp;type=list" class="btn btn-secondary flex-fill"><i class="fas fa-times me-1"></i> Limpiar</a>
                
                <?php if ($verEliminados): ?>
                    <a href="?url=proveedores&amp;type=list" class="btn btn-success flex-fill js-filtro-listado-ajax"><i class="fas fa-undo-alt me-1"></i> Ver Activos</a>
                <?php else: ?>
                    <a href="?url=proveedores&amp;type=list&amp;ver_eliminados=1" class="btn btn-danger flex-fill js-filtro-listado-ajax"><i class="fas fa-trash-alt me-1"></i> Ver Eliminados</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0"><i class="fas fa-truck me-2"></i> <?= htmlspecialchars((string) ($verEliminados ? 'Proveedores Eliminados' : 'Proveedores Registrados'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="py-3">RIF</th>
                        <th class="py-3">Razón Social</th>
                        <th class="py-3">Contacto</th>
                        <th class="py-3">Dirección</th>
                        <th class="py-3">Correo</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($proveedores) && is_array($proveedores) && count($proveedores) > 0): ?>
                        <?php foreach ($proveedores as $p): ?>
                            <?php $proveedorInactivo = (int) ($p['eliminado'] ?? 0) === 1; ?>
                            <tr>
                                <td><?= htmlspecialchars($p['rif'] ?? '') ?></td>
                                <td><?= htmlspecialchars($p['razon_social'] ?? '') ?></td>
                                <td><?= htmlspecialchars($p['numero_contacto'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['direccion'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['correo_electronico'] ?? 'N/A') ?></td>
                                <td class="text-center">
                                    <?php if ($proveedorInactivo): ?>
                                        <span class="badge" style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:6px 14px; border-radius:50px; font-weight:600;">
                                            <i class="fas fa-times-circle me-1"></i> Eliminado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:6px 14px; border-radius:50px; font-weight:600;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if (!$proveedorInactivo): ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                    data-proveedor-action="view" data-proveedor-id="<?= (int)$p['id_proveedor'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($puede_editar): ?>
                                                <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                        data-proveedor-action="edit" data-proveedor-id="<?= (int)$p['id_proveedor'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($puede_eliminar): ?>
                                                <button type="button" class="btn-action-circle btn-delete" title="Eliminar"
                                                        data-proveedor-action="inactivate" data-proveedor-id="<?= (int)$p['id_proveedor'] ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                    data-proveedor-action="view" data-proveedor-id="<?= (int)$p['id_proveedor'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($puede_eliminar): ?>
                                                <button type="button" class="btn-action-circle btn-reactivate" title="Reactivar"
                                                        data-proveedor-action="reactivate" data-proveedor-id="<?= (int)$p['id_proveedor'] ?>">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-truck fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">No hay proveedores registrados</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small"><i class="fas fa-truck me-1"></i> Total: <?= htmlspecialchars((string) ($totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> proveedores</span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/partials/modal_registrar_proveedor.php';
require_once __DIR__ . '/partials/modal_ver_proveedor.php';
require_once __DIR__ . '/partials/modal_editar_proveedor.php';
require_once __DIR__ . '/partials/modal_inactivar_proveedor.php'; // ÚNICO modal de estado (elimina y reactiva)
?>

<?php $scriptsModulo = ['proveedores.js', 'filtro_listado_ajax.js']; ?>
<?php require_once __DIR__ . '/../footer.php'; ?>