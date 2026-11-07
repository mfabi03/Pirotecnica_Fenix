<?php
// ============================================================
// PARTIAL: Modal de Cierre de Caja
// ============================================================
?>
<!-- ========================================================= -->
<!-- MODAL CIERRE DE CAJA                                      -->
<!-- ========================================================= -->
<div class="modal fade" id="modalCierreCaja" tabindex="-1" aria-hidden="true" aria-labelledby="modalCierreCajaLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" id="modalCierreCajaLabel" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-sign-out-alt me-2" style="color: #f39c12;"></i> Cierre de Caja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" id="contenidoCierreCaja" style="padding: 20px 24px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;"></div>
                    <p class="mt-3 text-muted fw-bold">Calculando cierre...</p>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
                
                <div>
                    <a href="#" id="btnExportarCierreCsv" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success fw-bold me-2" style="border-radius: 50px; padding: 8px 20px;">
                        <i class="fas fa-file-csv me-1"></i> Excel/CSV
                    </a>
                    <a href="#" id="btnExportarCierrePdf" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger fw-bold" style="border-radius: 50px; padding: 8px 20px;">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 50px; padding: 8px 20px; font-weight: 600;">
                        Cerrar
                    </button>
                    <button type="button" id="btnConfirmarCierreCaja" class="btn btn-dark-gold" style="border-radius: 50px; padding: 8px 22px; font-weight: 700; background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff;">
                        <i class="fas fa-lock me-1"></i> Cierre de caja
                    </button>
                    <button type="button" id="btnAceptarCierreExito" class="btn btn-success" style="display:none; border-radius: 50px; padding: 8px 22px; font-weight: 700;">
                        <i class="fas fa-check me-1"></i> Aceptar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>