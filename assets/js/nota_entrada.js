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

    function configurarModalAnidado(selector) {
        $(selector)
            .on('show.bs.modal', function() {
                var zIndex = 1055 + (10 * $('.modal.show').length);
                var $modal = $(this);
                $modal.css('z-index', zIndex);

                window.setTimeout(function() {
                    $('.modal-backdrop:not(.modal-stack)').last()
                        .css('z-index', zIndex - 5)
                        .addClass('modal-stack');
                }, 0);
            })
            .on('hidden.bs.modal', function() {
                $(this).css('z-index', '');
                if ($('.modal.show').length) $('body').addClass('modal-open');
            });
    }

    configurarModalAnidado('#modalRapidoProveedor, #modalRapidoCategoria');

    function actualizarOpcionSelect(select, id, texto, atributos) {
        if (!select) return;
        var opcion = Array.prototype.find.call(select.options, function(item) {
            return item.value === String(id);
        });
        if (!opcion) {
            opcion = new Option(texto, id);
            select.add(opcion);
        }
        opcion.textContent = texto;
        Object.keys(atributos || {}).forEach(function(nombre) {
            opcion.setAttribute(nombre, atributos[nombre]);
        });
        select.dispatchEvent(new Event('refresh-search'));
        select.value = String(id);
        select.dispatchEvent(new Event('change', { bubbles: true }));

        var inputBusqueda = $(select).closest('.ss-wrapper').find('.ss-input').get(0);
        if (inputBusqueda) inputBusqueda.value = texto;
    }

    function prepararModalRapido(modalId, formId, errorId) {
        var form = document.getElementById(formId);
        if (form) form.reset();
        $(errorId).hide();
        $(errorId + '-msg').text('');
        if (typeof window.Validador !== 'undefined' && form) {
            window.Validador.init(form);
            window.Validador.resetEstadoVisual(form);
        }
        modal(modalId, 'show');
    }

    function guardarRegistroRapido(configuracion) {
        var form = document.getElementById(configuracion.formId);
        var boton = $(configuracion.botonId);
        if (!form || !boton.length) return;

        var htmlOriginal = boton.html();
        if (!form.reportValidity()) return;

        var validador = window.Validador;
        if (!validador) {
            enviarRegistro();
            return;
        }

        validador.init(document.getElementById(configuracion.modalId.slice(1)));
        if (configuracion.validarUnicos) {
            $(form).find('[data-unico-endpoint]').data('unicoEstado', '');
        }
        if (!configuracion.validarUnicos) {
            if (!validador.validarFormulario(configuracion.formId)) return;
            enviarRegistro();
            return;
        }

        boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Verificando...');
        validador.validarFormularioCompleto(configuracion.formId)
            .then(function(resultado) {
                if (!resultado.valido) {
                    if (resultado.tipo === 'disponibilidad') {
                        $(configuracion.errorId + '-msg').text('No se pudo verificar la disponibilidad de los datos. Inténtalo nuevamente.');
                        $(configuracion.errorId).show();
                    }
                    return;
                }
                enviarRegistro();
            })
            .catch(function() {
                $(configuracion.errorId + '-msg').text('No se pudo completar la validación. Inténtalo nuevamente.');
                $(configuracion.errorId).show();
            })
            .finally(function() {
                boton.prop('disabled', false).html(htmlOriginal);
            });

        function enviarRegistro() {
            boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
            var datos = new FormData(form);
            if (configuracion.type) datos.append('type', configuracion.type);

            fetch(configuracion.url, { method: 'POST', body: datos })
                .then(function(respuesta) {
                    return respuesta.json().then(function(data) {
                        if (!respuesta.ok || !data.success) {
                            throw new Error(data.error || 'No se pudo completar el registro.');
                        }
                        return data;
                    });
                })
                .then(function(data) {
                    configuracion.alGuardar(data);
                    modal(configuracion.modalId, 'hide');
                    if (typeof window.mostrarAlerta === 'function') {
                        window.mostrarAlerta(configuracion.mensaje(data), 'success');
                    }
                })
                .catch(function(error) {
                    $(configuracion.errorId + '-msg').text(error.message);
                    $(configuracion.errorId).show();
                })
                .finally(function() {
                    boton.prop('disabled', false).html(htmlOriginal);
                });
        }
    }

    $('#btnAbrirModalProveedor').on('click', function() {
        prepararModalRapido('#modalRapidoProveedor', 'formRapidoProveedor', '#modal-rapido-proveedor-error');
    });

    $('#btnAbrirModalProveedorDesdeProducto').on('click', function() {
        prepararModalRapido('#modalRapidoProveedor', 'formRapidoProveedor', '#modal-rapido-proveedor-error');
    });

    $('#btnAbrirModalCategoriaNotaEntrada').on('click', function() {
        prepararModalRapido('#modalRapidoCategoria', 'formRapidoCategoria', '#modal-rapido-categoria-error');
    });

    $('#btnAbrirModalProducto').on('click', function() {
        prepararModalRapido('#modalRapidoProducto', 'formRapidoProducto', '#modal-rapido-producto-error');
        var proveedorSeleccionado = $('#id_proveedor').val();
        if (proveedorSeleccionado) {
            var selectorProveedor = document.getElementById('rapido-prod-proveedor');
            selectorProveedor.value = proveedorSeleccionado;
            selectorProveedor.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    $('#btnGuardarProveedorRapido').on('click', function() {
        guardarRegistroRapido({
            formId: 'formRapidoProveedor',
            botonId: '#btnGuardarProveedorRapido',
            modalId: '#modalRapidoProveedor',
            errorId: '#modal-rapido-proveedor-error',
            url: '?url=notaentrada',
            type: 'store_rapido_proveedor_ajax',
            validarUnicos: true,
            alGuardar: function(data) {
                var texto = data.razon_social + ' - ' + data.rif;
                actualizarOpcionSelect(document.getElementById('id_proveedor'), data.id_proveedor, texto);
                actualizarOpcionSelect(document.getElementById('rapido-prod-proveedor'), data.id_proveedor, data.razon_social);
            },
            mensaje: function(data) {
                return 'Proveedor "' + data.razon_social + '" registrado exitosamente.';
            }
        });
    });

    $('#btnGuardarProductoRapido').on('click', function() {
        guardarRegistroRapido({
            formId: 'formRapidoProducto',
            botonId: '#btnGuardarProductoRapido',
            modalId: '#modalRapidoProducto',
            errorId: '#modal-rapido-producto-error',
            url: '?url=notaentrada',
            type: 'store_rapido_producto_ajax',
            validarUnicos: true,
            alGuardar: function(data) {
                var costo = Number(data.costo_unitario) || 0;
                actualizarOpcionSelect(
                    document.getElementById('productoSelect'),
                    data.id_producto,
                    data.descripcion + ' (Stock: 0)',
                    {
                        'data-descripcion': data.descripcion,
                        'data-costo': costo.toFixed(2),
                        'data-stock': '0'
                    }
                );
                document.getElementById('costoInput').value = costo.toFixed(2);
            },
            mensaje: function(data) {
                return 'Producto "' + data.descripcion + '" registrado exitosamente.';
            }
        });
    });

    $('#btnGuardarCategoriaRapido').on('click', function() {
        guardarRegistroRapido({
            formId: 'formRapidoCategoria',
            botonId: '#btnGuardarCategoriaRapido',
            modalId: '#modalRapidoCategoria',
            errorId: '#modal-rapido-categoria-error',
            url: '?url=categorias&action=store_rapido_ajax',
            validarUnicos: true,
            alGuardar: function(data) {
                actualizarOpcionSelect(
                    document.getElementById('rapido-prod-categoria'),
                    data.id_categoria,
                    data.nombre_categoria
                );
            },
            mensaje: function(data) {
                return 'Categoría "' + data.nombre_categoria + '" registrada exitosamente.';
            }
        });
    });

    function inicializarDetalleNotaEntrada() {
        var $form = $('#notaEntradaForm');
        var productoSelect = $('#productoSelect').get(0);
        var cantidadInput = $('#cantidadInput').get(0);
        var costoInput = $('#costoInput').get(0);
        var $tbody = $('#detallesBody');
        var $detallesContainer = $('#detallesContainer');

        if (!$form.length || !productoSelect || !cantidadInput || !costoInput || !$tbody.length || !$detallesContainer.length) {
            return;
        }

        var detalles = [];

        function crearCampoOculto(nombre, valor) {
            return $('<input>', {
                type: 'hidden',
                name: nombre,
                value: valor
            });
        }

        function actualizarDetalles() {
            var total = 0;
            $tbody.empty();
            $detallesContainer.empty();

            if (detalles.length === 0) {
                $tbody.append(
                    '<tr id="filaVaciaDetalles">' +
                        '<td colspan="6" class="text-center py-3" style="color: rgba(255,255,255,0.2);">' +
                            '<i class="fas fa-box-open me-2"></i> No hay productos agregados' +
                        '</td>' +
                    '</tr>'
                );
                $('#totalNota').text('$0.00');
                return;
            }

            $.each(detalles, function(indice, detalle) {
                var subtotal = detalle.cantidad * detalle.costo;
                total += subtotal;

                var $fila = $('<tr>').attr('data-indice', indice);
                $fila.append($('<td>').addClass('ps-4').text(detalle.idProducto));
                $fila.append($('<td>').text(detalle.descripcion));
                $fila.append($('<td>').addClass('text-center').text(detalle.cantidad));
                $fila.append($('<td>').addClass('text-center').text('$' + detalle.costo.toFixed(2)));
                $fila.append($('<td>').addClass('text-center').text('$' + subtotal.toFixed(2)));

                var $eliminar = $('<button>', {
                    type: 'button',
                    class: 'btn btn-sm btn-outline-danger eliminar-producto',
                    title: 'Quitar producto',
                    'aria-label': 'Quitar producto'
                }).append($('<i>', { class: 'fas fa-trash' }));

                $fila.append($('<td>').addClass('pe-4 text-center').append($eliminar));
                $tbody.append($fila);

                $detallesContainer
                    .append(crearCampoOculto('detalle_producto[]', detalle.idProducto))
                    .append(crearCampoOculto('detalle_cantidad[]', detalle.cantidad))
                    .append(crearCampoOculto('detalle_costo[]', detalle.costo.toFixed(2)));
            });

            $('#totalNota').text('$' + total.toFixed(2));
        }

        $(productoSelect).on('change', function() {
            if (!this.value) return;

            var opcion = this.options[this.selectedIndex];
            var costo = Number(opcion.getAttribute('data-costo'));
            if (Number.isFinite(costo) && costo >= 0) {
                costoInput.value = costo.toFixed(2);
            }
        });

        $('#btnAgregarProducto').on('click', function() {
            var idProducto = productoSelect.value;
            var opcion = productoSelect.options[productoSelect.selectedIndex];
            var cantidad = Number(cantidadInput.value);
            var costo = Number(costoInput.value);

            if (!idProducto || !opcion) {
                mostrarError('Seleccione un producto.');
                return;
            }

            if (!Number.isInteger(cantidad) || cantidad < 1) {
                mostrarError('Ingrese una cantidad válida (mayor a 0).');
                return;
            }

            if (!Number.isFinite(costo) || costo < 0) {
                mostrarError('Ingrese un costo unitario válido.');
                return;
            }

            detalles.push({
                idProducto: parseInt(idProducto, 10),
                descripcion: opcion.getAttribute('data-descripcion') || opcion.textContent.trim(),
                cantidad: cantidad,
                costo: costo
            });
            actualizarDetalles();

            productoSelect.value = '';
            productoSelect.dispatchEvent(new Event('change', { bubbles: true }));
            var inputBusqueda = $(productoSelect).closest('.ss-wrapper').find('.ss-input').get(0);
            if (inputBusqueda) inputBusqueda.value = '';
            cantidadInput.value = '1';
            costoInput.value = '0.00';
            if (inputBusqueda) inputBusqueda.focus();
        });

        $tbody.on('click', '.eliminar-producto', function() {
            var indice = parseInt($(this).closest('tr').attr('data-indice'), 10);
            if (!Number.isInteger(indice) || indice < 0 || indice >= detalles.length) return;
            detalles.splice(indice, 1);
            actualizarDetalles();
        });

        $form.on('submit', function(event) {
            if (detalles.length === 0) {
                event.preventDefault();
                mostrarError('Agregue al menos un producto a la nota de entrada.');
            }
        });

        actualizarDetalles();
    }

    inicializarDetalleNotaEntrada();

    window.verDetalleNotaEntrada = function(id, button) {
        var $button = $(button);
        var originalHtml = $button.html();
        var $loading = $('#ver-nota-cargando');
        var $error = $('#ver-nota-error');
        var $content = $('#ver-nota-contenido');
        var $printButton = $('#btnImprimirNotaEntrada');

        $('#ver-nota-id').text('#' + id);
        $('#ver-nota-error-msg').text('');
        $printButton.hide().removeAttr('href');
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
                var idNota = nota.id_nota_entrada || id;
                var fecha = nota.fecha_ingreso ? new Date(nota.fecha_ingreso.replace(' ', 'T')) : null;
                var fechaValida = fecha && !Number.isNaN(fecha.getTime());
                var anulada = parseInt(nota.eliminado, 10) === 1;
                $loading.hide();
                $('#ver-nota-id').text('#' + nota.id_nota_entrada);
                $printButton
                    .attr('href', '?url=notaentrada&type=generar_pdf&id=' + encodeURIComponent(idNota))
                    .show();
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

    $(document).on('click', '[data-nota-action]', function() {
        var $button = $(this);
        var id = parseInt($button.attr('data-nota-id'), 10);
        var accion = $button.attr('data-nota-action');
        if (accion === 'view') window.verDetalleNotaEntrada(id, this);
        if (accion === 'cancel') window.prepararAnulacionNotaEntrada(id, $button.attr('data-proveedor'));
    });

    $(document).on('click', '[data-nota-motivo]', function() {
        var $button = $(this);
        $('#motivo-entrada-documento').text('#' + ($button.attr('data-nota-id') || '—'));
        $('#motivo-entrada-proveedor').text($button.attr('data-proveedor') || 'N/A');
        $('#motivo-entrada-rif').text('RIF: ' + ($button.attr('data-rif') || 'N/A'));
        $('#motivo-entrada-productos').text($button.attr('data-productos') || 'No se registraron productos.');
        $('#motivo-entrada-texto').text($button.attr('data-motivo') || 'No se registró un motivo.');
        modal('#modalMotivoAnulacionNotaEntrada', 'show');
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
