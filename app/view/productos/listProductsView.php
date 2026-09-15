<?php
// app/view/productos/listProductsView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

// Crear conexión si no existe
if (!isset($db) || $db === null) {
    try {
        $db = (new ConnectDB())->getConnection();
    } catch (Exception $e) {
        $db = null;
    }
}

// Obtener permisos del usuario actual
$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$puede_crear_producto = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Productos', 'crear') : false;
$puede_editar_producto = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Productos', 'actualizar') : false;
$puede_eliminar_producto = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Productos', 'eliminar') : false;

$jsonPath = __DIR__ . '/../../../public/uploads/products_imagenes.json';
$productosData = [];
if (file_exists($jsonPath)) {
    $productosData = json_decode(file_get_contents($jsonPath), true) ?? [];
}

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($productos) ? count($productos) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
            
            <!-- TARJETA DE TÍTULO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-cube text-gold me-2"></i> Lista de Productos
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona los productos registrados en el sistema
                        </small>
                    </div>
                    <div class="col-auto">
                        <?php if ($puede_crear_producto): ?>
                        <a href="?url=productos&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Producto
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
                    <input type="hidden" name="url" value="productos">
                    <input type="hidden" name="type" value="list">
                    
                    <!-- ✅ SELECT "MOSTRAR" ARRIBA -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                        <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ([5, 10, 25, 50] as $o): ?>
                                <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <input type="text" name="busqueda" class="form-control" 
                               list="listaProductos"
                               placeholder="Buscar por nombre, código o categoría..."
                               value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        <datalist id="listaProductos">
                            <?php
                            $sugerencias = [];
                            if (!empty($productos) && is_array($productos)):
                                foreach ($productos as $p):
                                    $nombre = trim($p['descripcion'] ?? '');
                                    $codigo = trim((string)($p['id_producto'] ?? ''));
                                    $categoria = trim($p['nombre_categoria'] ?? '');
                                    if ($nombre    !== '') $sugerencias[$nombre]    = 1;
                                    if ($codigo    !== '') $sugerencias['#' . $codigo] = 1;
                                    if ($categoria !== '') $sugerencias[$categoria] = 1;
                                endforeach;
                            endif;

                            foreach (array_keys($sugerencias) as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gold w-100">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                    
                    <div class="col-md-2">
                        <a href="?url=productos&type=list" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- MENSAJES -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert dark-alert-success alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fs-4"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CUADROS DE RESUMEN -->
            <div class="row mb-4 g-3">
                <div class="col-md-4">
                    <div class="dark-card card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                            <div>
                                <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                                    <i class="fas fa-cube me-1"></i> Total Productos
                                </h6>
                                <h2 style="color: #fdc304; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= $resumen['total_productos'] ?? 0 ?></h2>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(243,156,18,0.12); display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                                <i class="fas fa-cube"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dark-card card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                            <div>
                                <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                                    <i class="fas fa-boxes me-1"></i> Stock Total
                                </h6>
                                <h2 style="color: #0d6efd; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= $resumen['total_stock'] ?? 0 ?></h2>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(13,110,253,0.12); display: flex; align-items: center; justify-content: center; color: #0d6efd; font-size: 1.5rem;">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dark-card card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                            <div>
                                <h6 class="card-title" style="color: rgb(10, 1, 1); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                                    <i class="fas fa-tags me-1"></i> Categorías
                                </h6>
                                <h2 style="color: #fa0101; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= $resumen['total_categorias'] ?? 0 ?></h2>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(220,53,69,0.12); display: flex; align-items: center; justify-content: center; color: #dc3545; font-size: 1.5rem;">
                                <i class="fas fa-tags"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA DE PRODUCTOS -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-cube me-2"></i> Productos Registrados
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">CÓDIGO</th>
                                <th class="py-3">NOMBRE</th>
                                <th class="py-3">CATEGORÍA</th>
                                <th class="py-3">ESPECIFICACIONES</th>
                                <th class="py-3">COSTO</th>
                                <th class="py-3">STOCK</th>
                                <th class="pe-4 py-3 text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($productos) && is_array($productos) && count($productos) > 0): ?>
                                <?php foreach ($productos as $p): ?>
                                    <?php 
                                    $productoKey = (string) ($p['id_producto'] ?? '');
                                    $especificaciones = $productosData[$productoKey]['especificaciones'] ?? '';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= htmlspecialchars($p['id_producto'] ?? 'N/A') ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($p['descripcion'] ?? '') ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem;">
                                                <?= htmlspecialchars($p['nombre_categoria'] ?? 'Sin categoría') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($especificaciones)): ?>
                                                <span title="<?= htmlspecialchars($especificaciones) ?>" style="cursor: help;">
                                                    <?= htmlspecialchars(substr($especificaciones, 0, 30)) ?>
                                                    <?= strlen($especificaciones) > 30 ? '...' : '' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin especificaciones</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight: 600;">
                                            $<?= number_format($p['costo_unitario'] ?? 0, 2, ',', '.') ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $stock = $p['stock'] ?? 0;
                                                if ($stock <= 5):
                                            ?>
                                                <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> <?= $stock ?>
                                                </span>
                                            <?php elseif ($stock <= 15): ?>
                                                <span class="badge" style="background: rgba(255,193,7,0.15); color: #b8860b; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <?= $stock ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <i class="fas fa-check-circle me-1"></i> <?= $stock ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="?url=productos&type=show&id=<?= $p['id_producto'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($puede_editar_producto): ?>
                                                <a href="?url=productos&type=edit&id=<?= $p['id_producto'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($puede_eliminar_producto): ?>
                                                <form method="POST" action="?url=productos&type=delete" class="d-inline">
                                                    <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-cube fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay productos registrados</p>
                                            <small>Comienza registrando un nuevo producto</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- ✅ PAGINACIÓN: TOTAL IZQ + BOTONES DER -->
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-cube me-1"></i> 
                        Total: <?= $totalRegistros ?> productos
                    </span>
                    <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
                </div>
            </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>