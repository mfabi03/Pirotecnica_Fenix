/**
 * ============================================================
 * MÓDULO CLIENTES - Versión jQuery
 * ------------------------------------------------------------
 * Requiere:
 *   - jQuery 3.7.1 (cargado globalmente en footer.php)
 *   - Bootstrap 5 (cargado globalmente en footer.php)
 *   - Validador global (validaciones.jquery.js)
 * ============================================================
 */

(function ($) {
    'use strict';

    $(function () {

        // ---------------------------------------------------------
        // MAPA DE CLIENTES (id → datos)
        // ---------------------------------------------------------
        function inicializarMapaClientes() {
            var $scriptCliente = $('#clientesData');
            if (!$scriptCliente.length) {
                window.clientesPorId = {};
                return;
            }

            try {
                var clientesData = JSON.parse($scriptCliente.text() || '[]');
                var clientesPorId = {};
                $.each(clientesData, function (_, c) {
                    clientesPorId[parseInt(c.id_cliente, 10)] = c;
                });
                window.clientesPorId = clientesPorId;
            } catch (error) {
                console.error('No se pudieron cargar los datos de clientes:', error);
                window.clientesPorId = {};
            }
        }

        // ---------------------------------------------------------
        // FILTRO AJAX DE CLIENTES
        // ---------------------------------------------------------
        var $formClientes = $('#formFiltroClientes');
        if ($formClientes.length) {
            var enviarFiltroClientes = function () {
                var formData = new FormData($formClientes[0]);
                formData.set('ajax', '1');

                $.ajax({
                    url: '?url=clientes&type=list',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                })
                .done(function (data) {
                    if (!data || !data.success || !data.html) return;
                    var $wrap = $('<div>').html(data.html);
                    var $nuevaVista = $wrap.find('.col-md-8.col-lg-12').first();
                    if (!$nuevaVista.length) return;
                    var $contenedorActual = $('.col-md-8.col-lg-12').first();
                    if ($contenedorActual.length) $contenedorActual.replaceWith($nuevaVista);
                    inicializarMapaClientes();
                })
                .fail(function () {
                    $formClientes.off('submit.filtroClientes');
                    $formClientes[0].submit();
                });
            };

            $formClientes.on('submit', function (e) {
                e.preventDefault();
                enviarFiltroClientes();
            });

            $formClientes.find('select').on('change', function () {
                enviarFiltroClientes();
            });
        }

        inicializarMapaClientes();

        // ---------------------------------------------------------
        // CALLBACK MENOR DE EDAD
        // ---------------------------------------------------------
        var modalMenorEdadMostrado = false;

        if (typeof Validador !== 'undefined') {
            Validador.onMenorEdad(function (edad) {
                if (modalMenorEdadMostrado) return;

                var $modalEl = $('#modalAlertaMenorEdad');
                if (!$modalEl.length) {
                    console.warn('No se encontró el modal de menor de edad');
                    return;
                }

                modalMenorEdadMostrado = true;

                $('#alerta-edad-detectada').text(edad + ' años');

                var modal = bootstrap.Modal.getOrCreateInstance($modalEl[0]);
                modal.show();

                $modalEl.one('hidden.bs.modal', function () {
                    modalMenorEdadMostrado = false;
                });
            });
        }

        // ---------------------------------------------------------
        // INICIALIZAR VALIDADOR EN LOS MODALES
        // ---------------------------------------------------------
        var $modalReg = $('#modalRegistrarCliente');
        var $modalEdt = $('#modalEditarCliente');
        if (typeof Validador !== 'undefined') {
            if ($modalReg.length) Validador.init($modalReg[0]);
            if ($modalEdt.length) Validador.init($modalEdt[0]);
        }

        if ($modalReg.length) {
            $modalReg.on('show.bs.modal', function () {
                if (typeof Validador !== 'undefined') {
                    Validador.resetFormulario('formRegistrarNatural');
                    Validador.resetFormulario('formRegistrarJuridico');
                }
            });
        }

        // ---------------------------------------------------------
        // VALIDAR AL ENVIAR FORMULARIOS
        // ---------------------------------------------------------
        $.each(['formRegistrarNatural', 'formRegistrarJuridico', 'formEditarNatural', 'formEditarJuridico'], function (_, id) {
            var $form = $('#' + id);
            if (!$form.length) return;

            $form.on('submit', function (e) {
                if (id === 'formRegistrarNatural' || id === 'formEditarNatural') {
                    var inputFecha = (id === 'formRegistrarNatural')
                        ? document.getElementById('reg-fecha-nac')
                        : document.getElementById('editar-natural-fecha');

                    if (inputFecha && inputFecha.value) {
                        var partes = inputFecha.value.split('-');
                        var fechaNac = new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
                        var hoy = new Date();
                        var edad = hoy.getFullYear() - fechaNac.getFullYear();
                        var mes = hoy.getMonth() - fechaNac.getMonth();
                        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) edad--;

                        if (edad < 18) {
                            e.preventDefault();
                            e.stopPropagation();

                            var $modalEl = $('#modalAlertaMenorEdad');
                            if ($modalEl.length) {
                                $('#alerta-edad-detectada').text((edad >= 0 ? edad : 0) + ' años');
                                bootstrap.Modal.getOrCreateInstance($modalEl[0]).show();
                            }

                            $(inputFecha).addClass('campo-invalido').trigger('focus');
                            return false;
                        } else {
                            $(inputFecha).removeClass('campo-invalido');
                        }
                    }
                }

                if (typeof Validador !== 'undefined' && !Validador.validarFormulario(id)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        });

        // ---------------------------------------------------------
        // VER DETALLE DEL CLIENTE
        // ---------------------------------------------------------
        window.verDetalleCliente = function (id) {
            var clientesPorId = window.clientesPorId || {};
            var c = clientesPorId[id];
            if (!c) return;

            var esJuridico = (c.tipo_cliente === 'Jurídico');
            var nombre = esJuridico
                ? (c.razon_social || 'Sin razón social')
                : $.trim((c.nombre || '') + ' ' + (c.apellido || ''));
            var documento = esJuridico ? (c.rif || 'N/A') : (c.cedula || 'N/A');

            $('#ver-cliente-titulo').find('h4').text(nombre || 'Sin nombre');

            var $badge = $('#ver-cliente-tipo-badge');
            if (esJuridico) {
                $badge.text('Jurídico').css({ background: 'rgba(13,110,253,0.15)', color: '#0d6efd' });
            } else {
                $badge.text('Natural').css({ background: 'rgba(40,167,69,0.15)', color: '#28a745' });
            }

            $('#ver-cliente-id').text('#' + c.id_cliente);

            var $statusBadge = $('#ver-cliente-status-badge');
            // 🔥 FORZAMOS EL TEXTO "ELIMINADO" SIEMPRE QUE EL CLIENTE ESTÉ INACTIVO
            if (parseInt(c.eliminado, 10) === 1 || c.status === 'inactivo' || c.status === 'Eliminado' || c.eliminado == 1) {
                $statusBadge.text('Eliminado').css({ background: 'rgba(220,53,69,0.15)', color: '#dc3545' });
            } else {
                $statusBadge.text('Activo').css({ background: 'rgba(40,167,69,0.15)', color: '#28a745' });
            }

            $('#ver-cliente-documento').text(documento);
            $('#ver-cliente-nombre').text(nombre);
            $('#ver-cliente-telefono').text(c.telefono || 'N/A');
            $('#ver-cliente-correo').text(c.correo_electronico || 'N/A');
            $('#ver-cliente-direccion').text(c.direccion || 'Sin dirección registrada');

            var $fechaWrap = $('#ver-cliente-fecha-wrap');
            if (esJuridico || !c.fecha_de_nacimiento) {
                $fechaWrap.hide();
            } else {
                $fechaWrap.show();
                var fechaFmt = (typeof Validador !== 'undefined')
                    ? Validador.formatearFecha(c.fecha_de_nacimiento)
                    : c.fecha_de_nacimiento;
                $('#ver-cliente-fecha').text(fechaFmt);
            }

            bootstrap.Modal.getOrCreateInstance($('#modalVerCliente')[0]).show();
        };

        // ---------------------------------------------------------
        // EDITAR CLIENTE
        // ---------------------------------------------------------
        window.editarCliente = function (id) {
            var clientesPorId = window.clientesPorId || {};
            var c = clientesPorId[id];
            if (!c) return;

            var esJuridico = (c.tipo_cliente === 'Jurídico');
            var nombre = esJuridico
                ? (c.razon_social || '')
                : $.trim((c.nombre || '') + ' ' + (c.apellido || ''));

            $('#editar-cliente-nombre-header').text(nombre || 'Cliente');

            var $bloqueNatural = $('#editar-bloque-natural');
            var $bloqueJuridico = $('#editar-bloque-juridico');

            if (esJuridico) {
                $bloqueNatural.hide();
                $bloqueJuridico.show();

                $('#editar-juridico-id').val(c.id_cliente);
                $('#editar-juridico-rif').val(c.rif || '');
                $('#editar-juridico-telefono').val(c.telefono || '');
                $('#editar-juridico-razon').val(c.razon_social || '');
                $('#editar-juridico-correo').val(c.correo_electronico || '');
                $('#editar-juridico-direccion').val(c.direccion || '');

                setTimeout(function () {
                    if (typeof Validador !== 'undefined') {
                        $('#editar-bloque-juridico input[data-validate]').each(function () {
                            Validador.validarCampo(this);
                        });
                    }
                }, 100);
            } else {
                $bloqueNatural.show();
                $bloqueJuridico.hide();

                $('#editar-natural-id').val(c.id_cliente);
                $('#editar-natural-cedula').val(c.cedula || '');
                $('#editar-natural-telefono').val(c.telefono || '');
                $('#editar-natural-nombre').val(c.nombre || '');
                $('#editar-natural-apellido').val(c.apellido || '');
                $('#editar-natural-correo').val(c.correo_electronico || '');
                $('#editar-natural-fecha').val(c.fecha_de_nacimiento || '');
                $('#editar-natural-direccion').val(c.direccion || '');

                setTimeout(function () {
                    if (typeof Validador !== 'undefined') {
                        $('#editar-bloque-natural input[data-validate]').each(function () {
                            Validador.validarCampo(this);
                        });
                    }
                }, 100);
            }

            bootstrap.Modal.getOrCreateInstance($('#modalEditarCliente')[0]).show();
        };

        // ---------------------------------------------------------
        // CONFIRMAR ELIMINAR
        // ---------------------------------------------------------
        window.confirmarEliminarCliente = function (id, nombre) {
            $('#eliminar-cliente-id').val(id);
            $('#eliminar-cliente-nombre').text(nombre || 'Cliente #' + id);
            bootstrap.Modal.getOrCreateInstance($('#modalEliminarCliente')[0]).show();
        };

        // ---------------------------------------------------------
        // CONFIRMAR REACTIVAR
        // ---------------------------------------------------------
        window.confirmarReactivarCliente = function (id, nombre) {
            $('#reactivar-cliente-id').val(id);
            $('#reactivar-cliente-nombre').text(nombre || 'Cliente #' + id);
            bootstrap.Modal.getOrCreateInstance($('#modalReactivarCliente')[0]).show();
        };

        // ---------------------------------------------------------
        // ESTILO DE PESTAÑAS DEL MODAL DE REGISTRO
        // ---------------------------------------------------------
        $('#modalRegistrarCliente .nav-link').on('shown.bs.tab', function (e) {
            $('#modalRegistrarCliente .nav-link').css({ color: '#6c757d', borderBottom: 'none' });
            $(e.target).css({ color: '#f39c12', borderBottom: '2px solid #f39c12' });
        });

    });

})(jQuery);