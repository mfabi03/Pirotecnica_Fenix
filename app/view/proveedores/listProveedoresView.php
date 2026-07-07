<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-truck me-2"></i> Proveedores</h4>
            <a href="?url=proveedores&type=create" class="btn btn-gold">
                <i class="fas fa-plus me-1"></i> Registrar Proveedor
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-custom"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-custom"><?= $error ?></div>
        <?php endif; ?>

        <!-- BUSCADOR -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="?url=proveedores" class="d-flex">
                    <input type="hidden" name="url" value="proveedores">
                    <input type="hidden" name="type" value="list">
                    <input type="text" name="buscar" class="form-control" 
                           placeholder="Buscar por RIF, Razón Social, Dirección..." 
                           value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>"
                           style="border-radius: 50px 0 0 50px;">
                    <button type="submit" class="btn btn-gold" style="border-radius: 0 50px 50px 0;">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <?php if (isset($_GET['buscar']) && !empty($_GET['buscar'])): ?>
                    <a href="?url=proveedores&type=list" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Limpiar búsqueda
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-fenix table-hover">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>RIF</th>
                        <th>Razón Social</th>
                        <th>Contacto</th>
                        <th>Dirección</th>
                        <th>Correo</th>
                        <th class="pe-4 text-end" style="min-width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proveedores)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-database mb-2" style="font-size: 2rem; display: block;"></i>
                                <?= (isset($_GET['buscar']) && !empty($_GET['buscar'])) ? 'No hay proveedores que coincidan con la búsqueda.' : 'No hay proveedores registrados.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($proveedores as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= $p['id_proveedor'] ?></td>
                                <td><?= htmlspecialchars($p['rif']) ?></td>
                                <td><?= htmlspecialchars($p['razon_social']) ?></td>
                                <td><?= htmlspecialchars($p['numero_contacto'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['direccion'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($p['correo_electronico'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- ✅ VER DETALLE -->
                                        <a href="?url=proveedores&type=show&id=<?= $p['id_proveedor'] ?>" 
                                           class="btn btn-outline-info" 
                                           title="Ver Detalle"
                                           style="border-radius: 4px 0 0 4px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- ✅ EDITAR -->
                                        <a href="?url=proveedores&type=edit&id=<?= $p['id_proveedor'] ?>" 
                                           class="btn btn-outline-warning" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- ✅ ELIMINAR -->
                                        <form method="POST" action="?url=proveedores&type=delete" 
                                              style="display:inline;" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?');">
                                            <input type="hidden" name="id_proveedor" value="<?= $p['id_proveedor'] ?>">
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    title="Eliminar"
                                                    style="border-radius: 0 4px 4px 0;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>