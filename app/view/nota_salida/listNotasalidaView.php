<?php
// app/view/nota_salida/listNotasalidaView.php
require_once __DIR__ . '/../header.php';

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
$puede_crear_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Salida', 'crear') : false;
$puede_anular_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Salida', 'eliminar') : false;

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
                            <i class="fas fa-sign-out-alt text-gold me-2"></i> Lista de Notas de Salida
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona las notas de salida de productos
                        </small>
                    </div>
                    <div class="col-auto">
                        <?php if ($puede_crear_nota): ?>
                        <a href="?url=notasalida&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Nota de Salida
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
                    <input type="hidden" name="url" value="notasalida">
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
                               list="listaNotasSalida"
                               placeholder="Buscar por cliente, encargado o ID..."
                               value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        <datalist id="listaNotasSalida">
                            <?php if (isset($notas) && is_array($notas)): ?>
                                <?php foreach ($notas as $n): 
                                    $nombreCliente = !empty($n['cliente_razon_social']) 
                                        ? $n['cliente_razon_social'] 
                                        : trim(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                                    $nombreEncargado = trim(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? ''));
                                ?>
                                    <option value="<?= htmlspecialchars($nombreCliente) ?>">
                                    <option value="<?= htmlspecialchars($nombreEncargado) ?>">
                                    <option value="<?= $n['id_nota_salida'] ?>">
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </datalist>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gold w-100">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                    
                    <div class="col-md-2">
                        <a href="?url=notasalida&type=list" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- MENSAJES -->
            <?php if (isset($success)): ?>
                <div class="alert dark-alert-success alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <span><?= $success ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span><?= $error ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CUADROS DE RESUMEN -->
            <div class="row mb-4 g-3">
                <div class="col-md-6">
                    <div class="dark-card card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                            <div>
                                <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                                    <i class="fas fa-file-invoice me-1"></i> Total Notas
                                </h6>
                                <h2 style="color: #fdc304; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= $totalRegistros ?></h2>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(243,156,18,0.12); display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="dark-card card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                            <div>
                                <h6 class="card-title" style="color: rgb(10, 1, 1); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                                    <i class="fas fa-ban me-1"></i> Anuladas
                                </h6>
                                <h2 style="color: #fa0101; font-weight: 700; font-size: 2.2rem; margin: 0;">
                                    <?= (int) ($resumen['total_anuladas'] ?? 0) ?>
                                </h2>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(220,53,69,0.12); display: flex; align-items: center; justify-content: center; color: #dc3545; font-size: 1.5rem;">
                                <i class="fas fa-ban"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA DE NOTAS DE SALIDA -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-list me-2"></i> Notas Registradas
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0" id="tablaNotas">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Fecha</th>
                                <th class="py-3">Cliente</th>
                                <th class="py-3">Tipo</th>
                                <th class="py-3">Productos</th>
                                <th class="py-3">Categorías</th>
                                <th class="py-3">Unidades</th>
                                <th class="py-3">Encargado</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyNotas">
                            <?php if (empty($notas)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay notas de salida registradas</p>
                                            <small>Comienza registrando una nueva nota</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($notas as $n): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?= $n['id_nota_salida'] ?></td>
                                        <td><?= formatoFechaVenezuela($n['fecha']) ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($n['cliente_razon_social'])) {
                                                echo htmlspecialchars($n['cliente_razon_social']);
                                            } else {
                                                echo htmlspecialchars(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: <?= ($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? 'rgba(13,110,253,0.15)' : 'rgba(40,167,69,0.15)' ?>; color: <?= ($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? '#0d6efd' : '#28a745' ?>; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                <?= $n['tipo_cliente'] ?? 'Natural' ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($n['productos_lista'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($n['categorias_lista'] ?? 'N/A') ?></td>
                                        <td class="fw-bold"><?= $n['total_unidades'] ?? 0 ?></td>
                                        <td>
                                            <?php 
                                            echo htmlspecialchars(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? ''));
                                            ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="?url=notasalida&type=show&id=<?= $n['id_nota_salida'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($puede_anular_nota): ?>
                                                <button type="button" class="btn-action-circle btn-delete" 
                                                        data-bs-toggle="modal" data-bs-target="#anularModal"
                                                        data-id="<?= $n['id_nota_salida'] ?>"
                                                        data-cliente="<?php 
                                                            if (!empty($n['cliente_razon_social'])) {
                                                                echo htmlspecialchars($n['cliente_razon_social']);
                                                            } else {
                                                                echo htmlspecialchars(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                                                            }
                                                        ?>"
                                                        title="Anular Nota">
                                                    <i class="fas fa-ban"></i>
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
                
                <!-- ✅ PAGINACIÓN: TOTAL IZQ + BOTONES DER -->
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-file-invoice me-1"></i> 
                        Total: <?= $totalRegistros ?> notas
                    </span>
                    <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
                </div>
            </div>
</div>

<!-- MODAL ANULAR -->
<?php if ($puede_anular_nota): ?>
<div class="modal fade" id="anularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #0d0d14; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
            <div class="modal-header" style="background: rgba(220,53,69,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Anular Nota de Salida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.3;"></button>
            </div>
            <form method="POST" action="?url=notasalida&type=eliminar">
                <div class="modal-body" style="color: rgba(255,255,255,0.8);">
                    <p>¿Estás seguro de anular esta nota de salida?</p>
                    <p><strong style="color: rgba(255,255,255,0.5);">Cliente:</strong> <span id="modalCliente" style="color: #ffffff;"></span></p>
                    <div class="alert" style="background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.12); color: #ea868f; border-radius: 12px; padding: 12px 16px;">
                        <i class="fas fa-info-circle me-2"></i> 
                        Al anular, el stock se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Motivo de Anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo_eliminacion" class="form-control" rows="3" required 
                                  placeholder="Describe el motivo de la anulación..."
                                  style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 12px 16px;"></textarea>
                    </div>
                    <input type="hidden" name="id_nota_salida" id="modalNotaId">
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.04);">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 8px 20px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" style="background: #dc3545; border: none; color: #fff; border-radius: 50px; padding: 8px 20px; font-weight: 600;">
                        <i class="fas fa-ban me-1"></i> Anular
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alertElement = document.querySelector('.alert');
    if (alertElement) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertElement);
            if (bsAlert) bsAlert.close();
        }, 5000);
    }

    const anularModal = document.getElementById('anularModal');
    if (anularModal) {
        anularModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const cliente = button.getAttribute('data-cliente');
            
            document.getElementById('modalNotaId').value = id;
            document.getElementById('modalCliente').textContent = cliente;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>