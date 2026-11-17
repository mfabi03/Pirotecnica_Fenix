document.addEventListener('DOMContentLoaded', function() {
    var stateElement = document.getElementById('clientesModalState');
    var state = stateElement ? JSON.parse(stateElement.textContent) : {};
    var formModal = state.formModal;
    var formData = state.formData || {};
    var errores = state.errores || {};

    function setField(id, value) {
        var el = document.getElementById(id);
        if (el && value !== undefined && value !== null) el.value = value;
    }

    function pintarError(inputId, mensaje) {
        var input    = document.getElementById(inputId);
        var feedback = document.getElementById(inputId + '-feedback');
        if (input) {
            input.classList.remove('campo-valido', 'campo-verificando');
            input.classList.add('campo-invalido');
            input.dataset.unicoEstado = 'duplicado';
        }
        if (feedback) {
            feedback.classList.remove('info', 'success', 'checking');
            feedback.classList.add('error');
            feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i> ' + window.escapeHtml(mensaje);
        }
    }

    if (formModal === 'registrar_natural' || formModal === 'registrar_juridico') {
        var modalEl = document.getElementById('modalRegistrarCliente');
        if (!modalEl) return;

        modalEl.addEventListener('shown.bs.modal', function onShown() {
            modalEl.removeEventListener('shown.bs.modal', onShown);

            var tabId = (formModal === 'registrar_natural') ? 'tab-natural-reg' : 'tab-juridico-reg';
            var tabEl = document.getElementById(tabId);
            if (tabEl) new bootstrap.Tab(tabEl).show();

            if (formModal === 'registrar_natural') {
                setField('reg-cedula',          formData.cedula);
                setField('reg-nombre',          formData.nombre);
                setField('reg-apellido',        formData.apellido);
                setField('reg-telefono',        formData.telefono);
                setField('reg-correo',          formData.correo_electronico);
                setField('reg-fecha-nac',       formData.fecha_de_nacimiento);
                setField('reg-direccion',       formData.direccion);
                if (errores.cedula) pintarError('reg-cedula', errores.cedula);
            } else {
                setField('reg-rif',             formData.rif);
                setField('reg-jur-razon',       formData.razon_social);
                setField('reg-jur-telefono',    formData.telefono);
                setField('reg-jur-correo',      formData.correo_electronico);
                setField('reg-jur-direccion',   formData.direccion);
                if (errores.rif) pintarError('reg-rif', errores.rif);
            }
        }, { once: true });

        new bootstrap.Modal(modalEl).show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
        var ms = parseInt(el.dataset.autoDismiss, 10) || 5000;
        setTimeout(function () {
            if (!el.parentNode) return;
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function () { el.remove(); }, 400);
        }, ms);
    });
});
