<div class="modal fade" id="anularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #0d0d14; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
            <div class="modal-header" style="background: rgba(220,53,69,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Anular Nota de Entrada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.3;"></button>
            </div>
            <form id="formAnularNota">
                <div class="modal-body" style="color: rgba(255,255,255,0.8);">
                    <p>¿Estás seguro de anular esta nota de entrada?</p>
                    <p><strong style="color: rgba(255,255,255,0.5);">Proveedor:</strong> <span id="modalProveedor" style="color: #ffffff;"></span></p>
                    <div class="alert" style="background: rgba(220,53,69,0.08); border: 1px solid rgba(220,53,69,0.12); color: #ea868f; border-radius: 12px; padding: 12px 16px;">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock de los productos se revertirá automáticamente.
                    </div>
                    <input type="hidden" name="id_nota_entrada" id="modalIdNota">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Motivo de Anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required 
                                  placeholder="Describe el motivo por el cual se anula esta nota"
                                  style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #ffffff; border-radius: 10px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.05); border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i> Anular Nota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ============================================================ -->
