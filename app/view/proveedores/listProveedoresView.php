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
                            <i class="fas fa-truck text-gold me-2"></i> lista de Proveedores
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona los proveedores registrados en el sistema
                        </small>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <form method="GET" class="me-3">
                            <input type="hidden" name="url" value="proveedores">
                            <input type="hidden" name="type" value="list">
                            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                        </form>
                        <?php if ($puede_crear_proveedor): ?>
                        <a href="?url=proveedores&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Proveedor
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-center" autocomplete="off">
                    <input type="hidden" name="url" value="proveedores">
                    <input type="hidden" name="type" value="list">
                    
                    <div class="col-md-8">
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

            <!-- TABLA DE PROVEEDORES -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-truck me-2"></i> Proveedores Registrados
                    </h5>
                    <span class="text-muted small" style="color: rgba(255,255,255,0.3) !important; font-size: 0.75rem;">
                        <i class="fas fa-database me-1"></i> 
                        <?= isset($proveedores) ? count($proveedores) : 0 ?> registros
                    </span>
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
                                                <!-- Ver: siempre visible -->
                                                <a href="?url=proveedores&type=show&id=<?= $p['id_proveedor'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <!-- Editar: solo si tiene permiso -->
                                                <?php if ($puede_editar_proveedor): ?>
                                                <a href="?url=proveedores&type=edit&id=<?= $p['id_proveedor'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <!-- Eliminar: solo si tiene permiso -->
                                                <?php if ($puede_eliminar_proveedor): ?>
                                                <form method="POST" action="?url=proveedores&type=delete" class="d-inline">
                                                    <input type="hidden" name="id_proveedor" value="<?= $p['id_proveedor'] ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este proveedor?')">
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
                
                <div class="card-footer py-2 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-truck me-1"></i> 
                        Total: <?= isset($proveedores) ? count($proveedores) : 0 ?> proveedores
                    </span>
                </div>
            </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>