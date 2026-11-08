$(document).ready(function() {
    'use strict';

    const notaData = JSON.parse($('#notaEdicionData').text());
    let detalles = (notaData.detalles || []).map(function(detalle) {
        return {
            id_producto: parseInt(detalle.id_producto, 10),
            nombre_producto: detalle.nombre_producto || 'Producto',
            cantidad: parseInt(detalle.cantidad, 10),
            costo_unitario: parseFloat(detalle.costo_unitario)
        };
    });

    function mostrarError(mensaje) {
        $('#editarNotaError').text(mensaje).removeClass('d-none');
    }

    function renderizarDetalles() {
        const $tbody = $('#detallesBody').empty();
        const $inputs = $('#detallesContainer').empty();
        let total = 0;

        if (detalles.length === 0) {
            $('<tr><td colspan="6" class="text-center text-muted">Agrega al menos un producto.</td></tr>').appendTo($tbody);
        }

        detalles.forEach(function(detalle, indice) {
            const subtotal = detalle.cantidad * detalle.costo_unitario;
            total += subtotal;

            $('<tr>')
                .append($('<td>').text(detalle.id_producto))
                .append($('<td>').text(detalle.nombre_producto))
                .append($('<td class="text-end">').text(detalle.cantidad))
                .append($('<td class="text-end">').text('$' + detalle.costo_unitario.toFixed(2)))
                .append($('<td class="text-end fw-bold">').text('$' + subtotal.toFixed(2)))
                .append(
                    $('<td class="text-center">').append(
                        $('<button type="button" class="btn btn-sm btn-outline-danger" title="Quitar producto">')
                            .attr('data-indice', indice)
                            .addClass('quitar-producto')
                            .html('<i class="fas fa-trash"></i>')
                    )
                )
                .appendTo($tbody);

            $('<input>', { type: 'hidden', name: 'detalle_producto[]', value: detalle.id_producto }).appendTo($inputs);
            $('<input>', { type: 'hidden', name: 'detalle_cantidad[]', value: detalle.cantidad }).appendTo($inputs);
            $('<input>', { type: 'hidden', name: 'detalle_costo[]', value: detalle.costo_unitario }).appendTo($inputs);
        });

        $('#totalNota').text('$' + total.toFixed(2));
    }

    $('#productoSelect').on('change', function() {
        const $option = $(this).find('option:selected');
        if ($option.val()) {
            $('#costoInput').val(parseFloat($option.data('costo')) || 0);
        }
    });

    $('#agregarProductoNota').on('click', function() {
        const $select = $('#productoSelect');
        const $option = $select.find('option:selected');
        const idProducto = parseInt($select.val(), 10);
        const cantidad = parseInt($('#cantidadInput').val(), 10);
        const costo = parseFloat($('#costoInput').val());

        if (!idProducto || !cantidad || cantidad < 1 || !Number.isFinite(costo) || costo < 0) {
            mostrarError('Selecciona un producto e ingresa una cantidad y costo válidos.');
            return;
        }

        detalles.push({
            id_producto: idProducto,
            nombre_producto: $option.text(),
            cantidad: cantidad,
            costo_unitario: costo
        });
        $('#editarNotaError').addClass('d-none').text('');
        renderizarDetalles();
        $select.val('');
        $('#cantidadInput').val(1);
        $('#costoInput').val('0.00');
    });

    $('#detallesBody').on('click', '.quitar-producto', function() {
        detalles.splice(parseInt($(this).attr('data-indice'), 10), 1);
        renderizarDetalles();
    });

    $('#editarNotaEntradaForm').on('submit', function(event) {
        if (detalles.length === 0) {
            event.preventDefault();
            mostrarError('La nota debe conservar al menos un producto.');
        }
    });

    renderizarDetalles();
});
