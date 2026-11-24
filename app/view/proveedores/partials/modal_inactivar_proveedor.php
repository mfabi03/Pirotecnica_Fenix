<?php if ($puede_eliminar): ?>
<div class="modal fade" id="modalEstadoProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" id="estado-proveedor-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i id="estado-proveedor-icono" class="fas fa-trash-alt me-2" style="color: #dc3545;"></i>
                    <span id="estado-proveedor-titulo">Eliminar Proveedor</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <form method="POST" action="?url=proveedores&type=estado_ajax" id="formEstadoProveedor">
                <input type="hidden" name="accion" id="estado-proveedor-accion" value="inactivar">
                <input type="hidden" name="id_proveedor" id="estado-proveedor-id">
                
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p id="estado-proveedor-pregunta" style="margin-bottom: 12px; font-weight: 500;">¿Estás seguro de eliminar este proveedor?</p>
                    
                    <div class="p-3 mb-3" id="estado-proveedor-info-box" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;" id="estado-proveedor-label">Proveedor a eliminar</strong>
                        <span id="estado-proveedor-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>

                    <div class="alert" id="estado-proveedor-alerta" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="estado-proveedor-alerta-texto">Esta acción realizará la <strong>eliminación lógica</strong> del proveedor. Si el proveedor tiene notas de entrada asociadas, el sistema bloqueará la eliminación.</span>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" id="estado-proveedor-btn-confirmar"
                            style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-trash-alt me-1"></i> Sí, eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>