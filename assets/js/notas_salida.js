(function ($) {
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
        return error || status || 'No se pudo cargar el detalle de la nota.';
    }

    function mostrarModal(id) {
        var elemento = $(id).get(0);
        if (!elemento || !window.bootstrap || !window.bootstrap.Modal) return;
        window.bootstrap.Modal.getOrCreateInstance(elemento).show();
    }

    window.verDetalleNotaSalida = function (id, button) {
        var $button = button ? $(button) : $();
        var htmlOriginal = $button.length ? $button.html() : '';
        var $cargando = $('#ver-salida-cargando');
        var $error = $('#ver-salida-error');
        var $contenido = $('#ver-salida-contenido');
        var $botonImprimir = $('#btnImprimirNotaSalida');

        $('#ver-salida-id').text('#' + id);
        $('#ver-salida-error-msg').text('');
        $botonImprimir.hide().removeAttr('href');
        $cargando.show();
        $error.hide();
        $contenido.hide();
        if ($button.length) $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        mostrarModal('#modalVerNotaSalida');

        $.ajax({
            url: '?url=notasalida&type=detalle_json&id=' + encodeURIComponent(id),
            type: 'GET',
            dataType: 'json'
        })
            .done(function (response) {
                if (!response.success || !response.nota) {
                    $cargando.hide();
                    $error.show();
                    $('#ver-salida-error-msg').text(response.error || 'No se pudo cargar el detalle de la nota.');
                    return;
                }

                var nota = response.nota;
                var idNota = nota.id_nota_salida || id;
                var fecha = nota.fecha ? new Date(String(nota.fecha).replace(' ', 'T')) : null;
                var fechaValida = fecha && !Number.isNaN(fecha.getTime());
                var anulada = parseInt(nota.eliminado, 10) === 1;
                var identificacion = nota.rif
                    ? 'RIF: ' + nota.rif
                    : (nota.cedula ? 'Cédula: ' + nota.cedula : 'N/A');
                var detalles = $.isArray(nota.detalles) ? nota.detalles : [];
                var totalUnidades = 0;
                var $tbody = $('#ver-salida-productos-tbody').empty();

                $('#ver-salida-id').text('#' + (nota.id_nota_salida || id));
                $botonImprimir
                    .attr('href', '?url=notasalida&type=generar_pdf&id=' + encodeURIComponent(idNota))
                    .show();
                $('#ver-salida-fecha').text(fechaValida ? fecha.toLocaleDateString('es-VE') : '—');
                $('#ver-salida-hora').text(fechaValida
                    ? fecha.toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit' })
                    : '—');
                $('#ver-salida-encargado').text(nota.usuario_responsable || 'Sin asignar');
                $('#ver-salida-cliente').text(nota.cliente || 'N/A');
                $('#ver-salida-identificacion').text(identificacion);
                $('#ver-salida-estado')
                    .text(anulada ? 'Anulada' : 'Activa')
                    .attr('class', 'badge ' + (anulada ? 'bg-danger' : 'bg-success'));

                if (detalles.length) {
                    $.each(detalles, function (index, detalle) {
                        var cantidad = parseInt(detalle.cantidad, 10) || 0;
                        totalUnidades += cantidad;
                        var $fila = $('<tr>');
                        $fila.append($('<td>').addClass('ps-3').text(index + 1));
                        $fila.append($('<td>').text(detalle.nombre_producto || 'Producto'));
                        $fila.append($('<td>').text(detalle.nombre_categoria || 'Sin categoría'));
                        $fila.append($('<td>').addClass('text-center').text(cantidad));
                        $tbody.append($fila);
                    });
                } else {
                    $tbody.append(
                        $('<tr>').append(
                            $('<td>', { colspan: 4, class: 'text-center text-muted py-3' })
                                .text('No hay productos en esta nota.')
                        )
                    );
                }

                $('#ver-salida-total-unidades').text(totalUnidades);
                $cargando.hide();
                $contenido.show();
            })
            .fail(function (xhr, status, error) {
                $cargando.hide();
                $error.show();
                $('#ver-salida-error-msg').text(mensajeError(xhr, status, error));
            })
            .always(function () {
                if ($button.length) $button.prop('disabled', false).html(htmlOriginal);
            });
    };

    window.prepararAnulacionNotaSalida = function (id, cliente) {
        $('#modalAnularNotaSalidaId').val(id);
        $('#modalAnularNotaSalidaCliente').text(cliente || 'N/A');
        $('#modalAnularNotaSalidaMotivo').val('');
        mostrarModal('#modalAnularNotaSalida');
    };

    $(document).on('click', '[data-nota-action]', function () {
        var $button = $(this);
        var id = parseInt($button.attr('data-nota-id'), 10);
        if (!Number.isInteger(id) || id < 1) {
            mostrarError('No se pudo identificar la nota de salida.');
            return;
        }

        if ($button.attr('data-nota-action') === 'view') {
            window.verDetalleNotaSalida(id, this);
        } else if ($button.attr('data-nota-action') === 'cancel') {
            window.prepararAnulacionNotaSalida(id, $button.attr('data-cliente'));
        }
    });

    $(document).on('click', '[data-nota-salida-motivo]', function () {
        var $button = $(this);
        var elemento = $('#modalMotivoAnulacion').get(0);
        if (!elemento || !window.bootstrap || !window.bootstrap.Modal) return;

        $('#modal-motivo-doc').text($button.attr('data-documento') || '—');
        $('#modal-motivo-cliente').text($button.attr('data-cliente') || 'N/A');
        $('#modal-motivo-identificacion').text($button.attr('data-identificacion') || 'N/A');
        $('#modal-motivo-productos').text($button.attr('data-productos') || 'No se registraron productos.');
        $('#modal-motivo-usuario').text($button.attr('data-usuario') || 'N/A');
        $('#modal-motivo-cantidad').text($button.attr('data-cantidad') || '0');
        $('#modal-motivo-fecha').text($button.attr('data-fecha') || 'No registrada');
        $('#modal-motivo-texto').text($button.attr('data-motivo') || 'No se registró un motivo.');

        window.bootstrap.Modal.getOrCreateInstance(elemento).show();
    });
})(jQuery);
