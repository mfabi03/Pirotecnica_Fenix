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

// Incluir header del sistema
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="col-md-8 col-lg-12">
    
    <!-- ========================================== -->
    <!-- TARJETA DE TÍTULO - FONDO OSCURO -->
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
    <!-- FILTROS - ESTILO IGUAL A PRODUCTOS -->
    <!-- ========================================== -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" id="formFiltros" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="reportes">
            
            <!-- Mostrar -->
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" onchange="document.getElementById('formFiltros').submit();">
                    <option value="10" <?= ($porPaginaActual == 10) ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= ($porPaginaActual == 20) ? 'selected' : '' ?>>20</option>
                    <option value="30" <?= ($porPaginaActual == 30) ? 'selected' : '' ?>>30</option>
                    <option value="50" <?= ($porPaginaActual == 50) ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($porPaginaActual == 100) ? 'selected' : '' ?>>100</option>
                    <option value="150" <?= ($porPaginaActual == 150) ? 'selected' : '' ?>>150</option>
                    <option value="200" <?= ($porPaginaActual == 200) ? 'selected' : '' ?>>200</option>
                </select>
            </div>

            <!-- Fecha Única -->
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Fecha</label>
                <input type="date" name="fecha" class="form-control form-control-sm" 
                       value="<?= isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '' ?>">
            </div>

            <!-- Tipo de Movimiento -->
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Tipo</label>
                <select name="tipo_movimiento" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Entrada" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Entrada') ? 'selected' : '' ?>>Entradas</option>
                    <option value="Salida" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Salida') ? 'selected' : '' ?>>Salidas</option>
                </select>
            </div>

            <!-- Categoría -->
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

            <!-- Producto -->
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Producto</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       placeholder="Buscar..." 
                       value="<?= isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '' ?>">
            </div>

            <!-- Botones -->
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
    <!-- TABLA - CABECERA OSCURA -->
    <!-- ========================================== -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-history me-2"></i> Historial de Movimientos
            </h5>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small me-2">Mostrar:</span>
                <select name="por_pagina" class="form-select form-select-sm" style="width: auto;" onchange="document.getElementById('formFiltros').submit();">
                    <option value="10" <?= ($porPaginaActual == 10) ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= ($porPaginaActual == 20) ? 'selected' : '' ?>>20</option>
                    <option value="30" <?= ($porPaginaActual == 30) ? 'selected' : '' ?>>30</option>
                    <option value="50" <?= ($porPaginaActual == 50) ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($porPaginaActual == 100) ? 'selected' : '' ?>>100</option>
                    <option value="150" <?= ($porPaginaActual == 150) ? 'selected' : '' ?>>150</option>
                    <option value="200" <?= ($porPaginaActual == 200) ? 'selected' : '' ?>>200</option>
                </select>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">PRODUCTO</th>
                        <th class="py-3">TIPO</th>
                        <th class="py-3">CANTIDAD</th>
                        <th class="py-3">COSTO</th>
                        <th class="py-3">FECHA</th>
                        <th class="pe-4 py-3">RESPONSABLE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $movimiento): 
                            $esEntrada = ($movimiento['tipo_movimiento'] === 'Entrada');
                            $signo = $esEntrada ? '+' : '-';
                            $colorClass = $esEntrada ? 'text-success' : 'text-danger';
                            $badgeClass = $esEntrada ? 'badge bg-success' : 'badge bg-danger';
                            $fechaObj = new DateTime($movimiento['fecha_movimiento']);
                            $fechaFormateada = $fechaObj->format('d/m/Y');
                            $costo = isset($movimiento['costo_proveedor']) ? $movimiento['costo_proveedor'] : 0;
                        ?>
                            <tr>
                                <td class="ps-4 fw-medium"><?= htmlspecialchars($movimiento['nombre_producto']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $esEntrada ? '<i class="fas fa-arrow-down me-1"></i>' : '<i class="fas fa-arrow-up me-1"></i>' ?>
                                        <?= htmlspecialchars($movimiento['tipo_movimiento']) ?>
                                    </span>
                                </td>
                                <td class="<?= $colorClass ?> fw-bold">
                                    <?= $signo ?> <?= htmlspecialchars($movimiento['cantidad']) ?> unidades
                                </td>
                                <td>
                                    <?= $esEntrada ? '$' . number_format($costo, 2) : '-' ?>
                                </td>
                                <td><?= $fechaFormateada ?></td>
                                <td class="pe-4"><?= htmlspecialchars($movimiento['usuario_activo']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 dark-empty">
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
        
        <!-- Footer con paginación -->
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-history me-1"></i> 
                Total: <?= $totalRegistros ?> movimientos
            </span>
            <?php if ($totalPaginas > 1): ?>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php 
                    $queryStr = $_GET;
                    $queryStr['pagina'] = max(1, $paginaActual - 1);
                    $prevUrl = "?" . http_build_query($queryStr);
                    ?>
                    <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link text-dark" href="<?= $prevUrl ?>">Anterior</a>
                    </li>
                    
                    <?php for($i = 1; $i <= $totalPaginas; $i++): 
                        $queryStr['pagina'] = $i;
                        $pageUrl = "?" . http_build_query($queryStr);
                    ?>
                        <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                            <a class="page-link <?= ($i == $paginaActual) ? 'bg-dark text-white border-dark' : 'text-dark' ?>" href="<?= $pageUrl ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php 
                    $queryStr['pagina'] = min($totalPaginas, $paginaActual + 1);
                    $nextUrl = "?" . http_build_query($queryStr);
                    ?>
                    <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                        <a class="page-link text-dark" href="<?= $nextUrl ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>