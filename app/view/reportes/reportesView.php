<?php
// app/view/reportes/reportesView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

$movimientos = $movimientos ?? [];
$totalRegistros = $totalRegistros ?? 0;
$porPaginaActual = $porPaginaActual ?? 10;
$totalEntradas = $totalEntradas ?? 0;
$totalSalidas = $totalSalidas ?? 0;
$totalAnuladas = $totalAnuladas ?? 0;
$totalCierres = $totalCierres ?? 0; // 🔥 Agregamos la variable para las tarjetas

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
            
            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark">Tipo</label>
                <select name="tipo_movimiento" class="form-select form-select-sm" data-no-search="true">
                    <option value="">Todos</option>
                    <option value="Entrada" <?= (($_GET['tipo_movimiento'] ?? '') == 'Entrada') ? 'selected' : '' ?>>Entradas</option>
                    <option value="Salida" <?= (($_GET['tipo_movimiento'] ?? '') == 'Salida') ? 'selected' : '' ?>>Salidas</option>
                    <option value="Cierre de Caja" <?= (($_GET['tipo_movimiento'] ?? '') == 'Cierre de Caja') ? 'selected' : '' ?>>Cierres de Caja</option>
                    <option value="Anulación" <?= (($_GET['tipo_movimiento'] ?? '') == 'Anulación') ? 'selected' : '' ?>>Todas las Anulaciones</option>
                    <option value="Anulación Entrada" <?= (($_GET['tipo_movimiento'] ?? '') == 'Anulación Entrada') ? 'selected' : '' ?>>Anuladas de Entrada</option>
                    <option value="Anulación Salida" <?= (($_GET['tipo_movimiento'] ?? '') == 'Anulación Salida') ? 'selected' : '' ?>>Anuladas de Salida</option>
                </select>
            </div>

            <!-- 🔥 CAMPO DE BÚSQUEDA ACTUALIZADO -->
            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark">Buscar (Prod, Cat, Cliente, Prov)</label>
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

    <!-- 🔥 ESTADÍSTICAS (4 TARJETAS) -->
    <div class="row mb-4 g-3">
        <!-- Entradas -->
        <div class="col-md-3">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-sign-in-alt me-1"></i> Entradas
                        </h6>
                        <h2 style="color: #28a745; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            + <?= (int) $totalEntradas ?>
                        </h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(40,167,69,0.12); display: flex; align-items: center; justify-content: center; color: #28a745; font-size: 1.5rem;">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Salidas -->
        <div class="col-md-3">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-sign-out-alt me-1"></i> Salidas
                        </h6>
                        <h2 style="color: #dc3545; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            - <?= (int) $totalSalidas ?>
                        </h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(220,53,69,0.12); display: flex; align-items: center; justify-content: center; color: #dc3545; font-size: 1.5rem;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Anuladas -->
        <div class="col-md-3">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-ban me-1"></i> Anuladas
                        </h6>
                        <h2 style="color: #d97706; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            <?= (int) $totalAnuladas ?>
                        </h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(217, 119, 6, 0.15); display: flex; align-items: center; justify-content: center; color: #d97706; font-size: 1.5rem;">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cierres de Caja -->
        <div class="col-md-3">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-cash-register me-1"></i> Cierres
                        </h6>
                        <h2 style="color: #0d6efd; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            <?= (int) $totalCierres ?>
                        </h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(13,110,253,0.15); display: flex; align-items: center; justify-content: center; color: #0d6efd; font-size: 1.5rem;">
                        <i class="fas fa-cash-register"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
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
                            
                            // 🔥 LÓGICA MEJORADA: Si es cierre, calculamos desde la BD en vivo
                            if ($esCierre) {
                                $prodCount = $m['total_productos_distintos'] ?? $m['total_productos'] ?? 0;
                                $catCount = $m['total_categorias'] ?? 0;
                                
                                // Si da 0, hacemos la consulta a la base de datos en vivo
                                if ($catCount == 0 || $prodCount == 0) {
                                    try {
                                        $fechaCierre = date('Y-m-d', strtotime($m['fecha_movimiento']));
                                        $idUsuarioCierre = $m['id_usuario_cierre'] ?? 0; // 🔥 Capturamos el ID del cajero
                                        
                                        $sqlCheck = "SELECT 
                                                        COUNT(DISTINCT ds.id_producto) AS prods,
                                                        COUNT(DISTINCT p.id_categoria) AS cats
                                                     FROM nota_de_salida ns
                                                     INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
                                                     INNER JOIN producto p ON ds.id_producto = p.id_producto
                                                     WHERE DATE(ns.fecha) = :fecha 
                                                       AND ns.id_usuario = :id_usuario"; // 🔥 Filtramos por cajero
                                        
                                        $stmtCheck = $db->prepare($sqlCheck);
                                        $stmtCheck->execute([
                                            ':fecha' => $fechaCierre,
                                            ':id_usuario' => $idUsuarioCierre
                                        ]);
                                        $res = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                                        
                                        if ($res) {
                                            $prodCount = $res['prods'] > 0 ? $res['prods'] : $prodCount;
                                            $catCount = $res['cats'] > 0 ? $res['cats'] : $catCount;
                                        }
                                    } catch (Exception $e) {}
                                }
                            } else {
                                $prodCount = $m['total_productos'] ?? 0;
                                $catCount  = $m['total_categorias'] ?? 0;
                            }
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
                                <td class="text-center"><span class="fw-bold" style="font-size: 1.05rem;"><?= (int)$prodCount ?> Prod.</span></td>
                                <td class="text-center"><span class="fw-bold" style="font-size: 1.05rem;"><?= (int)$catCount ?> Cat.</span></td>
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

