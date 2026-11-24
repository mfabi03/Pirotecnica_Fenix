<?php
// app/view/configuracion/listCategoriaView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

$esVistaEliminados = (($estado_categoria ?? 'todos') === 'inactivos');
?>

<!-- ========================================== -->
<!-- DATATABLES CON BOOTSTRAP 5 DESDE CDN       -->
<!-- ========================================== -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
    /* Visibilidad forzada e integración visual con tema oscuro/claro */
    #tablaCategorias_wrapper,
    .dataTables_wrapper,
    .dataTables_wrapper .row,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        visibility: visible !important;
        opacity: 1 !important;
    }

    .dataTables_wrapper > .row {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_info {
        color: #1a1a2e !important;
        font-weight: 500 !important;
        font-size: 0.9rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px;
        margin-bottom: 0 !important;
    }

    .dataTables_wrapper .dataTables_filter {
        text-align: right !important;
    }

    /* Sobrescribir reglas globales de .dark-card en estilo.css */
    .dark-card .dataTables_wrapper .dataTables_filter input.form-control {
        background: #ffffff !important;
        color: #1a1a2e !important;
        border: 1.5px solid #ced4da !important;
        border-radius: 50px !important;
        padding: 6px 16px !important;
        min-height: 36px !important;
        width: 220px !important;
        display: inline-block !important;
        box-shadow: none !important;
    }

    .dark-card .dataTables_wrapper .dataTables_filter input.form-control:focus {
        border-color: #f39c12 !important;
        box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.15) !important;
    }

    .dark-card .dataTables_wrapper .dataTables_length select.form-select {
        background-color: #ffffff !important;
        color: #1a1a2e !important;
        border: 1.5px solid #ced4da !important;
        border-radius: 50px !important;
        padding: 5px 32px 5px 14px !important;
        min-height: 36px !important;
        width: auto !important;
        display: inline-block !important;
        box-shadow: none !important;
    }

    .dark-card .dataTables_wrapper .dataTables_length select.form-select:focus {
        border-color: #f39c12 !important;
        box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.15) !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        display: flex !important;
        justify-content: flex-end !important;
    }

    .dataTables_wrapper .dataTables_paginate ul.pagination {
        display: flex !important;
        gap: 4px;
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-item .page-link {
        border-radius: 50px !important;
        color: #1a1a2e !important;
        background: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        padding: 6px 14px !important;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        box-shadow: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-item .page-link:hover {
        background: #e67e22 !important;
        border-color: #e67e22 !important;
        color: #ffffff !important;
    }

    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background: linear-gradient(135deg, #f39c12, #e67e22) !important;
        border-color: #f39c12 !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_paginate .page-item.disabled .page-link {
        background: #f1f3f5 !important;
        color: #adb5bd !important;
        border-color: #e9ecef !important;
        cursor: not-allowed;
    }

    .dark-card.no-card-jump:hover {
        transform: none !important;
    }

    @media (max-width: 767.98px) {
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            justify-content: center !important;
            text-align: center !important;
            margin-bottom: 8px;
        }
    }
</style>

<div class="col-md-8 col-lg-12">
            
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-tags text-gold me-2"></i> 
                    <?= htmlspecialchars((string) ($esVistaEliminados ? 'Categorías Eliminadas' : 'Lista de Categorías'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= htmlspecialchars((string) ($esVistaEliminados ? 'Categorías inactivas en el sistema' : 'Gestiona las categorías de productos'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=categorias&action=lista" class="btn btn-secondary" style="border-radius: 50px; padding: 8px 22px; font-weight: 600;">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Categorías Activas
                    </a>
                <?php else: ?>
                    <?php if ($puede_crear): ?>
                    <button type="button" class="btn btn-dark-gold" 
                            data-bs-toggle="modal" data-bs-target="#modalRegistrarCategoria"
                            style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                        <i class="fas fa-plus me-1"></i> Registrar Categoría
                    </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
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
        <div class="alert <?= htmlspecialchars((string) ($estiloMsg['clase']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas <?= htmlspecialchars((string) ($estiloMsg['icono']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> me-3"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- TABLA DE CATEGORÍAS CON DATATABLES -->
    <div class="dark-card no-card-jump card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-tags me-2"></i> 
                <?= htmlspecialchars((string) ($esVistaEliminados ? 'Categorías Inactivas' : 'Categorías Registradas'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </h5>
            <div>
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=categorias&action=lista" class="btn btn-sm btn-success" style="border-radius: 50px;">
                        <i class="fas fa-undo-alt me-1"></i> Ver Activos
                    </a>
                <?php else: ?>
                    <a href="?url=categorias&action=lista&estado_categoria=inactivos" class="btn btn-sm btn-danger" style="border-radius: 50px;">
                        <i class="fas fa-trash-alt me-1"></i> Ver Eliminados
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card-body p-4">
            <div class="table-responsive">
            <table id="tablaCategorias" class="table table-hover align-middle m-0 w-100">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>DESCRIPCIÓN</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <?php $estaActiva = (($cat['eliminado'] ?? 0) == 0); ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($cat['nombre_categoria'] ?? '') ?></td>
                                <td><?= htmlspecialchars($cat['descripcion'] ?? 'N/A') ?></td>
                                
                                <td class="text-center">
                                    <?php if (!$estaActiva): ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-times-circle me-1"></i> Eliminado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                onclick="verDetalleCategoria(<?= (int) $cat['id_categoria'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if (!$estaActiva): ?>
                                            <?php if ($puede_editar): ?>
                                            <button type="button" class="btn-action-circle btn-reactivar" title="Reactivar Categoría"
                                                    onclick="confirmarReactivarCategoria(<?= (int) $cat['id_categoria'] ?>, '<?= htmlspecialchars(addslashes($cat['nombre_categoria'] ?? '')) ?>')">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($puede_editar): ?>
                                            <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                    onclick="editarCategoria(<?= (int) $cat['id_categoria'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($puede_eliminar): ?>
                                            <button type="button" class="btn-action-circle btn-delete" title="Eliminar"
                                                    onclick="confirmarEliminarCategoria(<?= (int) $cat['id_categoria'] ?>, '<?= htmlspecialchars(addslashes($cat['nombre_categoria'] ?? '')) ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<!-- MODALES (Se mantienen igual) -->
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_registrar_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_ver_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_editar_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_eliminar_categoria.php'; ?>

<?php if ($puede_editar): ?>
<div class="modal fade" id="modalReactivarCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(40,167,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-undo-alt me-2" style="color: #28a745;"></i> Reactivar Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form method="POST" action="?url=categorias&action=reactivar">
                <input type="hidden" name="accion" value="reactivar">
                <input type="hidden" name="id_categoria" id="reactivar-categoria-id">
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px;">¿Deseas reactivar esta categoría?</p>
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Categoría a reactivar</strong>
                        <span id="reactivar-categoria-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>
                    <div class="alert" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                        <i class="fas fa-check-circle me-2"></i>
                        La categoría volverá a estar <strong>Activa</strong>.
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: #28a745; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;"><i class="fas fa-check me-1"></i> Sí, reactivar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- DATOS EN JSON -->
<script id="categoriasData" type="application/json">
<?= json_encode(
    isset($categorias) && is_array($categorias) ? array_values($categorias) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>

<?php
$scriptsExternos = [
    'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js',
    'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js',
    'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js',
];
$scriptsModulo = ['categorias.js?v=' . filemtime(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'categorias.js')];
require_once dirname(__DIR__, 2) . "/view/footer.php";
?>