/**
 * ============================================================
 * VALIDADOR GLOBAL - Validación en tiempo real
 * ------------------------------------------------------------
 * Reglas de formato:      data-validate="cedula|rif|..."
 * Reglas de unicidad:     data-unico-endpoint="?url=..."
 *                         data-unico-campo="cedula|rif|..."
 *                         data-excluir-id="5"  (opcional, edición)
 * ============================================================
 */

const Validador = (function () {
    'use strict';

    // ---------------------------------------------------------
    // CONFIG
    // ---------------------------------------------------------
    const DEBOUNCE_MS = 400;

    // Estado interno para la validación asíncrona
    const timersUnico    = {};   // inputId → timerId
    const requestIdUnico = {};   // inputId → contador (evita respuestas viejas)

    // ---------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------
    function calcularEdad(fechaNacimiento) {
        if (!fechaNacimiento) return null;
        const hoy = new Date();
        const fechaNac = new Date(fechaNacimiento + 'T00:00:00');
        if (isNaN(fechaNac.getTime())) return null;
        let edad = hoy.getFullYear() - fechaNac.getFullYear();
        const mes = hoy.getMonth() - fechaNac.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) edad--;
        return edad;
    }

    function formatearFecha(fechaBD) {
        if (!fechaBD) return '—';
        const partes = fechaBD.split('-');
        if (partes.length !== 3) return fechaBD;
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }

    // ---------------------------------------------------------
    // VALIDADORES SÍNCRONOS (formato)
    // ---------------------------------------------------------
    const validadores = {
        cedula: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: V-12345678' };
            const regex = /^[VEJvej]-\d{7,9}$/;
            if (/^\d{7,9}$/.test(valor)) return { estado: 'invalido', mensaje: 'Debe incluir V, E o J al inicio' };
            if (!regex.test(valor)) return { estado: 'invalido', mensaje: 'Formato inválido. Ejemplo: V-12345678' };
            return { estado: 'valido', mensaje: 'Cédula válida' };
        },
        rif: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: J-123456789' };
            const regex = /^[VEJPGvejpg]-?\d{8,9}-?\d?$/;
            if (/^\d{8,9}$/.test(valor)) return { estado: 'invalido', mensaje: 'Debe incluir la letra J al inicio' };
            if (!regex.test(valor)) return { estado: 'invalido', mensaje: 'Formato inválido. Ejemplo: J-123456789' };
            return { estado: 'valido', mensaje: 'RIF válido' };
        },
        telefono: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: 0412-1234567' };
            const regex = /^(\+?58)?[\s\-]?(0?)(412|414|416|424|426|212|241|251|261|271|281|291)[\s\-]?\d{7}$/;
            const soloDigitos = valor.replace(/[\s\-+]/g, '');
            if (soloDigitos.length < 10) return { estado: 'invalido', mensaje: 'Debe tener al menos 10 dígitos' };
            if (!regex.test(valor)) return { estado: 'invalido', mensaje: 'Formato inválido. Ejemplo: 0412-1234567' };
            return { estado: 'valido', mensaje: 'Teléfono válido' };
        },
        nombre: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Mínimo 2 caracteres' };
            if (valor.trim().length < 2) return { estado: 'invalido', mensaje: 'Debe tener al menos 2 caracteres' };
            if (valor.trim().length > 50) return { estado: 'invalido', mensaje: 'Máximo 50 caracteres' };
            if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(valor)) return { estado: 'invalido', mensaje: 'Solo letras y espacios' };
            return { estado: 'valido', mensaje: 'Nombre válido' };
        },
        razon: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Mínimo 3 caracteres' };
            if (valor.trim().length < 3) return { estado: 'invalido', mensaje: 'Debe tener al menos 3 caracteres' };
            if (valor.trim().length > 150) return { estado: 'invalido', mensaje: 'Máximo 150 caracteres' };
            return { estado: 'valido', mensaje: 'Razón social válida' };
        },
        correo: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: usuario@dominio.com' };
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valor)) return { estado: 'invalido', mensaje: 'Correo electrónico inválido' };
            return { estado: 'valido', mensaje: 'Correo válido' };
        },
        fecha: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Debe ser mayor de 18 años' };
            const edad = calcularEdad(valor);
            if (edad === null) return { estado: 'invalido', mensaje: 'Fecha inválida' };
            if (edad < 0) return { estado: 'invalido', mensaje: 'La fecha no puede ser futura' };
            if (edad < 18) return { estado: 'invalido', mensaje: 'Cliente menor de edad: ' + edad + ' años', edad: edad };
            return { estado: 'valido', mensaje: 'Mayor de edad: ' + edad + ' años' };
        },
        fecha_simple: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Fecha requerida' };
            const edad = calcularEdad(valor);
            if (edad === null) return { estado: 'invalido', mensaje: 'Fecha inválida' };
            if (edad < 0) return { estado: 'invalido', mensaje: 'La fecha no puede ser futura' };
            return { estado: 'valido', mensaje: 'Fecha válida' };
        },
        direccion: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Mínimo 5 caracteres' };
            if (valor.trim().length < 5) return { estado: 'invalido', mensaje: 'Debe tener al menos 5 caracteres' };
            if (valor.trim().length > 255) return { estado: 'invalido', mensaje: 'Máximo 255 caracteres' };
            return { estado: 'valido', mensaje: 'Dirección válida' };
        },
        numero: function (valor) {
            if (!valor && valor !== 0) return { estado: 'vacio', mensaje: 'Ingrese un número' };
            if (isNaN(valor)) return { estado: 'invalido', mensaje: 'Debe ser un número' };
            if (parseFloat(valor) < 0) return { estado: 'invalido', mensaje: 'No puede ser negativo' };
            return { estado: 'valido', mensaje: 'Número válido' };
        },
        numero_positivo: function (valor) {
            if (!valor && valor !== 0) return { estado: 'vacio', mensaje: 'Ingrese un número' };
            if (isNaN(valor)) return { estado: 'invalido', mensaje: 'Debe ser un número' };
            if (parseFloat(valor) <= 0) return { estado: 'invalido', mensaje: 'Debe ser mayor a 0' };
            return { estado: 'valido', mensaje: 'Número válido' };
        }
    };

    // ---------------------------------------------------------
    // FEEDBACK VISUAL
    // ---------------------------------------------------------
    function limpiarFeedback(input, feedbackEl) {
        if (!input) return;
        input.classList.remove('campo-valido', 'campo-invalido', 'campo-verificando');
        input.style.borderColor = '';
        input.style.boxShadow = '';
        if (feedbackEl) {
            feedbackEl.classList.remove('info', 'error', 'success', 'checking');
            feedbackEl.textContent = '';
            feedbackEl.innerHTML = '';
        }
    }

    function mostrarFeedback(input, feedbackEl, estado, mensaje) {
        input.classList.remove('campo-valido', 'campo-invalido', 'campo-verificando');
        feedbackEl.classList.remove('info', 'error', 'success', 'checking');

        if (estado === 'vacio') {
            input.style.borderColor = '';
            input.style.boxShadow = '';
            feedbackEl.classList.add('info');
            feedbackEl.innerHTML = '<i class="fas fa-info-circle me-1"></i> ' + mensaje;
        } else if (estado === 'valido') {
            input.classList.add('campo-valido');
            feedbackEl.classList.add('success');
            feedbackEl.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + mensaje;
        } else {
            input.classList.add('campo-invalido');
            feedbackEl.classList.add('error');
            feedbackEl.innerHTML = '<i class="fas fa-times-circle me-1"></i> ' + mensaje;
        }
    }

    function mostrarFeedbackChecking(input, feedbackEl, mensaje) {
        input.classList.remove('campo-valido', 'campo-invalido');
        input.classList.add('campo-verificando');
        feedbackEl.classList.remove('info', 'error', 'success');
        feedbackEl.classList.add('checking');
        feedbackEl.innerHTML = '<i class="fas fa-spinner me-1"></i> ' + mensaje;
    }

    // ---------------------------------------------------------
    // AJAX genérico: consulta a CUALQUIER endpoint del sistema
    // El HTML decide qué endpoint y qué campo consultar.
    // ---------------------------------------------------------
    function consultarUnico(input) {
        const endpoint = input.dataset.unicoEndpoint;
        const campo    = input.dataset.unicoCampo;
        const valor    = input.value.trim();

        if (!endpoint || !campo || !valor) {
            return Promise.resolve(false);
        }

        let url = endpoint
            + (endpoint.indexOf('?') === -1 ? '?' : '&')
            + 'campo=' + encodeURIComponent(campo)
            + '&valor=' + encodeURIComponent(valor);

        // Excluir el propio registro en modo edición
        let excluirId = input.dataset.excluirId || '';
        if (!excluirId) {
            const form = input.closest('form');
            const hidden = form ? form.querySelector('input[name="id_cliente"]') : null;
            if (hidden && hidden.value) excluirId = hidden.value;
        }
        if (excluirId) url += '&excluir_id=' + encodeURIComponent(excluirId);

        return fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            if (data.error) throw new Error(data.error);
            return !!data.existe;
        });
    }

    // ---------------------------------------------------------
    // Valida disponibilidad (async)
    // ---------------------------------------------------------
    function validarUnico(input) {
        const campo    = input.dataset.unicoCampo;
        const endpoint = input.dataset.unicoEndpoint;
        if (!campo || !endpoint) return Promise.resolve(true);

        clearTimeout(timersUnico[input.id]);

        const feedbackEl = document.getElementById(input.id + '-feedback');
        if (!feedbackEl) return Promise.resolve(true);

        const valor = input.value.trim();

        // Si el formato no es válido, no consultamos al servidor
        const validadorFormato = validadores[campo];
        if (validadorFormato) {
            const r = validadorFormato(valor);
            if (r.estado !== 'valido') {
                input.dataset.unicoEstado = '';
                return Promise.resolve(false);
            }
        }

        // ID de petición (para descartar respuestas viejas)
        requestIdUnico[input.id] = (requestIdUnico[input.id] || 0) + 1;
        const reqId = requestIdUnico[input.id];

        // Etiqueta amigable
        const etiqueta = (campo === 'cedula') ? 'cédula'
                       : (campo === 'rif')    ? 'RIF'
                       : campo;

        // Estado visual "verificando..."
        input.dataset.unicoEstado = 'checking';
        mostrarFeedbackChecking(input, feedbackEl, 'Verificando ' + etiqueta + '...');

        return consultarUnico(input)
            .then(function (existe) {
                // Descartar si llegó una respuesta vieja
                if (reqId !== requestIdUnico[input.id]) return true;

                if (existe) {
                    input.dataset.unicoEstado = 'duplicado';
                    mostrarFeedback(input, feedbackEl, 'invalido',
                        'Esta ' + etiqueta + ' ya está registrada');
                    return false;
                }

                input.dataset.unicoEstado = 'ok';
                const etiquetaCap = etiqueta.charAt(0).toUpperCase() + etiqueta.slice(1);
                mostrarFeedback(input, feedbackEl, 'valido',
                    etiquetaCap + ' disponible');
                return true;
            })
            .catch(function () {
                if (reqId !== requestIdUnico[input.id]) return true;
                // Si falla la red, NO bloqueamos (el server valida al enviar)
                input.dataset.unicoEstado = '';
                mostrarFeedback(input, feedbackEl, 'vacio',
                    'No se pudo verificar disponibilidad');
                return true;
            });
    }

    // ---------------------------------------------------------
    // Programa la validación async con debounce
    // ---------------------------------------------------------
    function programarValidacionUnica(input, delayMs) {
        if (!input.dataset.unicoEndpoint) return;
        clearTimeout(timersUnico[input.id]);
        const espera = (typeof delayMs === 'number') ? delayMs : DEBOUNCE_MS;
        timersUnico[input.id] = setTimeout(function () {
            validarUnico(input);
        }, espera);
    }

    let callbackMenorEdad = null;
    function onMenorEdad(cb) { callbackMenorEdad = cb; }

    // ---------------------------------------------------------
    // VALIDACIÓN DE UN CAMPO
    // ---------------------------------------------------------
    function validarCampo(input, opciones) {
        const settings = opciones || {};
        const forzar = !!settings.forzar;
        const marcarComoTocado = !!settings.marcarComoTocado;

        if (marcarComoTocado) {
            input.dataset.touched = 'true';
        }

        const tipo = input.dataset.validate;
        if (!tipo || !validadores[tipo]) return true;

        const feedbackEl = document.getElementById(input.id + '-feedback');
        if (!feedbackEl) return true;

        const estabaTocado = input.dataset.touched === 'true';
        if (!estabaTocado && !forzar) {
            limpiarFeedback(input, feedbackEl);
            return true;
        }

        const resultado = validadores[tipo](input.value);
        mostrarFeedback(input, feedbackEl, resultado.estado, resultado.mensaje);

        // ✅ CAMBIO CLAVE: si es menor de edad, llamamos al callback
        //    pero NO bloqueamos el campo. El usuario debe poder corregir.
        if (tipo === 'fecha' && resultado.edad !== undefined && resultado.edad < 18 && input.value) {
            if (callbackMenorEdad) callbackMenorEdad(resultado.edad);
            // ⚠️ NO return false: el campo sigue editable para borrar/corregir
        }

        // Si tiene validación de unicidad y el formato es válido → consultar
        if (input.dataset.unicoEndpoint) {
            if (resultado.estado === 'valido') {
                programarValidacionUnica(input);
            } else {
                clearTimeout(timersUnico[input.id]);
                input.dataset.unicoEstado = '';
            }
        }

        if (input.hasAttribute('required') && (!input.value || !input.value.trim())) {
            return false;
        }

        return resultado.estado === 'valido' || (resultado.estado === 'vacio' && !input.hasAttribute('required'));
    }

    function validarFormulario(formId, opciones) {
        const settings = opciones || {};
        const form = document.getElementById(formId);
        if (!form) return true;

        let todoValido = true;
        form.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]').forEach(function (input) {
            input.dataset.touched = 'true';
            if (!validarCampo(input, { forzar: true, marcarComoTocado: true })) todoValido = false;
            if (input.dataset.unicoEstado === 'duplicado') todoValido = false;
        });
        return todoValido;
    }

    // ---------------------------------------------------------
    // AUTO FORMATO
    // ---------------------------------------------------------
    function aplicarAutoFormato(input) {
        const tipo = input.dataset.validate;
        if (tipo === 'cedula') {
            input.addEventListener('input', function () {
                let v = this.value.toUpperCase().replace(/[^VEJ0-9-]/g, '');
                if (/^\d+$/.test(v)) this.value = 'V-' + v;
                else if (/^[VEJ]\d+$/.test(v)) this.value = v.charAt(0) + '-' + v.substring(1);
                else this.value = v;
            });
        } else if (tipo === 'rif') {
            input.addEventListener('input', function () {
                let v = this.value.toUpperCase().replace(/[^VEJPG0-9-]/g, '');
                if (/^\d+$/.test(v)) this.value = 'J-' + v;
                else if (/^[VEJPG]\d+$/.test(v)) this.value = v.charAt(0) + '-' + v.substring(1);
                else this.value = v;
            });
        } else if (tipo === 'telefono') {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9\-\s+]/g, '');
            });
        }
    }

    // ---------------------------------------------------------
    // INIT
    // ---------------------------------------------------------
    function init(scope) {
        const container = scope || document;
        container.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]').forEach(function (input) {
            if (input.dataset.validatorInit === 'true') return;
            input.dataset.validatorInit = 'true';
            input.dataset.touched = 'false';
            input.addEventListener('input', function () {
                input.dataset.touched = 'true';
                validarCampo(this);
            });
            input.addEventListener('blur', function () {
                input.dataset.touched = 'true';
                validarCampo(this);
            });
            input.addEventListener('change', function () {
                input.dataset.touched = 'true';
                validarCampo(this);
            });
            limpiarFeedback(input, document.getElementById(input.id + '-feedback'));
            aplicarAutoFormato(input);
        });
        container.querySelectorAll('input[data-validate="fecha"]').forEach(function (input) {
            const hace18 = new Date();
            hace18.setFullYear(hace18.getFullYear() - 18);
            input.setAttribute('max', hace18.toISOString().split('T')[0]);
        });
        container.querySelectorAll('input[data-validate="fecha_simple"]').forEach(function (input) {
            input.setAttribute('max', new Date().toISOString().split('T')[0]);
        });
    }

    // ---------------------------------------------------------
    // RESET
    // ---------------------------------------------------------
    function resetFormulario(formId) {
        const form = document.getElementById(formId);
        if (!form) return;
        form.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]').forEach(function (input) {
            input.value = '';
            input.dataset.touched = 'false';
            input.dataset.unicoEstado = '';
            clearTimeout(timersUnico[input.id]);
            limpiarFeedback(input, document.getElementById(input.id + '-feedback'));
        });
    }

    function resetEstadoVisual(scope) {
        const container = scope || document;
        container.querySelectorAll('input[data-validate], textarea[data-validate], select[data-validate]').forEach(function (input) {
            input.dataset.touched = 'false';
            input.dataset.unicoEstado = '';
            clearTimeout(timersUnico[input.id]);
            input.classList.remove('campo-valido', 'campo-invalido', 'campo-verificando');
            input.style.borderColor = '';
            input.style.boxShadow = '';
            const feedbackEl = document.getElementById(input.id + '-feedback');
            if (!feedbackEl) return;
            feedbackEl.classList.remove('info', 'error', 'success', 'checking');
            feedbackEl.classList.add('info');
            feedbackEl.innerHTML = feedbackEl.dataset.defaultHtml || '';
        });
    }

    // ---------------------------------------------------------
    // API PÚBLICA
    // ---------------------------------------------------------
    return {
        init: init,
        validarCampo: validarCampo,
        validarFormulario: validarFormulario,
        resetFormulario: resetFormulario,
        resetEstadoVisual: resetEstadoVisual,
        calcularEdad: calcularEdad,
        formatearFecha: formatearFecha,
        onMenorEdad: onMenorEdad,
        validarUnico: validarUnico
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    Validador.init();
});