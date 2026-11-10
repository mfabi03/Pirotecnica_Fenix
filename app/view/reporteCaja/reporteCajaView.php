<?php
$movimientos     = $movimientos ?? [];
$paginaActual    = $paginaActual ?? 1;
$totalPaginas    = $totalPaginas ?? 1;
$totalRegistros  = $totalRegistros ?? 0;
$porPaginaActual = $porPaginaActual ?? 10;
$totalUnidades   = $totalUnidades ?? 0;
$totalDocumentos = $totalDocumentos ?? 0;

$categorias = [];
try { $stmt = $db->prepare("SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC"); $stmt->execute(); $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}

$fechaFiltro = $fechaFiltro ?? ''; 
$cajaCerrada = $cajaCerrada ?? false;

require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<style>
@media print {
    body * { visibility: hidden !important; }
    #modalCierreCaja, #modalCierreCaja * { visibility: visible !important; }
    #modalCierreCaja { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; display: block !important; background: #fff !important; margin: 0 !important; padding: 0 !important; }
    #modalCierreCaja .modal-dialog { display: block !important; max-width: 100% !important; margin: 0 !important; }
    #modalCierreCaja .modal-content { border: none !important; box-shadow: none !important; background: #fff !important; }
    #modalCierreCaja .modal-header, #modalCierreCaja .modal-footer { display: none !important; }
    .modal-backdrop { display: none !important; }
    .table { border-collapse: collapse !important; width: 100% !important; }
    .table th, .table td { border-bottom: 1px solid #ddd !important; padding: 8px !important; font-size: 12px !important; }
}
</style>

<div class="col-md-8 col-lg-12">
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-xl-8"><h3 class="m-0 dark-title"><i class="fas fa-cash-register text-gold me-2"></i> Reporte Diario de Caja</h3><small style="color: rgba(255, 255, 255, 0.6) !important;"><i class="fas fa-history me-2"></i> <?= $totalRegistros ?> movimientos registrados</small></div>
            <div class="col-xl-4 text-end">
                <?php if (!$cajaCerrada): ?>
                    <button type="button" id="btnAbrirCierreCaja" class="btn btn-dark-gold" data-fecha="<?= htmlspecialchars($fechaFiltro) ?>" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;"><i class="fas fa-lock me-1"></i> Realizar Cierre</button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" disabled style="border-radius: 50px; padding: 8px 22px; font-weight: 600; opacity: 0.6;"><i class="fas fa-check-circle me-1"></i> Caja Cerrada</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTRO GLOBAL (SIN FECHA) -->
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
                    <?php 
                    $stmtProd = $db->prepare("SELECT descripcion FROM producto WHERE eliminado = 0 ORDER BY descripcion ASC");
                    $stmtProd->execute();
                    foreach ($stmtProd->fetchAll(PDO::FETCH_ASSOC) as $prod): ?>
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
                <h2 class="card-number text-danger mb-0"><?= (int)$totalDocumentos ?></h2>
                <small class="text-muted mt-2 d-block" style="font-size: 0.8rem;"><?= (int)$totalUnidades ?> unidades en total</small>
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
                                <td class="ps-4 fw-bold">#<?= (int)$m['id_documento'] ?></td>
                                <td><?= date('h:i A', strtotime($m['fecha_movimiento'])) ?></td>
                                <td><?= htmlspecialchars($m['nombre_cliente'] ?? 'N/A') ?></td>
                                <td><span class="badge" style="<?= ($m['tipo_cliente'] === 'Jurídico') ? 'background: #e0f2fe; color: #0369a1;' : 'background: #dcfce7; color: #15803d;' ?> padding: 4px 12px; border-radius: 50px; font-weight: 600;"><?= htmlspecialchars($m['tipo_cliente'] ?? 'Natural') ?></span></td>
                                <td class="text-center fw-bold"><?= (int)($m['total_productos'] ?? 0) ?> Prod.</td>
                                <td class="text-center fw-bold"><?= (int)($m['total_categorias'] ?? 0) ?> Cat.</td>
                                <td class="text-center fw-bold"><?= (int)($m['cantidad'] ?? 0) ?> Und.</td>
                                <td><?= htmlspecialchars($m['usuario_activo'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-center"><button type="button" class="btn btn-sm btn-action-circle" style="background: #1a1a2e; color: #0d6efd; border: 1px solid #0d6efd; width: 32px; height: 32px; padding: 0; border-radius: 50%;" onclick="verDetalleCaja(<?= (int)$m['id_documento'] ?>)"><i class="fas fa-eye"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center py-5 dark-empty"><div class="py-4"><i class="fas fa-box-open fa-3x d-block mb-3"></i><p class="mb-0">No hay movimientos registrados</p><small>Registra una nota de salida para verla aquí</small></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer py-3 d-flex justify-content-between align-items-center"><span class="text-muted small"><i class="fas fa-history me-1"></i> Total: <?= $totalRegistros ?> movimientos</span><?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?></div>
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
                <button type="button" class="btn btn-danger" onclick="imprimirReciboCierre()"><i class="fas fa-file-pdf me-1"></i> Imprimir</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.location.reload()">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formFiltros');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form); formData.append('ajax', '1');
            fetch('?url=reportecaja', { method: 'POST', body: formData }).then(r => r.json()).then(data => {
                if (!data.success || !data.html) return;
                const wrap = document.createElement('div'); wrap.innerHTML = data.html;
                const nuevaVista = wrap.querySelector('.col-md-8.col-lg-12'); if (!nuevaVista) return;
                const contenedorActual = document.querySelector('.col-md-8.col-lg-12'); if (contenedorActual) contenedorActual.replaceWith(nuevaVista);
            }).catch(() => form.submit());
        });
    }

    const btn = document.getElementById('btnAbrirCierreCaja');
    if (!btn) return;

    const modalEl = document.getElementById('modalCierreCaja');
    const modal = new bootstrap.Modal(modalEl);
    const contenedor = document.getElementById('contenidoCierreCaja');
    
    // 🔥 SOLUCIÓN: Si el input de fecha no existe, usamos un objeto falso con la fecha de hoy
    const fechaInput = document.querySelector('input[name="fecha"]') || { value: new Date().toISOString().slice(0, 10) };
    
    const btnCsv = document.getElementById('btnExportarCierreCsv');
    const btnPdf = document.getElementById('btnExportarCierrePdf');
    const btnCerrarCaja = document.getElementById('btnConfirmarCierreCaja');
    const btnAceptarCierreExito = document.getElementById('btnAceptarCierreExito');

    if (btnAceptarCierreExito) {
        btnAceptarCierreExito.addEventListener('click', function () {
            modal.hide();
            window.location.href = `?url=reportecaja`;
        });
    }

    if (btnCerrarCaja) {
        btnCerrarCaja.addEventListener('click', async function () {
            const fechaSeleccionada = fechaInput.value;
            btnCerrarCaja.disabled = true;
            btnCerrarCaja.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            try {
                const response = await fetch('?url=confirmar_cierre_caja', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: new URLSearchParams({ fecha: fechaSeleccionada }) });
                const result = await response.json();
                
                if (!result.success) {
                    contenedor.innerHTML = `<div class="alert dark-alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>${result.mensaje}</div>`;
                    btnCerrarCaja.disabled = false;
                    btnCerrarCaja.innerHTML = '<i class="fas fa-lock me-1"></i> Cierre de caja';
                    return;
                }

                modal.hide();

                // 🔥 LLENAR EL MODAL DE RECIBO
                document.getElementById('recibo-fecha').textContent = result.data.fecha;
                document.getElementById('recibo-hora-apertura').textContent = result.data.hora_apertura || '—';
                document.getElementById('recibo-hora-cierre').textContent = new Date().toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit' });
                document.getElementById('recibo-encargado').textContent = result.data.encargado;
                document.getElementById('recibo-total-notas').textContent = result.data.total_documentos;
                document.getElementById('recibo-total-unidades').textContent = result.data.total_unidades;
                document.getElementById('recibo-total-productos').textContent = result.data.total_productos;
                document.getElementById('recibo-total-clientes').textContent = result.data.total_clientes;

                // 🔥 LLENAR LA TABLA DE DETALLES DEL RECIBO
                const tbody = document.getElementById('recibo-tabla-detalles');
                tbody.innerHTML = '';
                
                if (result.data.detalle && result.data.detalle.length > 0) {
                    const notasAgrupadas = {};
                    result.data.detalle.forEach(m => {
                        const id = m.id_documento;
                        if (!notasAgrupadas[id]) {
                            notasAgrupadas[id] = { id_documento: id, fecha_movimiento: m.fecha_movimiento, nombre_cliente: m.nombre_cliente, tipo_cliente: m.tipo_cliente, usuario_activo: m.usuario_activo, productos: [], categorias: [], totalUnidades: 0 };
                        }
                        let prodLimpio = m.nombre_producto.split('(x')[0].trim();
                        if (!notasAgrupadas[id].productos.includes(prodLimpio)) notasAgrupadas[id].productos.push(prodLimpio);
                        if (m.categoria && !notasAgrupadas[id].categorias.includes(m.categoria)) notasAgrupadas[id].categorias.push(m.categoria);
                        notasAgrupadas[id].totalUnidades += parseInt(m.cantidad) || 0;
                    });

                    Object.values(notasAgrupadas).forEach(nota => {
                        let hora = nota.fecha_movimiento.split(' ')[1] || nota.fecha_movimiento;
                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-4 fw-bold">#${nota.id_documento}</td>
                                <td>${hora}</td>
                                <td>${nota.nombre_cliente}</td>
                                <td><span class="badge ${nota.tipo_cliente === 'Jurídico' ? 'bg-info text-dark' : 'bg-success'}">${nota.tipo_cliente}</span></td>
                                <td class="text-center fw-bold">${nota.productos.length}</td>
                                <td class="text-center fw-bold">${nota.categorias.length}</td>
                                <td class="text-center fw-bold">${nota.totalUnidades}</td>
                                <td class="pe-4">${nota.usuario_activo || 'N/A'}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3">No hay movimientos</td></tr>`;
                }

                new bootstrap.Modal(document.getElementById('modalReciboCierre')).show();

            } catch (error) {
                contenedor.innerHTML = `<div class="alert dark-alert-danger mb-0">Error de conexión.</div>`;
                btnCerrarCaja.disabled = false;
                btnCerrarCaja.innerHTML = '<i class="fas fa-lock me-1"></i> Cierre de caja';
            }
        });
    }

    function buildExportUrls(fecha) {
        const params = new URLSearchParams(); if (fecha) params.set('fecha', fecha);
        const cat = new URLSearchParams(window.location.search).get('id_categoria'); if (cat) params.set('id_categoria', cat);
        const bus = new URLSearchParams(window.location.search).get('busqueda'); if (bus) params.set('busqueda', bus);
        return { csv: `?url=exportar_caja_csv&${params.toString()}`, pdf: `?url=exportar_caja_pdf&${params.toString()}` };
    }

    if (btnCsv) btnCsv.addEventListener('click', e => { e.preventDefault(); if (btnCsv.href) window.open(btnCsv.href, '_blank'); });
    if (btnPdf) btnPdf.addEventListener('click', e => { e.preventDefault(); if (btnPdf.href) window.open(btnPdf.href, '_blank'); });

    btn.addEventListener('click', async function () {
        const fechaSeleccionada = fechaInput.value;
        btn.dataset.fecha = fechaSeleccionada;
        const exportUrls = buildExportUrls(fechaSeleccionada);
        btnCsv.href = exportUrls.csv; btnPdf.href = exportUrls.pdf;

        contenedor.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-warning" style="width: 3rem; height: 3rem;"></div><p class="mt-3 text-muted fw-bold">Calculando cierre...</p></div>`;
        modal.show();

        try {
            const resp = await fetch(`?url=cierre_caja_ajax&fecha=${encodeURIComponent(fechaSeleccionada)}`);
            const data = await resp.json();
            if (!data.success) { contenedor.innerHTML = `<div class="alert dark-alert-danger mb-0">${data.mensaje}</div>`; return; }

            const r = data.data;
            let filasDetalle = '';
            if (r.detalle && r.detalle.length > 0) {
                const notasAgrupadas = {};
                r.detalle.forEach(function(m) {
                    const idNota = m.id_documento;
                    if (!notasAgrupadas[idNota]) { notasAgrupadas[idNota] = { id_documento: idNota, fecha_movimiento: m.fecha_movimiento, usuario_activo: m.usuario_activo || 'Cajero', cliente: m.nombre_cliente || 'N/A', tipo_cliente: m.tipo_cliente || 'Natural', productos: [], categorias: [], totalUnidades: 0 }; }
                    let nombreLimpio = m.nombre_producto || 'Sin producto'; if (nombreLimpio.includes('(x')) { nombreLimpio = nombreLimpio.split('(x')[0].trim(); }
                    if (!notasAgrupadas[idNota].productos.includes(nombreLimpio)) { notasAgrupadas[idNota].productos.push(nombreLimpio); }
                    if (m.categoria && !notasAgrupadas[idNota].categorias.includes(m.categoria)) { notasAgrupadas[idNota].categorias.push(m.categoria); }
                    notasAgrupadas[idNota].totalUnidades += parseInt(m.cantidad) || 0;
                });
                const notasOrdenadas = Object.values(notasAgrupadas).sort((a, b) => Number(b.id_documento) - Number(a.id_documento));
                notasOrdenadas.forEach(nota => {
                    let fechaFmt = nota.fecha_movimiento;
                    if (fechaFmt && fechaFmt.indexOf('-') > -1) { const soloFecha = fechaFmt.split(' ')[0]; const partes = soloFecha.split('-'); fechaFmt = `${partes[2]}/${partes[1]}/${partes[0]}`; }
                    const badgeColor = (nota.tipo_cliente === 'Jurídico') ? 'background: #e0f2fe; color: #0369a1;' : 'background: #dcfce7; color: #15803d;';
                    filasDetalle += `<tr><td class="fw-bold text-dark">#${nota.id_documento}</td><td>${fechaFmt}</td><td>${nota.cliente}</td><td><span class="badge" style="${badgeColor} padding: 4px 12px; border-radius: 50px; font-weight: 600;">${nota.tipo_cliente}</span></td><td style="max-width: 250px; white-space: normal;">${nota.productos.join(', ')}</td><td style="max-width: 200px; white-space: normal;">${nota.categorias.join(', ')}</td><td class="text-center fw-bold">${nota.totalUnidades}</td><td>${nota.usuario_activo}</td></tr>`;
                });
            } else { filasDetalle = `<tr><td colspan="8" class="text-center text-muted py-4">No hay notas pendientes por cerrar</td></tr>`; }

            contenedor.innerHTML = `
                <div class="dark-card card shadow-sm dark-table-header border-0">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center"><h5 class="m-0"><i class="fas fa-history me-2"></i> Movimientos del Día</h5></div>
                    <div class="table-responsive"><table class="table table-hover align-middle m-0"><thead><tr><th class="ps-4 py-3">NOTA #</th><th class="py-3">FECHA</th><th class="py-3">CLIENTE</th><th class="py-3">TIPO</th><th class="py-3">PRODUCTOS</th><th class="py-3">CATEGORÍAS</th><th class="py-3 text-center">UNIDADES</th><th class="py-3">ENCARGADO</th></tr></thead><tbody>${filasDetalle}</tbody></table></div>
                    <div class="card-footer py-3 d-flex justify-content-between align-items-center"><span class="text-muted small fw-bold"><i class="fas fa-file-invoice me-1"></i> Total de notas: <span class="text-danger ms-1">${r.total_documentos}</span></span><span class="text-muted small fw-bold">Total unidades: <span class="text-dark ms-1">${r.total_unidades}</span></span></div>
                </div>
            `;
        } catch (e) { contenedor.innerHTML = `<div class="alert dark-alert-danger mb-0">Error de conexión.</div>`; }
    });
});

function imprimirReciboCierre() {
    const fecha = (document.querySelector('input[name="fecha"]') || { value: new Date().toISOString().slice(0, 10) }).value;
    window.open(`?url=exportar_caja_pdf&fecha=${fecha}`, '_blank');
}
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>