window.mostrarToast = function(mensaje, tipo = 'info') {
    const colores = { success: 'text-bg-success', error: 'text-bg-danger', danger: 'text-bg-danger', warning: 'text-bg-warning', info: 'text-bg-info' };
    const iconos  = { success: 'fa-check-circle', error: 'fa-exclamation-circle', danger: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const toast = document.createElement('div');
    toast.className = `toast align-items-center ${colores[tipo] || colores.info} border-0 shadow-lg mb-2`;
    toast.style.minWidth = '350px';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `<div class="d-flex"><div class="toast-body d-flex align-items-center gap-2"><i class="fas ${iconos[tipo] || iconos.info} fs-5"></i><span>${window.escapeHtml(mensaje)}</span></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.getElementById('toast-container').appendChild(toast);
    new bootstrap.Toast(toast, { delay: 4000 }).show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
};

window.mostrarAlerta = function(mensaje, tipo = 'info', autoCerrar = true) {
    const config = {
        success: { bg: 'rgba(40,167,69,0.15)', border: 'rgba(40,167,69,0.3)', color: '#28a745', icon: 'fa-check-circle' },
        error:   { bg: 'rgba(220,53,69,0.15)', border: 'rgba(220,53,69,0.3)', color: '#dc3545', icon: 'fa-exclamation-circle' },
        warning: { bg: 'rgba(255,193,7,0.15)', border: 'rgba(255,193,7,0.3)', color: '#ffc107', icon: 'fa-exclamation-triangle' },
        info:    { bg: 'rgba(13,202,240,0.15)', border: 'rgba(13,202,240,0.3)', color: '#0dcaf0', icon: 'fa-info-circle' }
    };
    const c = config[tipo] || config.info;
    let container = document.getElementById('alert-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'alert-container';
        container.style.cssText = 'position: fixed; top: 80px; left: 0; right: 0; z-index: 9999; padding: 0 20px; pointer-events: none;';
        document.body.appendChild(container);
    }
    const alerta = document.createElement('div');
    alerta.className = 'alert alert-dismissible fade show shadow-sm border-0 d-flex align-items-center';
    alerta.style.cssText = `background:${c.bg};border:1px solid ${c.border};border-radius:12px;padding:16px 20px;color:${c.color};margin-bottom:12px;max-width:1200px;margin-left:auto;margin-right:auto;pointer-events:auto;backdrop-filter:blur(10px);`;
    alerta.innerHTML = `<i class="fas ${c.icon} me-3 fs-4"></i><span style="flex:1;font-weight:500;">${window.escapeHtml(mensaje)}</span><button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="filter:invert(1);opacity:0.5;"></button>`;
    container.appendChild(alerta);
    if (autoCerrar) {
        setTimeout(() => { alerta.style.transition = 'opacity 0.3s, transform 0.3s'; alerta.style.opacity = '0'; alerta.style.transform = 'translateY(-10px)'; setTimeout(() => alerta.remove(), 300); }, 4000);
    }
    const btnClose = alerta.querySelector('.btn-close');
    if (btnClose) btnClose.addEventListener('click', () => alerta.remove());
};

window.escapeHtml = function(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
};

window.confirmarAccion = function(config) {
    var modal = document.getElementById('modalConfirmacionGlobal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modalConfirmacionGlobal';
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML =
            '<div class="modal-dialog modal-dialog-centered">' +
                '<div class="modal-content" style="background:#fff;border:0;border-radius:16px;overflow:hidden;">' +
                    '<div class="modal-header" style="background:#1a1a2e;">' +
                        '<h5 class="modal-title" style="color:#fff;font-weight:700;"></h5>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter:invert(1);"></button>' +
                    '</div>' +
                    '<div class="modal-body" data-confirmacion-mensaje></div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>' +
                        '<button type="button" class="btn" data-confirmacion-aceptar></button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(modal);
    }

    modal.querySelector('.modal-title').textContent = config.titulo || 'Confirmar acción';
    modal.querySelector('[data-confirmacion-mensaje]').textContent =
        String(config.mensaje || '').replace(/<\/?strong>/gi, '');

    var botonAceptar = modal.querySelector('[data-confirmacion-aceptar]');
    var claseBoton = config.tipo === 'success' ? 'btn-success' : 'btn-danger';
    botonAceptar.className = 'btn ' + claseBoton;
    botonAceptar.textContent = config.textoOk || 'Confirmar';
    botonAceptar.onclick = function() {
        bootstrap.Modal.getOrCreateInstance(modal).hide();
        if (typeof config.callback === 'function') config.callback();
    };

    bootstrap.Modal.getOrCreateInstance(modal).show();
};

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los collapse
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(el) {
        if (!bootstrap.Collapse.getInstance(el)) {
            new bootstrap.Collapse(el, { toggle: false });
        }
    });
    // Inicializar dropdowns
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el) {
        if (!bootstrap.Dropdown.getInstance(el)) new bootstrap.Dropdown(el);
    });
});
