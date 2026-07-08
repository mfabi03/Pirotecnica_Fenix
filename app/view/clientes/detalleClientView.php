<?php
// CAMBIO: Ajuste de nombre según BD - Vista de detalle de cliente CON BOOTSTRAP
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4 mb-4 mx-auto" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-user-circle me-2"></i> Detalle del Cliente</h4>
                <p class="text-muted mb-0">Información completa del cliente seleccionado.</p>
            </div>
            <a href="?url=clientes&type=list" class="btn btn-secondary btn-sm fw-bold">
                <i class="fas fa-list me-1"></i> Ver Clientes
            </a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?> alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($cliente) && !empty($cliente)): ?>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="text-secondary small fw-bold text-uppercase">Cédula / RIF</label>
                    <p class="fw-semibold text-primary mb-2">
                        <?= htmlspecialchars($cliente['cedula'] ?? $cliente['rif'] ?? 'N/A') ?>
                    </p>
                </div>

                <div class="col-md-6">
                    <label class="text-secondary small fw-bold text-uppercase">Teléfono</label>
                    <p class="mb-2"><?= htmlspecialchars($cliente['telefono'] ?? 'N/A') ?></p>
                </div>

                <div class="col-md-12">
                    <label class="text-secondary small fw-bold text-uppercase">
                        <?= ($cliente['tipo_cliente'] ?? '') === 'Jurídico' ? 'Razón Social' : 'Nombre Completo' ?>
                    </label>
                    <p class="h5 text-dark fw-bold">
                        <?php if (($cliente['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                            <?= htmlspecialchars($cliente['razon_social'] ?? 'N/A') ?>
                        <?php else: ?>
                            <?= htmlspecialchars(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')) ?>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="col-md-6">
                    <label class="text-secondary small fw-bold text-uppercase">Tipo de Cliente</label>
                    <p>
                        <span class="badge <?= ($cliente['tipo_cliente'] ?? '') === 'Jurídico' ? 'bg-primary' : 'bg-success' ?> fs-6 p-2">
                            <?= htmlspecialchars($cliente['tipo_cliente'] ?? 'N/A') ?>
                        </span>
                    </p>
                </div>

                <?php if (($cliente['tipo_cliente'] ?? '') === 'Natural'): ?>
                    <div class="col-md-6">
                        <label class="text-secondary small fw-bold text-uppercase">Fecha de Nacimiento</label>
                        <p class="h5">
                            <?= !empty($cliente['fecha_de_nacimiento']) ? date('d/m/Y', strtotime($cliente['fecha_de_nacimiento'])) : 'No registrada' ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="col-md-12">
                    <label class="text-secondary small fw-bold text-uppercase">Correo Electrónico</label>
                    <p class="h5">
                        <?= htmlspecialchars($cliente['correo_electrónico'] ?? $cliente['correo_electronico'] ?? 'No registrado') ?>
                    </p>
                </div>

                <div class="col-md-12">
                    <label class="text-secondary small fw-bold text-uppercase">Dirección</label>
                    <div class="bg-light p-3 rounded border">
                        <?= nl2br(htmlspecialchars($cliente['direccion'] ?? 'Sin dirección registrada')) ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="?url=clientes&type=list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
                <?php 
                $editType = ($cliente['tipo_cliente'] ?? '') === 'Jurídico' ? 'edit_juridico' : 'edit';
                ?>
                <a href="?url=clientes&type=<?= $editType ?>&id=<?= htmlspecialchars($cliente['id_cliente']) ?>" class="btn btn-gold">
                    <i class="fas fa-edit me-1"></i> Editar Cliente
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('⚠️ ¿Estás seguro de eliminar este cliente?');">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($cliente['id_cliente'] ?? 0) ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-warning alert-custom">
                <i class="fas fa-exclamation-triangle me-2"></i> Cliente no encontrado.
            </div>
            <div class="mt-3">
                <a href="?url=clientes&type=list" class="btn btn-gold">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Listado
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>