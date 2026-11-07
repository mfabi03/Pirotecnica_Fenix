<?php
// ============================================================
// PARTIAL: Modal para Ver Motivo de Anulación
// ============================================================
?>
<!-- ========================================================= -->
<!-- MODAL PARA VER EL MOTIVO DE LA ANULACIÓN                  -->
<!-- ========================================================= -->
<div class="modal fade" id="modalMotivoAnulacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Motivo de Anulación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 24px;">
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Documento:</span>
                    <div class="fs-5 fw-bold text-dark" id="modal-motivo-doc"></div>
                </div>
                <div class="mb-3">
                    <span class="text-muted small fw-bold text-uppercase">Producto / Cliente:</span>
                    <div class="fs-6 text-dark" id="modal-motivo-prod"></div>
                </div>
                <div class="mb-0">
                    <span class="text-muted small fw-bold text-uppercase">Motivo Registrado:</span>
                    <div class="p-3 mt-1 rounded" style="background: #f8f9fa; border-left: 4px solid #dc3545; color: #333; font-style: italic;" id="modal-motivo-texto"></div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 50px; padding: 8px 25px; font-weight: 600;">Cerrar</button>
            </div>
        </div>
    </div>
</div>