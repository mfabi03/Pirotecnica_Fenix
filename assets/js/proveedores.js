$(document).ready(function() {
    'use strict';

    function mostrarError(mensaje) {
        if (typeof window.mostrarAlerta === 'function') {
            window.mostrarAlerta(mensaje, 'danger');
        } else {
            window.alert(mensaje);
        }
    }

    function mensajeError(xhr, status, error) {
        if (xhr.responseJSON && xhr.responseJSON.error) return xhr.responseJSON.error;
        if (xhr.responseText) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.error) return response.error;
            } catch (e) {
                return 'Respuesta no válida del servidor.';
            }
        }
        return error || status || 'No se pudo completar la solicitud.';
    }

    function modal(id) {
        var element = $(id).get(0);
        if (element && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(element).show();
        }
    }

    function cargarProveedor(id, $button, onSuccess) {
        var originalHtml = $button.length ? $button.html() : '';
        if ($button.length) $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '?url=proveedores&type=detalle_ajax',
            type: 'POST',
            dataType: 'json',
            data: { id_proveedor: id }
        })
            .done(function(response) {
                if (!response.success || !response.proveedor) {
                    mostrarError(response.error || 'No se encontró el proveedor.');
                    return;
                }
                onSuccess(response.proveedor);
            })
            .fail(function(xhr, status, error) {
                mostrarError(mensajeError(xhr, status, error));
            })
            .always(function() {
                if ($button.length) $button.prop('disabled', false).html(originalHtml);
            });
    }

    window.verDetalleProveedor = function(id, button) {
        cargarProveedor(id, $(button), function(proveedor) {
            $('#ver-proveedor-razon-titulo, #ver-proveedor-razon').text(proveedor.razon_social || '—');
            $('#ver-proveedor-rif-badge, #ver-proveedor-rif').text(proveedor.rif || '—');
            $('#ver-proveedor-telefono').text(proveedor.numero_contacto || proveedor.telefono || '—');
            $('#ver-proveedor-correo').text(proveedor.correo_electronico || '—');
            $('#ver-proveedor-direccion').text(proveedor.direccion || '—');
            modal('#modalVerProveedor');
        });
    };

    window.editarProveedor = function(id, button) {
        cargarProveedor(id, $(button), function(proveedor) {
            $('#editar-proveedor-id').val(proveedor.id_proveedor);
            $('#editar-proveedor-rif').val(proveedor.rif || '');
            $('#editar-proveedor-telefono').val(proveedor.numero_contacto || '');
            $('#editar-proveedor-razon').val(proveedor.razon_social || '');
            $('#editar-proveedor-correo').val(proveedor.correo_electronico || '');
            $('#editar-proveedor-direccion').val(proveedor.direccion || '');
            $('#editar-proveedor-eliminado').val(String(proveedor.eliminado || 0));
            $('#editar-proveedor-razon-header').text(proveedor.razon_social || '—');
            if (typeof Validador !== 'undefined') Validador.init($('#modalEditarProveedor').get(0));
            modal('#modalEditarProveedor');
        });
    };

    window.confirmarEliminarProveedor = function(id, button) {
        cargarProveedor(id, $(button), function(proveedor) {
            $('#eliminar-proveedor-id').val(proveedor.id_proveedor);
            $('#eliminar-proveedor-nombre').text(proveedor.razon_social || proveedor.rif || '—');
            modal('#modalEliminarProveedor');
        });
    };

    function cambiarEstadoProveedor(id, accion, $button, cerrarModal) {
        var originalHtml = $button.length ? $button.html() : '';
        if ($button.length) $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '?url=proveedores&type=estado_ajax',
            type: 'POST',
            dataType: 'json',
            data: { id_proveedor: id, accion: accion }
        })
            .done(function(response) {
                if (!response.success) {
                    mostrarError(response.error || 'No se pudo cambiar el estado del proveedor.');
                    return;
                }
                if (cerrarModal) {
                    var element = $('#modalEliminarProveedor').get(0);
                    if (element && bootstrap.Modal.getInstance(element)) {
                        bootstrap.Modal.getInstance(element).hide();
                    }
                }
                window.location.reload();
            })
            .fail(function(xhr, status, error) {
                mostrarError(mensajeError(xhr, status, error));
            })
            .always(function() {
                if ($button.length) $button.prop('disabled', false).html(originalHtml);
            });
    }

    window.reactivarProveedor = function(id, button) {
        if (!window.confirm('¿Deseas reactivar este proveedor?')) return;
        cambiarEstadoProveedor(id, 'reactivar', $(button), false);
    };

    $(document).on('click', '[data-proveedor-action]', function() {
        var $button = $(this);
        var id = parseInt($button.attr('data-proveedor-id'), 10);
        var action = $button.attr('data-proveedor-action');
        if (action === 'view') window.verDetalleProveedor(id, this);
        if (action === 'edit') window.editarProveedor(id, this);
        if (action === 'inactivate') window.confirmarEliminarProveedor(id, this);
        if (action === 'reactivate') window.reactivarProveedor(id, this);
    });

    $(document).on('change', '[data-auto-submit]', function() {
        $(this).closest('form').trigger('submit');
    });

    $('.proveedor-rif, #rif').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    }).on('blur', function() {
        var value = $.trim($(this).val()).toUpperCase();
        if (/^\d+$/.test(value)) value = 'J-' + value;
        else if (/^J\d+$/.test(value)) value = 'J-' + value.substring(1);
        $(this).val(value);
    });

    $('.proveedor-razon, #razon_social').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    $('.proveedor-telefono, #numero_contacto').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9-]/g, ''));
    });

    $('#modalRegistrarProveedor').on('shown.bs.modal', function() {
        if (typeof Validador !== 'undefined') Validador.init(this);
    });

    function validarFormulario($form) {
        if (typeof Validador === 'undefined') return true;
        if (Validador.validarFormulario($form.attr('id'))) return true;
        mostrarError('Revisa los campos marcados en rojo.');
        return false;
    }

    function enviarProveedor($form, tipo) {
        var $submit = $form.find('[type="submit"]');
        var originalHtml = $submit.html();
        var url = tipo === 'store' ? '?url=proveedores&type=store_ajax' : '?url=proveedores&type=update_ajax';
        var $rif = $form.find('[name="rif"][data-unico-endpoint]');

        function enviar() {
            $submit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: $form.serialize()
            })
                .done(function(response) {
                    if (!response.success) {
                        mostrarError(response.error || 'No se pudo guardar el proveedor.');
                        return;
                    }
                    window.location.reload();
                })
                .fail(function(xhr, status, error) {
                    mostrarError(mensajeError(xhr, status, error));
                })
                .always(function() {
                    $submit.prop('disabled', false).html(originalHtml);
                });
        }

        if (!validarFormulario($form)) return;
        if (tipo === 'store' && $rif.length && typeof Validador !== 'undefined') {
            var rifValue = $rif.val();
            Validador.validarUnico($rif.get(0)).then(function(disponible) {
                if ($rif.val() !== rifValue) return;
                if (disponible) enviar();
            });
            return;
        }
        enviar();
    }

    $('#formRegistrarProveedor, #formEditarProveedor').on('submit', function(event) {
        event.preventDefault();
        enviarProveedor($(this), this.id === 'formRegistrarProveedor' ? 'store' : 'update');
    });

    $('#formEliminarProveedor').on('submit', function(event) {
        event.preventDefault();
        cambiarEstadoProveedor(
            $('#eliminar-proveedor-id').val(),
            'inactivar',
            $(this).find('[type="submit"]'),
            true
        );
    });
});
