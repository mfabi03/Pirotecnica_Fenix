<?php if ($puede_eliminar_proveedor): ?>
<div class="modal fade" id="modalReactivarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(40,167,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-undo-alt me-2" style="color: #28a745;"></i> Reactivar Proveedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form id="formReactivarProveedor">
                <input type="hidden" name="id_proveedor" id="reactivar-proveedor-id">
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px;">¿Deseas reactivar este proveedor?</p>
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Proveedor a reactivar</strong>
                        <span id="reactivar-proveedor-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>
                    <div class="alert" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                        <i class="fas fa-check-circle me-2"></i>
                        El proveedor volverá a estar <strong>Activo</strong> y disponible en el sistema.
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: #28a745; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-check me-1"></i> Sí, reactivar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
