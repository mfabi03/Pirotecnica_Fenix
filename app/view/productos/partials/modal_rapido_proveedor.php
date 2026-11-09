<!-- MODAL RÁPIDO DE PROVEEDOR (PARA PRODUCTOS) -->
<div class="modal fade" id="modalRapidoProveedorProducto" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-truck me-2" style="color: #f39c12;"></i> Registrar Proveedor Rápido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <div id="modal-rapido-proveedor-error" class="alert" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="modal-rapido-proveedor-error-msg"></span>
                </div>

                <form id="formRapidoProveedorProducto">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                            <input type="text" id="rapido-prov-rif-prod" name="rif" class="form-control" required
                                   placeholder="Ej: J-123456789" data-validate="rif">
                            <small class="feedback-validacion info" id="rapido-prov-rif-prod-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: J-123456789
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                            <input type="tel" id="rapido-prov-telefono-prod" name="numero_contacto" class="form-control" required
                                   placeholder="Ej: 0412-5556677" data-validate="telefono">
                            <small class="feedback-validacion info" id="rapido-prov-telefono-prod-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: 0412-1234567
                            </small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                            <input type="text" id="rapido-prov-razon-prod" name="razon_social" class="form-control" required
                                   placeholder="Nombre de la empresa" data-validate="razon">
                            <small class="feedback-validacion info" id="rapido-prov-razon-prod-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo</label>
                            <input type="email" id="rapido-prov-correo-prod" name="correo_electronico" class="form-control"
                                   placeholder="proveedor@correo.com" data-validate="correo">
                            <small class="feedback-validacion info" id="rapido-prov-correo-prod-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                            <input type="text" id="rapido-prov-direccion-prod" name="direccion" class="form-control" required
                                   placeholder="Dirección del proveedor" data-validate="direccion">
                            <small class="feedback-validacion info" id="rapido-prov-direccion-prod-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 5 caracteres
                            </small>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    Cancelar
                </button>
                <button type="button" class="btn" id="btnGuardarProveedorRapidoProd"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                    <i class="fas fa-save me-1"></i> Guardar Proveedor
                </button>
            </div>
        </div>
    </div>
</div>