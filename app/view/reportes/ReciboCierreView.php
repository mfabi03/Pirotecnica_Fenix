<?php
// app/view/reportes/ReciboCierreView.php
?>
<!-- MODAL DE RECIBO DE CIERRE -->
<div class="modal fade" id="modalReciboCierre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: #1a1a2e; padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;"><i class="fas fa-receipt me-2" style="color: #f39c12;"></i> Detalle del Cierre de Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div class="d-flex align-items-center justify-content-center mb-4 gap-3">
                    <img src="assets/imagenes/logo.png" alt="Logo" style="width: 60px; height: 60px; object-fit: contain;">
                    <div>
                        <h4 style="color: #1a1a2e; font-weight: 800; margin-bottom: 0;">PIROTECNICA FENIX C.A.</h4>
                        <p style="color: #6c757d; font-size: 0.85rem; margin: 0;">Sistema de gestión - Arqueo de Turno</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef;">
                            <h6 style="font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px;">Datos del Turno</h6>
                            <div class="mb-2"><strong>Fecha:</strong> <span id="hist-fecha"></span></div>
                            <div class="mb-2"><strong>Hora Cierre:</strong> <span id="hist-hora"></span></div>
                            <div class="mb-0"><strong>Encargado:</strong> <span id="hist-encargado"></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef;">
                            <h6 style="font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px;">Resumen</h6>
                            <div class="mb-2"><strong>Total Notas:</strong> <span id="hist-notas"></span></div>
                            <div class="mb-2"><strong>Total Unidades:</strong> <span id="hist-unidades"></span></div>
                            <div class="mb-0"><strong>Prods. Distintos:</strong> <span id="hist-productos"></span></div>
                        </div>
                    </div>
                </div>

                <div class="card" style="border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                    <div class="card-header py-3" style="background: #1a1a2e; border: none;">
                        <h6 class="m-0" style="color: #ffffff; font-weight: 700;"><i class="fas fa-list me-2" style="color: #f39c12;"></i> Detalle de Movimientos</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 py-3">NOTA #</th>
                                    <th class="py-3">HORA</th>
                                    <th class="py-3">CLIENTE</th>
                                    <th class="py-3">TIPO</th>
                                    <th class="py-3 text-center">PRODUCTOS</th>
                                    <th class="py-3 text-center">CATEGORÍAS</th>
                                    <th class="py-3 text-center">UNIDADES</th>
                                    <th class="pe-4 py-3">ENCARGADO</th>
                                </tr>
                            </thead>
                            <tbody id="hist-tabla-detalles"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: space-between;">
                <button type="button" id="btnImprimirCierreHist" class="btn btn-danger">
                    <i class="fas fa-file-pdf me-1"></i> Imprimir este Cierre
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>