$(document).ready(function() {
    'use strict';

    function mostrarError(mensaje) {
        if (typeof window.mostrarAlerta === 'function') {
            window.mostrarAlerta(mensaje, 'danger');
        } else {
            window.alert(mensaje);
        }
    }

    function obtenerMensajeError(xhr, status, error) {
        if (xhr.responseJSON && xhr.responseJSON.error) {
            return xhr.responseJSON.error;
        }
        return error || status || 'No se pudo completar la solicitud.';
    }

    function cargarProveedor(id, button, onSuccess) {
        var $button = $(button);
        var originalHtml = $button.length ? $button.html() : '';

        if ($button.length) {
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        }

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
                mostrarError(obtenerMensajeError(xhr, status, error));
            })
            .always(function() {
                if ($button.length) {
                    $button.prop('disabled', false).html(originalHtml);
                }
            });
    }

    window.verDetalleProveedor = function(id, button) {
        cargarProveedor(id, button, function(proveedor) {
            $('#ver-proveedor-razon-titulo, #ver-proveedor-razon').text(proveedor.razon_social || '—');
            $('#ver-proveedor-rif-badge, #ver-proveedor-rif').text(proveedor.rif || '—');
            $('#ver-proveedor-telefono').text(proveedor.numero_contacto || proveedor.telefono || '—');
            $('#ver-proveedor-correo').text(proveedor.correo_electronico || '—');
            $('#ver-proveedor-direccion').text(proveedor.direccion || '—');
            bootstrap.Modal.getOrCreateInstance($('#modalVerProveedor')[0]).show();
        });
    };

    window.editarProveedor = function(id, button) {
        cargarProveedor(id, button, function(proveedor) {
            $('#editar-proveedor-id').val(proveedor.id_proveedor);
            $('#editar-proveedor-rif').val(proveedor.rif || '');
            $('#editar-proveedor-telefono').val(proveedor.numero_contacto || '');
            $('#editar-proveedor-razon').val(proveedor.razon_social || '');
            $('#editar-proveedor-correo').val(proveedor.correo_electronico || '');
            $('#editar-proveedor-direccion').val(proveedor.direccion || '');
            $('#editar-proveedor-eliminado').val(String(proveedor.eliminado || 0));
            $('#editar-proveedor-razon-header').text(proveedor.razon_social || '—');
            bootstrap.Modal.getOrCreateInstance($('#modalEditarProveedor')[0]).show();
        });
    };

    window.confirmarEliminarProveedor = function(id, button) {
        cargarProveedor(id, button, function(proveedor) {
            $('#eliminar-proveedor-id').val(proveedor.id_proveedor);
            $('#eliminar-proveedor-nombre').text(proveedor.razon_social || proveedor.rif || '—');
            bootstrap.Modal.getOrCreateInstance($('#modalEliminarProveedor')[0]).show();
        });
    };

    function cambiarEstadoProveedor(id, accion, button, cerrarModal) {
        var $button = $(button);
        var originalHtml = $button.length ? $button.html() : '';
        var recargar = false;

        if ($button.length) {
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        }

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
                recargar = true;
                if (cerrarModal) {
                    bootstrap.Modal.getOrCreateInstance($('#modalEliminarProveedor')[0]).hide();
                }
            })
            .fail(function(xhr, status, error) {
                mostrarError(obtenerMensajeError(xhr, status, error));
            })
            .always(function() {
                if ($button.length) {
                    $button.prop('disabled', false).html(originalHtml);
                }
                if (recargar) {
                    window.location.reload();
                }
            });
    }

    window.reactivarProveedor = function(id, button) {
        if (!window.confirm('¿Deseas reactivar este proveedor?')) {
            return;
        }
        cambiarEstadoProveedor(id, 'reactivar', button, false);
    };

    $('#formEliminarProveedor').on('submit', function(event) {
        event.preventDefault();
        cambiarEstadoProveedor(
            $('#eliminar-proveedor-id').val(),
            'inactivar',
            $(this).find('[type="submit"]'),
            true
        );
    });

    $('.proveedor-rif').on('blur', function() {
        var $input = $(this);
        var value = $.trim($input.val()).toUpperCase();
        if (/^\d+$/.test(value)) {
            value = 'J-' + value;
        } else if (/^J\d+$/.test(value)) {
            value = 'J-' + value.substring(1);
        }
        $input.val(value);
    }).on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    $('.proveedor-razon').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    $('.proveedor-telefono').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9-]/g, ''));
    });

    $('#formEditarProveedor').on('submit', function(event) {
        if (typeof Validador !== 'undefined' && !Validador.validarFormulario('formEditarProveedor')) {
            event.preventDefault();
            if (typeof window.mostrarAlerta === 'function') {
                window.mostrarAlerta('Revisa los campos marcados en rojo', 'warning');
            }
        }
    });
});