<!-- 🔥 AQUÍ INCLUIMOS LOS MODALES -->
<?php require_once __DIR__ . '/verDetalleView.php'; ?>
<?php require_once __DIR__ . '/MotivoAnulacionView.php'; ?>
<?php require_once __DIR__ . '/ReciboCierreView.php'; ?> <!-- El nuevo archivo que creaste -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // 1. FILTRO AJAX
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

    // 2. VER MOTIVO
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

    // 3. VER DETALLE NOTA
    window.verDetalleNota = function (id, tipo) {
        const $cargando = document.getElementById('ver-nota-cargando');
        const $error = document.getElementById('ver-nota-error');
        const $contenido = document.getElementById('ver-nota-contenido');

        $cargando.style.display = 'block';
        $error.style.display = 'none';
        $contenido.style.display = 'none';
        document.getElementById('ver-nota-id').textContent = '#' + id;

        const modalEl = document.getElementById('modalVerNota');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        const esEntrada = (
            tipo === 'Entrada' ||
            tipo === 'Anulación Entrada' ||
            (typeof tipo === 'string' && tipo.indexOf('Entrada') !== -1)
        );

        const columnasDinero = document.querySelectorAll('.col-dinero');
        columnasDinero.forEach(el => {
            if (esEntrada) el.classList.remove('d-none');
            else el.classList.add('d-none');
        });

        document.getElementById('ver-nota-label-cliente').textContent = esEntrada ? 'PROVEEDOR' : 'CLIENTE';
        document.getElementById('ver-nota-th-descripcion').textContent = esEntrada ? 'Descripción' : 'Categoría';

        const $btnImprimir = document.getElementById('btnImprimirNotaIndividual');
        if ($btnImprimir) {
            const urlPdf = esEntrada
                ? '?url=exportar_pdf&tipo=entrada&id=' + id
                : '?url=exportar_pdf&tipo=salida&id=' + id;
            $btnImprimir.setAttribute('href', urlPdf);
        }

        // Petición GET para el controlador
        const url = esEntrada 
            ? `?url=notaentrada&type=show_json&id=${id}` 
            : `?url=notasalida&type=detalle_json&id=${id}`;

        fetch(url, { method: 'GET' })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(text.substring(0, 500)); 
            }
        })
        .then(data => {
            $cargando.style.display = 'none';

            if (!data || !data.success || !data.nota) {
                $error.style.display = 'block';
                document.getElementById('ver-nota-error-msg').textContent = (data && data.error) || 'Error al cargar la nota';
                return;
            }

            const nota = data.nota;
            $contenido.style.display = 'block';

            let fechaRaw = nota.fecha || nota.fecha_ingreso || '';
            let fechaFmt = fechaRaw;
            if (typeof Validador !== 'undefined' && Validador.formatearFecha) {
                fechaFmt = Validador.formatearFecha(fechaRaw);
            }
            document.getElementById('ver-nota-fecha').textContent = fechaFmt || '—';
            document.getElementById('ver-nota-encargado').textContent = nota.usuario_responsable || nota.encargado_nombre || 'N/A';

            if (esEntrada) {
                document.getElementById('ver-nota-cliente').textContent = nota.razon_social || 'Sin proveedor';
                document.getElementById('ver-nota-documento').textContent = nota.rif || 'N/A';
            } else {
                document.getElementById('ver-nota-cliente').textContent = nota.cliente || nota.nombre_cliente || 'N/A';
                document.getElementById('ver-nota-documento').textContent = nota.cedula || nota.rif || nota.documento || 'N/A';
            }

            const tbody = document.getElementById('ver-nota-productos-tbody');
            tbody.innerHTML = '';
            let totalUnidades = 0;
            let totalDolares = 0;

            if (nota.detalles && nota.detalles.length > 0) {
                nota.detalles.forEach(function (d) {
                    const cantidad = parseInt(d.cantidad, 10) || 0;
                    totalUnidades += cantidad;

                    const colDesc = esEntrada 
                        ? (nota.descripcion || 'Sin descripción') 
                        : (d.nombre_categoria || d.categoria || d.nombre_cat || 'Sin categoría');
                    
                    let filaHTML = `
                        <tr>
                            <td class="ps-4" style="color: #1a1a2e;">${d.nombre_producto || d.producto || 'N/A'}</td>
                            <td style="color: #6c757d;">${colDesc}</td>
                            <td class="text-center fw-bold" style="color: #1a1a2e;">${cantidad}</td>
                    `;

                    if (esEntrada) {
                        const costo = parseFloat(d.costo_unitario) || 0;
                        const subtotal = cantidad * costo;
                        totalDolares += subtotal;
                        
                        filaHTML += `
                            <td class="text-end" style="color: #1a1a2e;">$${costo.toFixed(2)}</td>
                            <td class="text-end pe-4 fw-bold" style="color: #1a1a2e;">$${subtotal.toFixed(2)}</td>
                        `;
                    }
                    filaHTML += `</tr>`;
                    tbody.innerHTML += filaHTML;
                });
            } else {
                const colspan = esEntrada ? 5 : 3;
                tbody.innerHTML = `<tr><td class="text-center py-3" colspan="${colspan}" style="color: #6c757d;">No hay productos</td></tr>`;
            }

            document.getElementById('ver-nota-total-unidades').textContent = totalUnidades;
            if (esEntrada) {
                document.getElementById('ver-nota-total-dolares').textContent = '$' + totalDolares.toFixed(2);
            }
        })
        .catch(err => {
            $cargando.style.display = 'none';
            $error.style.display = 'block';
            document.getElementById('ver-nota-error-msg').innerHTML = '<div style="font-size:12px; text-align:left; overflow:auto; max-height:200px;"><b>Error del servidor:</b><br>' + err.message.replace(/</g, "&lt;") + '</div>';
        });
    };

});
</script>

<!-- 🔥 INCLUIMOS EL JS DEL CIERRE -->
<script src="assets/js/reciboCierre.js"></script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>