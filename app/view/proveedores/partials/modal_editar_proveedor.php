<?php if ($puede_editar_proveedor): ?>
<div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-edit me-2" style="color: #f39c12;"></i> Editar Proveedor: <span id="editar-proveedor-razon-header" style="color: #f39c12;">—</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form method="POST" action="?url=proveedores&type=update" class="proveedor-form" id="formEditarProveedor">
                <input type="hidden" name="id_proveedor" id="editar-proveedor-id">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editar-proveedor-rif" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                            <input type="text" id="editar-proveedor-rif" name="rif" class="form-control proveedor-rif" required data-validate="rif">
                            <small class="feedback-validacion info" id="editar-proveedor-rif-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label for="editar-proveedor-telefono" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Número de Contacto *</label>
                            <input type="tel" id="editar-proveedor-telefono" name="numero_contacto" class="form-control proveedor-telefono" required data-validate="telefono">
                            <small class="feedback-validacion info" id="editar-proveedor-telefono-feedback"></small>
                        </div>
                        <div class="col-12">
                            <label for="editar-proveedor-razon" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                            <input type="text" id="editar-proveedor-razon" name="razon_social" class="form-control proveedor-razon" required data-validate="razon">
                            <small class="feedback-validacion info" id="editar-proveedor-razon-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label for="editar-proveedor-correo" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico</label>
                            <input type="email" id="editar-proveedor-correo" name="correo_electronico" class="form-control" data-validate="correo">
                            <small class="feedback-validacion info" id="editar-proveedor-correo-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label for="editar-proveedor-direccion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                            <textarea id="editar-proveedor-direccion" name="direccion" class="form-control" rows="2" required data-validate="direccion" style="resize: vertical;"></textarea>
                            <small class="feedback-validacion info" id="editar-proveedor-direccion-feedback"></small>
                        </div>
                        <div class="col-md-6">
                            <label for="editar-proveedor-eliminado" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Estado *</label>
                            <select id="editar-proveedor-eliminado" name="eliminado" class="form-select" required>
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;"><i class="fas fa-save me-1"></i> Actualizar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
