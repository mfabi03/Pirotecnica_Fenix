<?php
// Verificar que las variables existan
$movimientos = isset($movimientos) ? $movimientos : [];
$paginaActual = isset($paginaActual) ? $paginaActual : 1;
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
$totalRegistros = isset($totalRegistros) ? $totalRegistros : 0;
$porPaginaActual = isset($porPaginaActual) ? $porPaginaActual : 10;
$totalEntradas = isset($totalEntradas) ? $totalEntradas : 0;
$totalSalidas = isset($totalSalidas) ? $totalSalidas : 0;

// Obtener categorías para el filtro
$categorias = [];
try {
    $sql = "SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si no se pueden obtener, se deja vacío
}

// Pasar variables al partial
$por_pagina = $porPaginaActual;
$pagina_actual = $paginaActual;
$totalRegistros = $totalRegistros;
$totalPaginas = $totalPaginas;

// Incluir header del sistema
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="col-md-8 col-lg-12">
    
    <!-- ========================================== -->
    <!-- TARJETA DE TÍTULO -->
    <!-- ========================================== -->
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

    <!-- ========================================== -->
    <!-- FILTROS -->
    <!-- ========================================== -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" id="formFiltros" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="reportes">
            
            <!-- Select Mostrar -->
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
                <select name="tipo_movimiento" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Entrada" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Entrada') ? 'selected' : '' ?>>Entradas</option>
                    <option value="Salida" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Salida') ? 'selected' : '' ?>>Salidas</option>
                    <option value="Anulación" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Anulación') ? 'selected' : '' ?>>Anulaciones</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Categoría</label>
                <select name="id_categoria" class="form-select form-select-sm">
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
                       list="listaProductosReporte"
                       placeholder="Buscar..." 
                       value="<?= isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '' ?>">
                <datalist id="listaProductosReporte">
                    <?php 
                    $sqlProd = "SELECT descripcion FROM producto WHERE eliminado = 0 ORDER BY descripcion ASC";
                    $stmtProd = $db->prepare($sqlProd);
                    $stmtProd->execute();
                    $productosDatalist = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($productosDatalist as $prod): 
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

    <!-- ========================================== -->
    <!-- BOTONES DE EXPORTACIÓN -->
    <!-- ========================================== -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="?url=exportar_csv" class="btn btn-gold btn-sm fw-bold">
            <i class="fas fa-file-excel me-1"></i> CSV
        </a>
        <a href="?url=exportar_pdf" class="btn btn-danger btn-sm fw-bold">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>
    </div>

    <!-- ========================================== -->
    <!-- ESTADÍSTICAS -->
    <!-- ========================================== -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-total-notas border-left-gold text-center p-3">
                <h6 class="card-title text-muted">TOTAL ENTRADAS</h6>
                <h2 class="card-number text-success">+ <?= $totalEntradas ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-total-notas border-left-danger text-center p-3">
                <h6 class="card-title text-muted">TOTAL SALIDAS</h6>
                <h2 class="card-number text-danger">- <?= $totalSalidas ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-total-notas border-left-info text-center p-3">
                <h6 class="card-title text-muted">STOCK REAL</h6>
                <h2 class="card-number text-primary"><?= ($totalEntradas - $totalSalidas) ?></h2>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TABLA -->
    <!-- ========================================== -->
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
                                    <button type="button" class="btn btn-sm btn-outline-info" disabled title="Disponible en Fase 2">
                                        <i class="fas fa-eye"></i> Ver Motivo
                                    </button>
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
        
        <!-- ========================================== -->
        <!-- PAGINACIÓN: TOTAL IZQ + BOTONES DER -->
        <!-- ========================================== -->
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-history me-1"></i> 
                Total: <?= $totalRegistros ?> movimientos
            </span>
            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
        </div>
    </div>

</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>