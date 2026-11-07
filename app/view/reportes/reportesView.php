<?php
// app/view/reportes/reportesView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

// Variables
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

$paramsExport = http_build_query([
    'fecha' => $_GET['fecha'] ?? '',
    'tipo_movimiento' => $_GET['tipo_movimiento'] ?? '',
    'id_categoria' => $_GET['id_categoria'] ?? '',
    'busqueda' => $_GET['busqueda'] ?? ''
]);
?>

<!-- ========================================================= -->
<!-- ESTILOS PARA LOS BOTONES DE ACCIÓN (color fijo)            -->
<!-- ========================================================= -->
<style>
/* Botón circular base */
.btn-action-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    padding: 0;
    font-size: 0.85rem;
    color: #ffffff;
}

.btn-action-circle:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
    filter: brightness(1.1);
}

.btn-action-circle:active {
    transform: translateY(0);
    filter: brightness(0.95);
}

/* Botón Ver Detalle (azul sólido fijo) */
.btn-action-circle.btn-view {
    background-color: #0d6efd;
    color: #ffffff;
}

.btn-action-circle.btn-view:hover {
    background-color: #0b5ed7;
}

/* Botón Ver Motivo (rojo sólido fijo) */
.btn-action-circle.btn-motivo {
    background-color: #dc3545;
    color: #ffffff;
}

.btn-action-circle.btn-motivo:hover {
    background-color: #c82333;
}
</style>

