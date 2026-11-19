<?php
// app/view/reportes/reportesView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

// Variables que vienen del controlador
$movimientos     = $movimientos ?? [];
$totalEntradas   = $totalEntradas ?? 0;
$totalSalidas    = $totalSalidas ?? 0;
$totalAnuladas   = $totalAnuladas ?? 0;
$totalCierres    = $totalCierres ?? 0;

$fecha_inicio = $_GET['fecha_inicio'] ?? $_GET['fecha'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$paramsExport = http_build_query([
    'fecha_inicio'    => $fecha_inicio,
    'fecha_fin'       => $fecha_fin,
    'tipo_movimiento' => $_GET['tipo_movimiento'] ?? '',
    'busqueda'        => $_GET['busqueda'] ?? ''
]);
?>



<div class="col-md-8 col-lg-12">
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-xl-8">
                <h3 class="m-0 dark-title"><i class="fas fa-file-alt text-gold me-2"></i> Reporte de Movimientos</h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important;"><i class="fas fa-history me-2"></i> <?= htmlspecialchars((string) ((int)$totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> notas registradas</small>
            </div>
            <div class="col-xl-4 text-end">
                <a href="?url=exportar_csv&<?= htmlspecialchars((string) ($paramsExport), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="btn btn-gold btn-sm fw-bold me-2" style="background: linear-gradient(135deg, #f39c12, #e67e22) !important; border: none !important; color: #fff !important; box-shadow: 0 8px 20px rgba(243, 156, 18, 0.25);">
                    <i class="fas fa-file-excel me-1"></i> CSV
                </a>
                <a href="?url=exportar_pdf&<?= htmlspecialchars((string) ($paramsExport), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" target="_blank" class="btn btn-danger btn-sm fw-bold">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="?url=reportes" id="formFiltros" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="reportes">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.requestSubmit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= htmlspecialchars((string) (($porPaginaActual == $o) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>><?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($fecha_inicio) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?= htmlspecialchars($fecha_fin) ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark">Tipo</label>
                <select name="tipo_movimiento" class="form-select form-select-sm" data-no-search="true">
                    <option value="">Todos</option>
                    <option value="Entrada" <?= htmlspecialchars((string) ((($_GET['tipo_movimiento'] ?? '') == 'Entrada') ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>Entradas</option>
                    <option value="Salida" <?= htmlspecialchars((string) ((($_GET['tipo_movimiento'] ?? '') == 'Salida') ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>Salidas</option>
                    <option value="Cierre de Caja" <?= htmlspecialchars((string) ((($_GET['tipo_movimiento'] ?? '') == 'Cierre de Caja') ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>Cierres de Caja</option>
                    <option value="Anulación" <?= htmlspecialchars((string) ((($_GET['tipo_movimiento'] ?? '') == 'Anulación') ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>Todas las Anulaciones</option>
                    <option value="Anulación Entrada" <?= htmlspecialchars((string) ((($_GET['tipo_movimiento'] ?? '') == 'Anulación Entrada') ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>Anuladas de Entrada</option>
                    <option value="Anulación Salida" <?= htmlspecialchars((string) ((($_GET['tipo_movimiento'] ?? '') == 'Anulación Salida') ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>Anuladas de Salida</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark">Buscar</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       placeholder="Ej: Cohete, Juan" 
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
            </div>

            <div class="col-md-12 mt-3">
                <div class="d-flex justify-content-end gap-2">
                    <a href="?url=reportes" class="btn btn-secondary btn-sm fw-bold px-4"><i class="fas fa-eraser"></i> Limpiar</a>
                    <button type="submit" class="btn btn-gold btn-sm fw-bold px-4"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: #000; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-sign-in-alt me-1"></i> Entradas
                        </h6>
                        <h2 style="color: #28a745; font-weight: 700; font-size: 2.2rem; margin: 0;">+ <?= htmlspecialchars((string) ((int)$totalEntradas), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(40,167,69,0.12); display: flex; align-items: center; justify-content: center; color: #28a745; font-size: 1.5rem;">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: #000; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-sign-out-alt me-1"></i> Salidas
                        </h6>
                        <h2 style="color: #dc3545; font-weight: 700; font-size: 2.2rem; margin: 0;">- <?= htmlspecialchars((string) ((int)$totalSalidas), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(220,53,69,0.12); display: flex; align-items: center; justify-content: center; color: #dc3545; font-size: 1.5rem;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: #000; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-ban me-1"></i> Anuladas
                        </h6>
                        <h2 style="color: #d97706; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= htmlspecialchars((string) ((int)$totalAnuladas), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(217, 119, 6, 0.15); display: flex; align-items: center; justify-content: center; color: #d97706; font-size: 1.5rem;">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: #000; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-cash-register me-1"></i> Cierres
                        </h6>
                        <h2 style="color: #0d6efd; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= htmlspecialchars((string) ((int)$totalCierres), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(13,110,253,0.15); display: flex; align-items: center; justify-content: center; color: #0d6efd; font-size: 1.5rem;">
                        <i class="fas fa-cash-register"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0"><i class="fas fa-list me-2"></i> Historial de Movimientos</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">FECHA</th>
                        <th class="py-3">CLIENTE / PROVEEDOR</th>
                        <th class="py-3">TIPO</th>
                        <th class="py-3 text-center">PRODUCTOS</th>
                        <th class="py-3 text-center">CATEGORÍAS</th>
                        <th class="py-3 text-center">UNIDADES</th>
                        <th class="py-3">ENCARGADO</th>
                        <th class="pe-4 py-3 text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $m): 
                            $tipo = $m['tipo_movimiento'] ?? '';
                            $esAnulacion = (strpos($tipo, 'Anulación') === 0 || stripos($tipo, 'anulad') !== false);
                            $esCierre = ($tipo === 'Cierre de Caja');
                            $tipoBase = $tipo;
                            if ($esAnulacion) { 
                                if (stripos($tipo, 'Entrada') !== false) $tipoBase = 'Entrada'; 
                                elseif (stripos($tipo, 'Salida') !== false) $tipoBase = 'Salida'; 
                            }
                            $esEntrada = ($tipoBase === 'Entrada');
                            if ($esCierre) { 
                                $badgeClass = 'bg-primary'; 
                            } elseif ($esAnulacion) { 
                                $badgeClass = 'bg-secondary'; 
                            } else { 
                                $badgeClass = $esEntrada ? 'bg-success' : 'bg-danger'; 
                            }
                            $motivo = $m['motivo_anulacion'] ?? 'No se registró un motivo.';
                            
                            $prodCount = $m['total_productos'] ?? 0;
                            $catCount  = $m['total_categorias'] ?? 0;
                        ?>
                            <tr class="<?= htmlspecialchars((string) ($esAnulacion ? 'fila-anulada' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                <td class="ps-4"><?= htmlspecialchars((string) (formatoFechaVenezuela($m['fecha_movimiento'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;">
                                        <?= htmlspecialchars($m['nombre_cliente'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= htmlspecialchars((string) ($badgeClass), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" style="padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                        <?= htmlspecialchars($tipo) ?>
                                    </span>
                                </td>
                                <td class="text-center"><span class="fw-bold" style="font-size: 1.05rem;"><?= htmlspecialchars((string) ((int)$prodCount), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> Prod.</span></td>
                                <td class="text-center"><span class="fw-bold" style="font-size: 1.05rem;"><?= htmlspecialchars((string) ((int)$catCount), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> Cat.</span></td>
                                <td class="text-center"><span class="fw-bold" style="font-size: 1.05rem;"><?= htmlspecialchars((string) ((int)($m['total_unidades'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> Und.</span></td>
                                <td><?= htmlspecialchars($m['usuario_activo'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($esAnulacion): ?>
                                            <button type="button" class="btn-action-circle btn-motivo btn-ver-motivo" title="Ver Motivo"
                                                    data-documento="#<?= htmlspecialchars((string) ($m['id_documento']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                    data-producto="<?= htmlspecialchars($m['nombre_cliente'] ?? 'N/A') ?>"
                                                    data-motivo="<?= htmlspecialchars($motivo) ?>">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </button>
                                        <?php elseif ($esCierre): ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Recibo de Cierre" onclick="verReciboCierre(<?= htmlspecialchars((string) ($m['id_documento']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-action-circle btn-view btn-ver-detalle" title="Ver Detalle"
                                                    onclick="verDetalleNota(<?= htmlspecialchars((string) ($m['id_documento']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>, '<?= htmlspecialchars($tipoBase) ?>')">
                                                <i class="fas fa-eye"></i>
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
                                    <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">No hay movimientos registrados</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small"><i class="fas fa-history me-1"></i> Total: <?= htmlspecialchars((string) ((int)$totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> notas</span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/verDetalleView.php'; ?>
<?php require_once __DIR__ . '/MotivoAnulacionView.php'; ?>
<?php require_once __DIR__ . '/ReciboCierreView.php'; ?>

<?php $scriptsModulo = ['reportesjquery.js', 'verDetalleNota.jquery.js', 'reciboCierre.js']; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>