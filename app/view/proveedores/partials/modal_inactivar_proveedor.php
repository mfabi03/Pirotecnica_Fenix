<?php if ($puede_eliminar_proveedor): ?>
<div class="modal fade" id="modalEliminarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-trash-alt me-2" style="color: #dc3545;"></i> Eliminar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <form id="formEliminarProveedor">
                <input type="hidden" name="id_proveedor" id="eliminar-proveedor-id">
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px;">¿Estás seguro de eliminar este proveedor?</p>
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong class="text-muted d-block small text-uppercase mb-1">PROVEEDOR A ELIMINAR</strong>
                        <span id="eliminar-proveedor-nombre" style="color: #1a1a2e; font-weight: 600;">—</span>
                    </div>
                    <div class="alert alert-danger mb-0" role="alert"><i class="fas fa-info-circle me-2"></i>La eliminación se bloqueará si existen notas de entrada asociadas.</div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">Cancelar</button>
                    <button type="submit" class="btn" style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;"><i class="fas fa-trash-alt me-1"></i> Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
