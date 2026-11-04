<?php
// Verificar que las variables existan
$movimientos = isset($movimientos) ? $movimientos : [];
$paginaActual = isset($paginaActual) ? $paginaActual : 1;
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
$totalRegistros = isset($totalRegistros) ? $totalRegistros : 0;
$porPaginaActual = isset($porPaginaActual) ? $porPaginaActual : 10;
$totalEntradas = isset($totalEntradas) ? $totalEntradas : 0;
$totalSalidas = isset($totalSalidas) ? $totalSalidas : 0;
$totalAnuladas = isset($totalAnuladas) ? $totalAnuladas : 0;

// Obtener categorías para el filtro
$categorias = [];
try {
    $sql = "SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$por_pagina = $porPaginaActual;
$pagina_actual = $paginaActual;
$totalRegistros = $totalRegistros;
$totalPaginas = $totalPaginas;

// Capturamos los filtros actuales para exportarlos en los botones
$paramsExport = http_build_query([
    'fecha' => $_GET['fecha'] ?? '',
    'tipo_movimiento' => $_GET['tipo_movimiento'] ?? '',
    'id_categoria' => $_GET['id_categoria'] ?? '',
    'busqueda' => $_GET['busqueda'] ?? ''
]);

require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="col-md-8 col-lg-12">
    
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-xl-12">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-file-alt text-gold me-2"></i> Reporte de Movimientos
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important;">
                    <i class="fas fa-history me-2"></i> 
                    <?= $totalRegistros ?> movimientos registrados
                </small>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" id="formFiltros" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="reportes">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($porPaginaActual == $o) ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Fecha</label>
                <input type="date" name="fecha" class="form-control form-control-sm" 
                       value="<?= isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '' ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Tipo</label>
                <select name="tipo_movimiento" class="form-select form-select-sm" data-no-search="true">
                    <option value="">Todos</option>
                    <option value="Entrada" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Entrada') ? 'selected' : '' ?>>Entradas</option>
                    <option value="Salida" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Salida') ? 'selected' : '' ?>>Salidas</option>
                    <option value="Anulación Entrada" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Anulación Entrada') ? 'selected' : '' ?>>Anulaciones Entrada</option>
                    <option value="Anulación Salida" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Anulación Salida') ? 'selected' : '' ?>>Anulaciones Salida</option>
                    <option value="Anulación" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Anulación') ? 'selected' : '' ?>>Anulaciones</option>
                </select>
            </div>

            <div class="col-md-2">
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

            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Producto</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       list="listaProductosReporte" placeholder="Buscar..." 
                       value="<?= isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '' ?>">
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

            <div class="col-md-2">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-gold btn-sm fw-bold">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="?url=reportes" class="btn btn-secondary btn-sm fw-bold">
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

    <!-- EXPORTACIÓN -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="?url=exportar_csv&<?= $paramsExport ?>" class="btn btn-gold btn-sm fw-bold">
            <i class="fas fa-file-excel me-1"></i> CSV
        </a>
        <a href="?url=exportar_pdf&<?= $paramsExport ?>" target="_blank" class="btn btn-danger btn-sm fw-bold">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-total-notas border-left-gold text-center p-3">
                <h6 class="card-title text-muted">TOTAL ENTRADAS (OPS)</h6>
                <h2 class="card-number text-success">+ <?= $totalEntradas ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-total-notas border-left-danger text-center p-3">
                <h6 class="card-title text-muted">TOTAL SALIDAS (OPS)</h6>
                <h2 class="card-number text-danger">- <?= $totalSalidas ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-total-notas border-left-info text-center p-3">
                <h6 class="card-title text-muted">ANULADAS</h6>
                <h2 class="card-number text-warning"><?= $totalAnuladas ?></h2>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-history me-2"></i> Historial de Movimientos
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">PRODUCTO</th>
                        <th class="py-3">CATEGORÍA</th>
                        <th class="py-3">TIPO</th>
                        <th class="py-3">CANTIDAD</th>
                        <th class="py-3">COSTO</th>
                        <th class="py-3">FECHA</th>
                        <th class="py-3">RESPONSABLE</th>
                        <th class="pe-4 py-3 text-center">ACCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $movimiento): 
                            $esEntrada = ($movimiento['tipo_movimiento'] === 'Entrada');
                            $esAnulacion = ($movimiento['tipo_movimiento'] === 'Anulación');
                            
                            $signo = $esEntrada ? '+' : ($esAnulacion ? '↺' : '-');
                            $colorClass = $esEntrada ? 'text-success' : ($esAnulacion ? 'text-secondary' : 'text-danger');
                            $badgeClass = $esEntrada ? 'badge bg-success' : ($esAnulacion ? 'badge bg-secondary' : 'badge bg-danger');
                            
                            $fechaObj = new DateTime($movimiento['fecha_movimiento']);
                            $fechaFormateada = $fechaObj->format('d/m/Y');
                            $costo = isset($movimiento['costo_proveedor']) ? $movimiento['costo_proveedor'] : 0;
                            
                            // Si el modelo trae el motivo, lo usamos, sino mostramos el mensaje por defecto
                            $motivo = $movimiento['motivo_anulacion'] ?? 'No se registró un motivo en la base de datos.';
                        ?>
                            <tr>
                                <td class="ps-4 fw-medium"><?= htmlspecialchars($movimiento['nombre_producto']) ?></td>
                                <td>
                                    <span class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 4px 12px; border-radius: 50px; font-weight: 600;">
                                        <?= htmlspecialchars($movimiento['categoria'] ?? 'Sin categoría') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $esEntrada ? '<i class="fas fa-arrow-down me-1"></i>' : ($esAnulacion ? '<i class="fas fa-ban me-1"></i>' : '<i class="fas fa-arrow-up me-1"></i>') ?>
                                        <?= htmlspecialchars($movimiento['tipo_movimiento']) ?>
                                    </span>
                                </td>
                                <td class="<?= $colorClass ?> fw-bold">
                                    <?= $signo ?> <?= htmlspecialchars(abs($movimiento['cantidad'])) ?> unidades
                                </td>
                                <td>
                                    <?= ($esEntrada || $esAnulacion) ? '$' . number_format($costo, 2) : '-' ?>
                                </td>
                                <td><?= $fechaFormateada ?></td>
                                <td><?= htmlspecialchars($movimiento['usuario_activo']) ?></td>
                                <td class="pe-4 text-center">
                                    
                                    <!-- SOLO APARECE SI ES ANULACIÓN -->
                                    <?php if ($esAnulacion): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info btn-ver-motivo" 
                                                data-documento="#<?= $movimiento['id_documento'] ?>"
                                                data-producto="<?= htmlspecialchars($movimiento['nombre_producto']) ?>"
                                                data-motivo="<?= htmlspecialchars($motivo) ?>">
                                            <i class="fas fa-eye"></i> Ver Motivo
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-box-open fa-3x d-block mb-3"></i>
                                    <p class="mb-0">No hay movimientos registrados</p>
                                    <small>Realiza una entrada o salida de productos para verlos aquí</small>
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
                <i class="fas fa-history me-1"></i> 
                Total: <?= $totalRegistros ?> movimientos
            </span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- MODAL PARA VER EL MOTIVO DE LA ANULACIÓN                  -->
