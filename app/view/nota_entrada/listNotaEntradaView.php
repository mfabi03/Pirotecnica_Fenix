<?php
// app/view/nota_entrada/listNotaEntradaView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

// La vista no instancia PermisoHelper ni ConnectDB.
// Los permisos disponibles ya vienen calculados desde su controlador.

$esVistaAnuladas = !empty($_GET['ver_anuladas']);
?>



<div class="col-md-8 col-lg-12">
            
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-sign-in-alt text-gold me-2"></i> 
                    <?= htmlspecialchars((string) ($esVistaAnuladas ? 'Notas de Entrada Anuladas' : 'Lista de Notas de Entrada'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= htmlspecialchars((string) ($esVistaAnuladas ? 'Consulta el motivo de anulación de las notas' : 'Gestiona las notas de entrada de productos'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if ($puede_crear && !$esVistaAnuladas): ?>
                <a href="?url=notaentrada&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                    <i class="fas fa-plus me-1"></i> Registrar Nota de Entrada
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="notaentrada">
            <input type="hidden" name="type" value="list">
            <?php if ($esVistaAnuladas): ?><input type="hidden" name="ver_anuladas" value="1"><?php endif; ?>
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-no-search="true" data-auto-submit>
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= htmlspecialchars((string) (($por_pagina == $o) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>><?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <input type="text" name="busqueda" class="form-control form-control-sm"
                       list="listaNotasEntrada"
                       placeholder="Buscar por proveedor, encargado o ID..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaNotasEntrada">
                    <?php
                    $sugerencias = [];
                    if (!empty($notas) && is_array($notas)):
                        foreach ($notas as $n):
                            $prov = trim($n['razon_social'] ?? '');
                            $enc  = trim($n['encargado_nombre'] ?? '');
                            if ($prov !== '') $sugerencias[$prov] = 1;
                            if ($enc  !== '') $sugerencias[$enc]  = 1;
                            $sugerencias[(string)$n['id_nota_entrada']] = 1;
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
                <a href="?url=notaentrada&amp;type=list" class="btn btn-secondary flex-fill">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
                <?php if ($esVistaAnuladas): ?>
                    <a href="?url=notaentrada&amp;type=list" class="btn btn-secondary flex-fill js-filtro-listado-ajax">
                        <i class="fas fa-check-circle me-1"></i> Ver Activas
                    </a>
                <?php else: ?>
                    <a href="?url=notaentrada&amp;type=list&amp;ver_anuladas=1" class="btn btn-danger flex-fill js-filtro-listado-ajax">
                        <i class="fas fa-ban me-1"></i> Ver Anuladas
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert dark-alert-success alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <span><?= htmlspecialchars($success) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert dark-alert-danger alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span><?= htmlspecialchars($error) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-file-invoice me-1"></i> Total Notas
                        </h6>
                        <h2 style="color: #fdc304; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= htmlspecialchars((string) ((int)($resumen['total_notas'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(243,156,18,0.12); display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-dollar-sign me-1"></i> Total Compras
                        </h6>
                        <h2 style="color: #28a745; font-weight: 700; font-size: 2.2rem; margin: 0;">$<?= htmlspecialchars((string) (number_format((float)($resumen['total_compras'] ?? 0), 2)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(40,167,69,0.12); display: flex; align-items: center; justify-content: center; color: #28a745; font-size: 1.5rem;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(10, 1, 1); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-ban me-1"></i> Anuladas
                        </h6>
                        <h2 style="color: #fa0101; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= htmlspecialchars((string) ((int) ($resumen['total_anuladas'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(220,53,69,0.12); display: flex; align-items: center; justify-content: center; color: #dc3545; font-size: 1.5rem;">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-list me-2"></i> <?= htmlspecialchars((string) ($esVistaAnuladas ? 'Notas Anuladas' : 'Notas Registradas'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Fecha</th>
                        <th class="py-3">Proveedor</th>
                        <th class="py-3">Encargado</th>
                        <th class="py-3">Productos</th>
                        <th class="py-3">Total</th>
                        <th class="py-3">Estatus</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0"><?= htmlspecialchars((string) ($esVistaAnuladas ? 'No hay notas anuladas' : 'No hay notas de entrada registradas'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                                    <small><?= htmlspecialchars((string) ($esVistaAnuladas ? 'Las notas activas están en la lista principal' : 'Comienza registrando una nueva nota'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notas as $n): ?>
                            <?php $anulada = (int) ($n['eliminado'] ?? 0) === 1; ?>
                            <tr <?= htmlspecialchars((string) ($anulada ? 'style="opacity: 0.6;"' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>
                                <td class="ps-4"><?= htmlspecialchars((string) (formatoFechaVenezuela($n['fecha_ingreso'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($n['razon_social'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($n['encargado_nombre'] ?? 'Sin asignar') ?></td>
                                <td>
                                    <?php if (!empty($n['productos_lista'])): ?>
                                        <?php 
                                        $productos = explode(', ', $n['productos_lista']);
                                        $totalProd = count($productos);
                                        ?>
                                        <?php for ($i = 0; $i < min(3, $totalProd); $i++): ?>
                                            <span class="badge" style="background: #e9ecef; color: #495057; padding: 4px 10px; border-radius: 50px; font-weight: 500; font-size: 0.7rem;"><?= htmlspecialchars($productos[$i]) ?></span>
                                        <?php endfor; ?>
                                        <?php if ($totalProd > 3): ?>
                                            <span class="badge" style="background: rgba(243,156,18,0.12); color: #f39c12; padding: 4px 10px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">+<?= htmlspecialchars((string) ($totalProd - 3), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> más</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #adb5bd;">Sin productos</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold">
                                    <?php if ($anulada): ?>
                                        <del style="color: #6c757d;">$<?= htmlspecialchars((string) (number_format((float)($n['costo_total'] ?? 0), 2)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></del>
                                    <?php else: ?>
                                        $<?= htmlspecialchars((string) (number_format((float)($n['costo_total'] ?? 0), 2)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($anulada): ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-ban me-1"></i> Anulada
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if (!$anulada): ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                    data-nota-action="view" data-nota-id="<?= htmlspecialchars((string) ((int) $n['id_nota_entrada']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($puede_eliminar): ?>
                                                <button type="button" class="btn-action-circle btn-delete"
                                                        data-nota-action="cancel" data-nota-id="<?= htmlspecialchars((string) ((int) $n['id_nota_entrada']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                        data-proveedor="<?= htmlspecialchars($n['razon_social'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>"
                                                        title="Anular">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Motivo"
                                                    data-nota-motivo
                                                    data-nota-id="<?= htmlspecialchars((string) ((int) $n['id_nota_entrada']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                    data-proveedor="<?= htmlspecialchars($n['razon_social'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-rif="<?= htmlspecialchars($n['rif'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-productos="<?= htmlspecialchars($n['productos_lista'] ?? 'No se registraron productos.', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-motivo="<?= htmlspecialchars($n['motivo_anulacion'] ?? 'No se registró un motivo.', ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
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
                <i class="fas fa-file-invoice me-1"></i> 
                Total: <?= htmlspecialchars((string) ($totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> notas
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/partials/modal_ver_nota.php';
require_once __DIR__ . '/partials/modal_anular_nota.php';
?>

<div class="modal fade" id="modalMotivoAnulacionNotaEntrada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Motivo de Anulación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 24px;">
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Documento:</span>
                    <div class="fs-5 fw-bold text-dark" id="motivo-entrada-documento"></div>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Proveedor:</span>
                    <div class="fs-6 text-dark fw-semibold" id="motivo-entrada-proveedor"></div>
                    <small class="text-muted" id="motivo-entrada-rif"></small>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Productos de la nota:</span>
                    <div class="fs-6 text-dark" id="motivo-entrada-productos"></div>
                </div>
                <div class="mb-0">
                    <span class="text-muted small fw-bold text-uppercase">Motivo registrado:</span>
                    <div class="p-3 mt-1 rounded" style="background: #f8f9fa; border-left: 4px solid #dc3545; color: #333; font-style: italic;" id="motivo-entrada-texto"></div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 50px; padding: 8px 25px; font-weight: 600;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php $scriptsModulo = ['nota_entrada.js?v=' . filemtime(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'nota_entrada.js'), 'filtro_listado_ajax.js']; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>