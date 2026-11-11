<?php
// CAMBIO: Ajuste de enlace en menú - Footer centralizado
?>
            </div> <!-- Cierre content-wrapper -->
        </div> <!-- Cierre col-lg-10 col-12 -->
    </div> <!-- Cierre row align-items-stretch -->
</div> <!-- Cierre container-fluid -->

<!-- ==========================================
FOOTER - VERSIÓN PEQUEÑA
========================================== -->
<footer class="footer-fenix py-0" style="background: radial-gradient(circle at center, #141423 0%, #08080f 100%); border-top: 1px solid rgba(212, 175, 55, 0.1);">
    <div class="container text-center">
        <div style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 4px 0;">
            <span style="color: #d4af37; font-size: 0.55rem; opacity: 0.5;">✦</span>
            <span class="fenix-wordmark" style="color: #d4af37;">
                PIROTECNICA FÉNIX
            </span>
            <span style="color: #d4af37; font-size: 0.55rem; opacity: 0.5;">✦</span>
            <span style="color: rgba(255,255,255,0.06);">|</span>
            <span style="color: #e5c158; font-size: 0.5rem; opacity: 0.4;">&copy; <?= anioActualVenezuela() ?></span>
        </div>
    </div>
</footer>

<!-- CONTENEDOR DE TOASTS (global) -->
<div id="toast-container" class="toast-container position-fixed top-50 start-50 translate-middle p-3" style="z-index: 9999;"></div>

<!-- ==========================================
MODAL GLOBAL DE CONFIRMACIÓN
========================================== -->
<div class="modal fade" id="modalConfirmGlobal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" id="confirm-global-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i id="confirm-global-icon" class="fas fa-question-circle me-2" style="color: #f39c12;"></i>
                    <span id="confirm-global-titulo">Confirmar acción</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 24px;">
                <p id="confirm-global-mensaje" style="margin: 0; color: #1a1a2e; font-size: 1rem; line-height: 1.5;">
                    ¿Estás seguro?
                </p>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn" id="confirm-global-btn-ok"
                        style="border-radius: 50px; padding: 10px 30px; font-weight: 600; border: none; color: #fff; background: #dc3545;">
                    <i class="fas fa-check me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
SCRIPTS GLOBALES
========================================== -->

<!-- 1. jQuery PRIMERO -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- 2. Bootstrap después de jQuery -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. Validador global (versión jQuery) -->
<script src="/pirotecnica_fenix/assets/js/validaciones.jquery.js"></script>

<!-- 4. Búsqueda en selects -->
<script src="/pirotecnica_fenix/assets/js/select-search.js"></script>

<!-- 5. Scripts del módulo (los declara cada vista con $scriptsModulo) -->
<?php if (!empty($scriptsModulo) && is_array($scriptsModulo)): ?>
    <?php foreach ($scriptsModulo as $script): ?>
        <script src="/pirotecnica_fenix/assets/js/<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<style>
/* ==========================================
   BUSCADOR DE SELECTS - UNIFICADO CON EL DISEÑO
   ========================================== */
.ss-wrapper { 
    position: relative; 
    flex: 1; 
    min-width: 0; 
}

/* El input del buscador (reemplaza al select original) */
.ss-wrapper .ss-input {
    background: #fff;
    border: 1.5px solid rgba(0,0,0,0.08);
    color: #0e0505;
    padding: 14px 16px;
    border-radius: 12px 0 0 12px;
    width: 100%;
    font-size: 1rem;
    min-height: 48px;
    box-sizing: border-box;
}

/* Al enfocar */
.ss-wrapper .ss-input:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 3px rgba(243,156,18,0.15);
    outline: none;
}

/* Placeholder */
.ss-wrapper .ss-input::placeholder {
    color: #6c757d;
}

/* Dropdown de opciones */
.ss-wrapper .ss-dropdown {
    background: #fff;
    border: 1.5px solid rgba(0,0,0,0.08);
    border-radius: 12px;
    position: absolute;
    z-index: 1050;
    left: 0;
    right: 0;
    top: calc(100% + 4px);
    max-height: 220px;
    overflow-y: auto;
    display: none;
    box-shadow: 0 10px 24px rgba(0,0,0,0.15);
    padding: 6px;
    list-style: none;
    margin: 0;
}

