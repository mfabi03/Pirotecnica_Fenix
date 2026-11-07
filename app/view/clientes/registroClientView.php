<?php
// ============================================================
// PARTIAL: Modal para Registrar Cliente (Natural y Jurídico)
// ============================================================
// - NO incluye header.php ni footer.php (es un fragmento).
// - Se incluye desde listClienteView.php con require_once.
// - Contiene 2 formularios: Natural y Jurídico (en pestañas).
// - Incluye el modal de alerta de "Menor de Edad".
// ============================================================

// Fallback: si la vista se incluye fuera de contexto, permitir crear
if (!isset($puede_crear_cliente)) {
    $puede_crear_cliente = true;
}
?>

<?php if ($puede_crear_cliente): ?>
<div class="modal fade" id="modalRegistrarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <?php // HEADER OSCURO DEL MODAL ?>
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-user-plus me-2" style="color: #f39c12;"></i> Registrar Nuevo Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <?php // PESTAÑAS: Natural / Jurídico ?>
                <ul class="nav nav-tabs mb-4" style="border-bottom: 1px solid #e9ecef;">
                    <li class="nav-item">
                        <button class="nav-link active" id="tab-natural-reg" data-bs-toggle="tab" data-bs-target="#pane-natural-reg" type="button"
                                style="color: #f39c12; background: transparent; border: none; border-bottom: 2px solid #f39c12; font-weight: 600;">
                            <i class="fas fa-user me-1"></i> Cliente Natural
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-juridico-reg" data-bs-toggle="tab" data-bs-target="#pane-juridico-reg" type="button"
                                style="color: #6c757d; background: transparent; border: none; font-weight: 600;">
                            <i class="fas fa-building me-1"></i> Cliente Jurídico
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <?php // ============================================================ ?>
                    <?php // PESTAÑA: CLIENTE NATURAL                                ?>
                    <?php // ============================================================ ?>
                    <div class="tab-pane fade show active" id="pane-natural-reg">
                        <form method="POST" action="?url=clientes&type=register" id="formRegistrarNatural">
                            <?php // Campo oculto: indica al controller qué acción ejecutar ?>
                            <input type="hidden" name="accion" value="register_natural">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula *</label>
                                    <?php // data-unico-endpoint: consulta AJAX para validar duplicado ?>
                                    <input type="text" id="reg-cedula" name="cedula" class="form-control" required
                                           placeholder="Ej: V-12345678"
                                           data-validate="cedula"
                                           data-unico-endpoint="?url=clientes&type=check_duplicado"
                                           data-unico-campo="cedula">
                                    <?php // Feedback visual del validador (verde/rojo) ?>
                                    <small class="feedback-validacion info" id="reg-cedula-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: V-12345678
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                    <input type="tel" id="reg-telefono" name="telefono" class="form-control" required
                                           placeholder="Ej: 0412-5556677" data-validate="telefono">
                                    <small class="feedback-validacion info" id="reg-telefono-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: 0412-1234567
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombres *</label>
                                    <input type="text" id="reg-nombre" name="nombre" class="form-control" required
                                           placeholder="Solo letras" data-validate="nombre">
                                    <small class="feedback-validacion info" id="reg-nombre-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 2 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Apellidos *</label>
                                    <input type="text" id="reg-apellido" name="apellido" class="form-control" required
                                           placeholder="Solo letras" data-validate="nombre">
                                    <small class="feedback-validacion info" id="reg-apellido-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 2 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                    <input type="email" id="reg-correo" name="correo_electronico" class="form-control" required
                                           placeholder="ejemplo@correo.com" data-validate="correo">
                                    <small class="feedback-validacion info" id="reg-correo-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Fecha de Nacimiento *</label>
                                    <?php // data-validate="fecha" valida que sea mayor de 18 años ?>
                                    <input type="date" id="reg-fecha-nac" name="fecha_de_nacimiento" class="form-control" required data-validate="fecha">
                                    <small class="feedback-validacion info" id="reg-fecha-nac-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Debe ser mayor de 18 años
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                                    <?php // Sin data-validate para permitir escribir libremente ?>
                                    <textarea id="reg-direccion" name="direccion" class="form-control" rows="3" required
                                              style="resize: vertical; min-height: 70px;" 
                                              placeholder="Escribe la dirección completa..."></textarea>
                                    <small class="text-muted" style="font-size: 0.7rem;">Mínimo 5 caracteres</small>
                                </div>
                            </div>

                            <?php // BOTONES: Cancelar (cierra modal) y Registrar (envía POST) ?>
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn"
                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                                    <i class="fas fa-save me-1"></i> Registrar Cliente Natural
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php // ============================================================ ?>
                    <?php // PESTAÑA: CLIENTE JURÍDICO                               ?>
                    <?php // ============================================================ ?>
                    <div class="tab-pane fade" id="pane-juridico-reg">
                        <form method="POST" action="?url=clientes&type=register_juridico" id="formRegistrarJuridico">
                            <?php // Campo oculto: indica al controller qué acción ejecutar ?>
                            <input type="hidden" name="accion" value="register_juridico">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                                    <?php // data-unico-endpoint: consulta AJAX para validar duplicado ?>
                                    <input type="text" id="reg-rif" name="rif" class="form-control" required
                                           placeholder="Ej: J-123456789"
                                           data-validate="rif"
                                           data-unico-endpoint="?url=clientes&type=check_duplicado"
                                           data-unico-campo="rif">
                                    <?php // Feedback visual del validador (verde/rojo) ?>
                                    <small class="feedback-validacion info" id="reg-rif-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: J-123456789
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                                    <input type="tel" id="reg-jur-telefono" name="telefono" class="form-control" required
                                           placeholder="Ej: 0251-1234567" data-validate="telefono">
                                    <small class="feedback-validacion info" id="reg-jur-telefono-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: 0251-1234567
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                                    <input type="text" id="reg-jur-razon" name="razon_social" class="form-control" required
                                           placeholder="Nombre de la empresa" data-validate="razon">
                                    <small class="feedback-validacion info" id="reg-jur-razon-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo Electrónico *</label>
                                    <input type="email" id="reg-jur-correo" name="correo_electronico" class="form-control" required
                                           placeholder="empresa@correo.com" data-validate="correo">
                                    <small class="feedback-validacion info" id="reg-jur-correo-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección Fiscal *</label>
                                    <input type="text" id="reg-jur-direccion" name="direccion" class="form-control" required
                                           placeholder="Dirección fiscal" data-validate="direccion">
                                    <small class="feedback-validacion info" id="reg-jur-direccion-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 5 caracteres
                                    </small>
                                </div>
                            </div>

                            <?php // BOTONES: Cancelar (cierra modal) y Registrar (envía POST) ?>
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn"
                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                                    <i class="fas fa-save me-1"></i> Registrar Cliente Jurídico
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<?php // ============================================================ ?>
<?php // MODAL: ALERTA DE MENOR DE EDAD (fuera del modal principal)  ?>
<?php // ============================================================ ?>
<div class="modal fade" id="modalAlertaMenorEdad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <?php // HEADER OSCURO DEL MODAL (rojo para alerta) ?>
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-user-slash me-2" style="color: #dc3545;"></i> Cliente Menor de Edad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 24px; text-align: center;">
                <?php // Ícono de advertencia grande ?>
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #dc3545; margin-bottom: 16px;"></i>
                
                <h4 style="color: #1a1a2e; font-weight: 700; margin-bottom: 12px;">
                    ¡No se puede registrar!
                </h4>
                
                <p style="color: #6c757d; margin-bottom: 20px;">
                    El cliente es <strong>menor de 18 años</strong>. Por normativa de seguridad, no se permite registrar menores de edad en el sistema.
                </p>

                <?php // Muestra la edad detectada (se rellena desde el JS) ?>
                <div class="p-3 mb-0" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px; text-align: left;">
                    <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Edad detectada</strong>
                    <span id="alerta-edad-detectada" style="color: #dc3545; font-weight: 700; font-size: 1.2rem;">—</span>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                        style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; width: 100%;">
                    <i class="fas fa-check me-1"></i> Entendido
                </button>
            </div>
        </div>
    </div>
</div>