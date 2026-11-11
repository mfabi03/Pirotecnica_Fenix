(function ($) {
    'use strict';

    function cargarVista(url, actualizarHistorial) {
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'html'
        }).done(function (html) {
            var documento = new DOMParser().parseFromString(html, 'text/html');
            var nuevaVista = documento.querySelector('.col-md-8.col-lg-12');
            var vistaActual = document.querySelector('.col-md-8.col-lg-12');

            if (!nuevaVista || !vistaActual) {
                window.location.assign(url);
                return;
            }

            vistaActual.replaceWith(nuevaVista);
            if (actualizarHistorial) {
                window.history.pushState({ listadoAjax: true }, '', url);
            }
        }).fail(function () {
            window.location.assign(url);
        });
    }

    $(document).on('click', 'a.js-filtro-listado-ajax', function (event) {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        event.preventDefault();
        cargarVista(this.href, true);
    });

    $(document).on('click', '[data-ver-motivo]', function () {
        var elemento = document.getElementById('modalMotivoAnulacion');
        if (!elemento || !window.bootstrap || !window.bootstrap.Modal) return;
        $(elemento).find('[data-motivo-texto]').text($(this).attr('data-ver-motivo') || 'No se registró un motivo.');
        window.bootstrap.Modal.getOrCreateInstance(elemento).show();
    });

    window.addEventListener('popstate', function () {
        cargarVista(window.location.href, false);
    });
})(jQuery);
