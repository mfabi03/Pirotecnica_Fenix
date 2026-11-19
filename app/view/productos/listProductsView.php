<?php
// app/view/productos/listProductsView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

// La vista no instancia PermisoHelper ni ConnectDB.
// Los permisos disponibles ya vienen calculados desde su controlador.

// Saber si estamos en la vista de eliminados (para cambiar el título)
$esVistaEliminados = (($estado_producto ?? 'todos') === 'inactivos');
?>



<div class="col-md-8 col-lg-12">

    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-cube text-gold me-2"></i> 
                    <?= htmlspecialchars((string) ($esVistaEliminados ? 'Productos Eliminados' : 'Lista de Productos'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= htmlspecialchars((string) ($esVistaEliminados ? 'Productos inactivos en el sistema' : 'Gestiona los productos registrados en el sistema'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </small>
            </div>
            <div class="col-auto d-flex align-items-center">
                <?php if ($puede_crear && !$esVistaEliminados): ?>
                <button type="button" class="btn btn-dark-gold" data-bs-toggle="modal" data-bs-target="#modalRegistrarProducto" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease;">
                    <i class="fas fa-plus me-1"></i> Registrar Producto
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-center" autocomplete="off">
            <input type="hidden" name="url" value="productos">
            <input type="hidden" name="type" value="list">
            <input type="hidden" name="estado_producto" value="<?= htmlspecialchars((string) ($esVistaEliminados ? 'inactivos' : 'activos'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-auto-submit>
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= htmlspecialchars((string) (($por_pagina == $o) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>><?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <input type="text" name="busqueda" class="form-control"
                       list="listaProductos"
                       placeholder="Nombre, código o categoría..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaProductos">
                    <?php
                    $sugerencias = [];
                    if (!empty($productos) && is_array($productos)):
                        foreach ($productos as $p):
                            $nombre = trim($p['descripcion'] ?? '');
                            $codigo = trim((string)($p['id_producto'] ?? ''));
                            $categoria = trim($p['nombre_categoria'] ?? '');
                            if ($nombre    !== '') $sugerencias[$nombre]    = 1;
                            if ($codigo    !== '') $sugerencias['#' . $codigo] = 1;
                            if ($categoria !== '') $sugerencias[$categoria] = 1;
                        endforeach;
                    endif;

                    foreach (array_keys($sugerencias) as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="col-md-7 d-flex flex-column flex-sm-row gap-2">
                <button type="submit" class="btn btn-gold flex-fill">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
                <a href="?url=productos&amp;type=list" class="btn btn-secondary flex-fill">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=productos&amp;type=list" class="btn btn-secondary flex-fill">
                        <i class="fas fa-undo-alt me-1"></i> Ver Activos
                    </a>
                <?php else: ?>
                    <a href="?url=productos&amp;type=list&amp;estado_producto=inactivos" class="btn btn-danger flex-fill">
                        <i class="fas fa-trash-alt me-1"></i> Ver Eliminados
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

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

    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-cube me-2"></i> 
                <?= htmlspecialchars((string) ($esVistaEliminados ? 'Productos Inactivos' : 'Productos Registrados'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="tablaProductos">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">NOMBRE</th>
                        <th class="py-3">CATEGORÍA</th>
                        <th class="py-3">ESPECIFICACIONES</th>
                        <th class="py-3">COSTO</th>
                        <th class="py-3">STOCK</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($productos) && is_array($productos) && count($productos) > 0): ?>
                        <?php foreach ($productos as $p): ?>
                            <?php 
                                $estaActivo = (($p['eliminado'] ?? 0) == 0);
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($p['descripcion'] ?? '') ?></td>
                                
                                <td>
                                    <span class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem;">
                                        <?= htmlspecialchars($p['nombre_categoria'] ?? 'Sin categoría') ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <?php if (!empty($p['especificaciones'])): ?>
                                        <span title="<?= htmlspecialchars($p['especificaciones']) ?>" style="cursor: help;">
                                            <?= htmlspecialchars(substr($p['especificaciones'], 0, 30)) ?>
                                            <?= htmlspecialchars((string) (strlen($p['especificaciones']) > 30 ? '...' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Sin especificaciones</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td style="font-weight: 600;">
                                    $<?= htmlspecialchars((string) (number_format($p['costo_unitario'] ?? 0, 2, ',', '.')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </td>
                                
                                <td>
                                    <?php 
                                        $stock = $p['stock'] ?? 0;
                                        $stockMin = $p['stock_minimo'] ?? 10;
                                        if ($stock <= $stockMin):
                                    ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars((string) ($stock), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars((string) ($stock), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- COLUMNA ESTADO -->
                                <td class="text-center">
                                    <?php if (!$estaActivo): ?>
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
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle" onclick="verDetalleProducto(<?= htmlspecialchars((string) ((int) $p['id_producto']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if (!$estaActivo): ?>
                                            <!-- INACTIVO: SOLO REACTIVAR -->
                                            <?php if ($puede_editar): ?>
                                            <button type="button" class="btn-action-circle btn-reactivate" title="Reactivar Producto"
                                                    onclick="confirmarReactivarProducto(<?= htmlspecialchars((string) ((int) $p['id_producto']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>, '<?= htmlspecialchars(addslashes($p['descripcion'] ?? '')) ?>')">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- ACTIVO: EDITAR Y ELIMINAR -->
                                            <?php if ($puede_editar): ?>
                                            <button type="button" class="btn-action-circle btn-edit" title="Editar" onclick="editarProducto(<?= htmlspecialchars((string) ((int) $p['id_producto']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($puede_eliminar): ?>
                                            <button type="button" class="btn-action-circle btn-delete" title="Eliminar" onclick="confirmarEliminarProducto(<?= htmlspecialchars((string) ((int) $p['id_producto']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>, '<?= htmlspecialchars(addslashes($p['descripcion'] ?? '')) ?>')">
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
                            <td colspan="7" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-cube fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">
                                        <?= htmlspecialchars((string) ($esVistaEliminados ? 'No hay productos inactivos' : 'No hay productos registrados'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </p>
                                    <small><?= htmlspecialchars((string) ($esVistaEliminados ? 'Los productos activos están en la lista principal' : 'Comienza registrando un nuevo producto'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- PAGINACIÓN -->
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-cube me-1"></i> 
                Total: <?= htmlspecialchars((string) ($totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> productos
            </span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/modal_registrar_producto.php'; ?>
<?php require_once __DIR__ . '/partials/modal_ver_producto.php'; ?>
<?php require_once __DIR__ . '/partials/modal_editar_producto.php'; ?>
<?php require_once __DIR__ . '/partials/modal_eliminar_producto.php'; ?>
<?php require_once __DIR__ . '/partials/modal_rapido_categoria.php'; ?>
<?php require_once __DIR__ . '/partials/modal_rapido_proveedor.php'; ?>
<?php if ($puede_editar): ?>
<div class="modal fade" id="modalReactivarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(40,167,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-undo-alt me-2" style="color: #28a745;"></i> Reactivar Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <form method="POST" action="?url=productos&type=reactivar">
                <input type="hidden" name="id_producto" id="reactivar-producto-id">
                
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px;">¿Deseas reactivar este producto?</p>
                    
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Producto a reactivar</strong>
                        <span id="reactivar-producto-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>

                    <div class="alert" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                        <i class="fas fa-check-circle me-2"></i>
                        El producto volverá a estar <strong>Activo</strong> y disponible para asignarse a notas de salida.
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

<?php require_once __DIR__ . '/partials/scripts_productos.php'; ?>



<?php $scriptsModulo = ['productos.js']; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>