<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-sign-in-alt me-2"></i> Notas de Entrada</h4>
            <a href="?url=notaentrada&type=create" class="btn btn-gold">
                <i class="fas fa-plus me-1"></i> Registrar Nota de Entrada
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-custom"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-custom"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Resumen -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-total-notas border-left-gold text-center p-3">
                    <h6 class="text-muted">TOTAL NOTAS</h6>
                    <h2 class="fw-bold"><?= $resumen['total_notas'] ?? 0 ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-total-notas border-left-info text-center p-3">
                    <h6 class="text-muted">TOTAL COMPRAS</h6>
                    <h2 class="fw-bold">$<?= number_format($resumen['total_compras'] ?? 0, 2) ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-total-notas border-left-danger text-center p-3">
                    <h6 class="text-muted">ANULADAS</h6>
                    <h2 class="fw-bold" style="color: #dc3545;"><?= $resumen['total_anuladas'] ?? 0 ?></h2>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="table-responsive">
            <table class="table table-fenix table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Encargado</th>
                        <th>Producto</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notas)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-database mb-2 fa-2x"></i>
                                No hay notas de entrada registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notas as $n): ?>
                            <?php 
                            // 🔥 DETECTAR SI ESTÁ ANULADA (por el contador o por estado)
                            $anulada = ($n['estado'] ?? 'ACTIVA') === 'ANULADA';
                            ?>
                            <tr <?= $anulada ? 'class="table-danger"' : '' ?>>
                                <td class="fw-bold">
                                    <?php if ($anulada): ?>
                                        <del>#<?= htmlspecialchars($n['id_nota_entrada']) ?></del>
                                    <?php else: ?>
                                        #<?= htmlspecialchars($n['id_nota_entrada']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($n['fecha_ingreso'])) ?></td>
                                <td><?= htmlspecialchars($n['razon_social'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($n['encargado_nombre'] ?? 'Sin asignar') ?></td>
                                <td>
                                    <?php if (!empty($n['productos_lista'])): ?>
                                        <?php 
                                        $productos = explode(', ', $n['productos_lista']);
                                        $total = count($productos);
                                        ?>
                                        <?php for ($i = 0; $i < min(3, $total); $i++): ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($productos[$i]) ?></span>
                                        <?php endfor; ?>
                                        <?php if ($total > 3): ?>
                                            <span class="badge bg-info">+<?= $total - 3 ?> más</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin productos</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold">
                                    <?php if ($anulada): ?>
                                        <del>$<?= number_format($n['costo_total'] ?? 0, 2) ?></del>
                                    <?php else: ?>
                                        $<?= number_format($n['costo_total'] ?? 0, 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($anulada): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-ban me-1"></i> ANULADA
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> ACTIVA
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <!-- VER DETALLE -->
                                        <a href="?url=notaentrada&type=show&id=<?= $n['id_nota_entrada'] ?>" 
                                           class="btn btn-outline-info" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- ANULAR (solo si está ACTIVA) -->
                                        <?php if (!$anulada): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#anularModal"
                                                    data-id="<?= $n['id_nota_entrada'] ?>"
                                                    data-proveedor="<?= htmlspecialchars($n['razon_social'] ?? 'N/A') ?>"
                                                    title="Anular Nota">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Anulada</span>
                                        <?php endif; ?>
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

<!-- ==========================================
MODAL ANULAR
========================================== -->
<div class="modal fade" id="anularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Anular Nota de Entrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?url=notaentrada&type=anular">
                <div class="modal-body">
                    <p>¿Estás seguro de anular esta nota de entrada?</p>
                    <p><strong>Proveedor:</strong> <span id="modalProveedor"></span></p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock de los productos se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo de Anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required 
                                  placeholder="Describe el motivo por el cual se anula esta nota..."></textarea>
                    </div>
                    <input type="hidden" name="id_nota_entrada" id="modalNotaId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i> Anular Nota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
SCRIPTS
========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-cierre de alertas
    const alertElement = document.querySelector('.alert');
    if (alertElement) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertElement);
            if (bsAlert) bsAlert.close();
        }, 5000);
    }

    // Modal Anular
    const anularModal = document.getElementById('anularModal');
    if (anularModal) {
        anularModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const proveedor = button.getAttribute('data-proveedor');
            
            document.getElementById('modalNotaId').value = id;
            document.getElementById('modalProveedor').textContent = proveedor;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>