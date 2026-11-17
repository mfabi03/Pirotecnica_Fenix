(function () {
    const formatNumber = new Intl.NumberFormat('es-VE');
    const config = JSON.parse(document.getElementById('dashboardConfig').textContent);
    let lastNotaEntradaId = Number(config.lastNotaEntradaId || 0);
    let lastNotaSalidaId = Number(config.lastNotaSalidaId || 0);
    const puedeVerEntradas = config.puedeVerEntradas === true;

    document.querySelectorAll('.dashboard-stat-interactive').forEach(function (card) {
        card.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            const modalElement = document.querySelector(card.getAttribute('data-bs-target'));
            if (modalElement) bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    });

    function renderNotas(tbody, notas, tipo) {
        if (!tbody) return;
        tbody.replaceChildren();

        if (!Array.isArray(notas) || notas.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 5;
            cell.className = 'text-center text-secondary py-3';
            cell.textContent = tipo === 'entrada'
                ? 'No hay notas de entrada recientes.'
                : 'No hay notas de salida recientes.';
            row.appendChild(cell);
            tbody.appendChild(row);
            return;
        }

        notas.forEach(function (nota) {
            const row = document.createElement('tr');
            const values = tipo === 'entrada'
                ? [
                    '#' + (nota.id_nota_entrada || ''),
                    nota.proveedor || 'Sin proveedor',
                    nota.fecha_ingreso || '',
                    '$' + Number(nota.costo_total || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    nota.estado || 'Activa'
                ]
                : [
                    '#' + (nota.id_nota_salida || ''),
                    nota.cliente || 'Sin cliente',
                    nota.fecha || '',
                    formatNumber.format(Number(nota.total || 0)) + ' unidades',
                    nota.estado || 'Activa'
                ];

            values.forEach(function (value, index) {
                const cell = document.createElement('td');
                if (index === 4) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-success';
                    badge.textContent = String(value);
                    cell.appendChild(badge);
                } else {
                    cell.textContent = String(value);
                }
                row.appendChild(cell);
            });
            tbody.appendChild(row);
        });
    }

    function actualizarDashboard() {
        const url = new URL(window.location.href);
        url.searchParams.set('url', 'dashboard');
        url.searchParams.set('action', 'ajax_stats');

        fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Respuesta HTTP ' + response.status);
                return response.json();
            })
            .then(function (data) {
                if (data.error) throw new Error(data.error);

                Object.keys(data.stats || {}).forEach(function (key) {
                    const element = document.querySelector('[data-stat-key="' + key + '"]');
                    if (element) element.textContent = formatNumber.format(Number(data.stats[key] || 0));
                });

                if (puedeVerEntradas) {
                    renderNotas(document.getElementById('notasEntradaBody'), data.notas_entrada, 'entrada');
                    renderNotas(document.getElementById('modalEntradasBody'), data.notas_entrada, 'entrada');
                }
                renderNotas(document.getElementById('notasSalidaBody'), data.notas_salida, 'salida');
                renderNotas(document.getElementById('modalSalidasBody'), data.notas_salida, 'salida');

                const newEntradaId = Number(data.ultimo_id_nota_entrada || 0);
                const newSalidaId = Number(data.ultimo_id_nota_salida || 0);
                const hayNuevaNota = newEntradaId > lastNotaEntradaId || newSalidaId > lastNotaSalidaId;
                lastNotaEntradaId = Math.max(lastNotaEntradaId, newEntradaId);
                lastNotaSalidaId = Math.max(lastNotaSalidaId, newSalidaId);

                if (hayNuevaNota && typeof window.mostrarToast === 'function') {
                    window.mostrarToast('¡Nueva nota registrada!', 'success');
                }
            })
            .catch(function (error) {
                console.error('No se pudo actualizar el dashboard:', error);
            });
    }

    window.setInterval(actualizarDashboard, 60000);
})();
