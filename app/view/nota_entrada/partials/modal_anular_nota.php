<div class="modal fade" id="anularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3);">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Anular Nota de Entrada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form id="formAnularNota">
                <div class="modal-body" style="color: #1a1a2e;">
                    <p>¿Estás seguro de anular esta nota de entrada?</p>
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong class="text-muted d-block small text-uppercase mb-1">Proveedor de la nota</strong>
                        <span id="modalProveedor" style="color: #1a1a2e; font-weight: 600;"></span>
                    </div>
                    <div class="alert alert-danger" style="border-radius: 12px; padding: 12px 16px;">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock de los productos se revertirá automáticamente.
                    </div>
                    <input type="hidden" name="id_nota_entrada" id="modalIdNota">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #1a1a2e; font-size: 0.85rem;">Motivo de Anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control" rows="3" required 
                                  placeholder="Describe el motivo por el cual se anula esta nota"
                                  style="background: #ffffff; border: 1px solid #ced4da; color: #212529; border-radius: 10px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn btn-danger" style="border-radius: 50px; padding: 10px 30px; font-weight: 600;">
                        <i class="fas fa-ban me-1"></i> Sí, anular
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ============================================================ -->
