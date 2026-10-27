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
?>

<div class="col-md-8 col-lg-12">
            
            <!-- TARJETA DE TÍTULO - FONDO OSCURO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-truck text-gold me-2"></i> Lista de Proveedores
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
                    
                    <!-- ✅ SELECT "MOSTRAR" ARRIBA -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                        <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ([5, 10, 25, 50] as $o): ?>
                                <option value="<?= $o ?>" <?= (isset($por_pagina) && $por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
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
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gold w-100">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                    
                    <div class="col-md-2">
                        <a href="?url=proveedores&type=list" class="btn btn-secondary w-100">
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

            <?php if (isset($success) && !empty($success)): ?>
                <div class="alert dark-alert-success alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fs-4"></i>
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
                        <i class="fas fa-truck me-2"></i> Proveedores Registrados
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">RIF</th>
                                <th class="py-3">Razón Social</th>
                                <th class="py-3">Contacto</th>
                                <th class="py-3">Dirección</th>
                                <th class="py-3">Correo</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($proveedores) && is_array($proveedores) && count($proveedores) > 0): ?>
                                <?php foreach ($proveedores as $p): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($p['id_proveedor'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($p['rif'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($p['razon_social'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($p['numero_contacto'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($p['direccion'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($p['correo_electronico'] ?? 'N/A') ?></td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                        onclick="verDetalleProveedor(<?= (int) $p['id_proveedor'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if ($puede_editar_proveedor): ?>
                                                <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                        onclick="editarProveedor(<?= (int) $p['id_proveedor'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($puede_eliminar_proveedor): ?>
                                                <button type="button" class="btn-action-circle btn-delete"
                                                        title="Eliminar" onclick="confirmarEliminarProveedor(<?= (int) $p['id_proveedor'] ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
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

<?php if ($puede_crear_proveedor): ?>
<div class="modal fade" id="modalRegistrarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-truck me-2" style="color: #f39c12;"></i> Registrar Nuevo Proveedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form method="POST" action="?url=proveedores&type=store" class="proveedor-form">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="registrar-proveedor-rif" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                            <input type="text" id="registrar-proveedor-rif" name="rif" class="form-control proveedor-rif" placeholder="Ej: J-123456789" required>
                        </div>
                        <div class="col-md-6">
                            <label for="registrar-proveedor-telefono" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Número de Contacto *</label>
                            <input type="tel" id="registrar-proveedor-telefono" name="numero_contacto" class="form-control proveedor-telefono" placeholder="0412-1234567" required>
                        </div>
                        <div class="col-12">
                            <label for="registrar-proveedor-razon" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                            <input type="text" id="registrar-proveedor-razon" name="razon_social" class="form-control proveedor-razon" placeholder="Nombre de la empresa" required>
                        </div>
                        <div class="col-md-6">
                            <label for="registrar-proveedor-correo" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico</label>
                            <input type="email" id="registrar-proveedor-correo" name="correo_electronico" class="form-control" placeholder="correo@empresa.com">
                        </div>
                        <div class="col-md-6">
                            <label for="registrar-proveedor-direccion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                            <textarea id="registrar-proveedor-direccion" name="direccion" class="form-control" rows="2" required style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-save me-1"></i> Registrar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modalVerProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <div class="modal-body" style="padding: 20px 24px;">
                <div class="text-center mb-4">
                    <h4 id="ver-proveedor-razon-titulo" style="color: #f39c12; font-weight: 700; margin-bottom: 6px;">—</h4>
                    <span id="ver-proveedor-rif-badge" class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 6px 16px; border-radius: 50px; font-size: 0.75rem;">—</span>
                </div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;"><i class="fas fa-building me-2" style="color: #f39c12;"></i> Información del Proveedor</h6>
                            <div class="mb-3"><label class="text-muted small fw-bold">RIF</label><p id="ver-proveedor-rif" class="mb-0 fw-semibold">—</p></div>
                            <div class="mb-0"><label class="text-muted small fw-bold">Razón Social</label><p id="ver-proveedor-razon" class="mb-0 fw-semibold">—</p></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;"><i class="fas fa-address-book me-2" style="color: #f39c12;"></i> Datos de Contacto</h6>
                            <div class="mb-3"><label class="text-muted small fw-bold">Teléfono</label><p id="ver-proveedor-telefono" class="mb-0">—</p></div>
                            <div class="mb-3"><label class="text-muted small fw-bold">Correo Electrónico</label><p id="ver-proveedor-correo" class="mb-0">—</p></div>
                            <div class="mb-0"><label class="text-muted small fw-bold">Dirección</label><p id="ver-proveedor-direccion" class="mb-0">—</p></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;"><i class="fas fa-times me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php if ($puede_editar_proveedor): ?>
<div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-edit me-2" style="color: #f39c12;"></i> Editar Proveedor: <span id="editar-proveedor-razon-header" style="color: #f39c12;">—</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form method="POST" action="?url=proveedores&type=update" class="proveedor-form">
                <input type="hidden" name="id_proveedor" id="editar-proveedor-id">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editar-proveedor-rif" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                            <input type="text" id="editar-proveedor-rif" name="rif" class="form-control proveedor-rif" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editar-proveedor-telefono" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Número de Contacto *</label>
                            <input type="tel" id="editar-proveedor-telefono" name="numero_contacto" class="form-control proveedor-telefono" required>
                        </div>
                        <div class="col-12">
                            <label for="editar-proveedor-razon" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                            <input type="text" id="editar-proveedor-razon" name="razon_social" class="form-control proveedor-razon" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editar-proveedor-correo" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico</label>
                            <input type="email" id="editar-proveedor-correo" name="correo_electronico" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="editar-proveedor-direccion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                            <textarea id="editar-proveedor-direccion" name="direccion" class="form-control" rows="2" required style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;"><i class="fas fa-save me-1"></i> Actualizar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($puede_eliminar_proveedor): ?>
<div class="modal fade" id="modalEliminarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Eliminar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form method="POST" action="?url=proveedores&type=delete">
                <input type="hidden" name="id_proveedor" id="eliminar-proveedor-id">
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px;">¿Estás seguro de eliminar este proveedor?</p>
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong class="text-muted d-block small text-uppercase mb-1">Proveedor a eliminar</strong>
                        <span id="eliminar-proveedor-nombre" style="color: #1a1a2e; font-weight: 600;">—</span>
                    </div>
                    <div class="alert alert-danger mb-0" role="alert"><i class="fas fa-info-circle me-2"></i>La eliminación se bloqueará si existen notas de entrada asociadas.</div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;"><i class="fas fa-trash-alt me-1"></i> Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script id="proveedoresData" type="application/json">
<?= json_encode(isset($proveedores) && is_array($proveedores) ? array_values($proveedores) : [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const proveedoresData = JSON.parse(document.getElementById('proveedoresData').textContent);
    const proveedoresPorId = {};
    proveedoresData.forEach(function(proveedor) {
        proveedoresPorId[parseInt(proveedor.id_proveedor, 10)] = proveedor;
    });

    window.verDetalleProveedor = function(id) {
        const proveedor = proveedoresPorId[id];
        if (!proveedor) return;

        document.getElementById('ver-proveedor-razon-titulo').textContent = proveedor.razon_social || 'Proveedor sin razón social';
        document.getElementById('ver-proveedor-rif-badge').textContent = proveedor.rif || 'Sin RIF';
        document.getElementById('ver-proveedor-rif').textContent = proveedor.rif || 'N/A';
        document.getElementById('ver-proveedor-razon').textContent = proveedor.razon_social || 'N/A';
        document.getElementById('ver-proveedor-telefono').textContent = proveedor.numero_contacto || proveedor.telefono || 'N/A';
        document.getElementById('ver-proveedor-correo').textContent = proveedor.correo_electronico || 'N/A';
        document.getElementById('ver-proveedor-direccion').textContent = proveedor.direccion || 'Sin dirección registrada';
        new bootstrap.Modal(document.getElementById('modalVerProveedor')).show();
    };

    window.editarProveedor = function(id) {
        const proveedor = proveedoresPorId[id];
        if (!proveedor) return;

        document.getElementById('editar-proveedor-id').value = proveedor.id_proveedor || '';
        document.getElementById('editar-proveedor-rif').value = proveedor.rif || '';
        document.getElementById('editar-proveedor-razon').value = proveedor.razon_social || '';
        document.getElementById('editar-proveedor-telefono').value = proveedor.numero_contacto || proveedor.telefono || '';
        document.getElementById('editar-proveedor-correo').value = proveedor.correo_electronico || '';
        document.getElementById('editar-proveedor-direccion').value = proveedor.direccion || '';
        document.getElementById('editar-proveedor-razon-header').textContent = proveedor.razon_social || 'Proveedor';
        new bootstrap.Modal(document.getElementById('modalEditarProveedor')).show();
    };

    window.confirmarEliminarProveedor = function(id) {
        const proveedor = proveedoresPorId[id];
        if (!proveedor) return;

        document.getElementById('eliminar-proveedor-id').value = id;
        document.getElementById('eliminar-proveedor-nombre').textContent = proveedor.razon_social || 'Proveedor #' + id;
        new bootstrap.Modal(document.getElementById('modalEliminarProveedor')).show();
    };

    document.querySelectorAll('.proveedor-rif').forEach(function(input) {
        input.addEventListener('blur', function() {
            const value = this.value.trim().toUpperCase();
            if (/^\d+$/.test(value)) this.value = 'J-' + value;
            else if (/^J\d+$/.test(value)) this.value = 'J-' + value.substring(1);
        });
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    document.querySelectorAll('.proveedor-razon').forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    document.querySelectorAll('.proveedor-telefono').forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9-]/g, '');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>