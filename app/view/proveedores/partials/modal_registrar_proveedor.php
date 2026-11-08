<?php if ($puede_crear_proveedor): ?>
<div class="modal fade" id="modalRegistrarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-truck me-2" style="color: #f39c12;"></i> Registrar Nuevo Proveedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form method="POST" action="?url=proveedores&type=store" class="proveedor-form">
                <div class="modal-body" style="padding: 20px 24px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="registrar-proveedor-rif" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                            <input type="text" id="registrar-proveedor-rif" name="rif" class="form-control proveedor-rif" placeholder="Ej: J-123456789" required>
                        </div>
                        <div class="col-md-6">
                            <label for="registrar-proveedor-telefono" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Número de Contacto *</label>
                            <input type="tel" id="registrar-proveedor-telefono" name="numero_contacto" class="form-control proveedor-telefono" placeholder="0412-1234567" required>
                        </div>
                        <div class="col-12">
                            <label for="registrar-proveedor-razon" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                            <input type="text" id="registrar-proveedor-razon" name="razon_social" class="form-control proveedor-razon" placeholder="Nombre de la empresa" required>
                        </div>
                        <div class="col-md-6">
                            <label for="registrar-proveedor-correo" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico</label>
                            <input type="email" id="registrar-proveedor-correo" name="correo_electronico" class="form-control" placeholder="correo@empresa.com">
                        </div>
                        <div class="col-md-6">
                            <label for="registrar-proveedor-direccion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                            <textarea id="registrar-proveedor-direccion" name="direccion" class="form-control" rows="2" required style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-save me-1"></i> Registrar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
