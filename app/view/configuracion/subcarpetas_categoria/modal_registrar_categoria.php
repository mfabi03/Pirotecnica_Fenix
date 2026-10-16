<?php if ($puede_crear_categoria): ?>
<div class="modal fade" id="modalRegistrarCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-tag me-2" style="color: #f39c12;"></i> Registrar Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <form method="POST" action="?url=categorias&action=guardar" id="formRegistrarCategoria">
                    <input type="hidden" name="accion" value="guardar">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre de la Categoría *</label>
                            <input type="text" name="nombre_categoria" id="reg-cat-nombre" class="form-control" required
                                   placeholder="Ej: Volcanes, Estrellitas..."
                                   data-validate="razon"
                                   style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                            <small class="feedback-validacion info" id="reg-cat-nombre-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                            </small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"
                                      placeholder="Descripción de la categoría (opcional)"
                                      style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); resize: vertical;"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                            Cancelar
                        </button>
                        <button type="submit" class="btn"
                                style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                            <i class="fas fa-save me-1"></i> Registrar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>