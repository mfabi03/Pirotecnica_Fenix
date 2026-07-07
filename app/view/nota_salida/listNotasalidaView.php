<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4" style="border-radius: 16px; border-top: 4px solid #DAA520;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 style="color: #000000; font-weight: 700;">
                <i class="fas fa-sign-out-alt" style="color: #DAA520;"></i> Notas de Salida
            </h4>
            <a href="?url=notasalida&type=create" class="btn btn-gold" style="border-radius: 50px; padding: 8px 25px;">
                <i class="fas fa-plus me-1"></i> Registrar Nota
            </a>
        </div>

        <!-- Mensajes de éxito/error -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ==========================================
        CUADROS DE RESUMEN
        ========================================== -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-total-notas">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title"><i class="fas fa-file-invoice me-1"></i> Total Notas</h6>
                            <h2 class="card-number" id="totalNotas"><?= count($notas) ?></h2>
                        </div>
                        <div class="card-icon"><i class="fas fa-file-invoice"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-total-anuladas">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title"><i class="fas fa-ban me-1"></i> Eliminadas</h6>
                            <h2 class="card-number" id="totalEliminadas">
                                <?= $_SESSION['contador_eliminaciones'] ?? 0 ?>
                            </h2>
                        </div>
                        <div class="card-icon"><i class="fas fa-ban"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
        TABLA DE NOTAS DE SALIDA
        ========================================== -->
        <div class="table-responsive">
            <table class="table table-fenix table-hover" id="tablaNotas">
                <thead style="background: #000000; color: white;">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Productos</th>
                        <th>Categorías</th>
                        <th>Unidades</th>
                        <th>Encargado</th>
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyNotas">
                    <?php if (empty($notas)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-database mb-2" style="font-size: 2rem; display: block;"></i>
                                No hay notas de salida registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notas as $n): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $n['id_nota_salida'] ?></td>
                                <td><?= date('d/m/Y', strtotime($n['fecha'])) ?></td>
                                <td>
                                    <?php 
                                    if (!empty($n['cliente_razon_social'])) {
                                        echo htmlspecialchars($n['cliente_razon_social']);
                                    } else {
                                        echo htmlspecialchars(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?= ($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? 'bg-primary' : 'bg-success' ?>">
                                        <?= $n['tipo_cliente'] ?? 'Natural' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($n['productos_lista'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($n['categorias_lista'] ?? 'N/A') ?></td>
                                <td class="fw-bold"><?= $n['total_unidades'] ?? 0 ?></td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? ''));
                                    ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <!-- VER DETALLE -->
                                        <a href="?url=notasalida&type=show&id=<?= $n['id_nota_salida'] ?>" 
                                           class="btn btn-outline-info" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- EDITAR -->
                                        <a href="?url=notasalida&type=edit&id=<?= $n['id_nota_salida'] ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- ELIMINAR -->
                                        <button type="button" class="btn btn-outline-danger" 
                                                data-bs-toggle="modal" data-bs-target="#eliminarModal"
                                                data-id="<?= $n['id_nota_salida'] ?>"
                                                data-cliente="<?php 
                                                    if (!empty($n['cliente_razon_social'])) {
                                                        echo htmlspecialchars($n['cliente_razon_social']);
                                                    } else {
                                                        echo htmlspecialchars(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                                                    }
                                                ?>"
                                                title="Eliminar Nota">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
MODAL ELIMINAR
========================================== -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Eliminar Nota de Salida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?url=notasalida&type=eliminar">
                <div class="modal-body">
                    <p>¿Estás seguro de eliminar esta nota de salida?</p>
                    <p><strong>Cliente:</strong> <span id="modalCliente"></span></p>
                    <div class="alert alert-danger">
                        <i class="fas fa-info-circle me-2"></i> 
                        Al eliminar, el stock se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo de Eliminación <span class="text-danger">*</span></label>
                        <textarea name="motivo_eliminacion" class="form-control" rows="3" required 
                                  placeholder="Describe el motivo de la eliminación..."></textarea>
                    </div>
                    <input type="hidden" name="id_nota_salida" id="modalNotaId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Eliminar
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

    // Modal Eliminar
    const eliminarModal = document.getElementById('eliminarModal');
    if (eliminarModal) {
        eliminarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const cliente = button.getAttribute('data-cliente');
            
            document.getElementById('modalNotaId').value = id;
            document.getElementById('modalCliente').textContent = cliente;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>