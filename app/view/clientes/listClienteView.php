<?php
// app/view/clientes/listClienteView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

// La vista no instancia PermisoHelper ni ConnectDB.
// Los permisos disponibles ya vienen calculados desde su controlador.

// Paginación
$por_pagina     = $por_pagina     ?? (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = $totalRegistros ?? (isset($clientes) ? count($clientes) : 0);
$pagina_actual  = $pagina_actual  ?? (int)($_GET['pagina'] ?? 1);
$totalPaginas   = $totalPaginas   ?? 1;

$esVistaEliminados = (($estado_cliente ?? 'todos') === 'inactivos');
?>



<div class="col-md-8 col-lg-12">
    
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-users text-gold me-2"></i> 
                    <?= htmlspecialchars((string) ($esVistaEliminados ? 'Clientes Eliminados' : 'Lista de Clientes'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= htmlspecialchars((string) ($esVistaEliminados ? 'Clientes eliminados del sistema' : 'Gestiona los clientes registrados en el sistema'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=clientes&type=list" class="btn btn-secondary" style="border-radius: 50px; padding: 8px 22px; font-weight: 600;">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Clientes Activos
                    </a>
                <?php else: ?>
                    <?php if ($puede_crear): ?>
                    <button type="button" class="btn btn-dark-gold" 
                            data-bs-toggle="modal" data-bs-target="#modalRegistrarCliente"
                            style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                        <i class="fas fa-plus me-1"></i> Registrar Cliente
                    </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form id="formFiltroClientes" method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="clientes">
            <input type="hidden" name="type" value="list">
            <input type="hidden" name="estado_cliente" value="<?= htmlspecialchars((string) ($esVistaEliminados ? 'inactivos' : 'activos'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-no-search="true">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= htmlspecialchars((string) (($por_pagina == $o) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>><?= htmlspecialchars((string) ($o), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small text-dark mb-0">Buscar</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       list="listaClientes"
                       placeholder="Nombre, cédula, RIF o razón social..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaClientes">
                    <?php
                    $sugerencias = [];
                    if (!empty($clientes) && is_array($clientes)):
                        foreach ($clientes as $c):
                            $razon  = trim($c['razon_social'] ?? '');
                            $nombre = trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                            $doc    = trim($c['cedula'] ?? $c['rif'] ?? '');
                            if ($razon  !== '') $sugerencias[$razon]  = 1;
                            if ($nombre !== '') $sugerencias[$nombre] = 1;
                            if ($doc    !== '') $sugerencias[$doc]    = 1;
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
                <a href="?url=clientes&type=list" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>

            <div class="col-md-2">
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=clientes&type=list" class="btn btn-success w-100" style="background: #28a745; border: none; color: #fff;">
                        <i class="fas fa-undo-alt me-1"></i> Ver Activos
                    </a>
                <?php else: ?>
                    <a href="?url=clientes&type=list&estado_cliente=inactivos" class="btn btn-danger w-100" style="background: #dc3545; border: none; color: #fff;">
                        <i class="fas fa-trash-alt me-1"></i> Ver Eliminados
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
        <div class="alert <?= htmlspecialchars((string) ($estiloMsg['clase']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas <?= htmlspecialchars((string) ($estiloMsg['icono']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> me-3"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-users me-2"></i> 
                <?= htmlspecialchars((string) ($esVistaEliminados ? 'Clientes Eliminados' : 'Clientes Registrados'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">Cédula / RIF</th>
                        <th class="py-3">Nombre / Razón Social</th>
                        <th class="py-3">Tipo</th>
                        <th class="py-3">Teléfono</th>
                        <th class="py-3">Correo</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($clientes) && is_array($clientes) && count($clientes) > 0): ?>
                        <?php foreach ($clientes as $c): ?>
                            <?php 
                                $nombreCliente = ($c['tipo_cliente'] ?? '') === 'Jurídico' 
                                    ? ($c['razon_social'] ?? '') 
                                    : (($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                                $estaActivo = (($c['status'] ?? 'activo') === 'activo' && ($c['eliminado'] ?? 0) == 0);
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($c['cedula'] ?? $c['rif'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (($c['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                        <strong><?= htmlspecialchars($c['razon_social'] ?? 'N/A') ?></strong>
                                    <?php else: ?>
                                        <?= htmlspecialchars(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($c['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                        <span class="badge" style="background: rgba(13,110,253,0.15); color: #0d6efd; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-building me-1"></i> Jurídico
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.15); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-user me-1"></i> Natural
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($c['telefono'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($c['correo_electronico'] ?? 'N/A') ?></td>
                                
                                <td class="text-center">
                                    <?php if ($estaActivo): ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.15); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.15); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-times-circle me-1"></i> Eliminado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                onclick="verDetalleCliente(<?= htmlspecialchars((string) ((int) $c['id_cliente']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($estaActivo): ?>
                                            <?php if ($puede_editar): ?>
                                                <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                        onclick="editarCliente(<?= htmlspecialchars((string) ((int) $c['id_cliente']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($puede_eliminar): ?>
                                                <button type="button" class="btn-action-circle btn-delete" title="Eliminar"
                                                        onclick="confirmarEliminarCliente(<?= htmlspecialchars((string) ((int) $c['id_cliente']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>, '<?= htmlspecialchars(addslashes($nombreCliente)) ?>')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($puede_editar): ?>
                                                <button type="button" class="btn-action-circle btn-reactivar" title="Reactivar Cliente"
                                                        onclick="confirmarReactivarCliente(<?= htmlspecialchars((string) ((int) $c['id_cliente']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>, '<?= htmlspecialchars(addslashes($nombreCliente)) ?>')">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-users fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">
                                        <?= htmlspecialchars((string) ($esVistaEliminados ? 'No hay clientes eliminados' : 'No hay clientes registrados'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </p>
                                    <small><?= htmlspecialchars((string) ($esVistaEliminados ? 'Los clientes activos están en la lista principal' : 'Comienza registrando un nuevo cliente'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-users me-1"></i> 
                Total: <?= htmlspecialchars((string) ($totalRegistros), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> clientes
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/registroClientView.php'; ?>
<?php require_once __DIR__ . '/detalleClientView.php'; ?>
<?php require_once __DIR__ . '/editarClientView.php'; ?>
<?php require_once __DIR__ . '/eliminarClientView.php'; ?>

<?php if (!empty($_SESSION['errores_campo']) && !empty($_SESSION['form_modal'])): 
    $erroresCampo = $_SESSION['errores_campo'];
    $formModal    = $_SESSION['form_modal'];
    $formData     = $_SESSION['form_data'] ?? [];
    unset($_SESSION['errores_campo'], $_SESSION['form_modal'], $_SESSION['form_data']);
?>
<script id="clientesModalState" type="application/json">
<?= json_encode(
    [
        'formModal' => $formModal,
        'formData' => $formData,
        'errores' => $erroresCampo,
    ],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>
<?php endif; ?>

<script id="clientesData" type="application/json">
<?= json_encode(
    isset($clientes) && is_array($clientes) ? array_values($clientes) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>



<?php $scriptsModulo = ['clientesVista.js', 'clientesjquery.js']; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>