<div class="col-md-8 col-lg-12">
    
    <!-- TÍTULO Y BOTONES -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-xl-8">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-file-alt text-gold me-2"></i> Reporte de Movimientos
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important;">
                    <i class="fas fa-history me-2"></i> <?= $totalRegistros ?> notas registradas
                </small>
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
                <label class="form-label fw-bold small text-dark">Fecha</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['fecha'] ?? $_GET['fecha_inicio'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Tipo</label>
                <select name="tipo_movimiento" class="form-select form-select-sm" data-no-search="true">
                    <option value="">Todos</option>
                    <option value="Entrada" <?= (($_GET['tipo_movimiento'] ?? '') == 'Entrada') ? 'selected' : '' ?>>Entradas</option>
                    <option value="Salida" <?= (($_GET['tipo_movimiento'] ?? '') == 'Salida') ? 'selected' : '' ?>>Salidas</option>
                    <option value="Anulación" <?= (($_GET['tipo_movimiento'] ?? '') == 'Anulación') ? 'selected' : '' ?>>Todas las Anulaciones</option>
                    <option value="Anulación Entrada" <?= (($_GET['tipo_movimiento'] ?? '') == 'Anulación Entrada') ? 'selected' : '' ?>>Anuladas de Entrada</option>
                    <option value="Anulación Salida" <?= (($_GET['tipo_movimiento'] ?? '') == 'Anulación Salida') ? 'selected' : '' ?>>Anuladas de Salida</option>
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
            <div class="col-md-2">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-gold btn-sm fw-bold"><i class="fas fa-search"></i> Buscar</button>
                    <a href="?url=reportes" class="btn btn-secondary btn-sm fw-bold">Limpiar</a>
                </div>
            </div>
        </form>
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
            <h5 class="m-0"><i class="fas fa-list me-2"></i> Historial de Notas</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">N° NOTA</th>
                        <th class="py-3">FECHA</th>
                        <th class="py-3">CLIENTE / PROVEEDOR</th>
                        <th class="py-3">TIPO</th>
                        <th class="py-3">PRODUCTOS</th>
                        <th class="py-3">UNIDADES</th>
                        <th class="py-3">ENCARGADO</th>
                        <th class="pe-4 py-3 text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $m): 
                            $tipo = $m['tipo_movimiento'] ?? '';
                            
                            // Detección robusta de anulación
                            $esAnulacion = (
                                strpos($tipo, 'Anulación') === 0
                                || strpos($tipo, 'Anulacion') === 0
                                || stripos($tipo, 'anulad') !== false
                                || (isset($m['anulado']) && (int)$m['anulado'] === 1)
                                || (isset($m['status']) && in_array(strtolower((string)$m['status']), ['anulada','anulado','anulada_entrada','anulada_salida'], true))
                            );

                            $tipoBase = $tipo;
                            if ($esAnulacion) {
                                if (stripos($tipo, 'Entrada') !== false)    $tipoBase = 'Entrada';
                                elseif (stripos($tipo, 'Salida') !== false) $tipoBase = 'Salida';
                            }

                            $esEntrada = ($tipoBase === 'Entrada');
                            $badgeClass = $esAnulacion
                                ? 'bg-secondary'
                                : ($esEntrada ? 'bg-success' : 'bg-danger');

                            $motivo = $m['motivo_anulacion'] ?? 'No se registró un motivo.';
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= htmlspecialchars($m['id_documento']) ?></td>
                                <td><?= formatoFechaVenezuela($m['fecha_movimiento']) ?></td>
                                <td><?= htmlspecialchars($m['nombre_cliente'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($tipo) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $productosLista = trim((string)($m['productos_lista'] ?? ''));
                                    $productosLimpios = preg_replace('/\s*\(x\d+\)/', '', $productosLista);
                                    ?>
                                    <?= !empty($productosLimpios) ? htmlspecialchars($productosLimpios) : ((int)($m['total_productos'] ?? 0) . ' items') ?>
                                </td>
                                <td class="fw-bold"><?= $m['total_unidades'] ?? 0 ?></td>
                                <td><?= htmlspecialchars($m['usuario_activo']) ?></td>
                                
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($esAnulacion): ?>
                                            <!-- Botón circular Ver Motivo (rojo sólido fijo) -->
                                            <button type="button"
                                                    class="btn-action-circle btn-motivo btn-ver-motivo"
                                                    title="Ver Motivo"
                                                    data-documento="#<?= $m['id_documento'] ?>"
                                                    data-producto="<?= htmlspecialchars($m['nombre_cliente'] ?? 'N/A') ?>"
                                                    data-motivo="<?= htmlspecialchars($motivo) ?>">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </button>
                                        <?php else: ?>
                                            <!-- Botón circular Ver Detalle (azul sólido fijo) -->
                                            <button type="button"
                                                    class="btn-action-circle btn-view btn-ver-detalle"
                                                    title="Ver Detalle"
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
                                <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                <p class="mb-0">No hay movimientos registrados</p>
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

<!-- ========================================================= -->
<!-- INCLUIR MODALES EXTERNOS                                  -->
<!-- ========================================================= -->
<?php require_once __DIR__ . '/verDetalleView.php'; ?>
<?php require_once __DIR__ . '/MotivoAnulacionView.php'; ?>

<!-- ========================================================= -->
<!-- SCRIPT PARA FILTROS AJAX Y MODAL MOTIVO                   -->
<!-- ========================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // 1. Filtro AJAX
    const form = document.getElementById('formFiltros');
    if (form) {
        form.addEventListener('submit', function (e) {
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
                const url = new URL(window.location.href);
                url.search = new URLSearchParams(new FormData(form)).toString();
                window.location.href = url.toString();
            });
        });
    }

    // 2. Delegación de Eventos para el Botón "Ver Motivo"
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-ver-motivo');
        if (btn) {
            e.preventDefault();
            
            const doc = btn.getAttribute('data-documento');
            const prod = btn.getAttribute('data-producto');
            const motivo = btn.getAttribute('data-motivo');

            document.getElementById('modal-motivo-doc').textContent = doc;
            document.getElementById('modal-motivo-prod').textContent = prod;
            document.getElementById('modal-motivo-texto').textContent = motivo;
            
            const modalEl = document.getElementById('modalMotivoAnulacion');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    });
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>