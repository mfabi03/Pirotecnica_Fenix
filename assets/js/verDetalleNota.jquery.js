/**
 * ============================================================
 * VER DETALLE DE NOTA (Entrada / Salida) - Versión jQuery
 * ------------------------------------------------------------
 * Requiere:
 *   - jQuery 3.7.1
 *   - Bootstrap 5
 *   - Validador global (opcional, para formatear fecha)
 * ------------------------------------------------------------
 * Expone:
 *   window.verDetalleNota(id, tipo)
 * ============================================================
 */

(function ($) {
    'use strict';

    window.verDetalleNota = function (id, tipo) {
        var $cargando  = $('#ver-nota-cargando');
        var $error     = $('#ver-nota-error');
        var $contenido = $('#ver-nota-contenido');

        // Reset visual del modal
        $cargando.show();
        $error.hide();
        $contenido.hide();
        $('#ver-nota-id').text('#' + id);

        var modalEl = document.getElementById('modalVerNota');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        // Detectar si es entrada o salida
        var esEntrada = (
            tipo === 'Entrada' ||
            tipo === 'Anulación Entrada' ||
            (typeof tipo === 'string' && tipo.indexOf('Entrada') !== -1)
        );

        // Ajustar columnas y etiquetas según tipo
        $('.col-dinero').toggleClass('d-none', !esEntrada);
        $('#ver-nota-label-cliente').text(esEntrada ? 'PROVEEDOR' : 'CLIENTE');
        $('#ver-nota-th-descripcion').text(esEntrada ? 'Descripción' : 'Categoría');

        // Actualizar botón de imprimir PDF
        var $btnImprimir = $('#btnImprimirNotaIndividual');
        if ($btnImprimir.length) {
            var urlPdf = esEntrada
                ? '?url=exportar_pdf&tipo=entrada&id=' + id
                : '?url=exportar_pdf&tipo=salida&id=' + id;
            $btnImprimir.attr('href', urlPdf);
        }

        // Endpoint según tipo
        var url = esEntrada
            ? '?url=notaentrada&type=show_json&id=' + id
            : '?url=notasalida&type=detalle_json&id=' + id;

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json'
        })
        .done(function (data) {
            $cargando.hide();

            if (!data || !data.success || !data.nota) {
                $error.show();
                $('#ver-nota-error-msg').text(
                    (data && data.error) || 'Error al cargar la nota'
                );
                return;
            }

            var nota = data.nota;
            $contenido.show();

            // Fecha formateada
            var fechaRaw = nota.fecha || nota.fecha_ingreso || '';
            var fechaFmt = (typeof Validador !== 'undefined' && Validador.formatearFecha)
                ? Validador.formatearFecha(fechaRaw)
                : fechaRaw;
            $('#ver-nota-fecha').text(fechaFmt || '—');

            // Encargado
            $('#ver-nota-encargado').text(
                nota.usuario_responsable || nota.encargado_nombre || 'N/A'
            );

            // Cliente / Proveedor
            if (esEntrada) {
                $('#ver-nota-cliente').text(nota.razon_social || 'Sin proveedor');
                $('#ver-nota-documento').text(nota.rif || 'N/A');
            } else {
                $('#ver-nota-cliente').text(nota.cliente || 'N/A');
                $('#ver-nota-documento').text(nota.cedula || nota.rif || 'N/A');
            }

            // Productos
            var $tbody = $('#ver-nota-productos-tbody').empty();
            var totalUnidades = 0;
            var totalDolares  = 0;

            if (nota.detalles && nota.detalles.length > 0) {
                $.each(nota.detalles, function (_, d) {
                    var cantidad = parseInt(d.cantidad, 10) || 0;
                    totalUnidades += cantidad;

                    var colDesc = esEntrada
                        ? (nota.descripcion || 'Sin descripción')
                        : (d.nombre_categoria || d.descripcion || 'N/A');

                    var $tr = $('<tr>');

                    $tr.append(
                        $('<td class="ps-4">')
                            .css('color', '#1a1a2e')
                            .text(d.nombre_producto || 'N/A')
                    );
                    $tr.append(
                        $('<td>')
                            .css('color', '#6c757d')
                            .text(colDesc)
                    );
                    $tr.append(
                        $('<td class="text-center fw-bold">')
                            .css('color', '#1a1a2e')
                            .text(cantidad)
                    );

                    if (esEntrada) {
                        var costo    = parseFloat(d.costo_unitario) || 0;
                        var subtotal = cantidad * costo;
                        totalDolares += subtotal;

                        $tr.append(
                            $('<td class="text-end">')
                                .css('color', '#1a1a2e')
                                .text('$' + costo.toFixed(2))
                        );
                        $tr.append(
                            $('<td class="text-end pe-4 fw-bold">')
                                .css('color', '#1a1a2e')
                                .text('$' + subtotal.toFixed(2))
                        );
                    }

                    $tbody.append($tr);
                });
            } else {
                var colspan = esEntrada ? 5 : 3;
                $tbody.append(
                    $('<tr>').append(
                        $('<td class="text-center py-3">')
                            .attr('colspan', colspan)
                            .css('color', '#6c757d')
                            .text('No hay productos')
                    )
                );
            }

            // Totales
            $('#ver-nota-total-unidades').text(totalUnidades);
            if (esEntrada) {
                $('#ver-nota-total-dolares').text('$' + totalDolares.toFixed(2));
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            $cargando.hide();
            $error.show();
            $('#ver-nota-error-msg').text('Error de conexión: ' + textStatus);
            console.error('verDetalleNota error:', textStatus, errorThrown);
        });
    };

})(jQuery);