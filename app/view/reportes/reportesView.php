<?php
// Verificar que las variables existan
$movimientos = isset($movimientos) ? $movimientos : [];
$paginaActual = isset($paginaActual) ? $paginaActual : 1;
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
$totalRegistros = isset($totalRegistros) ? $totalRegistros : 0;
$porPaginaActual = isset($porPaginaActual) ? $porPaginaActual : 10;
$totalEntradas = isset($totalEntradas) ? $totalEntradas : 0;
$totalVentas = isset($totalVentas) ? $totalVentas : 0;

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

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">

            <!-- ========================================== -->
            <!-- TARJETA DE FILTROS (estilo global) -->
            <!-- ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title"><i class="fas fa-file-alt text-gold me-2"></i> Reporte de Movimientos</h3>
                        <small style="color: rgba(255,255,255,0.6); display:block; margin-top:4px;">Historial de entradas y salidas</small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=reportes" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none;">Ver todos</a>
                    </div>
                </div>
            </div>

            <div class="card mb-4 dark-card">
                <div class="card-body pb-0">
                    <form method="GET" action="" id="formFiltros">
                        <input type="hidden" name="url" value="reportes">

                        <div class="d-flex justify-content-between mb-3">
                            <div class="d-flex gap-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-dark text-white"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" name="fecha_inicio" class="form-control form-control-sm bg-dark text-white border-0" 
                                           value="<?= isset($_GET['fecha_inicio']) ? htmlspecialchars($_GET['fecha_inicio']) : '' ?>" title="Fecha de inicio">
                                </div>

                                <select name="tipo_movimiento" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="Entrada" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Entrada') ? 'selected' : '' ?>>Entradas</option>
                                    <option value="Salida" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Salida') ? 'selected' : '' ?>>Salidas</option>
                                    <option value="Anulacion" <?= (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'Anulacion') ? 'selected' : '' ?>>Anulaciones</option>
                                </select>

                                <select name="id_categoria" class="form-select form-select-sm">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id_categoria'] ?>" <?= (isset($_GET['id_categoria']) && $_GET['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <input type="text" name="busqueda" class="form-control form-control-sm" placeholder="Buscar producto..." value="<?= isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '' ?>">
                            </div>

                            <div class="d-flex align-items-center">
                                <?php
                                    // Pasar variables al partial para que calcule paginación coherente
                                    $por_pagina = $porPaginaActual ?? 10;
                                    $pagina_actual = $paginaActual ?? 1;
                                    $totalRegistros = $totalRegistros ?? 0;
                                    $totalPaginas = $totalPaginas ?? 1;
                                    require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php";
                                ?>
                                <button class="btn btn-dark-gold btn-sm ms-2" type="submit"><i class="fas fa-search me-1"></i> Buscar</button>
                                <?php if(!empty($_GET['fecha_inicio']) || !empty($_GET['fecha_fin']) || !empty($_GET['id_categoria']) || !empty($_GET['busqueda']) || !empty($_GET['tipo_movimiento'])): ?>
                                    <a href="?url=reportes" class="btn btn-secondary btn-sm ms-2" title="Limpiar filtros"><i class="fas fa-times"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- BOTONES DE EXPORTACIÓN -->
            <!-- ========================================== -->
            <div class="d-flex justify-content-end gap-2 mb-3">
                <button class="btn btn-dark-gold btn-sm" onclick="alert('Funcionalidad de exportación a Excel en desarrollo')"><i class="fas fa-file-excel me-1"></i> Excel</button>
                <button class="btn btn-outline-danger btn-sm" onclick="alert('Funcionalidad de exportación a PDF en desarrollo')"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                <button class="btn btn-outline-info btn-sm" onclick="alert('Funcionalidad de exportación a CSV en desarrollo')"><i class="fas fa-file-csv me-1"></i> CSV</button>
            </div>

            <!-- ========================================== -->
            <!-- TABLA DE MOVIMIENTOS (estilo global) -->
            <!-- ========================================== -->
            <div class="dark-card card card-custom mb-4">
                <div class="card-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <h5 class="m-0" style="color: #fff; font-weight: 700;"><i class="fas fa-history text-muted me-2"></i> Historial de Movimientos</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-fenix m-0">
                <thead>
                    <tr>
                        <th class="ps-4">Producto</th>
                        <th>Categoría</th>
                        <th>Tipo de Movimiento</th>
                        <th>Estado</th>
                        <th>Cantidad</th>
                        <th>Costo Prev.</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $movimiento): 
                            $tipo = $movimiento['tipo_movimiento'] ?? '';
                            $esEntrada = ($tipo === 'Entrada');
                            $esSalida = ($tipo === 'Salida');
                            $esAnulacion = ($tipo === 'Anulacion');
                            $esProducto = ($tipo === 'Producto');
                            $anulado = isset($movimiento['anulado']) ? (int)$movimiento['anulado'] : 0;

                            // Badge y color: Entradas y Productos activos -> verde; Salidas o Anuladas -> rojo
                            if ($anulado === 1 || $esSalida || $esAnulacion) {
                                $badgeClass = 'badge bg-danger text-white';
                                $colorClass = 'text-danger';
                            } else {
                                $badgeClass = 'badge bg-success text-white';
                                $colorClass = 'text-success';
                            }

                            // Signo: + para entradas, - para salidas, vacío para productos
                            $signo = $esEntrada ? '+' : ($esSalida ? '-' : '');
                            if (!empty($movimiento['fecha_movimiento'])) {
                                try {
                                    $fechaObj = new DateTime($movimiento['fecha_movimiento']);
                                    $fechaFormateada = $fechaObj->format('d/m/Y H:i');
                                } catch (Exception $e) {
                                    $fechaFormateada = '-';
                                }
                            } else {
                                $fechaFormateada = '-';
                            }
                            $costo = isset($movimiento['costo_proveedor']) ? $movimiento['costo_proveedor'] : 0;
                        ?>
                            <tr>
                                <td class="ps-4 fw-medium text-dark"><?= htmlspecialchars($movimiento['nombre_producto']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($movimiento['categoria_nombre'] ?? '-') ?></td>
                                <td>
                                    <?php
                                        // Tipo de movimiento: mostrar en minúsculas y con color
                                        if ($esEntrada) {
                                            $tipoLabel = 'entrada';
                                            $tipoClass = 'badge bg-success text-white px-2 py-1 rounded-pill fw-bold';
                                            $tipoIcon = '<i class="fas fa-arrow-down me-1"></i>';
                                        } elseif ($esSalida) {
                                            $tipoLabel = 'salida';
                                            $tipoClass = 'badge bg-danger text-white px-2 py-1 rounded-pill fw-bold';
                                            $tipoIcon = '<i class="fas fa-arrow-up me-1"></i>';
                                        } elseif ($esAnulacion) {
                                            $tipoLabel = 'anulacion';
                                            $tipoClass = 'badge bg-danger text-white px-2 py-1 rounded-pill fw-bold';
                                            $tipoIcon = '<i class="fas fa-ban me-1"></i>';
                                        } else {
                                            $tipoLabel = 'producto';
                                            $tipoClass = 'badge bg-secondary text-white px-2 py-1 rounded-pill fw-bold';
                                            $tipoIcon = '<i class="fas fa-box me-1"></i>';
                                        }
                                    ?>
                                    <span class="<?= $tipoClass ?>"><?= $tipoIcon ?> <?= $tipoLabel ?></span>
                                </td>
                                    <td>
                                        <?php if ($anulado === 1): ?>
                                            <span class="badge bg-danger text-white">inactivo</span>
                                        <?php else: ?>
                                            <span class="badge bg-success text-white">activo</span>
                                        <?php endif; ?>
                                    </td>
                                <td class="<?= $colorClass ?> fw-bold">
                                        <?= $signo ?> <?= htmlspecialchars($movimiento['cantidad']) ?> <?= $esProducto ? 'unidades' : 'unidades' ?>
                                </td>
                                <td class="fw-bold text-secondary">
                                    <?= isset($movimiento['costo_prev']) ? '$' . number_format($movimiento['costo_prev'], 2) : '-' ?>
                                </td>
                                <td class="text-muted"><i class="far fa-clock me-1"></i> <?= $fechaFormateada ?></td>
                                <td class="text-dark"><i class="far fa-user-circle text-muted me-1"></i>
                                    <?= htmlspecialchars($movimiento['usuario_activo'] ?? 'N/A') ?>
                                    <small class="text-muted">(<?= htmlspecialchars($movimiento['rol_usuario'] ?? 'N/A') ?>)</small>
                                </td>
                                
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open mb-3 text-secondary opacity-50" style="font-size: 3rem; display: block;"></i>
                                <h5 class="fw-bold text-white-50">No hay movimientos registrados</h5>
                                <p class="mb-0">El historial de transacciones se encuentra vacío actualmente.<br>
                                <small class="text-muted">Sugerencia: Amplíe el rango de fechas o los filtros de búsqueda.</small></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div class="card-footer bg-dark border-top py-3 d-flex justify-content-between align-items-center">
            <span class="text-white-50 small">Mostrando página <?= $paginaActual ?> de <?= $totalPaginas ?> (<?= $totalRegistros ?> registros totales)</span>
            <nav aria-label="Navegación de páginas">
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
                            <a class="page-link <?= ($i == $paginaActual) ? 'bg-dark border-dark text-white' : 'text-dark' ?>" href="<?= $pageUrl ?>"><?= $i ?></a>
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
        </div>
        <?php endif; ?>
    </div>

    <!-- ========================================== -->
    <!-- TARJETA DE VALIDACIÓN DE INTEGRIDAD -->
    <!-- ========================================== -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card card-custom border-start border-danger border-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-shield-alt me-2"></i>🛡️ Validación de Integridad de Datos</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted">Total Entradas:</span>
                        <strong class="text-success">+ <?= $totalEntradas; ?> unidades</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                        <span class="text-muted">Total Ventas:</span>
                        <strong class="text-danger">- <?= $totalVentas; ?> unidades</strong>
                    </div>
                    <div class="d-flex justify-content-between pt-2">
                        <span class="fw-bold text-dark">Stock Real:</span>
                        <strong class="text-primary" style="font-size: 1.1rem;"><?= ($totalEntradas - $totalVentas); ?> unidades</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>