<!-- ========================================================= -->
<div class="modal fade" id="modalMotivoAnulacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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
                    <div class="fs-5 fw-bold text-dark" id="modal-motivo-doc"></div>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Producto Afectado:</span>
                    <div class="fs-6 text-dark" id="modal-motivo-prod"></div>
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

<!-- SCRIPT PARA ABRIR EL MODAL CON LOS DATOS DE LA FILA -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formFiltros');
    if (form) {
        form.addEventListener('submit', function (e) {
            const ajaxToggle = form.querySelector('input[name="ajax"]');
            if (ajaxToggle) return;
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('ajax', '1');

            fetch('?url=reportes', {
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

    const botones = document.querySelectorAll('.btn-ver-motivo');
    const modalEl = document.getElementById('modalMotivoAnulacion');
    const modal = new bootstrap.Modal(modalEl);

    botones.forEach(btn => {
        btn.addEventListener('click', function () {
            const doc = this.getAttribute('data-documento');
            const prod = this.getAttribute('data-producto');
            const motivo = this.getAttribute('data-motivo');

            document.getElementById('modal-motivo-doc').textContent = doc;
            document.getElementById('modal-motivo-prod').textContent = prod;
            document.getElementById('modal-motivo-texto').textContent = motivo;

            modal.show();
        });
    });
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>