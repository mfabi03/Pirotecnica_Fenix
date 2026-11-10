<?php
// ============================================================
// PARTIAL: Modales para Eliminar y Reactivar Cliente
// ============================================================
// Nombre técnico del archivo: eliminarClientView.php
// Nombre visual para el usuario: Eliminar / Reactivar
// - NO incluye header.php ni footer.php (es un fragmento).
// - Se incluye desde listClienteView.php con require_once.
// ============================================================

// Fallback: si la vista se incluye fuera de contexto, permitir eliminar
if (!isset($puede_eliminar_cliente)) {
    $puede_eliminar_cliente = true;
}
?>

<?php if ($puede_eliminar_cliente): ?>

<?php // ============================================================ ?>
<?php // MODAL: ELIMINAR CLIENTE (Eliminación Lógica)             ?>
<?php // ============================================================ ?>
<div class="modal fade" id="modalEliminarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <?php // HEADER OSCURO DEL MODAL ?>
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-trash-alt me-2" style="color: #dc3545;"></i> Eliminar Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <?php // FORMULARIO: envía POST a ?url=clientes&type=delete ?>
            <form method="POST" action="?url=clientes&type=delete">
                <?php // Campo oculto: indica al controller qué acción ejecutar ?>
                <input type="hidden" name="accion" value="eliminar">
                <?php // ID del cliente (se rellena desde el JS) ?>
                <input type="hidden" name="id_cliente" id="eliminar-cliente-id">
                
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px; font-weight: 500;">¿Estás seguro de eliminar este cliente?</p>
                    
                    <?php // Nombre del cliente (se rellena desde el JS) ?>
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Cliente a eliminar</strong>
                        <span id="eliminar-cliente-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>

                    <?php // Advertencia: la eliminación es lógica (no se borra de la BD) ?>
                    <div class="alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                        <i class="fas fa-info-circle me-2"></i>
                        Esta acción realizará la <strong>eliminación lógica</strong> del cliente. Si el cliente tiene notas de entrada o salida asociadas, el sistema bloqueará la eliminación.
                    </div>
                </div>

                <?php // BOTONES: Cancelar (cierra modal) y Eliminar (envía POST) ?>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn"
                            style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-trash-alt me-1"></i> Sí, eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php // ============================================================ ?>
<?php // MODAL: REACTIVAR CLIENTE                                  ?>
<?php // ============================================================ ?>
<div class="modal fade" id="modalReactivarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <?php // HEADER OSCURO DEL MODAL (verde para reactivar) ?>
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(40,167,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-check-circle me-2" style="color: #28a745;"></i> Reactivar Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <?php // FORMULARIO: envía POST a ?url=clientes&type=reactivar ?>
            <form method="POST" action="?url=clientes&type=reactivar">
                <?php // Campo oculto: indica al controller qué acción ejecutar ?>
                <input type="hidden" name="accion" value="reactivar">
                <?php // ID del cliente (se rellena desde el JS) ?>
                <input type="hidden" name="id_cliente" id="reactivar-cliente-id">
                
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px; font-weight: 500;">¿Deseas reactivar este cliente?</p>
                    
                    <?php // Nombre del cliente (se rellena desde el JS) ?>
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Cliente a reactivar</strong>
                        <span id="reactivar-cliente-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>

                    <?php // Advertencia: el cliente vuelve a estar disponible ?>
                    <div class="alert" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                        <i class="fas fa-check-circle me-2"></i>
                        El cliente volverá a estar <strong>Activo</strong> y aparecerá disponible en los selectores de futuras notas.
                    </div>
                </div>

                <?php // BOTONES: Cancelar (cierra modal) y Reactivar (envía POST) ?>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn"
                            style="background: #28a745; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-check me-1"></i> Sí, reactivar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>