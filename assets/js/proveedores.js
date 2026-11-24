// app/view/proveedores/proveedores.js
document.addEventListener('DOMContentLoaded', function () {

    function getModal(id) {
        const el = document.getElementById(id);
        return el ? bootstrap.Modal.getOrCreateInstance(el) : null;
    }

    const setVal = (elId, val) => {
        const el = document.getElementById(elId);
        if (el) el.value = val ?? '';
    };
    const setText = (elId, val) => {
        const el = document.getElementById(elId);
        if (el) el.textContent = val ?? '—';
    };

    // ---------- MENSAJE FLOTANTE ----------
    function mostrarMensaje(texto, tipo) {
        const colores = {
            success: { bg: '#d4edda', border: '#c3e6cb', color: '#155724', icon: 'fa-check-circle' },
            danger:  { bg: '#f8d7da', border: '#f5c6cb', color: '#721c24', icon: 'fa-exclamation-circle' },
            warning: { bg: '#fff3cd', border: '#ffeeba', color: '#856404', icon: 'fa-exclamation-triangle' },
            info:    { bg: '#d1ecf1', border: '#bee5eb', color: '#0c5460', icon: 'fa-info-circle' }
        };
        const c = colores[tipo] || colores.success;

        const alerta = document.createElement('div');
        alerta.className = 'alert alert-dismissible fade show shadow-sm';
        alerta.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            border-radius: 12px; font-weight: 600; min-width: 320px;
            background: ${c.bg}; border: 1px solid ${c.border}; color: ${c.color};
        `;
        alerta.innerHTML = `<i class="fas ${c.icon} me-2"></i>${texto}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(alerta);
        setTimeout(() => alerta.remove(), 3500);
    }

    // ---------- DELEGACIÓN (funciona con tabla recargada por AJAX) ----------
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-proveedor-action]');
        if (!btn) return;

        const accion = btn.dataset.proveedorAction;
        const id = btn.dataset.proveedorId;
        const fila = btn.closest('tr');
        const nombre = fila ? (fila.querySelector('td:nth-child(2)')?.textContent.trim() || '') : '';

        if (!id) return;

        if (accion === 'view') {
            cargarDetalle(id, 'ver');
        } else if (accion === 'edit') {
            cargarDetalle(id, 'editar');
        } else if (accion === 'inactivate') {
            configurarModalEstado('inactivar', nombre, id);
        } else if (accion === 'reactivate') {
            configurarModalEstado('reactivar', nombre, id);
        }
    });

    // ---------- CARGAR DETALLE ----------
    function cargarDetalle(id, modo) {
        fetch('?url=proveedores&type=detalle_ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_proveedor=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.error || 'Error al cargar');
            const p = data.proveedor;

            if (modo === 'ver') {
                setText('ver-proveedor-razon-titulo', p.razon_social);
                setText('ver-proveedor-rif-badge', p.rif);
                setText('ver-proveedor-rif', p.rif);
                setText('ver-proveedor-razon', p.razon_social);
                setText('ver-proveedor-telefono', p.numero_contacto);
                setText('ver-proveedor-correo', p.correo_electronico);
                setText('ver-proveedor-direccion', p.direccion);
                getModal('modalVerProveedor')?.show();
            } else {
                setVal('editar-proveedor-id', p.id_proveedor);
                setVal('editar-proveedor-rif', p.rif);
                setVal('editar-proveedor-telefono', p.numero_contacto);
                setVal('editar-proveedor-razon', p.razon_social);
                setVal('editar-proveedor-correo', p.correo_electronico);
                setVal('editar-proveedor-direccion', p.direccion);
                setText('editar-proveedor-razon-header', p.razon_social);
                getModal('modalEditarProveedor')?.show();
            }
        })
        .catch(err => mostrarMensaje('Error: ' + err.message, 'danger'));
    }

    // ---------- MODAL UNIFICADO (eliminar / reactivar) ----------
    function configurarModalEstado(accion, nombre, id) {
        const esReactivar = accion === 'reactivar';

        setVal('estado-proveedor-accion', accion);
        setVal('estado-proveedor-id', id);
        setText('estado-proveedor-nombre', nombre || '—');
        setText('estado-proveedor-titulo', esReactivar ? 'Reactivar Proveedor' : 'Eliminar Proveedor');

        const icono = document.getElementById('estado-proveedor-icono');
        if (icono) {
            icono.className = esReactivar ? 'fas fa-check-circle me-2' : 'fas fa-trash-alt me-2';
            icono.style.color = esReactivar ? '#28a745' : '#dc3545';
        }

        const header = document.getElementById('estado-proveedor-header');
        if (header) {
            header.style.borderBottom = esReactivar
                ? '1px solid rgba(40,167,69,0.3)'
                : '1px solid rgba(220,53,69,0.3)';
        }

        setText('estado-proveedor-pregunta', esReactivar ? '¿Deseas reactivar este proveedor?' : '¿Estás seguro de eliminar este proveedor?');
        setText('estado-proveedor-label', esReactivar ? 'Proveedor a reactivar' : 'Proveedor a eliminar');

        const infoBox = document.getElementById('estado-proveedor-info-box');
        if (infoBox) infoBox.style.borderLeftColor = esReactivar ? '#28a745' : '#dc3545';

        const alerta = document.getElementById('estado-proveedor-alerta');
        const alertaTexto = document.getElementById('estado-proveedor-alerta-texto');
        if (alerta && alertaTexto) {
            if (esReactivar) {
                alerta.style.background = '#d4edda';
                alerta.style.border = '1px solid #c3e6cb';
                alerta.style.color = '#155724';
                alertaTexto.innerHTML = 'El proveedor volverá a estar <strong>Activo</strong> y aparecerá disponible en los selectores de futuras notas.';
            } else {
                alerta.style.background = '#f8d7da';
                alerta.style.border = '1px solid #f5c6cb';
                alerta.style.color = '#721c24';
                alertaTexto.innerHTML = 'Esta acción realizará la <strong>eliminación lógica</strong> del proveedor. Si el proveedor tiene notas de entrada asociadas, el sistema bloqueará la eliminación.';
            }
        }

        const btn = document.getElementById('estado-proveedor-btn-confirmar');
        if (btn) {
            btn.style.background = esReactivar ? '#28a745' : '#dc3545';
            btn.innerHTML = esReactivar
                ? '<i class="fas fa-check me-1"></i> Sí, reactivar'
                : '<i class="fas fa-trash-alt me-1"></i> Sí, eliminar';
        }

        getModal('modalEstadoProveedor')?.show();
    }

    // ---------- SUBMIT MODAL UNIFICADO ----------
    document.addEventListener('submit', function (e) {
        if (e.target && e.target.id === 'formEstadoProveedor') {
            e.preventDefault();
            const formData = new FormData(e.target);
            fetch('?url=proveedores&type=estado_ajax', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        getModal('modalEstadoProveedor')?.hide();
                        mostrarMensaje(data.message || 'Operación realizada con éxito', 'success');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        mostrarMensaje(data.error || 'Error al cambiar estado', 'danger');
                    }
                })
                .catch(err => mostrarMensaje('Error: ' + err.message, 'danger'));
        }
    });
});