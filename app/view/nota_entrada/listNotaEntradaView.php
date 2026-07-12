<?php
// app/view/notaentrada/notaentradaLista.php
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-sign-in-alt text-gold me-2"></i> Notas de Entrada
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona las notas de entrada de productos
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=notaentrada&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Nota de Entrada
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($success)): ?>
                <div class="alert dark-alert-success alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ==========================================
                 CUADROS DE RESUMEN - CORREGIDOS
                 ========================================== -->
            <div class="row mb-4 g-3">
                <div class="col-md-4">
                    <div class="dark-card card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px; background: rgba(255,255,255,0.03);">
                            <div>
                                <h6 class="card-title" style="color: rgba(17, 16, 16, 0.93); font-size: 0.75rem; font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-file-invoice me-1"></i> TOTAL NOTAS
                                </h6>
                                <h2 style="color: #030303; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= $resumen['total_notas'] ?? 0 ?></h2>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(243,156,18,0.15); display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dark-card card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px; background: rgba(255,255,255,0.03);">
                            <div>
                                <h6 class="card-title" style="color: rgba(14, 13, 13, 0.89); font-size: 0.75rem; font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-dollar-sign me-1"></i> TOTAL COMPRAS
                                </h6>
                                <h2 style="color: #0a0a0a; font-weight: 700; font-size: 2.2rem; margin: 0;">$<?= number_format($resumen['total_compras'] ?? 0, 2) ?></h2>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(40,167,69,0.15); display: flex; align-items: center; justify-content: center; color: #28a745; font-size: 1.5rem;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dark-card card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px; background: rgba(255,255,255,0.03);">
                            <div>
                                <h6 class="card-title" style="color: rgba(12, 12, 12, 0.9); font-size: 0.75rem; font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-ban me-1"></i> ANULADAS
                                </h6>
                                <h2 style="color: #0a0a0a; font-weight: 700; font-size: 2.2rem; margin: 0;"><?= $resumen['total_anuladas'] ?? 0 ?></h2>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(220,53,69,0.15); display: flex; align-items: center; justify-content: center; color: #dc3545; font-size: 1.5rem;">
                                <i class="fas fa-ban"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 TABLA DE NOTAS DE ENTRADA - CORREGIDA
                 ========================================== -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-list me-2"></i> Notas Registradas
                    </h5>
                    <span class="text-muted small" style="color: rgba(255,255,255,0.3) !important; font-size: 0.75rem;">
                        <i class="fas fa-database me-1"></i> 
                        <?= isset($notas) ? count($notas) : 0 ?> registros
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3" style="color: #495057; background: #f8f9fa; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">ID</th>
                                <th class="py-3" style="color: #495057; background: #f8f9fa; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Fecha</th>
                                <th class="py-3" style="color: #495057; background: #f8f9fa; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Proveedor</th>
                                <th class="py-3" style="color: #495057; background: #f8f9fa; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Encargado</th>
                                <th class="py-3" style="color: #495057; background: #f8f9fa; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Productos</th>
                                <th class="py-3" style="color: #495057; background: #f8f9fa; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total</th>
                                <th class="py-3" style="color: #495057; background: #f8f9fa; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Estado</th>
                                <th class="pe-4 py-3 text-center" style="color: #495057; background: #f8f9fa; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($notas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay notas de entrada registradas</p>
                                            <small>Comienza registrando una nueva nota</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($notas as $n): ?>
                                    <?php 
                                    $anulada = ($n['estado'] ?? 'ACTIVA') === 'ANULADA';
                                    ?>
                                    <tr <?= $anulada ? 'style="opacity: 0.6;"' : '' ?>>
                                        <td class="ps-4 fw-bold" style="color: #212529;">
                                            <?php if ($anulada): ?>
                                                <del style="color: #6c757d;">#<?= htmlspecialchars($n['id_nota_entrada']) ?></del>
                                            <?php else: ?>
                                                #<?= htmlspecialchars($n['id_nota_entrada']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color: #212529;"><?= date('d/m/Y', strtotime($n['fecha_ingreso'])) ?></td>
                                        <td style="color: #212529;"><?= htmlspecialchars($n['razon_social'] ?? 'N/A') ?></td>
                                        <td style="color: #212529;"><?= htmlspecialchars($n['encargado_nombre'] ?? 'Sin asignar') ?></td>
                                        <td>
                                            <?php if (!empty($n['productos_lista'])): ?>
                                                <?php 
                                                $productos = explode(', ', $n['productos_lista']);
                                                $total = count($productos);
                                                ?>
                                                <?php for ($i = 0; $i < min(3, $total); $i++): ?>
                                                    <span class="badge" style="background: #e9ecef; color: #495057; padding: 4px 10px; border-radius: 50px; font-weight: 500; font-size: 0.7rem;"><?= htmlspecialchars($productos[$i]) ?></span>
                                                <?php endfor; ?>
                                                <?php if ($total > 3): ?>
                                                    <span class="badge" style="background: rgba(243,156,18,0.12); color: #f39c12; padding: 4px 10px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">+<?= $total - 3 ?> más</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span style="color: #adb5bd;">Sin productos</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold" style="color: #212529;">
                                            <?php if ($anulada): ?>
                                                <del style="color: #6c757d;">$<?= number_format($n['costo_total'] ?? 0, 2) ?></del>
                                            <?php else: ?>
                                                $<?= number_format($n['costo_total'] ?? 0, 2) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($anulada): ?>
                                                <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <i class="fas fa-ban me-1"></i> ANULADA
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <i class="fas fa-check-circle me-1"></i> ACTIVA
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="?url=notaentrada&type=show&id=<?= $n['id_nota_entrada'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if (!$anulada): ?>
                                                    <button type="button" class="btn-action-circle btn-delete" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#anularModal"
                                                            data-id="<?= $n['id_nota_entrada'] ?>"
                                                            data-proveedor="<?= htmlspecialchars($n['razon_social'] ?? 'N/A') ?>"
                                                            title="Anular Nota">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span style="color: #adb5bd; font-size: 0.7rem;">Anulada</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer de la tabla -->
                <div class="card-footer py-2 d-flex justify-content-between align-items-center" style="background: #f8f9fa; border-top: 1px solid rgba(0,0,0,0.04); border-radius: 0 0 16px 16px !important;">
                    <span class="text-muted small" style="color: #6c757d !important;">
                        <i class="fas fa-file-invoice me-1"></i> 
                        Total: <?= isset($notas) ? count($notas) : 0 ?> notas
                    </span>
                  
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
MODAL ANULAR
========================================== -->
<div class="modal fade" id="anularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #0d0d14; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
            <div class="modal-header" style="background: rgba(220,53,69,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Anular Nota de Entrada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.3;"></button>
            </div>
            <form method="POST" action="?url=notaentrada&type=anular">
                <div class="modal-body" style="color: rgba(255,255,255,0.8);">
                    <p>¿Estás seguro de anular esta nota de entrada?</p>
                    <p><strong style="color: rgba(255,255,255,0.5);">Proveedor:</strong> <span id="modalProveedor" style="color: #ffffff;"></span></p>
                    <div class="alert" style="background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.12); color: #ea868f; border-radius: 12px; padding: 12px 16px;">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock de los productos se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Motivo de Anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required 
                                  placeholder="Describe el motivo por el cual se anula esta nota..."
                                  style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 12px 16px;"></textarea>
                    </div>
                    <input type="hidden" name="id_nota_entrada" id="modalNotaId">
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.04);">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 8px 20px; font-weight: 600; transition: all 0.3s ease;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" style="background: #dc3545; border: none; color: #fff; border-radius: 50px; padding: 8px 20px; font-weight: 600; transition: all 0.3s ease;">
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
    const alertElement = document.querySelector('.alert');
    if (alertElement) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertElement);
            if (bsAlert) bsAlert.close();
        }, 5000);
    }

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

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>