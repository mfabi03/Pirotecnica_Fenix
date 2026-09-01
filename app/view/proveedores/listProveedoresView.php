<?php
// app/view/proveedores/proveedores_lista.php
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 ENCABEZADO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-truck text-gold me-2"></i> Lista de Proveedores
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important;">
                            <i class="fas fa-database me-2"></i> 
                            <?= isset($proveedores) ? count($proveedores) : 0 ?> proveedores registrados
                        </small>
                    </div>
                    <div class="col-xl-6">
                        <form method="GET" id="formFiltros" class="row g-2">
                            <input type="hidden" name="url" value="proveedores">
                            <input type="hidden" name="type" value="list">
                            <div class="col-7">
                                <div class="dark-search input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="buscar" class="form-control shadow-none" 
                                           placeholder="Buscar por RIF, Razón Social o Dirección..."
                                           value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-3">
                                <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-dark-search w-100" type="submit">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ==========================================
                 TABLA DE PROVEEDORES - CABECERA OSCURA
                 ========================================== -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-truck me-2"></i> Proveedores Registrados
                    </h5>
                    <a href="?url=proveedores&type=create" class="btn btn-dark-gold">
                        <i class="fas fa-plus me-1"></i> Registrar
                    </a>
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
                                                <a href="?url=proveedores&type=show&id=<?= $p['id_proveedor'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?url=proveedores&type=edit&id=<?= $p['id_proveedor'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="?url=proveedores&type=delete" class="d-inline">
                                                    <input type="hidden" name="id_proveedor" value="<?= $p['id_proveedor'] ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este proveedor?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-truck fa-3x d-block mb-3"></i>
                                            <p class="mb-0">No hay proveedores registrados</p>
                                            <small>Comienza registrando un nuevo proveedor</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer -->
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-truck me-1"></i> 
                        Total: <?= isset($proveedores) ? count($proveedores) : 0 ?> proveedores
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>