<?php
// ============================================================
// PARTIAL: Modal para Editar Cliente (Natural y Jurídico)
// ============================================================
// - NO incluye header.php ni footer.php (es un fragmento).
// - Se incluye desde listClienteView.php con require_once.
// - Contiene 2 formularios: Natural y Jurídico.
// - El JS decide cuál mostrar según el tipo de cliente.
// ============================================================

// Fallback: si la vista se incluye fuera de contexto, permitir edición
if (!isset($puede_editar_cliente)) {
    $puede_editar_cliente = true;
}
?>

<?php if ($puede_editar_cliente): ?>
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <!-- HEADER OSCURO DEL MODAL -->
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-edit me-2" style="color: #f39c12;"></i> 
                    Editar Cliente: <span id="editar-cliente-nombre-header" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">

                <!-- ============================================================ -->
                <!-- FORMULARIO 1: CLIENTE NATURAL -->
                <!-- ============================================================ -->
                <!-- Se muestra cuando el cliente es de tipo "Natural". -->
                <!-- El campo 'cedula' tiene validación de unicidad vía AJAX. -->
                <!-- ============================================================ -->
                <div id="editar-bloque-natural" style="display: none;">
                    <form method="POST" action="?url=clientes&type=edit" id="formEditarNatural">
                        <!-- Campo oculto: indica al controller qué acción ejecutar -->
                        <input type="hidden" name="accion" value="edit_natural">
                        <!-- ID del cliente (necesario para el UPDATE) -->
                        <input type="hidden" name="id_cliente" id="editar-natural-id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula *</label>
                                <!-- data-unico-endpoint: consulta AJAX para validar duplicado -->
                                <!-- data-unico-campo: nombre del campo en el endpoint -->
                                <input type="text" id="editar-natural-cedula" name="cedula" class="form-control" required
                                       data-validate="cedula"
                                       data-unico-endpoint="?url=clientes&type=check_duplicado"
                                       data-unico-campo="cedula">
                                <!-- Feedback visual del validador (verde/rojo) -->
                                <small class="feedback-validacion info" id="editar-natural-cedula-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                <input type="tel" id="editar-natural-telefono" name="telefono" class="form-control" required data-validate="telefono">
                                <small class="feedback-validacion info" id="editar-natural-telefono-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombres *</label>
                                <input type="text" id="editar-natural-nombre" name="nombre" class="form-control" required data-validate="nombre">
                                <small class="feedback-validacion info" id="editar-natural-nombre-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Apellidos *</label>
                                <input type="text" id="editar-natural-apellido" name="apellido" class="form-control" required data-validate="nombre">
                                <small class="feedback-validacion info" id="editar-natural-apellido-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                <input type="email" id="editar-natural-correo" name="correo_electronico" class="form-control" required data-validate="correo">
                                <small class="feedback-validacion info" id="editar-natural-correo-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Fecha de Nacimiento *</label>
                                <!-- data-validate="fecha" valida que sea mayor de 18 años -->
                                <input type="date" id="editar-natural-fecha" name="fecha_de_nacimiento" class="form-control" required data-validate="fecha">
                                <small class="feedback-validacion info" id="editar-natural-fecha-feedback"></small>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                                <!-- Sin data-validate para permitir escribir libremente -->
                                <textarea id="editar-natural-direccion" name="direccion" class="form-control" rows="3" required style="resize: vertical; min-height: 70px;" placeholder="Escribe la dirección completa..."></textarea>
                                <small class="text-muted" style="font-size: 0.7rem;">Mínimo 5 caracteres</small>
                            </div>
                        </div>

                        <!-- BOTONES: Cancelar (cierra modal) y Guardar (envía POST) -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                    style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                                Cancelar
                            </button>
                            <button type="submit" class="btn"
                                    style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                                <i class="fas fa-save me-1"></i> Actualizar Cliente
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ============================================================ -->
                <!-- FORMULARIO 2: CLIENTE JURÍDICO -->
                <!-- ============================================================ -->
                <!-- Se muestra cuando el cliente es de tipo "Jurídico". -->
                <!-- El campo 'rif' tiene validación de unicidad vía AJAX. -->
                <!-- ============================================================ -->
                <div id="editar-bloque-juridico" style="display: none;">
                    <form method="POST" action="?url=clientes&type=edit_juridico" id="formEditarJuridico">
                        <!-- Campo oculto: indica al controller qué acción ejecutar -->
                        <input type="hidden" name="accion" value="edit_juridico">
                        <!-- ID del cliente (necesario para el UPDATE) -->
                        <input type="hidden" name="id_cliente" id="editar-juridico-id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                                <!-- data-unico-endpoint: consulta AJAX para validar duplicado -->
                                <input type="text" id="editar-juridico-rif" name="rif" class="form-control" required
                                       data-validate="rif"
                                       data-unico-endpoint="?url=clientes&type=check_duplicado"
                                       data-unico-campo="rif">
                                <small class="feedback-validacion info" id="editar-juridico-rif-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                <input type="tel" id="editar-juridico-telefono" name="telefono" class="form-control" required data-validate="telefono">
                                <small class="feedback-validacion info" id="editar-juridico-telefono-feedback"></small>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                                <input type="text" id="editar-juridico-razon" name="razon_social" class="form-control" required data-validate="razon">
                                <small class="feedback-validacion info" id="editar-juridico-razon-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                <input type="email" id="editar-juridico-correo" name="correo_electronico" class="form-control" required data-validate="correo">
                                <small class="feedback-validacion info" id="editar-juridico-correo-feedback"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección Fiscal *</label>
                                <input type="text" id="editar-juridico-direccion" name="direccion" class="form-control" required>
                            </div>
                        </div>

                        <!-- BOTONES: Cancelar (cierra modal) y Guardar (envía POST) -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                    style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                                Cancelar
                            </button>
                            <button type="submit" class="btn"
                                    style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                                <i class="fas fa-save me-1"></i> Actualizar Cliente
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
<?php endif; ?>