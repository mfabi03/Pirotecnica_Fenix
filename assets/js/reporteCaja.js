window.verDetalleCaja = function(id) {
    document.getElementById('ver-nota-cargando').style.display = 'block';
    document.getElementById('ver-nota-error').style.display = 'none';
    document.getElementById('ver-nota-contenido').style.display = 'none';
    document.getElementById('ver-nota-id').textContent = '#' + id;

    const modalEl = document.getElementById('modalVerNotaCaja');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) { modal = new bootstrap.Modal(modalEl); }
    modal.show();

    let url = '?url=detalle_caja_json&id=' + id; 

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

            const fechaOriginal = nota.fecha || nota.fecha_movimiento || '';
            let fechaFormateada = 'N/A';
            if (fechaOriginal) {
                const soloFecha = fechaOriginal.split(' ')[0]; 
                if (typeof Validador !== 'undefined') { fechaFormateada = Validador.formatearFecha(soloFecha); } 
                else {
                    const partes = soloFecha.split('-');
                    if (partes.length === 3) fechaFormateada = `${partes[2]}/${partes[1]}/${partes[0]}`;
                    else fechaFormateada = soloFecha;
                }
            }
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
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formFiltros');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form); formData.append('ajax', '1');
            fetch('?url=reportecaja', { method: 'POST', body: formData }).then(r => r.json()).then(data => {
                if (!data.success || !data.html) return;
                const wrap = document.createElement('div'); wrap.innerHTML = data.html;
                const nuevaVista = wrap.querySelector('.col-md-8.col-lg-12'); if (!nuevaVista) return;
                const contenedorActual = document.querySelector('.col-md-8.col-lg-12'); if (contenedorActual) contenedorActual.replaceWith(nuevaVista);
            }).catch(() => form.submit());
        });
    }

    const btn = document.getElementById('btnAbrirCierreCaja');
    if (!btn) return;

    const modalEl = document.getElementById('modalCierreCaja');
    const modal = new bootstrap.Modal(modalEl);
    const contenedor = document.getElementById('contenidoCierreCaja');
    
    const fechaSeleccionada = btn.getAttribute('data-fecha') || new Date().toISOString().slice(0, 10);
    
    const btnCerrarCaja = document.getElementById('btnConfirmarCierreCaja');

    if (btnCerrarCaja) {
        btnCerrarCaja.addEventListener('click', async function () {
            btnCerrarCaja.disabled = true;
            btnCerrarCaja.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            // 🔥 Capturar la hora de cierre exacta en formato HH:MM:SS
            const ahora = new Date();
            const horaCierre = ahora.toLocaleTimeString('en-GB', { hour12: false });

            try {
                const response = await fetch('?url=confirmar_cierre_caja', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, 
                    body: new URLSearchParams({ 
                        fecha: fechaSeleccionada,
                        hora_cierre: horaCierre // 🔥 Enviamos la hora de cierre
                    }) 
                });
                const result = await response.json();
                
                if (!result.success) {
                    contenedor.innerHTML = `<div class="alert dark-alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>${escapeHtml(result.mensaje)}</div>`;
                    btnCerrarCaja.disabled = false;
                    btnCerrarCaja.innerHTML = '<i class="fas fa-lock me-1"></i> Cierre de caja';
                    return;
                }

                modal.hide();

                const idCierreNuevo = result.data.id_cierre || 0;
                const urlPdf = `?url=exportar_caja_pdf&id_cierre=${idCierreNuevo}&fecha=${encodeURIComponent(fechaSeleccionada)}`;
                const urlCsv = `?url=exportar_caja_csv&id_cierre=${idCierreNuevo}&fecha=${encodeURIComponent(fechaSeleccionada)}`;
                
                document.getElementById('btnReciboPdf').href = urlPdf;
                document.getElementById('btnReciboExcel').href = urlCsv;

                document.getElementById('recibo-fecha').textContent = result.data.fecha;
                document.getElementById('recibo-hora-apertura').textContent = result.data.hora_apertura || '—';
                document.getElementById('recibo-hora-cierre').textContent = result.data.hora_cierre || '—'; // 🔥 Hora de cierre del backend
                document.getElementById('recibo-encargado').textContent = result.data.encargado;
                document.getElementById('recibo-total-notas').textContent = result.data.total_documentos;
                document.getElementById('recibo-total-unidades').textContent = result.data.total_unidades;
                document.getElementById('recibo-total-productos').textContent = result.data.total_productos;
                document.getElementById('recibo-total-clientes').textContent = result.data.total_clientes;

                const tbody = document.getElementById('recibo-tabla-detalles');
                tbody.innerHTML = '';
                
                if (result.data.detalle && result.data.detalle.length > 0) {
                    const notasAgrupadas = {};
                    result.data.detalle.forEach(m => {
                        const id = m.id_documento;
                        if (!notasAgrupadas[id]) {
                            notasAgrupadas[id] = { id_documento: id, fecha_movimiento: m.fecha_movimiento, nombre_cliente: m.nombre_cliente, tipo_cliente: m.tipo_cliente, usuario_activo: m.usuario_activo, productos: [], categorias: [], totalUnidades: 0 };
                        }
                        let prodLimpio = m.nombre_producto.split('(x')[0].trim();
                        if (!notasAgrupadas[id].productos.includes(prodLimpio)) notasAgrupadas[id].productos.push(prodLimpio);
                        if (m.categoria && !notasAgrupadas[id].categorias.includes(m.categoria)) notasAgrupadas[id].categorias.push(m.categoria);
                        notasAgrupadas[id].totalUnidades += parseInt(m.cantidad) || 0;
                    });

                    Object.values(notasAgrupadas).forEach(nota => {
                        let hora = nota.fecha_movimiento.split(' ')[1] || nota.fecha_movimiento;
                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-4 fw-bold">#${escapeHtml(nota.id_documento)}</td>
                                <td>${escapeHtml(hora)}</td>
                                <td>${escapeHtml(nota.nombre_cliente)}</td>
                                <td><span class="badge ${nota.tipo_cliente === 'Jurídico' ? 'bg-info text-dark' : 'bg-success'}">${escapeHtml(nota.tipo_cliente)}</span></td>
                                <td class="text-center fw-bold">${escapeHtml(nota.productos.length)}</td>
                                <td class="text-center fw-bold">${escapeHtml(nota.categorias.length)}</td>
                                <td class="text-center fw-bold">${escapeHtml(nota.totalUnidades)}</td>
                                <td class="pe-4">${escapeHtml(nota.usuario_activo || 'N/A')}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3">No hay movimientos</td></tr>`;
                }

                new bootstrap.Modal(document.getElementById('modalReciboCierre')).show();

            } catch (error) {
                contenedor.innerHTML = `<div class="alert dark-alert-danger mb-0">Error de conexión.</div>`;
                btnCerrarCaja.disabled = false;
                btnCerrarCaja.innerHTML = '<i class="fas fa-lock me-1"></i> Cierre de caja';
            }
        });
    }

    btn.addEventListener('click', async function () {
        btn.dataset.fecha = fechaSeleccionada;

        contenedor.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-warning" style="width: 3rem; height: 3rem;"></div><p class="mt-3 text-muted fw-bold">Calculando cierre...</p></div>`;
        modal.show();

        try {
            const formData = new FormData();
            formData.append('fecha', fechaSeleccionada);

            const resp = await fetch('?url=cierre_caja_ajax&fecha=' + encodeURIComponent(fechaSeleccionada), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const data = await resp.json();
            
            if (!data.success) { 
                contenedor.innerHTML = `<div class="alert dark-alert-danger mb-0">${escapeHtml(data.mensaje)}</div>`; 
                return; 
            }

            const r = data.data;
            let filasDetalle = '';
            
            if (r.detalle && r.detalle.length > 0) {
                const notasAgrupadas = {};
                r.detalle.forEach(function(m) {
                    const idNota = m.id_documento;
                    if (!notasAgrupadas[idNota]) { 
                        notasAgrupadas[idNota] = { 
                            id_documento: idNota, 
                            fecha_movimiento: m.fecha_movimiento, 
                            usuario_activo: m.usuario_activo || 'Cajero', 
                            cliente: m.nombre_cliente || 'N/A', 
                            tipo_cliente: m.tipo_cliente || 'Natural', 
                            productos: [], 
                            categorias: [], 
                            totalUnidades: 0 
                        }; 
                    }
                    let nombreLimpio = m.nombre_producto || 'Sin producto'; 
                    if (nombreLimpio.includes('(x')) { 
                        nombreLimpio = nombreLimpio.split('(x')[0].trim(); 
                    }
                    if (!notasAgrupadas[idNota].productos.includes(nombreLimpio)) { 
                        notasAgrupadas[idNota].productos.push(nombreLimpio); 
                    }
                    if (m.categoria && !notasAgrupadas[idNota].categorias.includes(m.categoria)) { 
                        notasAgrupadas[idNota].categorias.push(m.categoria); 
                    }
                    notasAgrupadas[idNota].totalUnidades += parseInt(m.cantidad) || 0;
                });

                const notasOrdenadas = Object.values(notasAgrupadas).sort((a, b) => Number(b.id_documento) - Number(a.id_documento));
                
                notasOrdenadas.forEach(nota => {
                    let soloHora = nota.fecha_movimiento;
                    if (soloHora && soloHora.indexOf(' ') > -1) {
                        soloHora = soloHora.split(' ')[1] + ' ' + (soloHora.split(' ')[2] || '');
                    }
                    const badgeColor = (nota.tipo_cliente === 'Jurídico') ? 'background: #e0f2fe; color: #0369a1;' : 'background: #dcfce7; color: #15803d;';
                    
                    filasDetalle += `
                        <tr>
                            <td class="ps-4 fw-bold">#${escapeHtml(nota.id_documento)}</td>
                            <td>${escapeHtml(soloHora)}</td>
                            <td>${escapeHtml(nota.cliente)}</td>
                            <td><span class="badge" style="${badgeColor} padding: 4px 12px; border-radius: 50px; font-weight: 600;">${escapeHtml(nota.tipo_cliente)}</span></td>
                            <td class="text-center fw-bold">${escapeHtml(nota.productos.length)} Prod.</td>
                            <td class="text-center fw-bold">${escapeHtml(nota.categorias.length)} Cat.</td>
                            <td class="text-center fw-bold">${escapeHtml(nota.totalUnidades)} Und.</td>
                            <td>${escapeHtml(nota.usuario_activo)}</td>
                        </tr>`;
                });
            } else { 
                filasDetalle = `<tr><td colspan="8" class="text-center text-muted py-4">No hay notas pendientes por cerrar</td></tr>`; 
            }

            contenedor.innerHTML = `
                <div class="dark-card card shadow-sm dark-table-header border-0 m-0">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0"><i class="fas fa-history me-2"></i> Movimientos del Día</h5>
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
                            <tbody>
                                ${filasDetalle}
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-bold"><i class="fas fa-file-invoice me-1"></i> Total de notas: <span class="text-danger ms-1">${escapeHtml(r.total_documentos)}</span></span>
                        <span class="text-muted small fw-bold">Total unidades: <span class="text-dark ms-1">${escapeHtml(r.total_unidades)}</span></span>
                    </div>
                </div>
            `;
        } catch (e) { 
            contenedor.innerHTML = `<div class="alert dark-alert-danger mb-0">Error de conexión: ${e.message}</div>`; 
        }
    });
});