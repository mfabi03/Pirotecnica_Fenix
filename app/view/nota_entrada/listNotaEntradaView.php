<?php
// app/view/nota_entrada/listNotaEntradaView.php
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
$puede_crear_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Entrada', 'crear') : false;
$puede_editar_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Entrada', 'actualizar') : false;
$puede_anular_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Entrada', 'eliminar') : false;

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($notas) ? count($notas) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
            
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-sign-in-alt text-gold me-2"></i> Lista de Notas de Entrada
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    Gestiona las notas de entrada de productos
                </small>
            </div>
            <div class="col-auto">
                <?php if ($puede_crear_nota): ?>
                <a href="?url=notaentrada&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                    <i class="fas fa-plus me-1"></i> Registrar Nota de Entrada
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="notaentrada">
            <input type="hidden" name="type" value="list">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-no-search="true" onchange="this.form.submit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Estado</label>
                <select name="estado_nota" class="form-select form-select-sm" data-no-search="true" onchange="this.form.submit()">
                    <option value="todos" <?= (($estado_nota ?? 'todos') === 'todos') ? 'selected' : '' ?>>Todos</option>
                    <option value="activos" <?= (($estado_nota ?? 'todos') === 'activos') ? 'selected' : '' ?>>Activos</option>
                    <option value="inactivos" <?= (($estado_nota ?? 'todos') === 'inactivos') ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>

            <div class="col-md-4">
                <input type="text" name="busqueda" class="form-control form-control-sm"
                       list="listaNotasEntrada"
                       placeholder="Buscar por proveedor, encargado o ID..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaNotasEntrada">
                    <?php
                    $sugerencias = [];
                    if (!empty($notas) && is_array($notas)):
                        foreach ($notas as $n):
                            $prov = trim($n['razon_social'] ?? '');
                            $enc  = trim($n['encargado_nombre'] ?? '');
                            if ($prov !== '') $sugerencias[$prov] = 1;
                            if ($enc  !== '') $sugerencias[$enc]  = 1;
                            $sugerencias[(string)$n['id_nota_entrada']] = 1;
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
                <a href="?url=notaentrada&type=list" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- MENSAJES -->
    <?php if (isset($success)): ?>
        <div class="alert dark-alert-success alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <span><?= htmlspecialchars($success) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert dark-alert-danger alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span><?= htmlspecialchars($error) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
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
                            <i class="fas fa-file-invoice me-1"></i> Total Notas
                        </h6>
                        <h2 style="color: #fdc304; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= $resumen['total_notas'] ?? 0 ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(243,156,18,0.12); display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-dollar-sign me-1"></i> Total Compras
                        </h6>
                        <h2 style="color: #28a745; font-weight: 700; font-size: 2.2rem; margin: 0;">$<?= number_format($resumen['total_compras'] ?? 0, 2) ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(40,167,69,0.12); display: flex; align-items: center; justify-content: center; color: #28a745; font-size: 1.5rem;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(10, 1, 1); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-ban me-1"></i> Anuladas
                        </h6>
                        <h2 style="color: #fa0101; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= (int) ($resumen['total_anuladas'] ?? 0) ?></h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(220,53,69,0.12); display: flex; align-items: center; justify-content: center; color: #dc3545; font-size: 1.5rem;">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-list me-2"></i> Notas Registradas
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Fecha</th>
                        <th class="py-3">Proveedor</th>
                        <th class="py-3">Encargado</th>
                        <th class="py-3">Productos</th>
                        <th class="py-3">Total</th>
                        <th class="py-3">Status</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">No hay notas de entrada registradas</p>
                                    <small>Comienza registrando una nueva nota</small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notas as $n): ?>
                            <?php 
                            $anulada = (int) ($n['eliminado'] ?? 0) === 1;
                            ?>
                            <tr <?= $anulada ? 'style="opacity: 0.6;"' : '' ?>>
                                <td class="ps-4"><?= formatoFechaVenezuela($n['fecha_ingreso']) ?></td>
                                <td><?= htmlspecialchars($n['razon_social'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($n['encargado_nombre'] ?? 'Sin asignar') ?></td>
                                <td>
                                    <?php if (!empty($n['productos_lista'])): ?>
                                        <?php 
                                        $productos = explode(', ', $n['productos_lista']);
                                        $totalProd = count($productos);
                                        ?>
                                        <?php for ($i = 0; $i < min(3, $totalProd); $i++): ?>
                                            <span class="badge" style="background: #e9ecef; color: #495057; padding: 4px 10px; border-radius: 50px; font-weight: 500; font-size: 0.7rem;"><?= htmlspecialchars($productos[$i]) ?></span>
                                        <?php endfor; ?>
                                        <?php if ($totalProd > 3): ?>
                                            <span class="badge" style="background: rgba(243,156,18,0.12); color: #f39c12; padding: 4px 10px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">+<?= $totalProd - 3 ?> más</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #adb5bd;">Sin productos</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold">
                                    <?php if ($anulada): ?>
                                        <del style="color: #6c757d;">$<?= number_format($n['costo_total'] ?? 0, 2) ?></del>
                                    <?php else: ?>
                                        $<?= number_format($n['costo_total'] ?? 0, 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($anulada): ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-ban me-1"></i> Inactivo / Anulado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if (!$anulada): ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                    onclick="verDetalleNotaEntrada(<?= (int) $n['id_nota_entrada'] ?>, this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($puede_editar_nota): ?>
                                                <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                        onclick="editarNotaEntrada(<?= (int) $n['id_nota_entrada'] ?>, this)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($puede_anular_nota): ?>
                                                <button type="button" class="btn-action-circle btn-delete"
                                                        onclick="prepararAnulacionNotaEntrada(<?= (int) $n['id_nota_entrada'] ?>, <?= htmlspecialchars(json_encode($n['razon_social'] ?? 'N/A', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>, this)"
                                                        title="Inactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php elseif ($puede_anular_nota): ?>
                                            <button type="button" class="btn-action-circle btn-reactivate" title="Reactivar"
                                                    onclick="reactivarNotaEntrada(<?= (int) $n['id_nota_entrada'] ?>, this)">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-file-invoice me-1"></i> 
                Total: <?= $totalRegistros ?> notas
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<?php
require_once __DIR__ . '/partials/modal_ver_nota.php';
require_once __DIR__ . '/partials/modal_anular_nota.php';
?>

<style>
    .btn-action-circle.btn-reactivate {
        background: #28a745 !important;
        color: #fff !important;
    }
    .btn-action-circle.btn-reactivate:hover {
        background: #218838 !important;
        box-shadow: 0 6px 20px rgba(40, 167, 69, .4);
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/pirotecnica_fenix/assets/js/nota_entrada.js"></script>



<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>