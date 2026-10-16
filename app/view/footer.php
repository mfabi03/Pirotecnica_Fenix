<?php
// CAMBIO: Ajuste de enlace en menú - Footer centralizado
?>
            </div> <!-- Cierre p-3 -->
        </div> <!-- Cierre col-md-9 col-lg-10 -->
    </div> <!-- Cierre row -->
</div> <!-- Cierre container-fluid -->

<!-- ==========================================
FOOTER - VERSIÓN PEQUEÑA
========================================== -->
<footer class="footer-fenix py-0" style="background: radial-gradient(circle at center, #141423 0%, #08080f 100%); border-top: 1px solid rgba(212, 175, 55, 0.1);">
    <div class="container text-center">
        <div style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 4px 0;">
            <span style="color: #d4af37; font-size: 0.55rem; opacity: 0.5;">✦</span>
            <span style="color: #d4af37; letter-spacing: 1.5px; font-family: 'Cinzel', 'Times New Roman', serif; font-size: 0.65rem; font-weight: 700;">
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
SCRIPTS GLOBALES
========================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
// FUNCIÓN GLOBAL fetchAJAX
// ==========================================
window.fetchAJAX = async function(url, formData) {
    try {
        const respuesta = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        if (!respuesta.ok) {
            throw new Error(`HTTP ${respuesta.status}`);
        }
        
        const data = await respuesta.json();
        return data;
    } catch (error) {
        console.error('Error en fetchAJAX:', error);
        return {
            success: false,
            mensaje: 'Error de conexión. Intenta nuevamente.'
        };
    }
};

// ==========================================
// FUNCIÓN GLOBAL para eliminar con confirmación
// ==========================================
window.eliminarConConfirmacion = function(url, formData, callback) {
    if (!confirm('¿Estás seguro de eliminar este registro?\n\nEsta acción no se puede deshacer.')) {
        return;
    }
    
    fetchAJAX(url, formData).then(function(data) {
        if (data.success) {
            mostrarAlerta(data.mensaje, 'success');
            if (typeof callback === 'function') {
                callback(data);
            }
        } else {
            mostrarAlerta(data.mensaje || 'Error al eliminar', 'error');
        }
    });
};

// ==========================================
// SISTEMA DE VALIDACIÓN EN TIEMPO REAL
// ==========================================
window.validarCampoUnico = function(inputSelector, urlCheck, campoNombre) {
    const input = document.querySelector(inputSelector);
    if (!input) return;
    
    let timeout = null;
    
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const valor = this.value.trim();
        
        if (valor === '') {
            this.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        timeout = setTimeout(async function() {
            try {
                const respuesta = await fetch(`${urlCheck}&valor=${encodeURIComponent(valor)}`);
                const data = await respuesta.json();
                
                if (data.existe) {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                    mostrarAlerta(`El ${campoNombre} "${valor}" ya existe`, 'warning');
                } else {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                }
            } catch (error) {
                console.error('Error validando campo:', error);
            }
        }, 500);
    });
};

// ==========================================
// FUNCIONES EXISTENTES
// ==========================================
window.confirmarEliminacion = function(mensaje) {
    return confirm(mensaje || '¿Estás seguro de eliminar este registro?');
};

window.formatearCedula = function(input) {
    let value = input.value.trim().toUpperCase();
    if (/^\d+$/.test(value)) {
        input.value = 'V-' + value;
    } else if (/^V\d+$/.test(value)) {
        input.value = 'V-' + value.substring(1);
    }
};

window.formatearRif = function(input) {
    let value = input.value.trim().toUpperCase();
    if (/^\d+$/.test(value)) {
        input.value = 'J-' + value;
    } else if (/^J\d+$/.test(value)) {
        input.value = 'J-' + value.substring(1);
    }
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

    // Ocultar columnas con IDs
    function ocultarColumnasId() {
        const re = /\b(id|ID|Id|codigo|Código|Codigo|cod|COD)\b/i;
        const tables = document.querySelectorAll('table');
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