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
                <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
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
                        <th class="py-3">PRODUCTO</th>
                        <th class="py-3">CATEGORÍA</th>
                        <th class="py-3">CANTIDAD</th>
                        <th class="py-3">FECHA</th>
                        <th class="pe-4 py-3">RESPONSABLE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= (int)$m['id_documento'] ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($m['nombre_producto']) ?></td>
                                <td>
                                    <span class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 4px 12px; border-radius: 50px; font-weight: 600;">
                                        <?= htmlspecialchars($m['categoria'] ?? 'Sin categoría') ?>
                                    </span>
                                </td>
                                <td class="text-danger fw-bold">
                                    - <?= htmlspecialchars(abs($m['cantidad'])) ?> unidades
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($m['fecha_movimiento'])) ?></td>
                                <td class="pe-4"><?= htmlspecialchars($m['usuario_activo']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 dark-empty">
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
<!-- MODAL CIERRE DE CAJA                                      -->
<!-- ========================================================= -->
<div class="modal fade" id="modalCierreCaja" tabindex="-1" aria-hidden="true" aria-labelledby="modalCierreCajaLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" id="modalCierreCajaLabel" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-sign-out-alt me-2" style="color: #f39c12;"></i> Cierre de Caja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" id="contenidoCierreCaja" style="padding: 20px 24px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;"></div>
                    <p class="mt-3 text-muted fw-bold">Calculando cierre...</p>
                </div>
            </div>

            <!-- PIE DEL MODAL: EXPORTACIONES A LA IZQUIERDA, CERRAR A LA DERECHA -->
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
                
                <!-- Botones de exportación -->
                <div>
                    <a href="#" id="btnExportarCierreCsv" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success fw-bold me-2" style="border-radius: 50px; padding: 8px 20px;">
                        <i class="fas fa-file-csv me-1"></i> Excel/CSV
                    </a>
                    <a href="#" id="btnExportarCierrePdf" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger fw-bold" style="border-radius: 50px; padding: 8px 20px;">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                </div>
                
                <!-- Botón de cierre final -->
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 50px; padding: 8px 20px; font-weight: 600;">
                        Cerrar
                    </button>
                    <button type="button" id="btnConfirmarCierreCaja" class="btn btn-dark-gold" style="border-radius: 50px; padding: 8px 22px; font-weight: 700; background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff;">
                        <i class="fas fa-lock me-1"></i> Cierre de caja
                    </button>
                    <button type="button" id="btnAceptarCierreExito" class="btn btn-success" style="display:none; border-radius: 50px; padding: 8px 22px; font-weight: 700;">
                        <i class="fas fa-check me-1"></i> Aceptar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- JS: Petición AJAX y Renderizado de la Tabla               -->
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
                    contenedor.innerHTML = `<div class="alert alert-danger mb-0">${result.mensaje || 'No se pudo guardar el cierre del día.'}</div>`;
                    btnCerrarCaja.disabled = false;
                    btnCerrarCaja.innerHTML = '<i class="fas fa-lock me-1"></i> Cierre de caja';
                    return;
                }

                contenedor.innerHTML = `
                    <div class="alert alert-success mb-0 shadow-sm border-0" style="border-radius: 14px; background: linear-gradient(135deg, #d4edda, #cfe8d4); color: #155724;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
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
                contenedor.innerHTML = '<div class="alert alert-danger mb-0">Error al guardar el cierre del día.</div>';
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
                contenedor.innerHTML = `<div class="alert alert-danger mb-0">${data.mensaje}</div>`;
                return;
            }

            const r = data.data;

            let filasDetalle = '';
            if (r.detalle && r.detalle.length > 0) {
                r.detalle.forEach(function(m) {
                    let fechaFmt = m.fecha_movimiento;
                    if (fechaFmt && fechaFmt.indexOf('-') > -1) {
                        const soloFecha = fechaFmt.split(' ')[0];
                        const partes = soloFecha.split('-');
                        fechaFmt = `${partes[2]}/${partes[1]}/${partes[0]}`;
                    }
                    
                    filasDetalle += `
                        <tr>
                            <td class="fw-bold text-dark">${m.id_documento}</td>
                            <td>${m.nombre_cliente || 'Sin cliente'}</td>
                            <td>${fechaFmt}</td>
                            <td>${m.cantidad} unidades</td>
                            <td><span class="text-success">Activa</span></td>
                        </tr>
                    `;
                });
            } else {
                filasDetalle = `<tr><td colspan="5" class="text-center text-muted py-4">No hay notas registradas hoy</td></tr>`;
            }

            contenedor.innerHTML = `
                <div class="container-fluid p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-nowrap text-muted">Número</th>
                                    <th scope="col" class="text-nowrap text-muted">Cliente</th>
                                    <th scope="col" class="text-nowrap text-muted">Fecha</th>
                                    <th scope="col" class="text-nowrap text-muted">Total</th>
                                    <th scope="col" class="text-nowrap text-muted">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filasDetalle}
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold">TOTAL DE NOTAS:</span>
                            <span class="fs-5 fw-bold text-danger ms-2">${r.total_documentos}</span>
                        </div>
                        <div>
                            <span class="text-muted small fw-bold">TOTAL UNIDADES:</span>
                            <span class="fs-6 fw-bold text-dark ms-2">${r.total_unidades}</span>
                        </div>
                    </div>
                </div>
            `;
        } catch (e) {
            contenedor.innerHTML = `<div class="alert alert-danger mb-0">Error de conexión. Verifica que la ruta cierre_caja_ajax esté registrada.</div>`;
        }
    });
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>