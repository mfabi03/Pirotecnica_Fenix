<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-truck me-2"></i> Detalle del Proveedor</h4>
            <a href="?url=proveedores&type=list" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
        <hr>

        <?php if (isset($proveedor) && $proveedor): ?>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID:</strong> <?= $proveedor['id_proveedor'] ?></p>
                    <p><strong>RIF:</strong> <?= htmlspecialchars($proveedor['rif']) ?></p>
                    <p><strong>Razón Social:</strong> <?= htmlspecialchars($proveedor['razon_social']) ?></p>
                    <p><strong>Contacto:</strong> <?= htmlspecialchars($proveedor['numero_contacto'] ?? 'N/A') ?></p>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($proveedor['direccion'] ?? 'N/A') ?></p>
                    <p><strong>Correo:</strong> <?= htmlspecialchars($proveedor['correo_electronico'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div class="mt-4">
                <a href="?url=proveedores&type=edit&id=<?= $proveedor['id_proveedor'] ?>" class="btn btn-gold">Editar</a>
                <form method="POST" action="?url=proveedores&type=delete" style="display:inline;">
                    <input type="hidden" name="id_proveedor" value="<?= $proveedor['id_proveedor'] ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Proveedor no encontrado.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>