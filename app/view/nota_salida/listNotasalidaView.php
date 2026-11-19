<?php
// app/view/nota_salida/listNotasalidaView.php
require_once __DIR__ . '/../header.php';

// La vista no instancia PermisoHelper ni ConnectDB.
// Los permisos disponibles ya vienen calculados desde su controlador.

// ✅ Viene del controlador
$caja_cerrada = $caja_cerrada ?? false;

$esVistaAnuladas = !empty($_REQUEST['ver_anuladas']);
?>



<div class="col-md-8 col-lg-12">
            
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-sign-out-alt text-gold me-2"></i> 
                    <?= htmlspecialchars((string) ($esVistaAnuladas ? 'Notas de Salida Anuladas' : 'Lista de Notas de Salida'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= htmlspecialchars((string) ($esVistaAnuladas ? 'Notas inactivas o anuladas permanentemente' : 'Gestiona las notas de salida de productos'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if (!$esVistaAnuladas): ?>
                    <?php if ($puede_crear && !$caja_cerrada): ?>
                    <a href="?url=notasalida&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                        <i class="fas fa-plus me-1"></i> Registrar Nota de Salida
                    </a>
                    <?php elseif ($caja_cerrada): ?>
                        <button class="btn btn-secondary" disabled style="border-radius: 50px; padding: 8px 22px; opacity: 0.7;">
                            <i class="fas fa-lock me-1"></i> Caja Cerrada Hoy
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form id="formFiltroNotasSalida" method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="notasalida">
            <input type="hidden" name="type" value="list">
            <?php if ($esVistaAnuladas): ?><input type="hidden" name="ver_anuladas" value="1"><?php endif; ?>
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-no-search="true" onchange="this.form.submit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= htmlspecialchars((string) (($por_pagina == $o) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>><?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark mb-0">Buscar</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       list="listaNotasSalida"
                       placeholder="Buscar por cliente, cédula, encargado o ID..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaNotasSalida">
                    <?php if (isset($notas) && is_array($notas)): ?>
                        <?php foreach ($notas as $n): 
                            $nombreCliente = !empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : trim(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                            $nombreEncargado = trim(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? ''));
                        ?>
                            <option value="<?= htmlspecialchars($nombreCliente) ?>">
                            <option value="<?= htmlspecialchars($n['cedula'] ?? '') ?>">
                            <option value="<?= htmlspecialchars($nombreEncargado) ?>">
                            <option value="<?= htmlspecialchars((string) ($n['id_nota_salida']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
            </div>
            
            <div class="col-md-7 d-flex flex-column flex-sm-row gap-2">
                <button type="submit" class="btn btn-gold flex-fill">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
                <a href="?url=notasalida&amp;type=list" class="btn btn-secondary flex-fill">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
                <?php if ($esVistaAnuladas): ?>
                    <a href="?url=notasalida&amp;type=list" class="btn btn-secondary flex-fill js-filtro-listado-ajax">
                        <i class="fas fa-check-circle me-1"></i> Ver Activas
                    </a>
                <?php else: ?>
                    <a href="?url=notasalida&amp;type=list&amp;ver_anuladas=1" class="btn btn-danger flex-fill js-filtro-listado-ajax">
                        <i class="fas fa-ban me-1"></i> Ver Anuladas
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

    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-sign-out-alt me-1"></i> Total de Salidas
                        </h6>
                        <h2 style="color: #fdc304; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            <?= htmlspecialchars((string) ((int) ($resumen['total_notas'] ?? $totalRegistros ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(243,156,18,0.12); display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(10, 1, 1); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-ban me-1"></i> Anuladas
                        </h6>
                        <h2 style="color: #fa0101; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            <?= htmlspecialchars((string) ((int) ($resumen['total_anuladas'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </h2>
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
                <i class="fas fa-list me-2"></i> 
                <?= htmlspecialchars((string) ($esVistaAnuladas ? 'Notas Anuladas' : 'Notas Registradas'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="tablaNotas">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">FECHA</th>
                        <th class="py-3">CLIENTE</th>
                        <th class="py-3">TIPO</th>
                        <th class="py-3 text-center">PRODUCTOS</th>
                        <th class="py-3 text-center">CATEGORÍAS</th>
                        <th class="py-3 text-center">UNIDADES</th>
                        <th class="py-3">ENCARGADO</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="tbodyNotas">
                    <?php if (empty($notas)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">
                                        <?= htmlspecialchars((string) ($esVistaAnuladas ? 'No hay notas anuladas' : 'No hay notas de salida registradas'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </p>
                                    <small><?= htmlspecialchars((string) ($esVistaAnuladas ? 'Las notas activas están en la lista principal' : 'Comienza registrando una nueva nota'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notas as $n): ?>
                            <?php $anulada = ($n['eliminado'] == 1); ?>
                            <tr <?= htmlspecialchars((string) ($anulada ? 'style="opacity: 0.6;"' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>
                                <td class="ps-4"><?= htmlspecialchars((string) (formatoFechaVenezuela($n['fecha'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;">
                                        <?= htmlspecialchars(!empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : (($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''))) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?= htmlspecialchars((string) (($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? 'rgba(13,110,253,0.15)' : 'rgba(40,167,69,0.15)'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>; color: <?= htmlspecialchars((string) (($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? '#0d6efd' : '#28a745'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                        <?= htmlspecialchars($n['tipo_cliente'] ?? 'Natural') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: #1a1a2e; font-size: 1.05rem;">
                                        <?= htmlspecialchars((string) ((int)($n['total_productos'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Prod.</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: #6c757d; font-size: 1.05rem;">
                                        <?= htmlspecialchars((string) ((int)($n['total_categorias'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Cat.</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: #1a1a2e; font-size: 1.05rem;">
                                        <?= htmlspecialchars((string) ((int)($n['total_unidades'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Und.</small>
                                </td>
                                <td><?= htmlspecialchars(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? '')) ?></td>
                                
                                <td class="text-center">
                                    <?php if ($anulada): ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            Anulada
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            Activa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($anulada): ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Motivo de Anulación"
                                                    data-documento="#<?= htmlspecialchars((string) ($n['id_nota_salida']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                    data-cliente="<?= htmlspecialchars(!empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : trim(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-identificacion="<?= htmlspecialchars(!empty($n['cliente_rif']) ? 'RIF: ' . $n['cliente_rif'] : (!empty($n['cliente_cedula']) ? 'Cédula: ' . $n['cliente_cedula'] : 'N/A'), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-productos="<?= htmlspecialchars($n['productos_lista'] ?? 'No se registraron productos.', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-motivo="<?= htmlspecialchars($n['motivo_anulacion'] ?? 'No se registró un motivo.', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-usuario="<?= htmlspecialchars(($n['usuario_nombre'] ?? 'N/A') . ' ' . ($n['usuario_apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-fecha="<?= htmlspecialchars((string) (!empty($n['fecha_anulacion']) ? date('d/m/Y h:i A', strtotime($n['fecha_anulacion'])) : 'No registrada'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                    data-cantidad="<?= htmlspecialchars((string) ((int)($n['total_unidades'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                    data-nota-salida-motivo>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                    data-nota-action="view" data-nota-id="<?= htmlspecialchars((string) ((int) $n['id_nota_salida']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (!$anulada && $puede_eliminar): ?>
                                            <button type="button" class="btn-action-circle btn-delete" title="Anular Nota"
                                                    data-nota-action="cancel" data-nota-id="<?= htmlspecialchars((string) ((int) $n['id_nota_salida']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                    data-cliente="<?= htmlspecialchars(!empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : trim(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="fas fa-ban"></i>
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
                Total: <?= htmlspecialchars((string) ($totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> notas
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/partials/modal_ver_nota.php';
if ($puede_eliminar) {
    require_once __DIR__ . '/partials/modal_anular_nota.php';
}
?>

<!-- MODAL MOTIVO ANULACIÓN -->
<div class="modal fade" id="modalMotivoAnulacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Detalle de Anulación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 24px;">
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Documento:</span>
                    <div class="fs-5 fw-bold text-dark" id="modal-motivo-doc"></div>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Cliente:</span>
                    <div class="fs-6 text-dark fw-semibold" id="modal-motivo-cliente"></div>
                    <small class="text-muted" id="modal-motivo-identificacion"></small>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Productos de la nota:</span>
                    <div class="fs-6 text-dark" id="modal-motivo-productos"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <span class="text-muted small fw-bold text-uppercase">Anulada por:</span>
                        <div class="fs-6 text-dark fw-semibold" id="modal-motivo-usuario"></div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small fw-bold text-uppercase">Total de unidades:</span>
                        <div class="fs-6 text-danger fw-bold" id="modal-motivo-cantidad"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Fecha y Hora de Anulación:</span>
                    <div class="fs-6 text-dark" id="modal-motivo-fecha"></div>
                </div>
                <div class="mb-0">
                    <span class="text-muted small fw-bold text-uppercase">Motivo Registrado:</span>
                    <div class="p-3 mt-1 rounded" style="background: #f8f9fa; border-left: 4px solid #dc3545; color: #333; font-style: italic;" id="modal-motivo-texto"></div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 50px; padding: 8px 25px; font-weight: 600;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php $scriptsModulo = ['filtro_listado_ajax.js', 'notas_salida.js?v=' . filemtime(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'notas_salida.js')]; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>