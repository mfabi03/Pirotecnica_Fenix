<?php
// ============================================================
// PARTIAL: Modal para Ver Detalle del Cliente
// ============================================================
?>

<div class="modal fade" id="modalVerCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <div id="ver-cliente-titulo" class="text-center mb-4">
                    <h4 style="color: #f39c12; font-weight: 700; margin-bottom: 6px;">—</h4>
                    <span id="ver-cliente-tipo-badge" class="badge" style="padding: 6px 16px; border-radius: 50px; font-size: 0.75rem;">—</span>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Información del Cliente
                            </h6>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">ID Cliente</label>
                                <p id="ver-cliente-id" style="color: #1a1a2e; margin: 0; font-weight: 700;">—</p>
                            </div>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Status</label>
                                <div id="ver-cliente-status-wrap">
                                    <span id="ver-cliente-status-badge" class="badge" style="padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem;">—</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Cédula / RIF</label>
                                <p id="ver-cliente-documento" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                            </div>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Nombre / Razón Social</label>
                                <p id="ver-cliente-nombre" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                            </div>
                            <div class="mb-0" id="ver-cliente-fecha-wrap">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Fecha de Nacimiento</label>
                                <p id="ver-cliente-fecha" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                <i class="fas fa-address-book me-2" style="color: #f39c12;"></i> Datos de Contacto
                            </h6>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Teléfono</label>
                                <p id="ver-cliente-telefono" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Correo Electrónico</label>
                                <p id="ver-cliente-correo" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                            <div class="mb-0">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Dirección</label>
                                <p id="ver-cliente-direccion" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>