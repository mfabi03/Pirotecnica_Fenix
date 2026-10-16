<div class="modal fade" id="modalVerCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle de la Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <div class="text-center mb-4">
                    <h4 id="ver-categoria-nombre" style="color: #f39c12; font-weight: 700; margin-bottom: 6px;">—</h4>
                    <span class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 6px 16px; border-radius: 50px; font-size: 0.75rem;">
                        ID #<span id="ver-categoria-id">—</span>
                    </span>
                </div>

                <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                    <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                        <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Información de la Categoría
                    </h6>
                    <div class="mb-3">
                        <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Nombre</label>
                        <p id="ver-categoria-nombre-detalle" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                    </div>
                    <div class="mb-0">
                        <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Descripción</label>
                        <p id="ver-categoria-descripcion" style="color: #1a1a2e; margin: 0;">—</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>