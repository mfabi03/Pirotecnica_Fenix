<?php
// ============================================================
// Variables de la vista
// ============================================================
$movimientos     = $movimientos ?? [];
$paginaActual    = $paginaActual ?? 1;
$totalPaginas    = $totalPaginas ?? 1;
$totalRegistros  = $totalRegistros ?? 0;
$porPaginaActual = $porPaginaActual ?? 10;
$totalUnidades   = $totalUnidades ?? 0;
$totalDocumentos = $totalDocumentos ?? 0;

// Categorías para el filtro
$categorias = [];
try {
    $stmt = $db->prepare("SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$por_pagina     = $porPaginaActual;
$pagina_actual  = $paginaActual;
$totalRegistros = $totalRegistros;
$totalPaginas   = $totalPaginas;

$fechaFiltro = $_GET['fecha'] ?? date('Y-m-d');
$cajaCerrada = $cajaCerrada ?? false;

require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<!-- ========================================================= -->
<!-- CSS DE IMPRESIÓN (Solo se imprime el modal)               -->
<!-- ========================================================= -->
<style>
@media print {
    body * { visibility: hidden !important; }
    #modalCierreCaja, #modalCierreCaja * { visibility: visible !important; }
    #modalCierreCaja {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        display: block !important;
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    #modalCierreCaja .modal-dialog {
        display: block !important;
        max-width: 100% !important;
        margin: 0 !important;
    }
    #modalCierreCaja .modal-content {
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
    }
    #modalCierreCaja .modal-header,
    #modalCierreCaja .modal-footer { display: none !important; }
    .modal-backdrop { display: none !important; }
    
    .table { border-collapse: collapse !important; width: 100% !important; }
    .table th, .table td { border-bottom: 1px solid #ddd !important; padding: 8px !important; font-size: 12px !important; }
}
</style>

<div class="col-md-8 col-lg-12">
    
    <!-- ========================================== -->
    <!-- TARJETA DE TÍTULO + BOTÓN DE CIERRE        -->
    <!-- ========================================== -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-xl-8">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-cash-register text-gold me-2"></i> Reporte Diario de Caja
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important;">
                    <i class="fas fa-history me-2"></i> 
                    <?= $totalRegistros ?> movimientos registrados
                </small>
            </div>
            <div class="col-xl-4 text-end">
                <!-- ÚNICO BOTÓN EN LA CABECERA: REALIZAR CIERRE -->
                <button type="button"
                        id="btnAbrirCierreCaja"
                        class="btn btn-dark-gold"
                        data-fecha="<?= htmlspecialchars($fechaFiltro) ?>"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                    <i class="fas fa-lock me-1"></i> Realizar Cierre
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FILTROS                                    -->
    <!-- ========================================== -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" id="formFiltros" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="reportecaja">
            
            <!-- Mostrar -->
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.requestSubmit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($porPaginaActual == $o) ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fecha -->
            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark">Fecha</label>
                <input type="date" name="fecha" class="form-control form-control-sm" 
                       value="<?= htmlspecialchars($fechaFiltro) ?>">
            </div>

            <!-- Categoría -->
            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark">Categoría</label>
                <select name="id_categoria" class="form-select form-select-sm" data-no-search="true">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>" 
                            <?= (isset($_GET['id_categoria']) && $_GET['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Producto -->
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Producto</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       list="listaProductosReporte"
                       placeholder="Buscar..." 
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaProductosReporte">
                    <?php 
                    $stmtProd = $db->prepare("SELECT descripcion FROM producto WHERE eliminado = 0 ORDER BY descripcion ASC");
                    $stmtProd->execute();
                    foreach ($stmtProd->fetchAll(PDO::FETCH_ASSOC) as $prod): 
                    ?>
                        <option value="<?= htmlspecialchars($prod['descripcion']) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <!-- Botones -->
            <div class="col-md-2">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-gold btn-sm fw-bold">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="?url=reportecaja" class="btn btn-secondary btn-sm fw-bold">
                        <i class="fas fa-times me-1"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($mensaje) && !empty($mensaje)): ?>
        <?php
        $tipoMensaje = $tipo_mensaje ?? 'info';
        $mapaAlertas = [
            'success' => ['clase' => 'dark-alert-success', 'icono' => 'fa-check-circle'],
            'danger'  => ['clase' => 'dark-alert-danger',  'icono' => 'fa-exclamation-circle'],
            'error'   => ['clase' => 'dark-alert-danger',  'icono' => 'fa-exclamation-circle'],
            'warning' => ['clase' => 'dark-alert-warning', 'icono' => 'fa-exclamation-triangle'],
            'info'    => ['clase' => 'dark-alert-info',    'icono' => 'fa-info-circle'],
        ];
        $alertaActual = $mapaAlertas[$tipoMensaje] ?? $mapaAlertas['info'];
        ?>
        <div class="alert <?= $alertaActual['clase'] ?> alert-dismissible fade show shadow-sm border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas <?= $alertaActual['icono'] ?> me-3 fs-5"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- ========================================== -->
    <!-- TARJETA ESTADÍSTICA (Centrada)             -->
    <!-- ========================================== -->
    <div class="row mb-4 justify-content-center">
        <div class="col-md-5">
            <div class="card card-total-notas border-left-danger text-center p-3 shadow-sm">
                <h6 class="card-title text-muted text-uppercase fw-bold" style="letter-spacing: 0.5px;">
                    Total de Notas de Salida
                </h6>
                <h2 class="card-number text-danger mb-0"><?= (int)$totalDocumentos ?></h2>
                <small class="text-muted mt-2 d-block" style="font-size: 0.8rem;">
                    <?= (int)$totalUnidades ?> unidades en total
                </small>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TABLA DE MOVIMIENTOS                       -->
    <!-- ========================================== -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-history me-2"></i> Movimientos del Día
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">NOTA #</th>
                        <th class="py-3">FECHA</th>
                        <th class="py-3">CLIENTE</th>
                        <th class="py-3">TIPO</th>
                        <th class="py-3">PRODUCTOS</th>
                        <th class="py-3">CATEGORÍAS</th>
                        <th class="py-3 text-center">UNIDADES</th>
                        <th class="py-3">ENCARGADO</th>
                        <th class="pe-4 py-3 text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($cajaCerrada && empty($movimientos)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-lock fa-3x d-block mb-3"></i>
                                    <p class="mb-0">La caja de esta fecha ya fue cerrada</p>
                                    <small>No hay movimientos nuevos después del cierre para este turno.</small>
                                </div>
                            </td>
                        </tr>
                    <?php elseif (!empty($movimientos)): ?>
                        <?php 
                        // Agrupamos los movimientos por nota
                        $notasAgrupadas = [];
                        foreach ($movimientos as $m) {
                            $idNota = $m['id_documento'];
                            if (!isset($notasAgrupadas[$idNota])) {
                                $notasAgrupadas[$idNota] = [
                                    'id_documento' => $idNota,
                                    'fecha_movimiento' => $m['fecha_movimiento'],
                                    'usuario_activo' => $m['usuario_activo'],
                                    'cliente' => $m['nombre_cliente'] ?? 'N/A',
                                    'tipo_cliente' => $m['tipo_cliente'] ?? 'Natural',
                                    'productos' => [],
                                    'categorias' => [],
                                    'total_unidades' => 0
                                ];
                            }
                            $prodLimpio = preg_replace('/\s*\(x\d+\)/', '', $m['nombre_producto']);
                            if (!in_array($prodLimpio, $notasAgrupadas[$idNota]['productos'])) {
                                $notasAgrupadas[$idNota]['productos'][] = $prodLimpio;
                            }
                            if (!empty($m['categoria']) && !in_array($m['categoria'], $notasAgrupadas[$idNota]['categorias'])) {
                                $notasAgrupadas[$idNota]['categorias'][] = $m['categoria'];
                            }
                            $notasAgrupadas[$idNota]['total_unidades'] += abs((int)$m['cantidad']);
                        }
                        uasort($notasAgrupadas, function ($a, $b) {
                            return ((int)$b['id_documento']) <=> ((int)$a['id_documento']);
                        });
                        ?>
                        <?php foreach ($notasAgrupadas as $nota): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= (int)$nota['id_documento'] ?></td>
                                <td><?= date('d/m/Y', strtotime($nota['fecha_movimiento'])) ?></td>
                                <td><?= htmlspecialchars($nota['cliente']) ?></td>
                                <td>
                                    <?php 
                                    $badgeColor = ($nota['tipo_cliente'] === 'Jurídico') ? 'background: #e0f2fe; color: #0369a1;' : 'background: #dcfce7; color: #15803d;';
                                    ?>
                                    <span class="badge" style="<?= $badgeColor ?> padding: 4px 12px; border-radius: 50px; font-weight: 600;">
                                        <?= htmlspecialchars($nota['tipo_cliente']) ?>
                                    </span>
                                </td>
                                <td class="fw-medium" style="max-width: 250px; white-space: normal;">
                                    <?= htmlspecialchars(implode(', ', $nota['productos'])) ?>
                                </td>
                                <td class="text-muted" style="max-width: 200px; white-space: normal;">
                                    <?= htmlspecialchars(implode(', ', $nota['categorias'])) ?>
                                </td>
                                <td class="text-center fw-bold"><?= $nota['total_unidades'] ?></td>
                                <td><?= htmlspecialchars($nota['usuario_activo']) ?></td>
                                <td class="pe-4 text-center">
                                    <button type="button" class="btn btn-sm btn-action-circle"
                                            style="background: #1a1a2e; color: #0d6efd; border: 1px solid #0d6efd; box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25); transition: all 0.2s; width: 32px; height: 32px; padding: 0; border-radius: 50%;"
                                            onclick="verDetalleCaja(<?= (int)$nota['id_documento'] ?>)"
                                            title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-box-open fa-3x d-block mb-3"></i>
                                    <p class="mb-0">No hay movimientos registrados</p>
                                    <small>Registra una nota de salida para verla aquí</small>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-history me-1"></i> 
                Total: <?= $totalRegistros ?> movimientos
            </span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- INCLUIR MODALES EXTERNOS                                  -->
<!-- ========================================================= -->
<?php require_once __DIR__ . '/verDetalleCajaView.php'; ?>
<?php require_once __DIR__ . '/CierreCajaView.php'; ?>

<!-- ========================================================= -->
<!-- SCRIPT PARA FILTROS Y CIERRE DE CAJA                      -->
<!-- ========================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formFiltros');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('ajax', '1');

            fetch('?url=reportecaja', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.html) return;
                const wrap = document.createElement('div');
                wrap.innerHTML = data.html;
                const nuevaVista = wrap.querySelector('.col-md-8.col-lg-12');
                if (!nuevaVista) return;
                const contenedorActual = document.querySelector('.col-md-8.col-lg-12');
                if (contenedorActual) contenedorActual.replaceWith(nuevaVista);
            })
            .catch(() => {
                form.submit();
            });
        });
    }

    const btn = document.getElementById('btnAbrirCierreCaja');
    if (!btn) return;

    const modalEl    = document.getElementById('modalCierreCaja');
    const modal      = new bootstrap.Modal(modalEl);
    const contenedor = document.getElementById('contenidoCierreCaja');
    const fechaInput = document.querySelector('input[name="fecha"]');
    const btnCsv     = document.getElementById('btnExportarCierreCsv');
    const btnPdf     = document.getElementById('btnExportarCierrePdf');
    const btnCerrarCaja = document.getElementById('btnConfirmarCierreCaja');
    const btnAceptarCierreExito = document.getElementById('btnAceptarCierreExito');

    if (btnAceptarCierreExito) {
        btnAceptarCierreExito.addEventListener('click', function () {
            const fechaInputActiva = document.querySelector('input[name="fecha"]');
            const fechaActual = fechaInputActiva ? fechaInputActiva.value : (btn.dataset.fecha || new Date().toISOString().slice(0, 10));
            const fechaSiguiente = new Date(`${fechaActual}T00:00:00`);
            fechaSiguiente.setDate(fechaSiguiente.getDate() + 1);
            const fechaSiguienteStr = fechaSiguiente.toISOString().slice(0, 10);

            if (fechaInputActiva) {
                fechaInputActiva.value = fechaSiguienteStr;
            }

            modal.hide();
            window.location.href = `?url=reportecaja&fecha=${encodeURIComponent(fechaSiguienteStr)}`;
        });
    }

    if (btnCerrarCaja) {
        btnCerrarCaja.addEventListener('click', async function () {
            const fechaSeleccionada = fechaInput?.value || btn.dataset.fecha || new Date().toISOString().slice(0, 10);
            btnCerrarCaja.disabled = true;
            btnCerrarCaja.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...';

            try {
                const response = await fetch('?url=confirmar_cierre_caja', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams({ fecha: fechaSeleccionada })
                });

                const result = await response.json();
                if (!result.success) {
                    contenedor.innerHTML = `
                        <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0 mb-0" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-3 fs-5"></i>
                                <span>${result.mensaje || 'No se pudo guardar el cierre del día.'}</span>
                            </div>
                        </div>
                    `;
                    btnCerrarCaja.disabled = false;
                    btnCerrarCaja.innerHTML = '<i class="fas fa-lock me-1"></i> Cierre de caja';
                    return;
                }

                contenedor.innerHTML = `
                    <div class="alert dark-alert-success alert-dismissible fade show shadow-sm border-0 mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-3 fs-5"></i>
                            <div>
                                <div class="fw-bold">Cierre realizado correctamente</div>
                                <small>El historial del día quedó guardado y la caja quedó lista para el siguiente turno.</small>
                            </div>
                        </div>
                    </div>
                `;

                btnCerrarCaja.style.display = 'none';
                btnAceptarCierreExito.style.display = 'inline-block';
                btnCerrarCaja.disabled = false;
                btnCerrarCaja.innerHTML = '<i class="fas fa-lock me-1"></i> Cierre de caja';
            } catch (error) {
                contenedor.innerHTML = `
                    <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0 mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-3 fs-5"></i>
                            <span>Error al guardar el cierre del día.</span>
                        </div>
                    </div>
                `;
                btnCerrarCaja.disabled = false;
                btnCerrarCaja.innerHTML = '<i class="fas fa-lock me-1"></i> Cierre de caja';
            }
        });
    }

    function buildExportUrls(fecha) {
        const params = new URLSearchParams({ fecha: fecha });
        const categoria = new URLSearchParams(window.location.search).get('id_categoria');
        const busqueda = new URLSearchParams(window.location.search).get('busqueda');
        if (categoria) params.set('id_categoria', categoria);
        if (busqueda) params.set('busqueda', busqueda);
        return {
            csv: `?url=exportar_caja_csv&${params.toString()}`,
            pdf: `?url=exportar_caja_pdf&${params.toString()}`
        };
    }

    if (btnCsv) {
        btnCsv.addEventListener('click', function (event) {
            event.preventDefault();
            if (btnCsv.href) {
                window.open(btnCsv.href, '_blank', 'noopener,noreferrer');
            }
        });
    }

    if (btnPdf) {
        btnPdf.addEventListener('click', function (event) {
            event.preventDefault();
            if (btnPdf.href) {
                window.open(btnPdf.href, '_blank', 'noopener,noreferrer');
            }
        });
    }

    btn.addEventListener('click', async function () {
        const fechaSeleccionada = fechaInput?.value || btn.dataset.fecha || new Date().toISOString().slice(0, 10);
        btn.dataset.fecha = fechaSeleccionada;

        const exportUrls = buildExportUrls(fechaSeleccionada);
        btnCsv.href = exportUrls.csv;
        btnPdf.href = exportUrls.pdf;

        contenedor.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-3 text-muted fw-bold">Calculando cierre...</p>
            </div>`;
        modal.show();

        try {
            const resp = await fetch(`?url=cierre_caja_ajax&fecha=${encodeURIComponent(fechaSeleccionada)}`);
            const data = await resp.json();

            if (!data.success) {
                contenedor.innerHTML = `
                    <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0 mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-3 fs-5"></i>
                            <span>${data.mensaje}</span>
                        </div>
                    </div>
                `;
                return;
            }

            const r = data.data;

            if (r.detalle && r.detalle.length > 0) {
                r.detalle.sort(function(a, b) {
                    return Number(b.id_documento) - Number(a.id_documento);
                });
            }

            let filasDetalle = '';
            if (r.detalle && r.detalle.length > 0) {
                const notasAgrupadas = {};
                
                r.detalle.forEach(function(m) {
                    const idNota = m.id_documento;
                    if (!notasAgrupadas[idNota]) {
                        notasAgrupadas[idNota] = {
                            id_documento: idNota,
                            fecha_movimiento: m.fecha_movimiento,
                            usuario_activo: m.usuario_activo || 'Cajero',
                            cliente: m.nombre_cliente || 'N/A',
                            tipo_cliente: m.tipo_cliente || 'Natural',
                            productos: [],
                            categorias: [],
                            totalUnidades: 0
                        };
                    }
                    
                    let nombreLimpio = m.nombre_producto || 'Sin producto';
                    if (nombreLimpio.includes('(x')) {
                        nombreLimpio = nombreLimpio.split('(x')[0].trim();
                    }
                    if (!notasAgrupadas[idNota].productos.includes(nombreLimpio)) {
                        notasAgrupadas[idNota].productos.push(nombreLimpio);
                    }
                    
                    if (m.categoria && !notasAgrupadas[idNota].categorias.includes(m.categoria)) {
                        notasAgrupadas[idNota].categorias.push(m.categoria);
                    }
                    
                    notasAgrupadas[idNota].totalUnidades += parseInt(m.cantidad) || 0;
                });

                const notasOrdenadas = Object.values(notasAgrupadas).sort(function(a, b) {
                    return Number(b.id_documento) - Number(a.id_documento);
                });

                notasOrdenadas.forEach(function(nota) {
                    let fechaFmt = nota.fecha_movimiento;
                    if (fechaFmt && fechaFmt.indexOf('-') > -1) {
                        const soloFecha = fechaFmt.split(' ')[0];
                        const partes = soloFecha.split('-');
                        fechaFmt = `${partes[2]}/${partes[1]}/${partes[0]}`;
                    }

                    const badgeColor = (nota.tipo_cliente === 'Jurídico') ? 'background: #e0f2fe; color: #0369a1;' : 'background: #dcfce7; color: #15803d;';
                    
                    filasDetalle += `
                        <tr>
                            <td class="fw-bold text-dark">#${nota.id_documento}</td>
                            <td>${fechaFmt}</td>
                            <td>${nota.cliente}</td>
                            <td><span class="badge" style="${badgeColor} padding: 4px 12px; border-radius: 50px; font-weight: 600;">${nota.tipo_cliente}</span></td>
                            <td style="max-width: 250px; white-space: normal;">${nota.productos.join(', ')}</td>
                            <td style="max-width: 200px; white-space: normal;">${nota.categorias.join(', ')}</td>
                            <td class="text-center fw-bold">${nota.totalUnidades}</td>
                            <td>${nota.usuario_activo}</td>
                        </tr>
                    `;
                });
            } else {
                filasDetalle = `<tr><td colspan="8" class="text-center text-muted py-4">No hay notas registradas hoy</td></tr>`;
            }

            contenedor.innerHTML = `
                <div class="dark-card card shadow-sm dark-table-header border-0">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0">
                            <i class="fas fa-history me-2"></i> Movimientos del Día
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 py-3">NOTA #</th>
                                    <th class="py-3">FECHA</th>
                                    <th class="py-3">CLIENTE</th>
                                    <th class="py-3">TIPO</th>
                                    <th class="py-3">PRODUCTOS</th>
                                    <th class="py-3">CATEGORÍAS</th>
                                    <th class="py-3 text-center">UNIDADES</th>
                                    <th class="py-3">ENCARGADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filasDetalle}
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-bold">
                            <i class="fas fa-file-invoice me-1"></i> Total de notas: <span class="text-danger ms-1">${r.total_documentos}</span>
                        </span>
                        <span class="text-muted small fw-bold">
                            Total unidades: <span class="text-dark ms-1">${r.total_unidades}</span>
                        </span>
                    </div>
                </div>
            `;
        } catch (e) {
            contenedor.innerHTML = `
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0 mb-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-5"></i>
                        <span>Error de conexión. Verifica que la ruta cierre_caja_ajax esté registrada.</span>
                    </div>
                </div>
            `;
        }
    });
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>