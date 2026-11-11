<?php
// app/view/proveedores/listProveedoresView.php
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
$puede_crear_proveedor = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Proveedores', 'crear') : false;
$puede_editar_proveedor = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Proveedores', 'actualizar') : false;
$puede_eliminar_proveedor = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Proveedores', 'eliminar') : false;
$verEliminados = !empty($_GET['ver_eliminados']);
?>

<div class="col-md-8 col-lg-12">
            
            <!-- TARJETA DE TÍTULO - FONDO OSCURO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-truck text-gold me-2"></i> <?= $verEliminados ? 'Proveedores Eliminados' : 'Lista de Proveedores' ?>
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona los proveedores registrados en el sistema
                        </small>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <?php if ($puede_crear_proveedor): ?>
                        <button type="button" class="btn btn-dark-gold" data-bs-toggle="modal" data-bs-target="#modalRegistrarProveedor" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease;">
                            <i class="fas fa-plus me-1"></i> Registrar Proveedor
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-center" autocomplete="off">
                    <input type="hidden" name="url" value="proveedores">
                    <input type="hidden" name="type" value="list">
                    <?php if ($verEliminados): ?><input type="hidden" name="ver_eliminados" value="1"><?php endif; ?>
                    
                    <!-- ✅ SELECT "MOSTRAR" ARRIBA -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                        <select name="por_pagina" class="form-select form-select-sm" data-auto-submit>
                            <?php foreach ([5, 10, 25, 50] as $o): ?>
                                <option value="<?= $o ?>" <?= (isset($por_pagina) && $por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="busqueda" class="form-control" 
                               list="listaProveedores"
                               placeholder="Buscar por RIF, razón social, contacto o dirección..."
                               value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        <datalist id="listaProveedores">
                            <?php
                            $sugerencias = [];
                            if (!empty($proveedores) && is_array($proveedores)):
                                foreach ($proveedores as $p):
                                    $rif     = trim($p['rif'] ?? '');
                                    $razon   = trim($p['razon_social'] ?? '');
                                    $contact = trim($p['numero_contacto'] ?? '');
                                    if ($rif     !== '') $sugerencias[$rif]     = 1;
                                    if ($razon   !== '') $sugerencias[$razon]   = 1;
                                    if ($contact !== '') $sugerencias[$contact] = 1;
                                endforeach;
                            endif;

                            foreach (array_keys($sugerencias) as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="col-md-7 d-flex flex-column flex-sm-row gap-2">
                        <button type="submit" class="btn btn-gold flex-fill">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                        <a href="?url=proveedores&amp;type=list" class="btn btn-secondary flex-fill">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                        <?php if ($verEliminados): ?>
                            <a href="?url=proveedores&amp;type=list" class="btn btn-secondary flex-fill js-filtro-listado-ajax">
                                <i class="fas fa-check-circle me-1"></i> Ver Activos
                            </a>
                        <?php else: ?>
                            <a href="?url=proveedores&amp;type=list&amp;ver_eliminados=1" class="btn btn-danger flex-fill js-filtro-listado-ajax">
                                <i class="fas fa-trash-alt me-1"></i> Ver Eliminados
                            </a>
                        <?php endif; ?>
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

            <?php if (isset($success) && !empty($success)): ?>
                <div class="alert <?= ($tipo_mensaje ?? 'success') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= ($tipo_mensaje ?? 'success') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABLA DE PROVEEDORES -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-truck me-2"></i> <?= $verEliminados ? 'Proveedores Eliminados' : 'Proveedores Registrados' ?>
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="py-3">RIF</th>
                                <th class="py-3">Razón Social</th>
                                <th class="py-3">Contacto</th>
                                <th class="py-3">Dirección</th>
                                <th class="py-3">Correo</th>
                                <th class="py-3">STATUS</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($proveedores) && is_array($proveedores) && count($proveedores) > 0): ?>
                                <?php foreach ($proveedores as $p): ?>
                                    <?php $proveedorEliminado = (int) ($p['eliminado'] ?? 0) === 1; ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['rif'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($p['razon_social'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($p['numero_contacto'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($p['direccion'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($p['correo_electronico'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= $proveedorEliminado ? 'bg-danger' : 'bg-success' ?>">
                                                <?= $proveedorEliminado ? 'Eliminado' : 'Activo' ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <?php if (!$proveedorEliminado): ?>
                                                    <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                            data-proveedor-action="view" data-proveedor-id="<?= (int) $p['id_proveedor'] ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($puede_editar_proveedor): ?>
                                                        <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                                data-proveedor-action="edit" data-proveedor-id="<?= (int) $p['id_proveedor'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($puede_eliminar_proveedor): ?>
                                                        <button type="button" class="btn-action-circle btn-delete"
                                                                title="Eliminar" data-proveedor-action="inactivate" data-proveedor-id="<?= (int) $p['id_proveedor'] ?>">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                            data-proveedor-action="view" data-proveedor-id="<?= (int) $p['id_proveedor'] ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($puede_eliminar_proveedor): ?>
                                                    <button type="button" class="btn-action-circle btn-reactivate"
                                                            title="Reactivar" data-proveedor-action="reactivate" data-proveedor-id="<?= (int) $p['id_proveedor'] ?>"
                                                            data-proveedor-nombre="<?= htmlspecialchars($p['razon_social'] ?? $p['rif'] ?? 'Proveedor', ENT_QUOTES, 'UTF-8') ?>">
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
                                            <i class="fas fa-truck fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay proveedores registrados</p>
                                            <small>Comienza registrando un nuevo proveedor</small>
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
                        <i class="fas fa-truck me-1"></i> 
                        Total: <?= isset($totalRegistros) ? $totalRegistros : (isset($proveedores) ? count($proveedores) : 0) ?> proveedores
                    </span>
                    <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                </div>
            </div>
</div>

<?php
require_once __DIR__ . '/partials/modal_registrar_proveedor.php';
require_once __DIR__ . '/partials/modal_ver_proveedor.php';
require_once __DIR__ . '/partials/modal_editar_proveedor.php';
require_once __DIR__ . '/partials/modal_inactivar_proveedor.php';
require_once __DIR__ . '/partials/modal_reactivar_proveedor.php';
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/pirotecnica_fenix/assets/js/filtro_listado_ajax.js"></script>
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
<script src="/pirotecnica_fenix/assets/js/proveedores.js"></script>

<?php require_once __DIR__ . '/../footer.php'; ?>