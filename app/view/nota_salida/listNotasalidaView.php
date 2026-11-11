<?php
require_once __DIR__ . '/../header.php';

use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

if (!isset($db) || $db === null) {
    try { $db = (new ConnectDB())->getConnection(); } catch (Exception $e) { $db = null; }
}

$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$puede_crear_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Salida', 'crear') : false;
$puede_anular_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Salida', 'eliminar') : false;

$id_usuario_actual = (int)($_SESSION['id_usuario'] ?? 0);
$caja_cerrada = false;
if ($db) {
    $stmtCheck = $db->prepare("SELECT 1 FROM cierre_caja WHERE fecha_cierre = CURDATE() AND id_usuario = :id LIMIT 1");
    $stmtCheck->execute([':id' => $id_usuario_actual]);
    $caja_cerrada = (bool)$stmtCheck->fetchColumn();
}

$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($notas) ? count($notas) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;

$esVistaEliminados = !empty($_REQUEST['ver_anuladas']);
?>

<style>
.btn-action-circle { width: 36px; height: 36px; border-radius: 50%; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; padding: 0; font-size: 0.85rem; color: #ffffff; text-decoration: none; }
.btn-action-circle:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); filter: brightness(1.1); }
.btn-action-circle.btn-view { background: rgba(13,110,253,0.15); color: #0d6efd; }
.btn-action-circle.btn-view:hover { background: #0d6efd; color: #fff; }
.btn-action-circle.btn-delete { background: rgba(220,53,69,0.15); color: #dc3545; }
.btn-action-circle.btn-delete:hover { background: #dc3545; color: #fff; }
</style>

<div class="col-md-8 col-lg-12">
            
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-sign-out-alt text-gold me-2"></i> 
                    <?= $esVistaEliminados ? 'Notas de Salida Anuladas' : 'Lista de Notas de Salida' ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= $esVistaEliminados ? 'Notas inactivas o anuladas permanentemente' : 'Gestiona las notas de salida de productos' ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if (!$esVistaEliminados): ?>
                    <?php if ($puede_crear_nota && !$caja_cerrada): ?>
                    <a href="?url=notasalida&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                        <i class="fas fa-plus me-1"></i> Registrar Nota de Salida
                    </a>
                    <?php elseif ($caja_cerrada): ?>
                        <button class="btn btn-secondary" disabled style="border-radius: 50px; padding: 8px 22px; opacity: 0.7;">
                            <i class="fas fa-lock me-1"></i> Caja Cerrada Hoy
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form id="formFiltroNotasSalida" method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="notasalida">
            <input type="hidden" name="type" value="list">
            <?php if ($esVistaEliminados): ?><input type="hidden" name="ver_anuladas" value="1"><?php endif; ?>
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-no-search="true">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark mb-0">Buscar</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       list="listaNotasSalida"
                       placeholder="Buscar por cliente, cédula, encargado o ID..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaNotasSalida">
                    <?php if (isset($notas) && is_array($notas)): ?>
                        <?php foreach ($notas as $n): 
                            $nombreCliente = !empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : trim(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                            $nombreEncargado = trim(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? ''));
                        ?>
                            <option value="<?= htmlspecialchars($nombreCliente) ?>">
                            <option value="<?= htmlspecialchars($n['cedula'] ?? '') ?>">
                            <option value="<?= htmlspecialchars($nombreEncargado) ?>">
                            <option value="<?= $n['id_nota_salida'] ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
            </div>
            
            <div class="col-md-7 d-flex flex-column flex-sm-row gap-2">
                <button type="submit" class="btn btn-gold flex-fill">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
                <a href="?url=notasalida&amp;type=list" class="btn btn-secondary flex-fill">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=notasalida&amp;type=list" class="btn btn-secondary flex-fill">
                        <i class="fas fa-check-circle me-1"></i> Ver Activas
                    </a>
                <?php else: ?>
                    <a href="?url=notasalida&amp;type=list&amp;ver_anuladas=1" class="btn btn-danger flex-fill">
                        <i class="fas fa-ban me-1"></i> Ver Anuladas
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <?php
    $mapaTipos = [
        'success' => ['clase' => 'dark-alert-success', 'icono' => 'fa-check-circle'],
        'danger'  => ['clase' => 'dark-alert-danger',  'icono' => 'fa-exclamation-circle'],
        'error'   => ['clase' => 'dark-alert-danger',  'icono' => 'fa-exclamation-circle'],
        'warning' => ['clase' => 'dark-alert-warning', 'icono' => 'fa-exclamation-triangle'],
        'info'    => ['clase' => 'dark-alert-info',    'icono' => 'fa-info-circle'],
    ];
    $estiloMsg = $mapaTipos[$tipo_mensaje ?? 'info'] ?? $mapaTipos['info'];
    ?>
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?= $estiloMsg['clase'] ?> alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas <?= $estiloMsg['icono'] ?> me-3"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-sign-out-alt me-1"></i> Total de Salidas
                        </h6>
                        <h2 style="color: #fdc304; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            <?= (int) ($resumen['total_notas'] ?? $totalRegistros ?? 0) ?>
                        </h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(243,156,18,0.12); display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                        <i class="fas fa-sign-out-alt"></i>
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

    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-list me-2"></i> 
                <?= $esVistaEliminados ? 'Notas Anuladas' : 'Notas Registradas' ?>
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="tablaNotas">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">FECHA</th>
                        <th class="py-3">CLIENTE</th>
                        <th class="py-3">TIPO</th>
                        <th class="py-3 text-center">PRODUCTOS</th>
                        <th class="py-3 text-center">CATEGORÍAS</th>
                        <th class="py-3 text-center">UNIDADES</th>
                        <th class="py-3">ENCARGADO</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="tbodyNotas">
                    <?php if (empty($notas)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">
                                        <?= $esVistaEliminados ? 'No hay notas anuladas' : 'No hay notas de salida registradas' ?>
                                    </p>
                                    <small><?= $esVistaEliminados ? 'Las notas activas están en la lista principal' : 'Comienza registrando una nueva nota' ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notas as $n): ?>
                            <?php $anulada = ($n['eliminado'] == 1); ?>
                            <tr <?= $anulada ? 'style="opacity: 0.6;"' : '' ?>>
                                <td class="ps-4"><?= formatoFechaVenezuela($n['fecha']) ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;">
                                        <?= htmlspecialchars(!empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : (($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''))) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?= ($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? 'rgba(13,110,253,0.15)' : 'rgba(40,167,69,0.15)' ?>; color: <?= ($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? '#0d6efd' : '#28a745' ?>; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                        <?= $n['tipo_cliente'] ?? 'Natural' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: #1a1a2e; font-size: 1.05rem;">
                                        <?= (int)($n['total_productos'] ?? 0) ?>
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Prod.</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: #6c757d; font-size: 1.05rem;">
                                        <?= (int)($n['total_categorias'] ?? 0) ?>
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Cat.</small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: #1a1a2e; font-size: 1.05rem;">
                                        <?= (int)($n['total_unidades'] ?? 0) ?>
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">Und.</small>
                                </td>
                                <td><?= htmlspecialchars(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? '')) ?></td>
                                
                                <td class="text-center">
                                    <?php if ($anulada): ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            Anulada
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            Activa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Botón ver detalle -->
                                        <?php if ($anulada): ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Motivo de Anulación"
                                                    data-documento="#<?= $n['id_nota_salida'] ?>"
                                                    data-producto="<?= htmlspecialchars(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? '')) ?>"
                                                    data-motivo="<?= htmlspecialchars($n['motivo_anulacion'] ?? 'No se registró un motivo.', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-usuario="<?= htmlspecialchars(($n['usuario_nombre'] ?? 'N/A') . ' ' . ($n['usuario_apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                    data-fecha="<?= !empty($n['fecha_anulacion']) ? date('d/m/Y h:i A', strtotime($n['fecha_anulacion'])) : 'N/A' ?>"
                                                    data-cantidad="<?= (int)($n['total_unidades'] ?? 0) ?>"
                                                    onclick="verMotivoAnulacion(this)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-action-circle btn-view" title="Ver Detalle" onclick="verDetalleNota(<?= (int) $n['id_nota_salida'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (!$anulada && $puede_anular_nota): ?>
                                            <button type="button" class="btn-action-circle btn-delete" title="Anular Nota" onclick="confirmarAnularNota(<?= (int) $n['id_nota_salida'] ?>, '<?= htmlspecialchars(addslashes(!empty($n['cliente_razon_social']) ? $n['cliente_razon_social'] : (($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? '')))) ?>')">
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
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                Total: <?= $totalRegistros ?> notas
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>

<!-- 1. MODAL PARA VER MOTIVO DE ANULACIÓN -->
<div class="modal fade" id="modalMotivoAnulacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Detalle de Anulación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 24px;">
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Documento:</span>
                    <div class="fs-5 fw-bold text-dark" id="modal-motivo-doc"></div>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Cliente:</span>
                    <div class="fs-6 text-dark" id="modal-motivo-prod"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <span class="text-muted small fw-bold text-uppercase">Anulada por:</span>
                        <div class="fs-6 text-dark fw-semibold" id="modal-motivo-usuario"></div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small fw-bold text-uppercase">Cant. Productos:</span>
                        <div class="fs-6 text-danger fw-bold" id="modal-motivo-cantidad"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Fecha y Hora de Anulación:</span>
                    <div class="fs-6 text-dark" id="modal-motivo-fecha"></div>
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

<!-- 2. MODAL PARA VER DETALLE DE NOTA ACTIVA -->
<div class="modal fade" id="modalVerNota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> 
                    Detalle de Nota de Salida <span id="ver-nota-id" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <div class="modal-body" style="padding: 20px 24px;">
                <div id="ver-nota-cargando" class="text-center py-5">
                    <div class="spinner-border" style="color: #f39c12;" role="status"></div>
                    <p class="mt-3" style="color: #6c757d;">Cargando información de la nota...</p>
                </div>
                <div id="ver-nota-error" class="text-center py-5" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3" style="color: #dc3545;"></i>
                    <p id="ver-nota-error-msg" style="color: #6c757d;">Error al cargar la nota</p>
                </div>
                <div id="ver-nota-contenido" style="display: none;">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 14px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos de la Nota
                                </h6>
                                <div class="mb-2">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Fecha</label>
                                    <p id="ver-nota-fecha" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                                </div>
                                <div class="mb-0">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Encargado</label>
                                    <p id="ver-nota-encargado" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 14px;">
                                    <i class="fas fa-user me-2" style="color: #f39c12;"></i> Cliente
                                </h6>
                                <div class="mb-2">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Nombre</label>
                                    <p id="ver-nota-cliente" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                                </div>
                                <div class="mb-0">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Documento</label>
                                    <p id="ver-nota-documento" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card" style="border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3" style="background: #1a1a2e; border: none;">
                            <h6 class="m-0" style="color: #ffffff; font-weight: 700;">
                                <i class="fas fa-box me-2" style="color: #f39c12;"></i> Productos de la Nota
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0" style="table-layout: fixed; width: 100%;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th class="ps-4 py-3 text-center" style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 8%;">#</th>
                                        <th class="py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 52%;">PRODUCTO</th>
                                        <th class="py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 25%;">CATEGORÍA</th>
                                        <th class="pe-4 text-center py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 15%;">CANTIDAD</th>
                                    </tr>
                                </thead>
                                <tbody id="ver-nota-productos-tbody">
                                </tbody>
                                <tfoot>
                                    <tr style="border-top: 2px solid rgba(243,156,18,0.2); background: #f8f9fa;">
                                        <th colspan="3" class="ps-4 py-3 text-end" style="color: #495057; font-size: 0.85rem;">Total Unidades:</th>
                                        <th class="pe-4 text-center py-3" id="ver-nota-total-unidades" style="color: #f39c12; font-size: 1.1rem; font-weight: 700;">0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-danger me-auto" onclick="imprimirNotaPDF()" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-file-pdf me-1"></i> Imprimir esta Nota
                </button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 3. MODAL PARA ANULAR NOTA -->
<?php if ($puede_anular_nota): ?>
<div class="modal fade" id="modalAnularNota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Anular Nota de Salida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form method="POST" action="?url=notasalida&type=eliminar">
                <input type="hidden" name="id_nota_salida" id="anular-nota-id">
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 16px; font-weight: 500;">¿Estás seguro de anular esta nota de salida?</p>
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Cliente de la nota</strong>
                        <span id="anular-nota-cliente" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>
                    <div class="alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px; margin-bottom: 16px;">
                        <i class="fas fa-info-circle me-2"></i> Al anular, el stock de los productos se <strong>revertirá automáticamente</strong> y la nota no se podrá reactivar.
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold" style="color: #1a1a2e; font-size: 0.85rem;">Motivo de Anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo_eliminacion" class="form-control" rows="3" required placeholder="Describe el motivo de la anulación..." style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); resize: vertical;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">Sí, anular</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Filtro AJAX
    const formNotasSalida = document.getElementById('formFiltroNotasSalida');
    if (formNotasSalida) {
        const enviarFiltroNotas = function () {
            const formData = new FormData(formNotasSalida);
            formData.set('ajax', '1');

            fetch('?url=notasalida&type=list', { method: 'POST', body: formData })
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
            .catch(() => formNotasSalida.submit());
        };

        formNotasSalida.addEventListener('submit', function (e) {
            e.preventDefault();
            enviarFiltroNotas();
        });
        formNotasSalida.querySelectorAll('select').forEach(function (selectEl) {
            selectEl.addEventListener('change', function () { enviarFiltroNotas(); });
        });
    }

    window.confirmarAnularNota = function(id, cliente) {
        document.getElementById('anular-nota-id').value = id;
        document.getElementById('anular-nota-cliente').textContent = cliente || 'Nota #' + id;
        new bootstrap.Modal(document.getElementById('modalAnularNota')).show();
    };
});

// 1. Función para abrir el modal de motivo de anulación
function verMotivoAnulacion(btn) {
    document.getElementById('modal-motivo-doc').textContent = btn.getAttribute('data-documento');
    document.getElementById('modal-motivo-prod').textContent = btn.getAttribute('data-producto');
    document.getElementById('modal-motivo-usuario').textContent = btn.getAttribute('data-usuario');
    document.getElementById('modal-motivo-fecha').textContent = btn.getAttribute('data-fecha');
    document.getElementById('modal-motivo-cantidad').textContent = btn.getAttribute('data-cantidad') + ' unidades';
    document.getElementById('modal-motivo-texto').textContent = btn.getAttribute('data-motivo');
    
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMotivoAnulacion')).show();
}

// 2. Función para abrir el modal de ver detalle (notas activas)
window.verDetalleNota = function(id) {
    document.getElementById('ver-nota-cargando').style.display = 'block';
    document.getElementById('ver-nota-error').style.display = 'none';
    document.getElementById('ver-nota-contenido').style.display = 'none';
    document.getElementById('ver-nota-id').textContent = '#' + id;

    new bootstrap.Modal(document.getElementById('modalVerNota')).show();

    fetch('?url=notasalida&type=detalle_json&id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('ver-nota-cargando').style.display = 'none';
            
            if (!data.success) {
                document.getElementById('ver-nota-error').style.display = 'block';
                document.getElementById('ver-nota-error-msg').textContent = data.error || 'Error al cargar la nota';
                return;
            }

            const nota = data.nota;
            document.getElementById('ver-nota-contenido').style.display = 'block';

            document.getElementById('ver-nota-fecha').textContent = Validador.formatearFecha(nota.fecha);
            document.getElementById('ver-nota-encargado').textContent = nota.usuario_responsable || 'N/A';
            document.getElementById('ver-nota-cliente').textContent = nota.cliente || 'N/A';
            document.getElementById('ver-nota-documento').textContent = nota.documento || 'N/A';

            const tbody = document.getElementById('ver-nota-productos-tbody');
            tbody.innerHTML = '';
            let totalUnidades = 0;

            if (nota.detalles && nota.detalles.length > 0) {
                nota.detalles.forEach(function(d, index) {
                    let cantidad = parseInt(d.cantidad) || 0;
                    totalUnidades += cantidad;

                    const tr = document.createElement('tr');
                    
                    const tdNum = document.createElement('td');
                    tdNum.className = 'ps-4 text-center';
                    tdNum.style.color = '#6c757d';
                    tdNum.style.fontWeight = '600';
                    tdNum.textContent = (index + 1);

                    const tdProd = document.createElement('td');
                    tdProd.style.color = '#1a1a2e';
                    tdProd.style.fontWeight = '600';
                    tdProd.textContent = d.nombre_producto;

                    const tdCat = document.createElement('td');
                    tdCat.style.color = '#6c757d';
                    tdCat.textContent = d.nombre_categoria || 'Sin categoría';

                    const tdCant = document.createElement('td');
                    tdCant.className = 'pe-4 text-center fw-bold';
                    tdCant.style.color = '#f39c12';
                    tdCant.style.fontSize = '1.1rem';
                    tdCant.textContent = cantidad;

                    tr.appendChild(tdNum);
                    tr.appendChild(tdProd);
                    tr.appendChild(tdCat);
                    tr.appendChild(tdCant);
                    
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-3" style="color: #6c757d;"><i class="fas fa-box-open me-2"></i> No hay productos en esta nota</td></tr>`;
            }
            
            document.getElementById('ver-nota-total-unidades').textContent = totalUnidades;
        })
        .catch(err => {
            document.getElementById('ver-nota-cargando').style.display = 'none';
            document.getElementById('ver-nota-error').style.display = 'block';
            document.getElementById('ver-nota-error-msg').textContent = 'Error de conexión: ' + err.message;
        });
};

window.imprimirNotaPDF = function() {
    var textoId = document.getElementById('ver-nota-id').textContent;
    var idNota = textoId.replace('#', '').trim();

    if (idNota) {
        window.open('?url=notasalida&type=generar_pdf&id=' + idNota, '_blank');
    } else {
        alert("Error: No se pudo identificar la nota.");
    }
};
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>