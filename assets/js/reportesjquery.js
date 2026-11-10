/**
 * ============================================================
 * MÓDULO REPORTES DE MOVIMIENTO - Versión jQuery
 * ------------------------------------------------------------
 * Requiere:
 *   - jQuery 3.7.1 (cargado globalmente en footer.php)
 *   - Bootstrap 5 (cargado globalmente en footer.php)
 *   - verDetalleNota.jquery.js (para el modal de detalle)
 * ------------------------------------------------------------
 * Cubre:
 *   1. Filtro AJAX del formulario #formFiltros
 *   2. Ver motivo de anulación (delegado)
 *   3. Ver detalle de nota entrada/salida (delegado)
 * ============================================================
 */

(function ($) {
    'use strict';

    $(function () {

        // ---------------------------------------------------------
        // 1. FILTRO AJAX DE REPORTES
        // ---------------------------------------------------------
        var $formReportes = $('#formFiltros');
        if ($formReportes.length) {

            var enviarFiltroReportes = function () {
                var formData = new FormData($formReportes[0]);
                formData.set('ajax', '1');

                $.ajax({
                    url: '?url=reportes',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                })
                .done(function (data) {
                    if (!data || !data.success || !data.html) {
                        if (window.mostrarAlerta) {
                            window.mostrarAlerta('No se pudo procesar el filtro de reportes', 'warning');
                        }
                        return;
                    }

                    var $wrap = $('<div>').html(data.html);
                    var $nuevaVista = $wrap.find('.col-md-8.col-lg-12').first();
                    if (!$nuevaVista.length) return;

                    var $contenedorActual = $('.col-md-8.col-lg-12').first();
                    if ($contenedorActual.length) {
                        $contenedorActual.replaceWith($nuevaVista);
                    }

                    // Re-vincular el form tras reemplazo (por si cambió)
                    $formReportes = $('#formFiltros');
                    if ($formReportes.length) {
                        bindFormReportes();
                    }
                })
                .fail(function () {
                    if (window.mostrarAlerta) {
                        window.mostrarAlerta('Error de conexión al filtrar reportes', 'error');
                    }
                    // Fallback: submit tradicional
                    $formReportes.off('submit.filtroReportes');
                    $formReportes[0].submit();
                });
            };

            var bindFormReportes = function () {
                $formReportes
                    .off('submit.filtroReportes')
                    .on('submit.filtroReportes', function (e) {
                        e.preventDefault();
                        enviarFiltroReportes();
                    });

                $formReportes
                    .find('select')
                    .off('change.filtroReportes')
                    .on('change.filtroReportes', function () {
                        enviarFiltroReportes();
                    });
            };

            bindFormReportes();
        }

        // ---------------------------------------------------------
        // 2. VER MOTIVO DE ANULACIÓN (delegado, sobrevive al AJAX)
        // ---------------------------------------------------------
        $(document).off('click.reportesMotivo', '.btn-ver-motivo')
                   .on('click.reportesMotivo', '.btn-ver-motivo', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var doc = $btn.data('documento') || '—';
            var prod = $btn.data('producto') || '—';
            var motivo = $btn.data('motivo') || 'No se registró un motivo.';

            $('#modal-motivo-doc').text(doc);
            $('#modal-motivo-prod').text(prod);
            $('#modal-motivo-texto').text(motivo);

            var modalEl = document.getElementById('modalMotivoAnulacion');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });

        // ---------------------------------------------------------
        // 3. VER DETALLE DE NOTA (delegado, sobrevive al AJAX)
        // ---------------------------------------------------------
        $(document).off('click.reportesDetalle', '.btn-ver-detalle')
                   .on('click.reportesDetalle', '.btn-ver-detalle', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var id   = $btn.data('id');
            var tipo = $btn.data('tipo') || 'Entrada';

            if (typeof window.verDetalleNota === 'function' && id) {
                window.verDetalleNota(id, tipo);
            } else {
                console.warn('verDetalleNota no está definido o id vacío', { id: id, tipo: tipo });
            }
        });

    });

})(jQuery);