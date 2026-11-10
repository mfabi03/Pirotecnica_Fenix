<?php
// app/view/reportes/reportesView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

$movimientos = $movimientos ?? [];
$totalRegistros = $totalRegistros ?? 0;
$porPaginaActual = $porPaginaActual ?? 10;
$totalEntradas = $totalEntradas ?? 0;
$totalSalidas = $totalSalidas ?? 0;
$totalAnuladas = $totalAnuladas ?? 0;

$categorias = [];
try { 
    $stmt = $db->prepare("SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC"); 
    $stmt->execute(); 
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {}

$fecha_inicio = $_GET['fecha_inicio'] ?? $_GET['fecha'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$paramsExport = http_build_query([
    'fecha_inicio' => $fecha_inicio, 
    'fecha_fin' => $fecha_fin, 
    'tipo_movimiento' => $_GET['tipo_movimiento'] ?? '', 
    'id_categoria' => $_GET['id_categoria'] ?? '', 
    'busqueda' => $_GET['busqueda'] ?? ''
]);
?>

<style>
.btn-action-circle { width: 36px; height: 36px; border-radius: 50%; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; padding: 0; font-size: 0.85rem; color: #ffffff; text-decoration: none; }
.btn-action-circle:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); filter: brightness(1.05); }
.btn-action-circle.btn-view { background: rgba(13,110,253,0.15); color: #0d6efd; }
.btn-action-circle.btn-view:hover { background: #0d6efd; color: #fff; }
.btn-action-circle.btn-motivo { background: #e63946 !important; color: #ffffff !important; opacity: 1 !important; box-shadow: none !important; }
.btn-action-circle.btn-motivo:hover { background: #d62839 !important; color: #ffffff !important; opacity: 1 !important; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important; filter: brightness(1.05) !important; }
tr.fila-anulada, tr.fila-anulada td, tr.fila-anulada th, tr.fila-anulada span, tr.fila-anulada div, tr.fila-anulada small, tr.fila-anulada strong, tr.fila-anulada b, tr.fila-anulada p { color: #000000 !important; opacity: 1 !important; }
tr.fila-anulada { background-color: rgba(0, 0, 0, 0.05) !important; }
tr.fila-anulada .badge { color: #000000 !important; opacity: 1 !important; }
</style>

<div class="col-md-8 col-lg-12">
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-xl-8">
                <h3 class="m-0 dark-title"><i class="fas fa-file-alt text-gold me-2"></i> Reporte de Movimientos</h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important;"><i class="fas fa-history me-2"></i> <?= $totalRegistros ?> notas registradas</small>
            </div>
            <div class="col-xl-4 text-end">
                <a href="?url=exportar_csv&<?= $paramsExport ?>" class="btn btn-gold btn-sm fw-bold me-2" style="background: linear-gradient(135deg, #f39c12, #e67e22) !important; border: none !important; color: #fff !important; box-shadow: 0 8px 20px rgba(243, 156, 18, 0.25);">
                    <i class="fas fa-file-excel me-1"></i> CSV
                </a>
                <a href="?url=exportar_pdf&<?= $paramsExport ?>" target="_blank" class="btn btn-danger btn-sm fw-bold">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="?url=reportes" id="formFiltros" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="reportes">
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.requestSubmit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($porPaginaActual == $o) ? 'selected' : '' ?>><?= $o ?></option>
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
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Tipo</label>
                <select name="tipo_movimiento" class="form-select form-select-sm" data-no-search="true">
                    <option value="">Todos</option>
                    <option value="Entrada" <?= (($_GET['tipo_movimiento'] ?? '') == 'Entrada') ? 'selected' : '' ?>>Entradas</option>
                    <option value="Salida" <?= (($_GET['tipo_movimiento'] ?? '') == 'Salida') ? 'selected' : '' ?>>Salidas</option>
                    <option value="Cierre de Caja" <?= (($_GET['tipo_movimiento'] ?? '') == 'Cierre de Caja') ? 'selected' : '' ?>>Cierres de Caja</option>
                    <option value="Anulación" <?= (($_GET['tipo_movimiento'] ?? '') == 'Anulación') ? 'selected' : '' ?>>Anulaciones</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Categoría</label>
                <select name="id_categoria" class="form-select form-select-sm" data-no-search="true">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>" <?= (($_GET['id_categoria'] ?? '') == $cat['id_categoria']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Producto</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" placeholder="Buscar..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
            </div>
            <div class="col-md-12 mt-3">
                <div class="d-flex justify-content-end gap-2">
                    <a href="?url=reportes" class="btn btn-secondary btn-sm fw-bold px-4"><i class="fas fa-eraser"></i> Limpiar</a>
                    <button type="submit" class="btn btn-gold btn-sm fw-bold px-4"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- TABLA -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0"><i class="fas fa-list me-2"></i> Historial de Notas</h5>
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
                        ?>
                            <tr class="<?= $esAnulacion ? 'fila-anulada' : '' ?>">
                                <td class="ps-4"><?= formatoFechaVenezuela($m['fecha_movimiento']) ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;">
                                        <?= htmlspecialchars($m['nombre_cliente'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>" style="padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                        <?= htmlspecialchars($tipo) ?>
                                    </span>
                                </td>
                                <td class="text-center"><span class="fw-bold" style="font-size: 1.05rem;"><?= (int)($m['total_productos'] ?? 0) ?> Prod.</span></td>
                                <td class="text-center"><span class="fw-bold" style="font-size: 1.05rem;"><?= (int)($m['total_categorias'] ?? 0) ?> Cat.</span></td>
                                <td class="text-center"><span class="fw-bold" style="font-size: 1.05rem;"><?= (int)($m['total_unidades'] ?? 0) ?> Und.</span></td>
                                <td><?= htmlspecialchars($m['usuario_activo']) ?></td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($esAnulacion): ?>
                                            <button type="button" class="btn-action-circle btn-motivo btn-ver-motivo" title="Ver Motivo"
                                                    data-documento="#<?= $m['id_documento'] ?>"
                                                    data-producto="<?= htmlspecialchars($m['nombre_cliente'] ?? 'N/A') ?>"
                                                    data-motivo="<?= htmlspecialchars($motivo) ?>">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </button>
                                        <?php elseif ($esCierre): ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Recibo de Cierre" onclick="verReciboCierre(<?= $m['id_documento'] ?>)">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-action-circle btn-view btn-ver-detalle" title="Ver Detalle"
                                                    onclick="verDetalleNota(<?= $m['id_documento'] ?>, '<?= htmlspecialchars($tipoBase) ?>')">
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
            <span class="text-muted small"><i class="fas fa-history me-1"></i> Total: <?= $totalRegistros ?> notas</span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/verDetalleView.php'; ?>
<?php require_once __DIR__ . '/MotivoAnulacionView.php'; ?>

<!-- 🔥 MODAL DE RECIBO DE CIERRE (CON LOGO Y BOTÓN IMPRIMIR) -->
<div class="modal fade" id="modalReciboCierre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-receipt me-2" style="color: #f39c12;"></i> Detalle del Cierre de Caja</h5>
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
                            <div class="mb-2"><strong>Fecha:</strong> <span id="hist-fecha"></span></div>
                            <div class="mb-2"><strong>Hora Cierre:</strong> <span id="hist-hora"></span></div>
                            <div class="mb-0"><strong>Encargado:</strong> <span id="hist-encargado"></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef;">
                            <h6 style="font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px;">Resumen</h6>
                            <div class="mb-2"><strong>Total Notas:</strong> <span id="hist-notas"></span></div>
                            <div class="mb-2"><strong>Total Unidades:</strong> <span id="hist-unidades"></span></div>
                            <div class="mb-0"><strong>Prods. Distintos:</strong> <span id="hist-productos"></span></div>
                        </div>
                    </div>
                </div>

                <div class="card" style="border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                    <div class="card-header py-3" style="background: #1a1a2e; border: none;">
                        <h6 class="m-0" style="color: #ffffff; font-weight: 700;"><i class="fas fa-list me-2" style="color: #f39c12;"></i> Detalle de Movimientos</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 py-3">NOTA #</th>
                                    <th class="py-3">HORA</th>
                                    <th class="py-3">CLIENTE</th>
                                    <th class="py-3">TIPO</th>
                                    <th class="py-3 text-center">PRODUCTOS</th>
                                    <th class="py-3 text-center">CATEGORÍAS</th>
                                    <th class="py-3 text-center">UNIDADES</th>
                                    <th class="pe-4 py-3">ENCARGADO</th>
                                </tr>
                            </thead>
                            <tbody id="hist-tabla-detalles"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: space-between;">
                <!-- 🔥 BOTÓN DE IMPRIMIR -->
                <button type="button" id="btnImprimirCierreHist" class="btn btn-danger">
                    <i class="fas fa-file-pdf me-1"></i> Imprimir este Cierre
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
            const formData = new FormData(form); 
            formData.append('ajax', '1');
            fetch('?url=reportes', { method: 'POST', body: formData })
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
            .catch(() => form.submit());
        });
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-ver-motivo');
        if (btn) {
            e.preventDefault();
            document.getElementById('modal-motivo-doc').textContent = btn.getAttribute('data-documento');
            document.getElementById('modal-motivo-prod').textContent = btn.getAttribute('data-producto');
            document.getElementById('modal-motivo-texto').textContent = btn.getAttribute('data-motivo');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMotivoAnulacion')).show();
        }
    });
});

// 🔥 FUNCIÓN PARA ABRIR EL MODAL CON DATOS REALES DEL CIERRE Y CONFIGURAR IMPRESIÓN
window.verReciboCierre = function(id) {
    const modalEl = document.getElementById('modalReciboCierre');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    // Mostrar spinner en la tabla
    document.getElementById('hist-tabla-detalles').innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-warning"></div></td></tr>';
    modal.show();

    fetch('?url=obtener_cierre_ajax&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('hist-tabla-detalles').innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger">${data.mensaje}</td></tr>`;
                return;
            }

            const c = data.cierre;
            const d = data.detalles;

            // Llenar cabecera
            document.getElementById('hist-fecha').textContent = c.fecha_cierre;
            document.getElementById('hist-hora').textContent = new Date(c.created_at).toLocaleTimeString('es-VE');
            document.getElementById('hist-encargado').textContent = c.nombre_usuario;
            document.getElementById('hist-notas').textContent = c.total_documentos;
            document.getElementById('hist-unidades').textContent = c.total_unidades;
            document.getElementById('hist-productos').textContent = c.total_productos_distintos;

            // 🔥 CONFIGURAR EL BOTÓN DE IMPRIMIR CON EL ID DEL CIERRE
            const btnImprimir = document.getElementById('btnImprimirCierreHist');
            if (btnImprimir) {
                btnImprimir.onclick = function() {
                    window.open(`?url=exportar_cierre_pdf&id_cierre=${c.id_cierre}`, '_blank');
                };
            }

            // Llenar tabla de detalles
            const tbody = document.getElementById('hist-tabla-detalles');
            tbody.innerHTML = '';

            if (d && d.length > 0) {
                d.forEach(det => {
                    let hora = det.fecha.split(' ')[1] || det.fecha;
                    const prodArray = det.productos ? det.productos.split(', ') : [];
                    const catArray = det.categorias ? det.categorias.split(', ') : [];

                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-4 fw-bold">#${det.id_documento}</td>
                            <td>${hora}</td>
                            <td>${det.nombre_cliente}</td>
                            <td><span class="badge ${det.tipo_cliente === 'Jurídico' ? 'bg-info text-dark' : 'bg-success'}">${det.tipo_cliente}</span></td>
                            <td class="text-center fw-bold">${prodArray.length}</td>
                            <td class="text-center fw-bold">${catArray.length}</td>
                            <td class="text-center fw-bold">${det.total_unidades}</td>
                            <td class="pe-4">${det.usuario_activo}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3">No hay movimientos en este cierre</td></tr>`;
            }
        })
        .catch(err => {
            document.getElementById('hist-tabla-detalles').innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger">Error de conexión</td></tr>`;
        });
};
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>