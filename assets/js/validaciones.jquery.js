/**
 * ============================================================
 * VALIDADOR GLOBAL (versión jQuery)
 * ------------------------------------------------------------
 * Reglas de formato:      data-validate="cedula|rif|..."
 * Reglas de unicidad:     data-unico-endpoint="?url=..."
 *                         data-unico-campo="cedula|rif|..."
 *                         data-excluir-id="5"  (opcional, edición)
 * ============================================================
 */

var Validador = (function ($) {
    'use strict';

    // ---------------------------------------------------------
    // CONFIG
    // ---------------------------------------------------------
    var DEBOUNCE_MS = 400;

    // Estado interno para la validación asíncrona
    var timersUnico = {};
    var requestIdUnico = {};

    // ---------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------
    function calcularEdad(fechaNacimiento) {
        if (!fechaNacimiento) return null;
        var hoy = new Date();
        var fechaNac = new Date(fechaNacimiento + 'T00:00:00');
        if (isNaN(fechaNac.getTime())) return null;
        var edad = hoy.getFullYear() - fechaNac.getFullYear();
        var mes = hoy.getMonth() - fechaNac.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) edad--;
        return edad;
    }

    function formatearFecha(fechaBD) {
        if (!fechaBD) return '—';
        var partes = fechaBD.split('-');
        if (partes.length !== 3) return fechaBD;
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }

    // ---------------------------------------------------------
    // VALIDADORES SÍNCRONOS (formato)
    // ---------------------------------------------------------
    var validadores = {
        cedula: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: V-12345678' };
            var regex = /^[VEJvej]-\d{7,9}$/;
            if (/^\d{7,9}$/.test(valor)) return { estado: 'invalido', mensaje: 'Debe incluir V, E o J al inicio' };
            if (!regex.test(valor)) return { estado: 'invalido', mensaje: 'Formato inválido. Ejemplo: V-12345678' };
            return { estado: 'valido', mensaje: 'Cédula válida' };
        },
        rif: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: J-123456789' };
            var regex = /^[VEJPGvejpg]-?\d{8,9}-?\d?$/;
            if (/^\d{8,9}$/.test(valor)) return { estado: 'invalido', mensaje: 'Debe incluir la letra J al inicio' };
            if (!regex.test(valor)) return { estado: 'invalido', mensaje: 'Formato inválido. Ejemplo: J-123456789' };
            return { estado: 'valido', mensaje: 'RIF válido' };
        },
        telefono: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: 0412-1234567' };
            var regex = /^(\+?58)?[\s\-]?(0?)(412|414|416|424|426|212|241|251|261|271|281|291)[\s\-]?\d{7}$/;
            var soloDigitos = valor.replace(/[\s\-+]/g, '');
            if (soloDigitos.length < 10) return { estado: 'invalido', mensaje: 'Debe tener al menos 10 dígitos' };
            if (!regex.test(valor)) return { estado: 'invalido', mensaje: 'Formato inválido. Ejemplo: 0412-1234567' };
            return { estado: 'valido', mensaje: 'Teléfono válido' };
        },
        telefono_proveedor: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: 0412-1234567' };
            if (!/^\d{4}-?\d{7}$/.test(valor)) {
                return { estado: 'invalido', mensaje: 'Debe tener exactamente 11 dígitos. Ejemplo: 0412-1234567' };
            }
            return { estado: 'valido', mensaje: 'Teléfono válido' };
        },
        nombre: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Mínimo 2 caracteres' };
            if ($.trim(valor).length < 2) return { estado: 'invalido', mensaje: 'Debe tener al menos 2 caracteres' };
            if ($.trim(valor).length > 50) return { estado: 'invalido', mensaje: 'Máximo 50 caracteres' };
            if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(valor)) return { estado: 'invalido', mensaje: 'Solo letras y espacios' };
            return { estado: 'valido', mensaje: 'Nombre válido' };
        },
        razon: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Mínimo 3 caracteres' };
            if ($.trim(valor).length < 3) return { estado: 'invalido', mensaje: 'Debe tener al menos 3 caracteres' };
            if ($.trim(valor).length > 150) return { estado: 'invalido', mensaje: 'Máximo 150 caracteres' };
            return { estado: 'valido', mensaje: 'Razón social válida' };
        },
        correo: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Formato: usuario@dominio.com' };
            var regex = /^[A-Z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[A-Z0-9!#$%&'*+\/=?^_`{|}~-]+)*@[A-Z0-9](?:[A-Z0-9-]{0,61}[A-Z0-9])?(?:\.[A-Z0-9](?:[A-Z0-9-]{0,61}[A-Z0-9])?)*\.[A-Z]{2,}$/i;
            if (!regex.test(valor)) return { estado: 'invalido', mensaje: 'Correo electrónico inválido' };
            return { estado: 'valido', mensaje: 'Correo válido' };
        },
        fecha: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Debe ser mayor de 18 años' };
            var edad = calcularEdad(valor);
            if (edad === null) return { estado: 'invalido', mensaje: 'Fecha inválida' };
            if (edad < 0) return { estado: 'invalido', mensaje: 'La fecha no puede ser futura' };
            if (edad < 18) return { estado: 'invalido', mensaje: 'Cliente menor de edad: ' + edad + ' años', edad: edad };
            return { estado: 'valido', mensaje: 'Mayor de edad: ' + edad + ' años' };
        },
        fecha_simple: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Fecha requerida' };
            var edad = calcularEdad(valor);
            if (edad === null) return { estado: 'invalido', mensaje: 'Fecha inválida' };
            if (edad < 0) return { estado: 'invalido', mensaje: 'La fecha no puede ser futura' };
            return { estado: 'valido', mensaje: 'Fecha válida' };
        },
        direccion: function (valor) {
            if (!valor) return { estado: 'vacio', mensaje: 'Mínimo 5 caracteres' };
            if ($.trim(valor).length < 5) return { estado: 'invalido', mensaje: 'Debe tener al menos 5 caracteres' };
            if ($.trim(valor).length > 255) return { estado: 'invalido', mensaje: 'Máximo 255 caracteres' };
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
    function limpiarFeedback($input, $feedbackEl) {
        $input.removeClass('campo-valido campo-invalido campo-verificando')
              .css({ borderColor: '', boxShadow: '' });
        if ($feedbackEl && $feedbackEl.length) {
            $feedbackEl.removeClass('info error success checking').text('').html('');
        }
    }

    function mostrarFeedback($input, $feedbackEl, estado, mensaje) {
        $input.removeClass('campo-valido campo-invalido campo-verificando');
        $feedbackEl.removeClass('info error success checking');

        if (estado === 'vacio') {
            $input.css({ borderColor: '', boxShadow: '' });
            $feedbackEl.addClass('info').html('<i class="fas fa-info-circle me-1"></i> ' + mensaje);
        } else if (estado === 'valido') {
            $input.addClass('campo-valido');
            $feedbackEl.addClass('success').html('<i class="fas fa-check-circle me-1"></i> ' + mensaje);
        } else {
            $input.addClass('campo-invalido');
            $feedbackEl.addClass('error').html('<i class="fas fa-times-circle me-1"></i> ' + mensaje);
        }
    }

    function mostrarFeedbackChecking($input, $feedbackEl, mensaje) {
        $input.removeClass('campo-valido campo-invalido').addClass('campo-verificando');
        $feedbackEl.removeClass('info error success').addClass('checking')
                    .html('<i class="fas fa-spinner me-1"></i> ' + mensaje);
    }

    // ---------------------------------------------------------
    // AJAX genérico con $.ajax
    // ---------------------------------------------------------
    function consultarUnico(input) {
        var $input    = $(input);
        var endpoint  = $input.data('unico-endpoint');
        var campo     = $input.data('unico-campo');
        var valor     = $.trim($input.val());

        if (!endpoint || !campo || !valor) {
            return $.Deferred().resolve(false).promise();
        }

        var url = endpoint
            + (endpoint.indexOf('?') === -1 ? '?' : '&')
            + 'campo=' + encodeURIComponent(campo)
            + '&valor=' + encodeURIComponent(valor);

        var excluirId = $input.data('excluir-id') || '';
        if (!excluirId) {
            var $hidden = $input.closest('form').find('input[name="id_cliente"]');
            if ($hidden.length && $hidden.val()) excluirId = $hidden.val();
        }
        if (excluirId) url += '&excluir_id=' + encodeURIComponent(excluirId);

        return $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            headers: { 'Accept': 'application/json' }
        }).then(function (data) {
            if (data.error) throw new Error(data.error);
            return !!data.existe;
        });
    }

    // ---------------------------------------------------------
    // Validación única
    // ---------------------------------------------------------
    function validarUnico(input) {
        var $input   = $(input);
        var campo    = $input.data('unico-campo');
        var endpoint = $input.data('unico-endpoint');
        if (!campo || !endpoint) return $.Deferred().resolve(true).promise();

        var $feedbackEl = $('#' + input.id + '-feedback');
        if (!$feedbackEl.length) return $.Deferred().resolve(true).promise();

        var valor = $.trim($input.val());

        var validadorFormato = validadores[campo];
        if (validadorFormato) {
            var r = validadorFormato(valor);
            if (r.estado !== 'valido') {
                $input.data('unicoEstado', '');
                return $.Deferred().resolve(false).promise();
            }
        }

        requestIdUnico[input.id] = (requestIdUnico[input.id] || 0) + 1;
        var reqId = requestIdUnico[input.id];

        var etiqueta = (campo === 'cedula') ? 'cédula'
                     : (campo === 'rif')    ? 'RIF'
                     : (campo === 'telefono_proveedor') ? 'teléfono'
                     : campo;

        $input.data('unicoEstado', 'checking');
        mostrarFeedbackChecking($input, $feedbackEl, 'Verificando ' + etiqueta + '...');

        return consultarUnico(input)
            .then(function (existe) {
                if (reqId !== requestIdUnico[input.id]) return true;
                if (existe) {
                    $input.data('unicoEstado', 'duplicado');
                    mostrarFeedback($input, $feedbackEl, 'invalido',
                        'Esta ' + etiqueta + ' ya está registrada');
                    return false;
                }
                $input.data('unicoEstado', 'ok');
                var etiquetaCap = etiqueta.charAt(0).toUpperCase() + etiqueta.slice(1);
                mostrarFeedback($input, $feedbackEl, 'valido',
                    etiquetaCap + ' disponible');
                return true;
            })
            .fail(function () {
                if (reqId !== requestIdUnico[input.id]) return;
                $input.data('unicoEstado', '');
                mostrarFeedback($input, $feedbackEl, 'vacio',
                    'No se pudo verificar disponibilidad');
            });
    }

    function programarValidacionUnica(input, delayMs) {
        var $input = $(input);
        if (!$input.data('unico-endpoint')) return;
        clearTimeout(timersUnico[input.id]);
        var espera = (typeof delayMs === 'number') ? delayMs : DEBOUNCE_MS;
        timersUnico[input.id] = setTimeout(function () {
            validarUnico(input);
        }, espera);
    }

    var callbackMenorEdad = null;
    function onMenorEdad(cb) { callbackMenorEdad = cb; }

    // ---------------------------------------------------------
    // Validar campo
    // ---------------------------------------------------------
    function validarCampo(input, opciones) {
        var $input = $(input);
        var settings = opciones || {};
        var forzar = !!settings.forzar;
        var marcarComoTocado = !!settings.marcarComoTocado;

        if (marcarComoTocado) $input.data('touched', true);

        var tipo = $input.data('validate');
        if (!tipo || !validadores[tipo]) return true;

        var $feedbackEl = $('#' + input.id + '-feedback');
        if (!$feedbackEl.length) return true;

        var estabaTocado = $input.data('touched') === true;
        if (!estabaTocado && !forzar) {
            limpiarFeedback($input, $feedbackEl);
            return true;
        }

        var resultado = validadores[tipo]($input.val());
        mostrarFeedback($input, $feedbackEl, resultado.estado, resultado.mensaje);

        if (tipo === 'fecha' && resultado.edad !== undefined && resultado.edad < 18 && $input.val()) {
            if (callbackMenorEdad) callbackMenorEdad(resultado.edad);
        }

        if ($input.data('unico-endpoint')) {
            if (resultado.estado === 'valido') {
                programarValidacionUnica(input);
            } else {
                clearTimeout(timersUnico[input.id]);
                $input.data('unicoEstado', '');
            }
        }

        var esRequired = $input.prop('required');
        if (esRequired && !$.trim($input.val())) return false;

        return resultado.estado === 'valido' || (resultado.estado === 'vacio' && !esRequired);
    }

    function validarFormulario(formId, opciones) {
        var $form = $('#' + formId);
        if (!$form.length) return true;

        var todoValido = true;
        $form.find('input[data-validate], textarea[data-validate], select[data-validate]').each(function () {
            var $input = $(this);
            $input.data('touched', true);
            if (!validarCampo(this, { forzar: true, marcarComoTocado: true })) todoValido = false;
            if ($input.data('unicoEstado') === 'duplicado') todoValido = false;
        });
        return todoValido;
    }

    // ---------------------------------------------------------
    // Auto formato
    // ---------------------------------------------------------
    function aplicarAutoFormato(input) {
        var $input = $(input);
        var tipo = $input.data('validate');

        if (tipo === 'cedula') {
            $input.on('input', function () {
                var v = this.value.toUpperCase().replace(/[^VEJ0-9-]/g, '');
                if (/^\d+$/.test(v)) this.value = 'V-' + v;
                else if (/^[VEJ]\d+$/.test(v)) this.value = v.charAt(0) + '-' + v.substring(1);
                else this.value = v;
            });
        } else if (tipo === 'rif') {
            $input.on('input', function () {
                var v = this.value.toUpperCase().replace(/[^VEJPG0-9-]/g, '');
                if (/^\d+$/.test(v)) this.value = 'J-' + v;
                else if (/^[VEJPG]\d+$/.test(v)) this.value = v.charAt(0) + '-' + v.substring(1);
                else this.value = v;
            });
        } else if (tipo === 'telefono' || tipo === 'telefono_proveedor') {
            $input.on('input', function () {
                this.value = this.value.replace(/[^0-9\-\s+]/g, '');
            });
        }
    }

    // ---------------------------------------------------------
    // Init
    // ---------------------------------------------------------
    function init(scope) {
        var $container = scope ? $(scope) : $(document);

        $container.find('input[data-validate], textarea[data-validate], select[data-validate]').each(function () {
            var $input = $(this);
            if ($input.data('validatorInit') === true) return;
            $input.data('validatorInit', true).data('touched', false);

            $input.on('input blur change', function () {
                $input.data('touched', true);
                validarCampo(this);
            });

            limpiarFeedback($input, $('#' + this.id + '-feedback'));
            aplicarAutoFormato(this);
        });

        $container.find('input[data-validate="fecha"]').each(function () {
            var hace18 = new Date();
            hace18.setFullYear(hace18.getFullYear() - 18);
            $(this).attr('max', hace18.toISOString().split('T')[0]);
        });

        $container.find('input[data-validate="fecha_simple"]').each(function () {
            $(this).attr('max', new Date().toISOString().split('T')[0]);
        });
    }

    // ---------------------------------------------------------
    // Reset
    // ---------------------------------------------------------
    function resetFormulario(formId) {
        var $form = $('#' + formId);
        if (!$form.length) return;
        $form.find('input[data-validate], textarea[data-validate], select[data-validate]').each(function () {
            var $input = $(this);
            $input.val('').data('touched', false).data('unicoEstado', '');
            clearTimeout(timersUnico[this.id]);
            limpiarFeedback($input, $('#' + this.id + '-feedback'));
        });
    }

    function resetEstadoVisual(scope) {
        var $container = scope ? $(scope) : $(document);
        $container.find('input[data-validate], textarea[data-validate], select[data-validate]').each(function () {
            var $input = $(this);
            $input.data('touched', false).data('unicoEstado', '');
            clearTimeout(timersUnico[this.id]);
            $input.removeClass('campo-valido campo-invalido campo-verificando')
                  .css({ borderColor: '', boxShadow: '' });

            var $feedbackEl = $('#' + this.id + '-feedback');
            if (!$feedbackEl.length) return;
            $feedbackEl.removeClass('info error success checking')
                       .addClass('info')
                       .html($feedbackEl.data('defaultHtml') || '');
        });
    }

    // ---------------------------------------------------------
    // API pública
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
})(jQuery);

// Auto-init
jQuery(function ($) {
    Validador.init();
});