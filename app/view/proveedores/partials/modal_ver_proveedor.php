<div class="modal fade" id="modalVerProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>
            <div class="modal-body" style="padding: 20px 24px;">
                <div class="text-center mb-4">
                    <h4 id="ver-proveedor-razon-titulo" style="color: #f39c12; font-weight: 700; margin-bottom: 6px;">—</h4>
                    <span id="ver-proveedor-rif-badge" class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 6px 16px; border-radius: 50px; font-size: 0.75rem;">—</span>
                </div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;"><i class="fas fa-building me-2" style="color: #f39c12;"></i> Información del Proveedor</h6>
                            <div class="mb-3"><label class="text-muted small fw-bold">RIF</label><p id="ver-proveedor-rif" class="mb-0 fw-semibold">—</p></div>
                            <div class="mb-0"><label class="text-muted small fw-bold">Razón Social</label><p id="ver-proveedor-razon" class="mb-0 fw-semibold">—</p></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;"><i class="fas fa-address-book me-2" style="color: #f39c12;"></i> Datos de Contacto</h6>
                            <div class="mb-3"><label class="text-muted small fw-bold">Teléfono</label><p id="ver-proveedor-telefono" class="mb-0">—</p></div>
                            <div class="mb-3"><label class="text-muted small fw-bold">Correo Electrónico</label><p id="ver-proveedor-correo" class="mb-0">—</p></div>
                            <div class="mb-0"><label class="text-muted small fw-bold">Dirección</label><p id="ver-proveedor-direccion" class="mb-0">—</p></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;"><i class="fas fa-times me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>
