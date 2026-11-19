<?php
// app/view/configuracion/listCategoriaView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

// La vista no instancia PermisoHelper ni ConnectDB.
// Los permisos disponibles ya vienen calculados desde su controlador.

// Saber si estamos en la vista de eliminados
$esVistaEliminados = (($estado_categoria ?? 'todos') === 'inactivos');
?>

<!-- ESTILOS PARA LOS BOTONES DE ACCIÓN CIRCULARES -->


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
                    <!-- 🔥 USAMOS LA VARIABLE $puede_crear -->
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

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form id="formFiltroCategorias" method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="categorias">
            <input type="hidden" name="action" value="lista">
            <input type="hidden" name="estado_categoria" value="<?= htmlspecialchars((string) ($esVistaEliminados ? 'inactivos' : 'activos'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            
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
                       list="listaCategorias"
                       placeholder="Nombre o ID..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaCategorias">
                    <?php
                    $sugerencias = [];
                    if (!empty($categorias) && is_array($categorias)):
                        foreach ($categorias as $cat):
                            $nombre = trim($cat['nombre_categoria'] ?? '');
                            $id     = trim((string)($cat['id_categoria'] ?? ''));
                            if ($nombre !== '') $sugerencias[$nombre]   = 1;
                            if ($id     !== '') $sugerencias['#' . $id] = 1;
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
                <a href="?url=categorias&action=lista" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>

            <!-- BOTÓN VER ELIMINADOS EN LOS FILTROS -->
            <div class="col-md-2">
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=categorias&action=lista" class="btn btn-success w-100" style="background: #28a745; border: none; color: #fff;">
                        <i class="fas fa-undo-alt me-1"></i> Ver Activos
                    </a>
                <?php else: ?>
                    <a href="?url=categorias&action=lista&estado_categoria=inactivos" class="btn btn-danger w-100" style="background: #dc3545; border: none; color: #fff;">
                        <i class="fas fa-trash-alt me-1"></i> Ver Eliminados
                    </a>
                <?php endif; ?>
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
        <div class="alert <?= htmlspecialchars((string) ($estiloMsg['clase']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas <?= htmlspecialchars((string) ($estiloMsg['icono']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> me-3"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- TABLA DE CATEGORÍAS -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-tags me-2"></i> 
                <?= htmlspecialchars((string) ($esVistaEliminados ? 'Categorías Inactivas' : 'Categorías Registradas'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">NOMBRE</th>
                        <th class="py-3">DESCRIPCIÓN</th>
                        <th class="py-3 text-center">STATUS</th>
                        <th class="pe-4 py-3 text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-tags fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">
                                        <?= htmlspecialchars((string) ($esVistaEliminados ? 'No hay categorías inactivas' : 'No hay categorías registradas'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </p>
                                    <small><?= htmlspecialchars((string) ($esVistaEliminados ? 'Las categorías activas están en la lista principal' : 'Comienza registrando una nueva categoría'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $cat): ?>
                            <?php $estaActiva = (($cat['eliminado'] ?? 0) == 0); ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($cat['nombre_categoria'] ?? '') ?></td>
                                <td><?= htmlspecialchars($cat['descripcion'] ?? 'N/A') ?></td>
                                
                                <td class="text-center">
                                    <?php if (!$estaActiva): ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-times-circle me-1"></i> Inactivo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- VER -->
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                onclick="verDetalleCategoria(<?= htmlspecialchars((string) ((int) $cat['id_categoria']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if (!$estaActiva): ?>
                                            <!-- INACTIVA: SOLO REACTIVAR -->
                                            <!-- 🔥 USAMOS LA VARIABLE $puede_editar -->
                                            <?php if ($puede_editar): ?>
                                            <button type="button" class="btn-action-circle btn-reactivar" title="Reactivar Categoría"
                                                    onclick="confirmarReactivarCategoria(<?= htmlspecialchars((string) ((int) $cat['id_categoria']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>, '<?= htmlspecialchars(addslashes($cat['nombre_categoria'] ?? '')) ?>')">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- ACTIVA: EDITAR Y ELIMINAR -->
                                            <!-- 🔥 USAMOS LA VARIABLE $puede_editar -->
                                            <?php if ($puede_editar): ?>
                                            <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                    onclick="editarCategoria(<?= htmlspecialchars((string) ((int) $cat['id_categoria']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <!-- 🔥 USAMOS LA VARIABLE $puede_eliminar -->
                                            <?php if ($puede_eliminar): ?>
                                            <button type="button" class="btn-action-circle btn-delete" title="Eliminar"
                                                    onclick="confirmarEliminarCategoria(<?= htmlspecialchars((string) ((int) $cat['id_categoria']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>, '<?= htmlspecialchars(addslashes($cat['nombre_categoria'] ?? '')) ?>')">
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
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-tags me-1"></i> 
                Total: <?= htmlspecialchars((string) ($totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> categorías
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>

<!-- MODALES -->
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_registrar_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_ver_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_editar_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_eliminar_categoria.php'; ?>

<!-- MODAL REACTIVAR -->
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
                        La categoría volverá a estar <strong>Activa</strong> y disponible para asignarse a productos.
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn"
                            style="background: #28a745; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-check me-1"></i> Sí, reactivar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- DATOS EN JSON + JAVASCRIPT -->
<script id="categoriasData" type="application/json">
<?= json_encode(
    isset($categorias) && is_array($categorias) ? array_values($categorias) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>



<?php $scriptsModulo = ['categorias.js']; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>