/* Items del dropdown */
.ss-wrapper .ss-dropdown .list-group-item {
    background: transparent;
    color: #1a1a2e;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

/* Item activo o hover */
.ss-wrapper .ss-dropdown .list-group-item.active,
.ss-wrapper .ss-dropdown .list-group-item:hover {
    background: rgba(243,156,18,0.15);
    color: #1a1a2e;
}
</style>

<script>
// ==========================================
// SISTEMA DE TOASTS GLOBAL
// ==========================================
window.mostrarToast = function(mensaje, tipo = 'info') {
    const colores = {
        success: 'text-bg-success',
        error: 'text-bg-danger',
        danger: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-info'
    };
    
    const iconos = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        danger: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const colorClass = colores[tipo] || colores.info;
    const iconClass = iconos[tipo] || iconos.info;
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center ${colorClass} border-0 shadow-lg mb-2`;
    toast.style.minWidth = '350px';
    toast.style.fontSize = '1rem';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="fas ${iconClass} fs-5"></i>
                <span>${mensaje}</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                    data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.getElementById('toast-container').appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
};


window.mostrarAlerta = function(mensaje, tipo = 'info', autoCerrar = true) {
    

    const config = {
        success: { 
            bg: 'rgba(40, 167, 69, 0.15)', 
            border: 'rgba(40, 167, 69, 0.3)', 
            color: '#28a745',
            icon: 'fa-check-circle'
        },
        error: { 
            bg: 'rgba(220, 53, 69, 0.15)', 
            border: 'rgba(220, 53, 69, 0.3)', 
            color: '#dc3545',
            icon: 'fa-exclamation-circle'
        },
        warning: { 
            bg: 'rgba(255, 193, 7, 0.15)', 
            border: 'rgba(255, 193, 7, 0.3)', 
            color: '#ffc107',
            icon: 'fa-exclamation-triangle'
        },
        info: { 
            bg: 'rgba(13, 202, 240, 0.15)', 
            border: 'rgba(13, 202, 240, 0.3)', 
            color: '#0dcaf0',
            icon: 'fa-info-circle'
        }
    };
    

    const c = config[tipo] || config.info;
    

    // Buscar o crear el contenedor de alertas
    let container = document.getElementById('alert-container');
    

    if (!container) {
        container = document.createElement('div');
        container.id = 'alert-container';
        container.style.cssText = 'position: fixed; top: 80px; left: 0; right: 0; z-index: 9999; padding: 0 20px; pointer-events: none;';
        document.body.appendChild(container);
    }
    

    // Crear la alerta
    const alerta = document.createElement('div');
    alerta.className = 'alert alert-dismissible fade show shadow-sm border-0 d-flex align-items-center';
    alerta.style.cssText = `
        background: ${c.bg};
        border: 1px solid ${c.border};
        border-radius: 12px;
        padding: 16px 20px;
        color: ${c.color};
        margin-bottom: 12px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        pointer-events: auto;
        backdrop-filter: blur(10px);
        animation: fadeInUp 0.3s ease-out;
    `;
    

    alerta.innerHTML = `
        <i class="fas ${c.icon} me-3 fs-4"></i>
        <span style="flex: 1; font-weight: 500;">${mensaje}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="filter: invert(1); opacity: 0.5;"></button>
    `;
    

    container.appendChild(alerta);
    

    // Auto-cerrar después de 4 segundos
    if (autoCerrar) {
        setTimeout(() => {
            alerta.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            alerta.style.opacity = '0';
            alerta.style.transform = 'translateY(-10px)';
            setTimeout(() => alerta.remove(), 300);
        }, 4000);
    }
    

    // Cerrar manualmente
    const btnClose = alerta.querySelector('.btn-close');
    if (btnClose) {
        btnClose.addEventListener('click', () => {
            alerta.remove();
        });
    }
};

// ==========================================
// CONFIRMACIÓN GLOBAL CON MODAL BOOTSTRAP
// ==========================================
window.confirmarAccion = function(opciones) {
    var op = opciones || {};
    var titulo   = op.titulo   || 'Confirmar acción';
    var mensaje  = op.mensaje  || '¿Estás seguro?';
    var tipo     = op.tipo     || 'danger'; // danger | warning | success | info | primary
    var textoOk  = op.textoOk  || 'Confirmar';
    var textoCancelar = op.textoCancelar || 'Cancelar';
    var callback = op.callback || function() {};

    var config = {
        danger:  { color: '#dc3545', icon: 'fa-exclamation-triangle', headerBorder: 'rgba(220,53,69,0.3)'  },
        warning: { color: '#ffc107', icon: 'fa-exclamation-triangle', headerBorder: 'rgba(255,193,7,0.3)'  },
        success: { color: '#28a745', icon: 'fa-check-circle',         headerBorder: 'rgba(40,167,69,0.3)'  },
        info:    { color: '#0dcaf0', icon: 'fa-info-circle',          headerBorder: 'rgba(13,202,240,0.3)' },
        primary: { color: '#0d6efd', icon: 'fa-question-circle',      headerBorder: 'rgba(13,110,253,0.3)' }
    };

    var c = config[tipo] || config.danger;
    var modalEl = document.getElementById('modalConfirmGlobal');
    if (!modalEl) {
        console.warn('modalConfirmGlobal no existe en el DOM');
        return;
    }

    // Pintar modal
    document.getElementById('confirm-global-titulo').textContent = titulo;
    document.getElementById('confirm-global-mensaje').innerHTML  = mensaje;

    var iconEl = document.getElementById('confirm-global-icon');
    iconEl.className = 'fas ' + c.icon + ' me-2';
    iconEl.style.color = c.color;
    document.getElementById('confirm-global-header').style.borderBottom = '1px solid ' + c.headerBorder;

    var btnOk = document.getElementById('confirm-global-btn-ok');
    btnOk.style.background = c.color;
    btnOk.innerHTML = '<i class="fas fa-check me-1"></i> ' + textoOk;

    var btnCancelar = modalEl.querySelector('.modal-footer .btn-light');
    if (btnCancelar) {
        btnCancelar.innerHTML = '<i class="fas fa-times me-1"></i> ' + textoCancelar;
    }

    // Clonar el botón para eliminar listeners anteriores
    var btnOkClonado = btnOk.cloneNode(true);
    btnOk.parentNode.replaceChild(btnOkClonado, btnOk);

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    btnOkClonado.addEventListener('click', function() {
        modal.hide();
        modalEl.addEventListener('hidden.bs.modal', function once() {
            modalEl.removeEventListener('hidden.bs.modal', once);
            callback();
        });
    });
};

// ==========================================
// FUNCIÓN GLOBAL fetchAJAX (con timeout)
// ==========================================
window.fetchAJAX = async function(url, formData, timeoutMs) {
    var timeout = (typeof timeoutMs === 'number') ? timeoutMs : 15000;
    var controller = new AbortController();
    var timer = setTimeout(function() { controller.abort(); }, timeout);

    try {
        var respuesta = await fetch(url, {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });
        clearTimeout(timer);

        if (!respuesta.ok) {
            throw new Error('HTTP ' + respuesta.status);
        }
        return await respuesta.json();
    } catch (error) {
        clearTimeout(timer);
        console.error('Error en fetchAJAX:', error);
        return {
            success: false,
            mensaje: (error.name === 'AbortError')
                ? 'La petición tardó demasiado. Intenta nuevamente.'
                : 'Error de conexión. Intenta nuevamente.'
        };
    }
};

// ==========================================
// FUNCIÓN GLOBAL para eliminar con confirmación
// ==========================================
window.eliminarConConfirmacion = function(url, formData, callback) {
    window.confirmarAccion({
        titulo:  'Eliminar registro',
        mensaje: '¿Estás seguro de eliminar este registro?<br><small style="color:#6c757d;">Esta acción no se puede deshacer.</small>',
        tipo:    'danger',
        textoOk: 'Sí, eliminar',
        callback: function() {
            window.fetchAJAX(url, formData).then(function(data) {
                if (data.success) {
                    window.mostrarAlerta(data.mensaje || 'Eliminado correctamente', 'success');
                    if (typeof callback === 'function') callback(data);
                } else {
                    window.mostrarAlerta(data.mensaje || 'Error al eliminar', 'error');
                }
            });
        }
    });
};

// ==========================================
// HELPER GLOBAL: escapeHtml
// ==========================================
window.escapeHtml = function(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
    });
};

// ==========================================
// INICIALIZACIÓN AL CARGAR
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar dropdowns
    const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdownToggles.forEach(function(toggle) {
        if (!bootstrap.Dropdown.getInstance(toggle)) {
            new bootstrap.Dropdown(toggle);
        }
    });

    // Auto-cierre de alertas
    const alertElement = document.querySelector('.alert');
    if (alertElement) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alertElement);
            if (bsAlert) bsAlert.close();
        }, 5000);
    }

    // Ocultar columnas con IDs (solo tablas con data-ocultar-id="true")
    function ocultarColumnasId() {
        const re = /\b(id|ID|Id|codigo|Código|Codigo|cod|COD)\b/i;
        const tables = document.querySelectorAll('table[data-ocultar-id="true"]');
        tables.forEach(table => {
            const ths = table.querySelectorAll('thead th');
            if (!ths || ths.length === 0) return;
            let indices = [];
            ths.forEach((th, idx) => {
                const text = (th.textContent || '').trim();
                if (re.test(text)) indices.push(idx);
            });
            if (indices.length === 0) return;
            indices.forEach(i => { if (ths[i]) ths[i].style.display = 'none'; });
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                indices.forEach(i => {
                    if (cells[i]) cells[i].style.display = 'none';
                });
            });
        });
    }

    ocultarColumnasId();
});
</script>

</body>
</html>