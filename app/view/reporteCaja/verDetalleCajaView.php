<?php
// ============================================================
// PARTIAL: Modal para Ver Detalle de un Movimiento de Caja
// ============================================================
?>

<div class="modal fade" id="modalVerNotaCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> 
                    Detalle de Movimiento <span id="ver-nota-id" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <div id="ver-nota-cargando" class="text-center py-5">
                    <div class="spinner-border" style="color: #f39c12;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3" style="color: #6c757d;">Cargando información...</p>
                </div>

                <div id="ver-nota-error" class="text-center py-5" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3" style="color: #dc3545;"></i>
                    <p id="ver-nota-error-msg" style="color: #6c757d;">Error al cargar</p>
                </div>

                <div id="ver-nota-contenido" style="display: none;">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 14px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos del Movimiento
                                </h6>
                                <div class="mb-2">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Fecha</label>
                                    <p id="ver-nota-fecha" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                                </div>
                                <div class="mb-0">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Responsable</label>
                                    <p id="ver-nota-encargado" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 14px;">
                                    <i class="fas fa-user me-2" style="color: #f39c12;"></i> Cliente
                                </h6>
                                <div class="mb-2">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Nombre</label>
                                    <p id="ver-nota-cliente" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                                </div>
                                <div class="mb-0">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Documento</label>
                                    <p id="ver-nota-documento" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3" style="background: #1a1a2e; border: none;">
                            <h6 class="m-0" style="color: #ffffff; font-weight: 700;">
                                <i class="fas fa-box me-2" style="color: #f39c12;"></i> Productos del Movimiento
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th class="ps-4 py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase;">Producto</th>
                                        <th class="py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase;">Categoría</th>
                                        <th class="pe-4 py-3 text-center" style="color: #495057; font-size: 0.75rem; text-transform: uppercase;">Cant.</th>
                                    </tr>
                                </thead>
                                <tbody id="ver-nota-productos-tbody">
                                </tbody>
                                <tfoot>
                                    <tr style="border-top: 2px solid rgba(243,156,18,0.2);">
                                        <th colspan="2" class="ps-4 py-2 text-end" style="color: #495057; font-size: 0.85rem;">Total Unidades:</th>
                                        <th class="pe-4 py-2 text-center" id="ver-nota-total-unidades" style="color: #f39c12; font-size: 1.1rem; font-weight: 700;">0</th>
                                    </tr>
                                </tfoot>
                            </table>
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

<script>
// Función para abrir el modal de detalle desde el botón de ojo
window.verDetalleCaja = function(id) {
    document.getElementById('ver-nota-cargando').style.display = 'block';
    document.getElementById('ver-nota-error').style.display = 'none';
    document.getElementById('ver-nota-contenido').style.display = 'none';
    document.getElementById('ver-nota-id').textContent = '#' + id;

    new bootstrap.Modal(document.getElementById('modalVerNotaCaja')).show();

    let url = '?url=reportecaja&type=detalle_json&id=' + id;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            document.getElementById('ver-nota-cargando').style.display = 'none';
            
            if (!data.success || !data.nota) {
                document.getElementById('ver-nota-error').style.display = 'block';
                document.getElementById('ver-nota-error-msg').textContent = data.error || 'Error al cargar';
                return;
            }

            const nota = data.nota;
            document.getElementById('ver-nota-contenido').style.display = 'block';

            const fechaFormateada = (typeof Validador !== 'undefined') 
                ? Validador.formatearFecha(nota.fecha || nota.fecha_movimiento) 
                : (nota.fecha || nota.fecha_movimiento || 'N/A');

            document.getElementById('ver-nota-fecha').textContent = fechaFormateada;
            document.getElementById('ver-nota-encargado').textContent = nota.usuario_responsable || 'N/A';
            document.getElementById('ver-nota-cliente').textContent = nota.cliente || 'N/A';
            document.getElementById('ver-nota-documento').textContent = nota.cedula || nota.rif || 'N/A';

            const tbody = document.getElementById('ver-nota-productos-tbody');
            tbody.innerHTML = '';
            let totalUnidades = 0;

            if (nota.detalles && nota.detalles.length > 0) {
                nota.detalles.forEach(function(d) {
                    const cantidad = parseInt(d.cantidad) || 0;
                    totalUnidades += cantidad;
                    
                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-4" style="color: #1a1a2e;">${escapeHtml(d.nombre_producto || 'N/A')}</td>
                            <td style="color: #6c757d;">${escapeHtml(d.nombre_categoria || 'Sin categoría')}</td>
                            <td class="pe-4 text-center fw-bold" style="color: #1a1a2e;">${cantidad}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center py-3" style="color: #6c757d;">No hay productos</td></tr>`;
            }

            document.getElementById('ver-nota-total-unidades').textContent = totalUnidades;
        })
        .catch(err => {
            document.getElementById('ver-nota-cargando').style.display = 'none';
            document.getElementById('ver-nota-error').style.display = 'block';
            document.getElementById('ver-nota-error-msg').textContent = 'Error de conexión: ' + err.message;
        });
};

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
}
</script>