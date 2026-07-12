<?php
// app/view/notaentrada/detalleNotaEntrada.php
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
                            <i class="fas fa-file-invoice text-gold me-2"></i> Detalle de Nota de Entrada
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Información completa de la nota seleccionada
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=notaentrada&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$nota): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span>Nota de entrada no encontrada.</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php else: ?>

                <!-- ==========================================
                ESTADO DE LA NOTA
                ========================================== -->
                <div class="text-center mb-4">
                    <?php if (($nota['estado'] ?? 'ACTIVA') === 'ANULADA'): ?>
                        <span class="badge" style="background: #dc3545; color: #fff; padding: 10px 24px; border-radius: 50px; font-size: 1.1rem;">
                            <i class="fas fa-times-circle me-2"></i> ANULADA
                        </span>
                    <?php else: ?>
                        <span class="badge" style="background: #28a745; color: #fff; padding: 10px 24px; border-radius: 50px; font-size: 1.1rem;">
                            <i class="fas fa-check-circle me-2"></i> ACTIVA
                        </span>
                    <?php endif; ?>
                </div>

                <!-- ==========================================
                INFORMACIÓN GENERAL - TEXTO OSCURO
                ========================================== -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 16px 20px;">
                            <h6 style="color: #6c757d; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #dee2e6; padding-bottom: 8px; margin-bottom: 12px;">
                                <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos de la Nota
                            </h6>
                            <div style="color: #212529;">
                                <p><strong style="color: #495057;">Código:</strong> <span class="badge" style="background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; padding: 4px 12px; border-radius: 50px;">#<?= $nota['id_nota_entrada'] ?></span></p>
                                <p><strong style="color: #495057;">Fecha:</strong> <?= date('d/m/Y H:i', strtotime($nota['fecha_ingreso'])) ?></p>
                                <p><strong style="color: #495057;">Encargado:</strong> <?= htmlspecialchars($nota['encargado_nombre'] ?? 'Sin asignar') ?></p>
                                <p><strong style="color: #495057;">Comentarios:</strong> <?= nl2br(htmlspecialchars($nota['descripcion'] ?? 'Sin comentarios')) ?></p>
                                <p><strong style="color: #495057;">Costo Total:</strong> <span style="color: #28a745; font-weight: 700;">$<?= number_format($nota['costo_total'] ?? 0, 2) ?></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 16px 20px;">
                            <h6 style="color: #6c757d; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #dee2e6; padding-bottom: 8px; margin-bottom: 12px;">
                                <i class="fas fa-truck me-2" style="color: #f39c12;"></i> Datos del Proveedor
                            </h6>
                            <div style="color: #212529;">
                                <p><strong style="color: #495057;">Razón Social:</strong> <?= htmlspecialchars($nota['razon_social'] ?? 'N/A') ?></p>
                                <p><strong style="color: #495057;">RIF:</strong> <?= htmlspecialchars($nota['rif'] ?? 'N/A') ?></p>
                                <?php if (($nota['estado'] ?? 'ACTIVA') === 'ANULADA'): ?>
                                    <p><strong style="color: #495057;">Motivo Anulación:</strong> <?= htmlspecialchars($nota['motivo_anulacion'] ?? 'No especificado') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==========================================
                TABLA DE PRODUCTOS - ESTILO OSCURO
                ========================================== -->
                <div class="dark-card card shadow-sm">
                    <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                        <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                            <i class="fas fa-box me-2"></i> Productos
                        </h5>
                    </div>
                    
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-2" style="color: #495057; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8f9fa;">ID</th>
                                        <th class="py-2" style="color: #495057; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8f9fa;">Producto</th>
                                        <th class="py-2 text-center" style="color: #495057; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8f9fa;">Cantidad</th>
                                        <th class="py-2 text-center" style="color: #495057; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8f9fa;">Costo Unitario</th>
                                        <th class="pe-4 py-2 text-center" style="color: #495057; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8f9fa;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($nota['detalles'])): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3" style="color: #6c757d;">
                                                <i class="fas fa-box-open me-2"></i> No hay productos en esta nota
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php 
                                        $totalGeneral = 0;
                                        foreach ($nota['detalles'] as $d): 
                                            $subtotal = $d['cantidad'] * $d['costo_unitario'];
                                            $totalGeneral += $subtotal;
                                        ?>
                                            <tr>
                                                <td class="ps-4" style="color: #212529;"><?= $d['id_producto'] ?></td>
                                                <td style="color: #212529;"><?= htmlspecialchars($d['nombre_producto']) ?></td>
                                                <td class="text-center" style="color: #212529;"><?= $d['cantidad'] ?></td>
                                                <td class="text-center" style="color: #212529;">$<?= number_format($d['costo_unitario'], 2) ?></td>
                                                <td class="pe-4 text-center" style="color: #f39c12; font-weight: 600;">$<?= number_format($subtotal, 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="border-top: 2px solid rgba(243,156,18,0.2);">
                                        <th colspan="4" class="ps-4 py-2 text-end" style="color: #495057; font-size: 0.85rem;">Total General:</th>
                                        <th class="pe-4 py-2 text-center" style="color: #28a745; font-size: 1.1rem; font-weight: 700;">$<?= number_format($totalGeneral ?? 0, 2) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ==========================================
                BOTONES DE ACCIÓN
                ========================================== -->
                <div class="mt-4 text-center" style="border-top: 1px solid rgba(0,0,0,0.06); padding-top: 20px;">
                    <a href="?url=notaentrada&type=list" class="btn" style="background: #f8f9fa; color: #495057; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px; border: 1px solid #dee2e6;">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    <?php if (($nota['estado'] ?? 'ACTIVA') !== 'ANULADA'): ?>
                        <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#anularModal" style="background: #dc3545; border: none; color: #fff; border-radius: 50px; padding: 10px 25px; font-weight: 600; transition: all 0.3s ease;">
                            <i class="fas fa-ban me-1"></i> Anular
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==========================================
MODAL ANULAR - ESTILO OSCURO
========================================== -->
<?php if (isset($nota) && ($nota['estado'] ?? 'ACTIVA') !== 'ANULADA'): ?>
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
                    <p><strong style="color: rgba(255,255,255,0.5);">Proveedor:</strong> <span style="color: #ffffff;"><?= htmlspecialchars($nota['razon_social'] ?? '') ?></span></p>
                    <div class="alert" style="background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.12); color: #ea868f; border-radius: 12px; padding: 12px 16px;">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Motivo <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required
                                  style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 12px 16px;"></textarea>
                    </div>
                    <input type="hidden" name="id_nota_entrada" value="<?= $nota['id_nota_entrada'] ?>">
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.04);">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 8px 20px; font-weight: 600; transition: all 0.3s ease;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" style="background: #dc3545; border: none; color: #fff; border-radius: 50px; padding: 8px 20px; font-weight: 600; transition: all 0.3s ease;">
                        <i class="fas fa-ban me-1"></i> Anular
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>