<?php
// CAMBIO: Ajuste de nombre según BD - Vista de detalle de nota de entrada CON BOOTSTRAP
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-8 col-lg-12">
    <!-- Tarjeta de título -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-file-invoice me-2"></i> Detalle de Nota de Entrada
                </h3>
                <p class="text-muted mb-0">Información completa de la nota seleccionada.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=notaentrada&type=list" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$nota): ?>
        <div class="alert alert-warning alert-custom">
            <i class="fas fa-exclamation-triangle me-2"></i> Nota de entrada no encontrada.
        </div>
    <?php else: ?>

        <!-- Estado de la nota -->
        <div class="text-center mb-4">
            <?php if (($nota['estado'] ?? 'ACTIVA') === 'ANULADA'): ?>
                <span class="badge badge-stock-low fs-5 p-3">
                    <i class="fas fa-times-circle me-2"></i> ANULADA
                </span>
            <?php else: ?>
                <span class="badge badge-stock-high fs-5 p-3">
                    <i class="fas fa-check-circle me-2"></i> ACTIVA
                </span>
            <?php endif; ?>
        </div>

        <!-- Información General -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card bg-light p-3 h-100">
                    <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i> Datos de la Nota
                    </h6>
                    <div class="mt-2">
                        <p><strong>Código:</strong> <span class="badge bg-primary">#<?= $nota['id_nota_entrada'] ?></span></p>
                        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($nota['fecha_ingreso'])) ?></p>
                        <p><strong>Encargado:</strong> <?= htmlspecialchars($nota['encargado_nombre'] ?? 'Sin asignar') ?></p>
                        <p><strong>Comentarios:</strong> <?= nl2br(htmlspecialchars($nota['descripcion'] ?? 'Sin comentarios')) ?></p>
                        <p><strong>Costo Total:</strong> <span class="fw-bold text-success">$<?= number_format($nota['costo_total'] ?? 0, 2) ?></span></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-light p-3 h-100">
                    <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2">
                        <i class="fas fa-truck me-2"></i> Datos del Proveedor
                    </h6>
                    <div class="mt-2">
                        <p><strong>Razón Social:</strong> <?= htmlspecialchars($nota['razon_social'] ?? 'N/A') ?></p>
                        <p><strong>RIF:</strong> <?= htmlspecialchars($nota['rif'] ?? 'N/A') ?></p>
                        <?php if (($nota['estado'] ?? 'ACTIVA') === 'ANULADA'): ?>
                            <p><strong>Motivo Anulación:</strong> <?= htmlspecialchars($nota['motivo_anulacion'] ?? 'No especificado') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de productos -->
        <div class="card card-custom p-4 mb-4 bg-white">
            <h5><i class="fas fa-box me-2"></i> Productos</h5>
            <div class="table-responsive">
                <table class="table table-fenix table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Costo Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($nota['detalles'])): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay productos en esta nota</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $totalGeneral = 0;
                            foreach ($nota['detalles'] as $d): 
                                $subtotal = $d['cantidad'] * $d['costo_unitario'];
                                $totalGeneral += $subtotal;
                            ?>
                                <tr>
                                    <td><?= $d['id_producto'] ?></td>
                                    <td><?= htmlspecialchars($d['nombre_producto']) ?></td>
                                    <td><?= $d['cantidad'] ?></td>
                                    <td>$<?= number_format($d['costo_unitario'], 2) ?></td>
                                    <td class="fw-bold">$<?= number_format($subtotal, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total General:</th>
                            <th class="text-success">$<?= number_format($totalGeneral ?? 0, 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="card card-custom p-4 bg-white">
            <div class="d-flex gap-2 flex-wrap">
                <a href="?url=notaentrada&type=list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
                <?php if (($nota['estado'] ?? 'ACTIVA') !== 'ANULADA'): ?>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#anularModal">
                        <i class="fas fa-ban me-1"></i> Anular
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Anular -->
<?php if (isset($nota) && ($nota['estado'] ?? 'ACTIVA') !== 'ANULADA'): ?>
<div class="modal fade" id="anularModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Anular Nota de Entrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?url=notaentrada&type=anular">
                <div class="modal-body">
                    <p>¿Estás seguro de anular esta nota de entrada?</p>
                    <p><strong>Proveedor:</strong> <?= htmlspecialchars($nota['razon_social'] ?? '') ?></p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="id_nota_entrada" value="<?= $nota['id_nota_entrada'] ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Anular</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../footer.php'; ?>