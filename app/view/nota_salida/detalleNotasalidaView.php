<?php
// app/view/nota_salida/detalleNotasalidaView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

if (!isset($db) || $db === null) {
    try { $db = (new ConnectDB())->getConnection(); } catch (Exception $e) { $db = null; }
}

$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$puede_anular_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Salida', 'eliminar') : false;
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8 col-lg-12">
            
            <!-- TARJETA DE TÍTULO -->
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
                        <button type="button" class="btn btn-danger me-2" onclick="imprimirNotaPDF()" style="border-radius: 50px; padding: 8px 20px; font-weight: 600;">
                            <i class="fas fa-file-pdf me-1"></i> Imprimir PDF
                        </button>
                        <a href="?url=notasalida&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!$nota): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0 no-print">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span>Nota de salida no encontrada.</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php else: ?>

                <div id="contenidoImprimir">

                    <!-- ESTADO DE LA NOTA -->
                    <div class="text-center mb-4">
                        <?php if (($nota['eliminado'] ?? 0) == 1): ?>
                            <span class="badge" style="background: #dc3545; color: #fff; padding: 10px 24px; border-radius: 50px; font-size: 1.1rem;">
                                <i class="fas fa-times-circle me-2"></i> ANULADA
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background: #28a745; color: #fff; padding: 10px 24px; border-radius: 50px; font-size: 1.1rem;">
                                <i class="fas fa-check-circle me-2"></i> ACTIVA
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- INFORMACIÓN GENERAL -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 12px; padding: 16px 20px;">
                                <h6 style="color: #6c757d; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #dee2e6; padding-bottom: 8px; margin-bottom: 12px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos de la Nota
                                </h6>
                                <div style="color: #212529;">
                                    <p><strong style="color: #495057;">Fecha:</strong> <?= formatoFechaVenezuela($nota['fecha']) ?></p>
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
                                    <p><strong style="color: #495057;">Cédula / Documento:</strong> <?= htmlspecialchars(!empty($nota['cedula']) ? $nota['cedula'] : (!empty($nota['rif']) ? $nota['rif'] : ($nota['documento'] ?? 'N/A'))) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA DE PRODUCTOS TIPO FACTURA -->
                    <div class="dark-card card shadow-sm">
                        <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                            <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                                <i class="fas fa-box me-2"></i> Productos de la Nota
                            </h5>
                        </div>
                        
                        <div class="card-body" style="padding: 0;">
                            <div class="table-responsive">
                                <!-- TABLA ESTRUCTURADA CON ANCHOS FIJOS -->
                                <table class="table table-hover align-middle m-0" style="table-layout: fixed; width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #f8f9fa;">
                                            <th style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 8%; padding: 12px; text-align: left;">ID</th>
                                            <th style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 32%; padding: 12px; text-align: left;">PRODUCTO</th>
                                            <th style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 20%; padding: 12px; text-align: left;">CATEGORÍA</th>
                                            <th style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 10%; padding: 12px; text-align: center;">CANT.</th>
                                            <th style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 15%; padding: 12px; text-align: right;">COSTO</th>
                                            <th style="color: #495057; font-size: 0.75rem; text-transform: uppercase; width: 15%; padding: 12px; text-align: right;">SUBTOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($nota['detalles'])): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-3" style="color: #6c757d;">No hay productos en esta nota</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php 
                                            $totalUnidades = 0;
                                            $totalCosto = 0;
                                            foreach ($nota['detalles'] as $d): 
                                                $cantidad = (int) $d['cantidad'];
                                                $costo = (float) ($d['costo_unitario'] ?? 0);
                                                $subtotal = $cantidad * $costo;
                                                
                                                $totalUnidades += $cantidad;
                                                $totalCosto += $subtotal;
                                            ?>
                                                <tr style="border-bottom: 1px solid #eee;">
                                                    <td style="padding: 12px; color: #212529; font-weight: 600;"><?= $d['id_producto'] ?></td>
                                                    <td style="padding: 12px; color: #212529;"><?= htmlspecialchars($d['nombre_producto']) ?></td>
                                                    <td style="padding: 12px; color: #6c757d;"><?= htmlspecialchars($d['nombre_categoria'] ?? 'Sin categoría') ?></td>
                                                    <td style="padding: 12px; color: #212529; text-align: center; font-weight: 600;"><?= $cantidad ?></td>
                                                    <td style="padding: 12px; color: #212529; text-align: right;">$<?= number_format($costo, 2) ?></td>
                                                    <td style="padding: 12px; color: #f39c12; text-align: right; font-weight: bold;">$<?= number_format($subtotal, 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <!-- TOTAL DE UNIDADES Y TOTAL DE COSTO -->
                                        <tr style="border-top: 2px solid rgba(243,156,18,0.4); background: #f8f9fa;">
                                            <td colspan="3" style="padding: 16px 12px; text-align: right; font-weight: bold; color: #495057;">TOTALES:</td>
                                            <td style="padding: 16px 12px; text-align: center; font-weight: bold; color: #f39c12; font-size: 1.1rem;"><?= $totalUnidades ?? 0 ?></td>
                                            <td style="padding: 16px 12px;"></td>
                                            <td style="padding: 16px 12px; text-align: right; font-weight: bold; color: #28a745; font-size: 1.1rem;">$<?= number_format($totalCosto ?? 0, 2) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- FIN DEL CONTENIDO QUE SE IMPRIME -->

                <!-- BOTONES DE ACCIÓN INFERIORES -->
                <div class="mt-4 text-center no-print" style="border-top: 1px solid rgba(0,0,0,0.06); padding-top: 20px;">
                    <a href="?url=notasalida&type=list" class="btn" style="background: #f8f9fa; color: #495057; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px; border: 1px solid #dee2e6;">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    
                    <?php if (($nota['eliminado'] ?? 0) != 1 && $puede_anular_nota): ?>
                        <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#anularModal" style="background: #dc3545; border: none; color: #fff; border-radius: 50px; padding: 10px 25px; font-weight: 600; transition: all 0.3s ease;">
                            <i class="fas fa-ban me-1"></i> Anular Nota
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL ANULAR -->
<?php if (isset($nota) && ($nota['eliminado'] ?? 0) != 1 && $puede_anular_nota): ?>
<div class="modal fade" id="anularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #0d0d14; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
            <div class="modal-header" style="background: rgba(220,53,69,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Anular Nota de Salida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.3;"></button>
            </div>
            <form method="POST" action="?url=notasalida&type=eliminar">
                <div class="modal-body" style="color: rgba(255,255,255,0.8);">
                    <p>¿Estás seguro de anular esta nota de salida?</p>
                    <p><strong style="color: rgba(255,255,255,0.5);">Cliente:</strong> <span style="color: #ffffff;"><?= htmlspecialchars($nota['cliente'] ?? '') ?></span></p>
                    <div class="alert" style="background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.12); color: #ea868f; border-radius: 12px; padding: 12px 16px;">
                        <i class="fas fa-info-circle me-2"></i> Al anular, el stock se revertirá automáticamente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Motivo <span class="text-danger">*</span></label>
                        <textarea name="motivo_eliminacion" class="form-control" rows="3" required style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 12px 16px;"></textarea>
                    </div>
                    <input type="hidden" name="id_nota_salida" value="<?= $nota['id_nota_salida'] ?>">
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.04);">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 8px 20px; font-weight: 600; transition: all 0.3s ease;">Cancelar</button>
                    <button type="submit" class="btn" style="background: #dc3545; border: none; color: #fff; border-radius: 50px; padding: 8px 20px; font-weight: 600; transition: all 0.3s ease;"><i class="fas fa-ban me-1"></i> Anular</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SCRIPT PARA IMPRIMIR EL PDF -->
<script>
    function imprimirNotaPDF() {
        var idNota = <?= json_encode($nota['id_nota_salida'] ?? 0) ?>;
        if (idNota > 0) {
            window.open('?url=notasalida&type=generar_pdf&id=' + idNota, '_blank');
        } else {
            alert("Error: No se pudo identificar la nota.");
        }
    }
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>