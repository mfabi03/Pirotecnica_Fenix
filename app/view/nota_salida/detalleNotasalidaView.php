<?php
// app/view/notasalida/detalleNotaSalida.php
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4 no-print">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-file-export text-gold me-2"></i> Detalle de Nota de Salida
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Información completa de la nota seleccionada
                        </small>
                    </div>
                    <div class="col-auto">
                        <button class="btn" onclick="window.print();" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease; margin-right: 8px;">
                            <i class="fas fa-print me-1"></i> Imprimir
                        </button>
                        <a href="?url=notasalida&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0 no-print">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$nota): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0 no-print">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span>Nota de salida no encontrada.</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php else: ?>

                <!-- ==========================================
                CONTENIDO QUE SE VA A IMPRIMIR
                ========================================== -->
                <div id="contenidoImprimir">

                    <!-- ==========================================
                    ESTADO DE LA NOTA
                    ========================================== -->
                    <div class="text-center mb-4">
                        <?php if (($nota['estado'] ?? 'ACTIVA') === 'ANULADA'): ?>
                            <span class="badge" style="background: #dc3545; color: #fff; padding: 10px 24px; border-radius: 50px; font-size: 1.1rem;">
                                <i class="fas fa-times-circle me-2"></i> ANULADA
                            </span>
                            <p style="color: #6c757d; margin-top: 8px; font-size: 0.85rem;">
                                <i class="fas fa-clock me-1"></i>
                                Anulada: <?= date('d/m/Y H:i', strtotime($nota['fecha_anulacion'] ?? 'now')) ?>
                            </p>
                        <?php else: ?>
                            <span class="badge" style="background: #28a745; color: #fff; padding: 10px 24px; border-radius: 50px; font-size: 1.1rem;">
                                <i class="fas fa-check-circle me-2"></i> ACTIVA
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- ==========================================
                    ALERTA DE ANULADA
                    ========================================== -->
                    <?php if (($nota['estado'] ?? 'ACTIVA') === 'ANULADA'): ?>
                        <div class="alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>NOTA ANULADA</strong> - 
                            <?= date('d/m/Y H:i', strtotime($nota['fecha_anulacion'] ?? 'now')) ?>
                            <br><small style="color: #6c757d;">El stock ha sido restaurado.</small>
                        </div>
                    <?php endif; ?>

                    <!-- ==========================================
                    INFORMACIÓN GENERAL - TEXTO OSCURO (CORREGIDO)
                    ========================================== -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 16px 20px;">
                                <h6 style="color: #6c757d; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #dee2e6; padding-bottom: 8px; margin-bottom: 12px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos de la Nota
                                </h6>
                                <div style="color: #212529;">
                                    <p><strong style="color: #495057;">ID:</strong> <span class="badge" style="background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; padding: 4px 12px; border-radius: 50px;">#<?= $nota['id_nota_salida'] ?></span></p>
                                    <p><strong style="color: #495057;">Fecha:</strong> <?= date('d/m/Y', strtotime($nota['fecha'])) ?></p>
                                    <p><strong style="color: #495057;">Encargado:</strong> <?= htmlspecialchars($nota['usuario_responsable'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 16px 20px;">
                                <h6 style="color: #6c757d; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #dee2e6; padding-bottom: 8px; margin-bottom: 12px;">
                                    <i class="fas fa-user me-2" style="color: #f39c12;"></i> Cliente
                                </h6>
                                <div style="color: #212529;">
                                    <p><strong style="color: #495057;">Cliente:</strong> <?= htmlspecialchars($nota['cliente'] ?? 'N/A') ?></p>
                                    <p><strong style="color: #495057;">Encargado:</strong> <?= htmlspecialchars($nota['usuario_responsable'] ?? 'N/A') ?></p>
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
                                            <th class="py-2" style="color: #495057; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8f9fa;">Categoría</th>
                                            <th class="pe-4 py-2 text-center" style="color: #495057; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8f9fa;">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($nota['detalles'])): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-3" style="color: #6c757d;">
                                                    <i class="fas fa-box-open me-2"></i> No hay productos en esta nota
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php 
                                            $totalUnidades = 0;
                                            foreach ($nota['detalles'] as $d): 
                                                $totalUnidades += $d['cantidad'];
                                            ?>
                                                <tr>
                                                    <td class="ps-4" style="color: #212529;"><?= $d['id_producto'] ?></td>
                                                    <td style="color: #212529;"><?= htmlspecialchars($d['nombre_producto']) ?></td>
                                                    <td style="color: #6c757d;"><?= htmlspecialchars($d['nombre_categoria'] ?? 'Sin categoría') ?></td>
                                                    <td class="pe-4 text-center" style="color: #212529; font-weight: 600;"><?= $d['cantidad'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr style="border-top: 2px solid rgba(243,156,18,0.2);">
                                            <th colspan="3" class="ps-4 py-2 text-end" style="color: #495057; font-size: 0.85rem;">Total Unidades:</th>
                                            <th class="pe-4 py-2 text-center" style="color: #f39c12; font-size: 1.1rem; font-weight: 700;"><?= $totalUnidades ?? 0 ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- ==========================================
                FIN DEL CONTENIDO QUE SE IMPRIME
                ========================================== -->

                <!-- ==========================================
                BOTONES DE ACCIÓN (NO se imprimen)
                ========================================== -->
                <div class="mt-4 text-center no-print" style="border-top: 1px solid rgba(0,0,0,0.06); padding-top: 20px;">
                    <a href="?url=notasalida&type=list" class="btn" style="background: #f8f9fa; color: #495057; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px; border: 1px solid #dee2e6;">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    <button class="btn btn-dark-gold" onclick="window.print();" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease; margin-right: 10px;">
                        <i class="fas fa-print me-2"></i> Imprimir
                    </button>
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
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Anular Nota de Salida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.3;"></button>
            </div>
            <form method="POST" action="?url=notasalida&type=anular">
                <div class="modal-body" style="color: rgba(255,255,255,0.8);">
                    <p>¿Estás seguro de anular esta nota de salida?</p>
                    <p><strong style="color: rgba(255,255,255,0.5);">Cliente:</strong> <span style="color: #ffffff;"><?= htmlspecialchars($nota['cliente'] ?? '') ?></span></p>
                    <p><strong style="color: rgba(255,255,255,0.5);">Encargado:</strong> <span style="color: rgba(255,255,255,0.7);"><?= htmlspecialchars($nota['usuario_responsable'] ?? '') ?></span></p>
                    <div class="alert" style="background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.12); color: #ea868f; border-radius: 12px; padding: 12px 16px;">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Motivo <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required
                                  style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 12px 16px;"></textarea>
                    </div>
                    <input type="hidden" name="id_nota_salida" value="<?= $nota['id_nota_salida'] ?>">
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