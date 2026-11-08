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
        if (xhr.responseJSON && xhr.responseJSON.error) {
            return xhr.responseJSON.error;
        }
        if (xhr.responseText) {
            return 'Respuesta no válida del servidor: ' +
                xhr.responseText.replace(/\s+/g, ' ').trim().slice(0, 300);
        }
        return error || status || 'No se pudo completar la solicitud.';
    }

    window.verDetalleNotaEntrada = function(id, button) {
        var $button = $(button);
        var originalHtml = $button.html();
        var $loading = $('#ver-nota-cargando');
        var $error = $('#ver-nota-error');
        var $content = $('#ver-nota-contenido');

        $('#ver-nota-id').text('#' + id);
        $('#ver-nota-error-msg').text('');
        $loading.show();
        $error.hide();
        $content.hide();
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        bootstrap.Modal.getOrCreateInstance($('#modalVerNotaEntrada')[0]).show();

        $.ajax({
            url: '?url=notaentrada',
            type: 'POST',
            dataType: 'json',
            data: { type: 'show_json', id: id }
        })
            .done(function(res) {
                if (!res.success || !res.nota) {
                    $loading.hide();
                    $error.show();
                    $('#ver-nota-error-msg').text(res.error || 'No se pudo cargar el detalle de la nota.');
                    return;
                }

                var nota = res.nota;
                var fecha = nota.fecha_ingreso ? new Date(nota.fecha_ingreso.replace(' ', 'T')) : null;
                var fechaValida = fecha && !Number.isNaN(fecha.getTime());
                var anulada = parseInt(nota.eliminado, 10) === 1;

                $loading.hide();
                $('#ver-nota-id').text('#' + nota.id_nota_entrada);
                $('#ver-nota-fecha').text(fechaValida ? fecha.toLocaleDateString('es-VE') : '—');
                $('#ver-nota-hora').text(fechaValida
                    ? fecha.toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit' })
                    : '—');
                $('#ver-nota-encargado').text(nota.encargado_nombre || 'Sin asignar');
                $('#ver-nota-proveedor').text(nota.razon_social || 'N/A');
                $('#ver-nota-rif').text(nota.rif || 'N/A');
                $('#ver-nota-descripcion').text(nota.descripcion || 'Sin descripción');
                $('#ver-nota-estado')
                    .text(anulada ? 'Inactiva / Anulada' : 'Activa')
                    .attr('class', 'badge ' + (anulada ? 'bg-danger' : 'bg-success'));

                var $tbody = $('#ver-nota-productos-tbody').empty();
                var totalCantidad = 0;
                var totalDinero = 0;
                var detalles = $.isArray(nota.detalles) ? nota.detalles : [];

                if (detalles.length) {
                    $.each(detalles, function(_, detalle) {
                        var cantidad = parseInt(detalle.cantidad, 10) || 0;
                        var costo = parseFloat(detalle.costo_unitario) || 0;
                        var subtotal = cantidad * costo;
                        totalCantidad += cantidad;
                        totalDinero += subtotal;

                        $('<tr>')
                            .append($('<td class="ps-4">').text(detalle.id_producto || ''))
                            .append($('<td class="fw-semibold">').text(detalle.nombre_producto || 'Producto'))
                            .append($('<td class="text-secondary">').text(detalle.nombre_categoria || 'Sin categoría'))
                            .append($('<td class="text-center fw-bold">').text(cantidad))
                            .append($('<td class="text-center">').text('$' + costo.toFixed(2)))
                            .append($('<td class="pe-4 text-center fw-bold">').text('$' + subtotal.toFixed(2)))
                            .appendTo($tbody);
                    });
                } else {
                    $('<tr><td colspan="6" class="text-center text-muted py-3">No hay productos en esta nota.</td></tr>')
                        .appendTo($tbody);
                }

                $('#ver-nota-total-unidades').text(totalCantidad);
                $('#ver-nota-total-costo').text('$' + totalDinero.toFixed(2));
                $content.show();
            })
            .fail(function(xhr, status, error) {
                $loading.hide();
                $error.show();
                $('#ver-nota-error-msg').text(mensajeError(xhr, status, error));
            })
            .always(function() {
                $button.prop('disabled', false).html(originalHtml);
            });
    };

    window.editarNotaEntrada = function(id, button) {
        $(button).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        window.location.href = '?url=notaentrada&type=edit&id=' + encodeURIComponent(id);
    };

    window.prepararAnulacionNotaEntrada = function(id, proveedor, button) {
        $('#modalIdNota').val(id);
        $('#modalProveedor').text(proveedor || 'N/A');
        $('#formAnularNota').data('trigger-button', button);
        bootstrap.Modal.getOrCreateInstance($('#anularModal')[0]).show();
    };

    function enviarCambioEstadoNota(id, accion, motivo, button) {
        var $button = $(button);
        var originalHtml = $button.html();
        var recargar = false;

        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '?url=notaentrada',
            type: 'POST',
            dataType: 'json',
            data: { type: 'estado_ajax', id: id, accion: accion, motivo: motivo }
        })
            .done(function(response) {
                if (!response.success) {
                    mostrarError(response.error || 'No se pudo cambiar el estado de la nota.');
                    return;
                }
                recargar = true;
                if (accion === 'inactivar') {
                    bootstrap.Modal.getOrCreateInstance($('#anularModal')[0]).hide();
                }
            })
            .fail(function(xhr, status, error) {
                mostrarError(mensajeError(xhr, status, error));
            })
            .always(function() {
                $button.prop('disabled', false).html(originalHtml);
                if (recargar) {
                    window.location.reload();
                }
            });
    }

    window.reactivarNotaEntrada = function(id, button) {
        if (!window.confirm('¿Deseas reactivar esta nota y restaurar las cantidades al inventario?')) {
            return;
        }
        enviarCambioEstadoNota(id, 'reactivar', '', button);
    };

    $('#formAnularNota').on('submit', function(event) {
        event.preventDefault();
        var $form = $(this);
        var motivo = $.trim($form.find('[name="motivo_anulacion"]').val());
        if (!motivo) {
            $form.find('[name="motivo_anulacion"]').trigger('focus');
            mostrarError('Debes indicar el motivo de anulación.');
            return;
        }

        enviarCambioEstadoNota(
            $('#modalIdNota').val(),
            'inactivar',
            motivo,
            $form.find('[type="submit"]')
        );
    });

    $('[data-auto-dismiss]').each(function() {
        var $alert = $(this);
        var delay = parseInt($alert.data('auto-dismiss'), 10) || 5000;
        $alert.delay(delay).fadeOut(400, function() {
            $alert.remove();
        });
    });
});
