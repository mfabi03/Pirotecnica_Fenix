<!-- ============================================================ -->
<!-- MODAL 2: REGISTRO RÁPIDO DE CATEGORÍA (AJAX)                 -->
<!-- ============================================================ -->
<div class="modal fade" id="modalRapidoCategoria" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-tags me-2" style="color: #f39c12;"></i> Registrar Categoría Rápida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <div id="modal-rapido-categoria-error" class="alert" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="modal-rapido-categoria-error-msg"></span>
                </div>

                <form id="formRapidoCategoria">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre de la Categoría *</label>
                            <input type="text" id="rapido-cat-nombre" name="nombre_categoria" class="form-control" required
                                   placeholder="Ej: Volcanes, Estrellitas..." data-validate="razon">
                            <small class="feedback-validacion info" id="rapido-cat-nombre-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                            </small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Descripción</label>
                            <textarea id="rapido-cat-desc" name="descripcion" class="form-control" rows="3"
                                      placeholder="Descripción de la categoría (opcional)" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    Cancelar
                </button>
                <button type="button" class="btn" id="btnGuardarCategoriaRapido"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                    <i class="fas fa-save me-1"></i> Guardar Categoría
                </button>
            </div>
        </div>
    </div>
</div>
