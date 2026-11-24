document.addEventListener('DOMContentLoaded', function () {
    
    if (typeof window.escapeHtml !== 'function') {
        window.escapeHtml = function(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
            });
        };
    }

    window.verReciboCierre = function(id) {
        const modalEl = document.getElementById('modalReciboCierre');
        if (!modalEl) return;
        
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const tbody = document.getElementById('hist-tabla-detalles');
        
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-warning"></div></td></tr>';
        }
        
        modal.show();

        // 🔥 Llamamos a la ruta que ya configuramos en el FrontController
        fetch('?url=obtener_cierre_ajax&id=' + encodeURIComponent(id))
            .then(response => {
                if (!response.ok) throw new Error('Error de red o servidor');
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    if (tbody) tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger">${window.escapeHtml(data.mensaje || data.error || 'Error al cargar')}</td></tr>`;
                    return;
                }

                const c = data.cierre || data.nota;
                const d = data.detalles || [];

                // Llenar cabecera
                document.getElementById('hist-fecha').textContent = c.fecha_cierre || '—';
                document.getElementById('hist-hora').textContent = new Date().toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit' });
                document.getElementById('hist-encargado').textContent = c.nombre_usuario || '—';
                document.getElementById('hist-notas').textContent = c.total_documentos || d.length || 0;
                
                let totalUnds = parseInt(c.total_unidades) || 0;
                let prodsDistintos = parseInt(c.total_productos_distintos) || 0;

                if (totalUnds === 0 && d.length > 0) {
                    d.forEach(det => totalUnds += parseInt(det.cantidad) || 0);
                }
                if (prodsDistintos === 0 && d.length > 0) {
                    prodsDistintos = d.length;
                }

                document.getElementById('hist-unidades').textContent = totalUnds;
                document.getElementById('hist-productos').textContent = prodsDistintos;

                // Botón de PDF
                const btnImprimir = document.getElementById('btnImprimirCierreHist');
                if (btnImprimir) {
                    btnImprimir.onclick = function(e) {
                        e.preventDefault();
                        window.open(`?url=exportar_cierre_pdf&id_cierre=${encodeURIComponent(c.id_cierre)}`, '_blank');
                    };
                }

                // Llenar la tabla
                if (tbody) {
                    tbody.innerHTML = '';
                    if (d && d.length > 0) {
                        d.forEach(det => {
                            let hora = '—';
                            if (det.fecha_movimiento && det.fecha_movimiento.includes(' ')) {
                                let partes = det.fecha_movimiento.split(' ');
                                hora = partes[1] + ' ' + (partes[2] || '');
                            }
                            
                            // 🔥 CORRECCIÓN: Usamos det.total_productos y det.total_categorias
                            tbody.innerHTML += `
                                <tr>
                                    <td class="ps-4 fw-bold">#${window.escapeHtml(det.id_documento)}</td>
                                    <td>${window.escapeHtml(hora)}</td>
                                    <td>${window.escapeHtml(det.nombre_cliente)}</td>
                                    <td><span class="badge ${det.tipo_cliente === 'Jurídico' ? 'bg-info text-dark' : 'bg-success'}">${window.escapeHtml(det.tipo_cliente)}</span></td>
                                    <td class="text-center fw-bold">${window.escapeHtml(det.total_productos || 0)} Prod.</td>
                                    <td class="text-center fw-bold">${window.escapeHtml(det.total_categorias || 0)} Cat.</td>
                                    <td class="text-center fw-bold">${window.escapeHtml(det.cantidad || 0)} Und.</td>
                                    <td class="pe-4">${window.escapeHtml(det.usuario_activo)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3">No hay movimientos en este cierre</td></tr>`;
                    }
                }
            })
            .catch(err => {
                if (tbody) tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger">Error: ${window.escapeHtml(err.message)}</td></tr>`;
            });
    };
});