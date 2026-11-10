document.addEventListener('DOMContentLoaded', function () {
    window.verReciboCierre = function(id) {
        const modalEl = document.getElementById('modalReciboCierre');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        
        document.getElementById('hist-tabla-detalles').innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-warning"></div></td></tr>';
        modal.show();

        fetch('?url=obtener_cierre_ajax&id=' + id)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    document.getElementById('hist-tabla-detalles').innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger">${data.mensaje}</td></tr>`;
                    return;
                }

                const c = data.cierre;
                const d = data.detalles;

                document.getElementById('hist-fecha').textContent = c.fecha_cierre;
                document.getElementById('hist-hora').textContent = new Date(c.created_at).toLocaleTimeString('es-VE');
                document.getElementById('hist-encargado').textContent = c.nombre_usuario;
                document.getElementById('hist-notas').textContent = c.total_documentos;
                document.getElementById('hist-unidades').textContent = c.total_unidades;

                let prodsDistintos = parseInt(c.total_productos_distintos) || 0;
                if (prodsDistintos === 0 && d && d.length > 0) {
                    prodsDistintos = d.length; 
                }
                document.getElementById('hist-productos').textContent = prodsDistintos;

                const btnImprimir = document.getElementById('btnImprimirCierreHist');
                if (btnImprimir) {
                    btnImprimir.onclick = function() {
                        window.open(`?url=exportar_cierre_pdf&id_cierre=${c.id_cierre}`, '_blank');
                    };
                }

                const tbody = document.getElementById('hist-tabla-detalles');
                tbody.innerHTML = '';

                if (d && d.length > 0) {
                    d.forEach(det => {
                        let hora = det.fecha.split(' ')[1] || det.fecha;
                        const prodArray = det.productos ? det.productos.split(', ') : [];
                        const catArray = det.categorias ? det.categorias.split(', ') : [];

                        tbody.innerHTML += `
                            <tr>
                                <td class="ps-4 fw-bold">#${det.id_documento}</td>
                                <td>${hora}</td>
                                <td>${det.nombre_cliente}</td>
                                <td><span class="badge ${det.tipo_cliente === 'Jurídico' ? 'bg-info text-dark' : 'bg-success'}">${det.tipo_cliente}</span></td>
                                <td class="text-center fw-bold">${prodArray.length}</td>
                                <td class="text-center fw-bold">${catArray.length}</td>
                                <td class="text-center fw-bold">${det.total_unidades}</td>
                                <td class="pe-4">${det.usuario_activo}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3">No hay movimientos en este cierre</td></tr>`;
                }
            })
            .catch(err => {
                document.getElementById('hist-tabla-detalles').innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger">Error de conexión</td></tr>`;
            });
    };
});