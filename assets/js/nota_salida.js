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

    function mostrarModal(id) {
        var element = $(id).get(0);
        if (!element || !window.bootstrap || !window.bootstrap.Modal) {
            mostrarError('No se pudo abrir la ventana solicitada.');
            return false;
        }
        window.bootstrap.Modal.getOrCreateInstance(element).show();
        return true;
    }

    function enviarFiltroNotasSalida(form) {
        var formData = new FormData(form);
        formData.set('ajax', '1');

        $.ajax({
            url: '?url=notasalida&type=list',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
            .done(function(data) {
                if (!data || !data.success || !data.html) {
                    mostrarError((data && data.error) || 'No se pudo aplicar el filtro.');
                    return;
                }

                var $wrap = $('<div>').html(data.html);
                var $newView = $wrap.find('.col-md-8.col-lg-12').first();
                var $currentView = $('.col-md-8.col-lg-12').first();
                if ($newView.length && $currentView.length) {
                    $currentView.replaceWith($newView);
                }
            })
            .fail(function() {
                form.submit();
            });
    }

    $(document).on('submit', '#formFiltroNotasSalida', function(event) {
        event.preventDefault();
        enviarFiltroNotasSalida(this);
    });

    $(document).on('change', '#formFiltroNotasSalida select', function() {
        enviarFiltroNotasSalida($(this).closest('form').get(0));
    });

    window.confirmarAnularNota = function(id, cliente) {
        $('#anular-nota-id').val(id);
        $('#anular-nota-cliente').text(cliente || 'Nota #' + id);
        mostrarModal('#modalAnularNota');
    };

    $(document).on('submit', '#formAnularNota', function(event) {
        event.preventDefault();

        var $form = $(this);
        var $submit = $form.find('[type="submit"]');
        var originalHtml = $submit.html();
        $submit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Anulando...');

        $.ajax({
            url: '?url=notasalida&type=estado_ajax',
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        })
            .done(function(response) {
                if (!response || !response.success) {
                    mostrarError((response && response.error) || 'No se pudo anular la nota.');
                    return;
                }
                var modalElement = $('#modalAnularNota').get(0);
                if (modalElement && window.bootstrap && window.bootstrap.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                }
                window.location.href = '?url=notasalida&type=list&ver_anuladas=1';
            })
            .fail(function(xhr, status, error) {
                mostrarError(mensajeError(xhr, status, error));
            })
            .always(function() {
                $submit.prop('disabled', false).html(originalHtml);
            });
    });

    window.verDetalleNota = function(id) {
        var $loading = $('#ver-nota-cargando');
        var $error = $('#ver-nota-error');
        var $content = $('#ver-nota-contenido');

        $('#ver-nota-id').text('#' + id);
        $('#ver-nota-error-msg').text('');
        $loading.show();
        $error.hide();
        $content.hide();
        mostrarModal('#modalVerNota');

        $.ajax({
            url: '?url=notasalida',
            method: 'POST',
            data: { type: 'detalle_json', id: id },
            dataType: 'json'
        })
            .done(function(response) {
                $loading.hide();
                if (!response || !response.success || !response.nota) {
                    $error.show();
                    $('#ver-nota-error-msg').text((response && response.error) || 'Error al cargar la nota.');
                    return;
                }

                var nota = response.nota;
                var fecha = nota.fecha || '';
                if (typeof Validador !== 'undefined' && Validador.formatearFecha) {
                    fecha = Validador.formatearFecha(fecha);
                }

                $('#ver-nota-fecha').text(fecha || '—');
                $('#ver-nota-encargado').text(nota.usuario_responsable || 'N/A');
                $('#ver-nota-cliente').text(nota.cliente || 'N/A');
                $('#ver-nota-documento').text(nota.documento || 'N/A');

                var totalUnidades = 0;
                var totalCosto = 0;
                var $tbody = $('#ver-nota-productos-tbody').empty();
                var detalles = $.isArray(nota.detalles) ? nota.detalles : [];

                if (detalles.length) {
                    $.each(detalles, function(index, detalle) {
                        var cantidad = parseInt(detalle.cantidad, 10) || 0;
                        var costo = parseFloat(detalle.costo_unitario) || 0;
                        var subtotal = cantidad * costo;
                        totalUnidades += cantidad;
                        totalCosto += subtotal;

                        var $row = $('<tr>');
                        $row.append($('<td class="ps-4 text-center">').text(index + 1));
                        $row.append($('<td>').text(detalle.nombre_producto || 'N/A'));
                        $row.append($('<td>').text(detalle.nombre_categoria || 'Sin categoría'));
                        $row.append($('<td class="text-center fw-bold">').text(cantidad));
                        $row.append($('<td class="text-end text-nowrap">').text('$' + costo.toFixed(2)));
                        $row.append($('<td class="pe-3 text-end text-nowrap fw-semibold">').text('$' + subtotal.toFixed(2)));
                        $tbody.append($row);
                    });
                } else {
                    $tbody.append(
                        $('<tr>').append(
                            $('<td class="text-center py-3" colspan="6">')
                                .html('<i class="fas fa-box-open me-2"></i> No hay productos en esta nota')
                        )
                    );
                }

                $('#ver-nota-total-unidades').text(totalUnidades);
                $('#ver-nota-total-costo').text('Total: $' + totalCosto.toFixed(2));
                $content.show();
            })
            .fail(function(xhr, status, error) {
                $loading.hide();
                $error.show();
                $('#ver-nota-error-msg').text('Error de conexión: ' + mensajeError(xhr, status, error));
            });
    };

    window.imprimirNotaPDF = function() {
        var idNota = $('#ver-nota-id').text().replace('#', '').trim();
        if (!idNota) {
            mostrarError('No se pudo identificar la nota.');
            return;
        }
        window.open('?url=notasalida&type=generar_pdf&id=' + encodeURIComponent(idNota), '_blank');
    };

    window.verMotivoAnulacion = function(id, button) {
        var $button = $(button);
        var originalHtml = $button.html();
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '?url=notasalida',
            type: 'POST',
            dataType: 'json',
            data: { type: 'show_json', id: id }
        })
            .done(function(response) {
                if (!response || !response.success || !response.nota) {
                    mostrarError((response && response.error) || 'No se pudo cargar el motivo de anulación.');
                    return;
                }

                var nota = response.nota;
                $('#motivo-doc').text('#' + nota.id_nota_salida);
                $('#motivo-cliente').text(nota.cliente || 'N/A');
                $('#motivo-texto').text(nota.motivo_anulacion || 'No se registró un motivo.');

                if (!mostrarModal('#modalMotivoAnulacion')) return;
            })
            .fail(function(xhr, status, error) {
                mostrarError(mensajeError(xhr, status, error));
            })
            .always(function() {
                $button.prop('disabled', false).html(originalHtml);
            });
    };
});
