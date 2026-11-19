<?php
// app/view/reporteCaja/reporteCajaView.php
$movimientos     = $movimientos ?? [];
$totalUnidades   = $totalUnidades ?? 0;
$totalDocumentos = $totalDocumentos ?? 0;

// ✅ Vienen del controlador (no consultes $db aquí)
$categorias      = $categorias ?? [];
$productosFiltro = $productosFiltro ?? [];
$fechaFiltro     = $fechaFiltro ?? '';
$cajaCerrada     = $cajaCerrada ?? false;
$estilosModulo   = ['reporteCaja.css'];

require_once dirname(__DIR__, 2) . "/view/header.php";
?>



<div class="col-md-8 col-lg-12">
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-xl-8"><h3 class="m-0 dark-title"><i class="fas fa-cash-register text-gold me-2"></i> Reporte Diario de Caja</h3><small style="color: rgba(255, 255, 255, 0.6) !important;"><i class="fas fa-history me-2"></i> <?= htmlspecialchars((string) ((int)$totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> movimientos registrados</small></div>
            <div class="col-xl-4 text-end">
                <?php if (!$cajaCerrada): ?>
                    <button type="button" id="btnAbrirCierreCaja" class="btn btn-dark-gold" data-fecha="<?= htmlspecialchars($fechaFiltro) ?>" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;"><i class="fas fa-lock me-1"></i> Realizar Cierre</button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" disabled style="border-radius: 50px; padding: 8px 22px; font-weight: 600; opacity: 0.6;"><i class="fas fa-check-circle me-1"></i> Caja Cerrada</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" id="formFiltros" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="reportecaja">
            
            <div class="col-md-8">
                <label class="form-label fw-bold small text-dark">Buscar (Cliente, Producto o Categoría)</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       list="listaGlobalReporte"
                       placeholder="Escribe para buscar en todo el turno..." 
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaGlobalReporte">
                    <?php 
                    $clientesUnicos = array_unique(array_column($movimientos, 'nombre_cliente'));
                    foreach ($clientesUnicos as $cli): if(empty($cli)) continue; ?>
                        <option value="<?= htmlspecialchars($cli) ?>">
                    <?php endforeach; ?>
                    <?php foreach ($productosFiltro as $prod): ?>
                        <option value="<?= htmlspecialchars($prod['descripcion']) ?>">
                    <?php endforeach; ?>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['nombre_categoria']) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="col-md-4">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="?url=reportecaja" class="btn btn-secondary btn-sm fw-bold px-3">
                        <i class="fas fa-times me-1"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-gold btn-sm fw-bold px-3">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="row mb-4 justify-content-center">
        <div class="col-md-5">
            <div class="card card-total-notas border-left-danger text-center p-3 shadow-sm">
                <h6 class="card-title text-muted text-uppercase fw-bold" style="letter-spacing: 0.5px;">Total de Notas de Salida</h6>
                <h2 class="card-number text-danger mb-0"><?= htmlspecialchars((string) ((int)$totalDocumentos), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                <small class="text-muted mt-2 d-block" style="font-size: 0.8rem;"><?= htmlspecialchars((string) ((int)$totalUnidades), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> unidades en total</small>
            </div>
        </div>
    </div>

    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center"><h5 class="m-0"><i class="fas fa-history me-2"></i> Movimientos del Día</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead><tr><th class="ps-4 py-3">NOTA #</th><th class="py-3">HORA</th><th class="py-3">CLIENTE</th><th class="py-3">TIPO</th><th class="py-3 text-center">PRODUCTOS</th><th class="py-3 text-center">CATEGORÍAS</th><th class="py-3 text-center">UNIDADES</th><th class="py-3">ENCARGADO</th><th class="pe-4 py-3 text-center">ACCIONES</th></tr></thead>
                <tbody>
                    <?php if ($cajaCerrada && empty($movimientos)): ?>
                        <tr><td colspan="9" class="text-center py-5 dark-empty"><div class="py-4"><i class="fas fa-lock fa-3x d-block mb-3"></i><p class="mb-0">La caja de esta fecha ya fue cerrada</p><small>No hay movimientos nuevos después del cierre para este turno.</small></div></td></tr>
                    <?php elseif (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= htmlspecialchars((string) ((int)$m['id_documento']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) (date('h:i A', strtotime($m['fecha_movimiento']))), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($m['nombre_cliente'] ?? 'N/A') ?></td>
                                <td><span class="badge" style="<?= htmlspecialchars((string) (($m['tipo_cliente'] === 'Jurídico') ? 'background: #e0f2fe; color: #0369a1;' : 'background: #dcfce7; color: #15803d;'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> padding: 4px 12px; border-radius: 50px; font-weight: 600;"><?= htmlspecialchars($m['tipo_cliente'] ?? 'Natural') ?></span></td>
                                <td class="text-center fw-bold"><?= htmlspecialchars((string) ((int)($m['total_productos'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> Prod.</td>
                                <td class="text-center fw-bold"><?= htmlspecialchars((string) ((int)($m['total_categorias'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> Cat.</td>
                                <td class="text-center fw-bold"><?= htmlspecialchars((string) ((int)($m['cantidad'] ?? 0)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> Und.</td>
                                <td><?= htmlspecialchars($m['usuario_activo'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-center"><button type="button" class="btn btn-sm btn-action-circle" style="background: #1a1a2e; color: #0d6efd; border: 1px solid #0d6efd; width: 32px; height: 32px; padding: 0; border-radius: 50%;" onclick="verDetalleCaja(<?= htmlspecialchars((string) ((int)$m['id_documento']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)"><i class="fas fa-eye"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center py-5 dark-empty"><div class="py-4"><i class="fas fa-box-open fa-3x d-block mb-3"></i><p class="mb-0">No hay movimientos registrados</p><small>Registra una nota de salida para verla aquí</small></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3 d-flex justify-content-between align-items-center"><span class="text-muted small"><i class="fas fa-history me-1"></i> Total: <?= htmlspecialchars((string) ((int)$totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> movimientos</span><?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?></div>
    </div>
</div>

<?php require_once __DIR__ . '/verDetalleCajaView.php'; ?>
<?php require_once __DIR__ . '/CierreCajaView.php'; ?>

<!-- MODAL DE RECIBO DE CIERRE EXITOSO (CON LOGO Y TABLA) -->
<div class="modal fade" id="modalReciboCierre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-check-circle me-2" style="color: #28a745;"></i> ¡Cierre Exitoso!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div class="d-flex align-items-center justify-content-center mb-4 gap-3">
                    <img src="assets/imagenes/logo.png" alt="Logo" style="width: 60px; height: 60px; object-fit: contain;">
                    <div>
                        <h4 style="color: #1a1a2e; font-weight: 800; margin-bottom: 0;">PIROTECNICA FENIX C.A.</h4>
                        <p style="color: #6c757d; font-size: 0.85rem; margin: 0;">Sistema de gestión - Arqueo de Turno</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef;">
                            <h6 style="font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px;">Datos del Turno</h6>
                            <div class="mb-2"><strong>Fecha:</strong> <span id="recibo-fecha"></span></div>
                            <div class="mb-2"><strong>Hora Apertura:</strong> <span id="recibo-hora-apertura"></span></div>
                            <div class="mb-2"><strong>Hora Cierre:</strong> <span id="recibo-hora-cierre"></span></div>
                            <div class="mb-0"><strong>Encargado:</strong> <span id="recibo-encargado"></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef;">
                            <h6 style="font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px;">Resumen</h6>
                            <div class="mb-2"><strong>Total Notas:</strong> <span id="recibo-total-notas"></span></div>
                            <div class="mb-2"><strong>Total Unidades:</strong> <span id="recibo-total-unidades"></span></div>
                            <div class="mb-2"><strong>Prods. Distintos:</strong> <span id="recibo-total-productos"></span></div>
                            <div class="mb-0"><strong>Clientes Atend.:</strong> <span id="recibo-total-clientes"></span></div>
                        </div>
                    </div>
                </div>

                <div class="card" style="border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                    <div class="card-header py-3" style="background: #1a1a2e; border: none;">
                        <h6 class="m-0" style="color: #ffffff; font-weight: 700;"><i class="fas fa-list me-2" style="color: #f39c12;"></i> Detalle de Movimientos</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0">
                            <thead><tr><th class="ps-4 py-3">NOTA #</th><th class="py-3">HORA</th><th class="py-3">CLIENTE</th><th class="py-3">TIPO</th><th class="py-3 text-center">PRODUCTOS</th><th class="py-3 text-center">CATEGORÍAS</th><th class="py-3 text-center">UNIDADES</th><th class="pe-4 py-3">ENCARGADO</th></tr></thead>
                            <tbody id="recibo-tabla-detalles"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: space-between;">
                <div>
                    <a href="#" id="btnReciboPdf" target="_blank" class="btn btn-outline-danger fw-bold me-2" style="border-radius: 50px; padding: 8px 20px;">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                    <a href="#" id="btnReciboExcel" target="_blank" class="btn btn-outline-success fw-bold" style="border-radius: 50px; padding: 8px 20px;">
                        <i class="fas fa-file-excel me-1"></i> Excel/CSV
                    </a>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.location.reload()">Aceptar</button>
            </div>
        </div>
    </div>
</div>


<?php $scriptsModulo = ['reporteCaja.js']; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>