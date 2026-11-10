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

    function escaparHtml(valor) {
        return $('<div>').text(valor == null ? '' : String(valor)).html();
    }

    function modal(id, accion) {
        var element = $(id).get(0);
        if (!element || !window.bootstrap || !bootstrap.Modal) return;
        var instance = bootstrap.Modal.getOrCreateInstance(element);
        if (accion === 'hide') instance.hide();
        else instance.show();
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
        modal('#modalVerNotaEntrada', 'show');

        $.ajax({
            url: '?url=notaentrada',
            type: 'POST',
            dataType: 'json',
            data: { type: 'show_json', id: id }
        })
            .done(function(response) {
                if (!response.success || !response.nota) {
                    $loading.hide();
                    $error.show();
                    $('#ver-nota-error-msg').text(response.error || 'No se pudo cargar el detalle de la nota.');
                    return;
                }

                var nota = response.nota;
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
                    .text(anulada ? 'Anulada' : 'Activa')
                    .attr('class', 'badge ' + (anulada ? 'bg-danger' : 'bg-success'));

                var totalUnidades = 0;
                var totalCosto = 0;
                var tbodyHtml = '';
                var detalles = $.isArray(nota.detalles) ? nota.detalles : [];

                if (detalles.length) {
                    $.each(detalles, function(index, detalle) {
                        var cantidad = parseInt(detalle.cantidad, 10) || 0;
                        var costo = parseFloat(detalle.costo_unitario) || 0;
                        totalUnidades += cantidad;
                        totalCosto += cantidad * costo;
                        tbodyHtml += '<tr>' +
                            '<td class="ps-3 fw-bold text-start">' + (index + 1) + '</td>' +
                            '<td class="fw-semibold text-start">' + escaparHtml(detalle.nombre_producto || 'Producto') + '</td>' +
                            '<td class="text-secondary text-start">' + escaparHtml(detalle.nombre_categoria || 'Sin categoría') + '</td>' +
                            '<td class="text-center fw-bold">' + cantidad + '</td>' +
                            '<td class="pe-3 text-end text-nowrap">$' + costo.toFixed(2) + '</td>' +
                            '</tr>';
                    });
                } else {
                    tbodyHtml = '<tr><td colspan="5" class="text-center text-muted py-3">No hay productos en esta nota.</td></tr>';
                }

                $('#ver-nota-productos-tbody').html(tbodyHtml);
                $('#ver-nota-total-unidades').text(totalUnidades);
                $('#ver-nota-total-costo').text('Total: $' + totalCosto.toFixed(2));
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

    window.prepararAnulacionNotaEntrada = function(id, proveedor) {
        $('#modalIdNota').val(id);
        $('#modalProveedor').text(proveedor || 'N/A');
        $('#formAnularNota').find('[name="motivo_anulacion"]').val('');
        modal('#anularModal', 'show');
    };

    function cambiarEstadoNota(id, accion, motivo, $button, cerrarModal) {
        var originalHtml = $button.length ? $button.html() : '';
        if ($button.length) $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

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
                if (cerrarModal) modal('#anularModal', 'hide');
                window.location.reload();
            })
            .fail(function(xhr, status, error) {
                mostrarError(mensajeError(xhr, status, error));
            })
            .always(function() {
                if ($button.length) $button.prop('disabled', false).html(originalHtml);
            });
    }

    window.reactivarNotaEntrada = function(id, button) {
        if (!window.confirm('¿Deseas reactivar esta nota y restaurar las cantidades al inventario?')) return;
        cambiarEstadoNota(id, 'reactivar', '', $(button), false);
    };

    $(document).on('click', '[data-nota-action]', function() {
        var $button = $(this);
        var id = parseInt($button.attr('data-nota-id'), 10);
        var accion = $button.attr('data-nota-action');
        if (accion === 'view') window.verDetalleNotaEntrada(id, this);
        if (accion === 'cancel') window.prepararAnulacionNotaEntrada(id, $button.attr('data-proveedor'));
        if (accion === 'reactivate') window.reactivarNotaEntrada(id, this);
    });

    $(document).on('change', '[data-auto-submit]', function() {
        $(this).closest('form').trigger('submit');
    });

    $('#formAnularNota').on('submit', function(event) {
        event.preventDefault();
        var $form = $(this);
        var motivo = $.trim($form.find('[name="motivo_anulacion"]').val());
        if (!motivo) {
            $form.find('[name="motivo_anulacion"]').trigger('focus');
            mostrarError('Debes indicar el motivo de anulación.');
            return;
        }
        cambiarEstadoNota(
            $('#modalIdNota').val(),
            'inactivar',
            motivo,
            $form.find('[type="submit"]'),
            true
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
