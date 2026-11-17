<div class="modal fade" id="modalVerNotaSalida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-white" style="border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i>
                    Detalle de Nota de Salida <span id="ver-salida-id" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                <div id="ver-salida-cargando" class="text-center py-5">
                    <div class="spinner-border" style="color: #f39c12;" role="status"></div>
                    <p class="mt-3 text-muted">Cargando información de la nota...</p>
                </div>
                <div id="ver-salida-error" class="text-center py-5" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3" style="color: #dc3545;"></i>
                    <p id="ver-salida-error-msg" class="text-muted">Error al cargar la nota.</p>
                </div>

                <div id="ver-salida-contenido" style="display: none;">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 h-100" style="background: #f8f9fa; border-radius: 12px;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 14px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos de la Nota
                                </h6>
                                <div class="mb-2">
                                    <label class="text-muted small fw-semibold text-uppercase">Fecha</label>
                                    <p id="ver-salida-fecha" class="mb-0 fw-semibold text-dark">—</p>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small fw-semibold text-uppercase">Hora</label>
                                    <p id="ver-salida-hora" class="mb-0 text-dark">—</p>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small fw-semibold text-uppercase">Encargado</label>
                                    <p id="ver-salida-encargado" class="mb-0 text-dark">—</p>
                                </div>
                                <div>
                                    <label class="text-muted small fw-semibold text-uppercase">Estado</label>
                                    <p class="mb-0"><span id="ver-salida-estado" class="badge">—</span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 h-100" style="background: #f8f9fa; border-radius: 12px;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 14px;">
                                    <i class="fas fa-user me-2" style="color: #f39c12;"></i> Cliente
                                </h6>
                                <div class="mb-2">
                                    <label class="text-muted small fw-semibold text-uppercase">Nombre / Razón Social</label>
                                    <p id="ver-salida-cliente" class="mb-0 fw-semibold text-dark">—</p>
                                </div>
                                <div>
                                    <label class="text-muted small fw-semibold text-uppercase">Cédula / RIF</label>
                                    <p id="ver-salida-identificacion" class="mb-0 text-dark">—</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3" style="background: #1a1a2e; border: none;">
                            <h6 class="m-0" style="color: #ffffff; font-weight: 700;">
                                <i class="fas fa-box me-2" style="color: #f39c12;"></i> Productos de la Nota
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th class="ps-3 py-3">#</th>
                                        <th class="py-3">PRODUCTO</th>
                                        <th class="py-3">CATEGORÍA</th>
                                        <th class="text-center py-3">CANT.</th>
                                    </tr>
                                </thead>
                                <tbody id="ver-salida-productos-tbody"></tbody>
                                <tfoot>
                                    <tr style="border-top: 2px solid rgba(243,156,18,0.2); background: #f8f9fa;">
                                        <th colspan="3" class="ps-4 py-3 text-end">TOTAL DE UNIDADES:</th>
                                        <th id="ver-salida-total-unidades" class="text-center py-3" style="color: #f39c12; font-size: 1.1rem;">0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <a href="#" id="btnImprimirNotaSalida" target="_blank" rel="noopener" class="btn btn-danger" style="display: none; border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-file-pdf me-1"></i> Imprimir PDF
                </a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
