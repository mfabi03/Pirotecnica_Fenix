<!-- ============================================================ -->
<!-- MODAL 2: VER DETALLE DEL PRODUCTO                            -->
<!-- ============================================================ -->
<div class="modal fade" id="modalVerProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <!-- HEADER OSCURO -->
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> Detalle del Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <!-- BODY BLANCO -->
            <div class="modal-body" style="padding: 20px 24px;">
                
                <!-- Título y badge -->
                <div class="text-center mb-4">
                    <h4 id="ver-producto-nombre" style="color: #f39c12; font-weight: 700; margin-bottom: 6px;">—</h4>
                    <span id="ver-producto-categoria" class="badge" style="background: #e9ecef; color: #1a1a2e; padding: 6px 16px; border-radius: 50px; font-size: 0.75rem;">—</span>
                </div>

                <div class="row g-4">
                    <!-- COLUMNA IZQUIERDA -->
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Información del Producto
                            </h6>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Código</label>
                                <p id="ver-producto-codigo" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                            </div>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Nombre</label>
                                <p id="ver-producto-nombre-detalle" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                            </div>
                            <div class="mb-0">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Proveedor</label>
                                <p id="ver-producto-proveedor" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- COLUMNA DERECHA -->
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                            <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                                <i class="fas fa-chart-line me-2" style="color: #f39c12;"></i> Datos de Inventario
                            </h6>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Stock Actual</label>
                                <p id="ver-producto-stock" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                            </div>
                            <div class="mb-3">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Stock Mínimo</label>
                                <p id="ver-producto-stock-minimo" style="color: #1a1a2e; margin: 0;">—</p>
                            </div>
                            <div class="mb-0">
                                <label style="color: #6c757d; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Costo Unitario</label>
                                <p id="ver-producto-costo" style="color: #f39c12; margin: 0; font-weight: 700; font-size: 1.2rem;">—</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ESPECIFICACIONES -->
                <div class="mt-4">
                    <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                        <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                            <i class="fas fa-list me-2" style="color: #f39c12;"></i> Especificaciones Técnicas
                        </h6>
                        <p id="ver-producto-especificaciones" style="color: #1a1a2e; margin: 0; white-space: pre-wrap;">—</p>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>