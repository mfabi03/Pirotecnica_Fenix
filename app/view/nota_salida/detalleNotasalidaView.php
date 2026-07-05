<?php
// CAMBIO: Ajuste de nombre según BD - Vista de detalle de nota de salida CON BOOTSTRAP
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <!-- Tarjeta de título -->
    <div class="card card-custom p-4 mb-4 bg-white no-print">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-file-export me-2"></i> Detalle de Nota de Salida
                </h3>
                <p class="text-muted mb-0">Información completa de la nota seleccionada.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <!-- 🔥 BOTÓN DE IMPRIMIR -->
                <button class="btn btn-gold btn-sm fw-bold" onclick="window.print();">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
                <a href="?url=notasalida&type=list" class="btn btn-secondary btn-sm fw-bold ms-1">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show no-print" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$nota): ?>
        <div class="alert alert-warning alert-custom no-print">
            <i class="fas fa-exclamation-triangle me-2"></i> Nota de salida no encontrada.
        </div>
    <?php else: ?>

        <!-- ========================================== -->
        <!-- CONTENIDO QUE SE VA A IMPRIMIR            -->
        <!-- ========================================== -->
        <div id="contenidoImprimir">

            <!-- Estado -->
            <div class="text-center mb-4">
                <?php if (($nota['estado'] ?? 'ACTIVA') === 'ANULADA'): ?>
                    <span class="badge badge-stock-low fs-5 p-3">
                        <i class="fas fa-times-circle me-2"></i> ANULADA
                    </span>
                    <p class="text-muted mt-2">
                        <i class="fas fa-clock me-1"></i>
                        Anulada: <?= date('d/m/Y H:i', strtotime($nota['fecha_anulacion'] ?? 'now')) ?>
                    </p>
                <?php else: ?>
                    <span class="badge badge-stock-high fs-5 p-3">
                        <i class="fas fa-check-circle me-2"></i> ACTIVA
                    </span>
                <?php endif; ?>
            </div>

            <!-- Alertas -->
            <?php if (($nota['estado'] ?? 'ACTIVA') === 'ANULADA'): ?>
                <div class="alert alert-danger alert-custom mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>NOTA ANULADA</strong> - 
                    <?= date('d/m/Y H:i', strtotime($nota['fecha_anulacion'] ?? 'now')) ?>
                    <br><small class="text-muted">El stock ha sido restaurado.</small>
                </div>
            <?php endif; ?>

            <!-- Información General -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card bg-light p-3 h-100">
                        <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2">
                            <i class="fas fa-info-circle me-2"></i> Datos de la Nota
                        </h6>
                        <div class="mt-2">
                            <p><strong>ID:</strong> <span class="badge bg-primary">#<?= $nota['id_nota_salida'] ?></span></p>
                            <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($nota['fecha'])) ?></p>
                            <p><strong>Encargado:</strong> <?= htmlspecialchars($nota['usuario_responsable'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-light p-3 h-100">
                        <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2">
                            <i class="fas fa-user me-2"></i> Cliente
                        </h6>
                        <div class="mt-2">
                            <p><strong>Cliente:</strong> <?= htmlspecialchars($nota['cliente'] ?? 'N/A') ?></p>
                            <p><strong>Encargado:</strong> <?= htmlspecialchars($nota['usuario_responsable'] ?? 'N/A') ?></p>
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
                                <th>Categoría</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($nota['detalles'])): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay productos en esta nota</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $totalUnidades = 0;
                                foreach ($nota['detalles'] as $d): 
                                    $totalUnidades += $d['cantidad'];
                                ?>
                                    <tr>
                                        <td><?= $d['id_producto'] ?></td>
                                        <td><?= htmlspecialchars($d['nombre_producto']) ?></td>
                                        <td><?= htmlspecialchars($d['nombre_categoria'] ?? 'Sin categoría') ?></td>
                                        <td><?= $d['cantidad'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Unidades:</th>
                                <th class="text-primary fs-5"><?= $totalUnidades ?? 0 ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
        <!-- ========================================== -->
        <!-- FIN DEL CONTENIDO QUE SE IMPRIME          -->
        <!-- ========================================== -->

        <!-- Botones (NO se imprimen) -->
        <div class="card card-custom p-4 bg-white no-print">
            <div class="d-flex gap-2 flex-wrap">
                <a href="?url=notasalida&type=list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
                <!-- 🔥 BOTÓN DE IMPRIMIR -->
                <button class="btn btn-gold" onclick="window.print();">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
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
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Anular Nota de Salida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?url=notasalida&type=anular">
                <div class="modal-body">
                    <p>¿Estás seguro de anular esta nota de salida?</p>
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($nota['cliente'] ?? '') ?></p>
                    <p><strong>Encargado:</strong> <?= htmlspecialchars($nota['usuario_responsable'] ?? '') ?></p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="id_nota_salida" value="<?= $nota['id_nota_salida'] ?>">
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