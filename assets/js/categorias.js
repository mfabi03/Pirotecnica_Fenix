document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const categoriasData = JSON.parse(document.getElementById('categoriasData').textContent);
    const categoriasPorId = {};
    categoriasData.forEach(function(c) {
        categoriasPorId[parseInt(c.id_categoria)] = c;
    });

    window.verDetalleCategoria = function(id) {
        const c = categoriasPorId[id];
        if (!c) return;

        document.getElementById('ver-categoria-nombre').textContent = c.nombre_categoria || 'Sin nombre';
        
        var badgeEstatus = document.getElementById('ver-categoria-estatus');
        if (badgeEstatus) {
            if (c.eliminado == 1) {
                badgeEstatus.textContent = 'Inactivo';
                badgeEstatus.style.background = 'rgba(220,53,69,0.15)';
                badgeEstatus.style.color = '#dc3545';
            } else {
                badgeEstatus.textContent = 'Activo';
                badgeEstatus.style.background = 'rgba(40,167,69,0.15)';
                badgeEstatus.style.color = '#28a745';
            }
        }

        document.getElementById('ver-categoria-nombre-detalle').textContent = c.nombre_categoria || 'N/A';
        document.getElementById('ver-categoria-descripcion').textContent = c.descripcion || 'Sin descripción';

        new bootstrap.Modal(document.getElementById('modalVerCategoria')).show();
    };

    window.editarCategoria = function(id) {
        const c = categoriasPorId[id];
        if (!c) return;

        document.getElementById('editar-categoria-nombre-header').textContent = c.nombre_categoria || 'Categoría';
        document.getElementById('editar-categoria-id').value = c.id_categoria;
        document.getElementById('editar-categoria-nombre').value = c.nombre_categoria || '';
        document.getElementById('editar-categoria-descripcion').value = c.descripcion || '';
        $('#editar-categoria-nombre').data('excluir-id', id);

        new bootstrap.Modal(document.getElementById('modalEditarCategoria')).show();
    };

    window.confirmarEliminarCategoria = function(id, nombre) {
        document.getElementById('eliminar-categoria-id').value = id;
        document.getElementById('eliminar-categoria-nombre').textContent = nombre || 'Categoría #' + id;
        new bootstrap.Modal(document.getElementById('modalEliminarCategoria')).show();
    };

    window.confirmarReactivarCategoria = function(id, nombre) {
        document.getElementById('reactivar-categoria-id').value = id;
        document.getElementById('reactivar-categoria-nombre').textContent = nombre || 'Categoría #' + id;
        new bootstrap.Modal(document.getElementById('modalReactivarCategoria')).show();
    };

    function validarYEnviarCategoria(form) {
        const $form = $(form);
        const $camposUnicos = $form.find('[data-unico-endpoint]');
        $camposUnicos.data('unicoEstado', '');

        if (typeof Validador !== 'undefined' && !Validador.validarFormulario(form.id)) {
            if (typeof window.mostrarAlerta === 'function') {
                window.mostrarAlerta('Revisa los campos marcados en rojo.', 'warning');
            }
            return;
        }

        const valores = $camposUnicos.map(function() { return $(this).val(); }).get();
        const verificaciones = $camposUnicos.map(function() {
            return Validador.validarUnico(this).then(function(disponible) {
                return disponible;
            }, function() {
                return false;
            });
        }).get();
        const $boton = $form.find('[type="submit"]');
        const htmlOriginal = $boton.html();

        $boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Verificando...');
        Promise.all(verificaciones).then(function(resultados) {
            let sinCambios = true;
            $camposUnicos.each(function(index) {
                if ($(this).val() !== valores[index]) sinCambios = false;
            });

            if (sinCambios && resultados.every(Boolean)) {
                form.submit();
                return;
            }

            $boton.prop('disabled', false).html(htmlOriginal);
            if (typeof window.mostrarAlerta === 'function') {
                const duplicado = $camposUnicos.filter(function() {
                    return $(this).data('unicoEstado') === 'duplicado';
                }).length > 0;
                window.mostrarAlerta(
                    duplicado ? 'Ya existe una categoría activa con ese nombre.' : 'No se pudo verificar la disponibilidad de la categoría.',
                    duplicado ? 'warning' : 'danger'
                );
            }
        }).catch(function() {
            $boton.prop('disabled', false).html(htmlOriginal);
            if (typeof window.mostrarAlerta === 'function') {
                window.mostrarAlerta('No se pudo verificar la disponibilidad de la categoría.', 'danger');
            }
        });
    }

    ['formRegistrarCategoria', 'formEditarCategoria'].forEach(function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                validarYEnviarCategoria(form);
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
        var ms = parseInt(el.dataset.autoDismiss, 10) || 5000;
        setTimeout(function () {
            if (!el.parentNode) return;
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function () { el.remove(); }, 400);
        }, ms);
    });
});
$(document).ready(function($) {
    function inicializarTablaCategorias() {
        const $tabla = $('#tablaCategorias');
        if (!$tabla.length || $.fn.DataTable.isDataTable('#tablaCategorias')) {
            return;
        }

        $tabla.DataTable({
            language: {
                decimal: '',
                emptyTable: 'No hay categorías disponibles',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ categorías',
                infoEmpty: 'Mostrando 0 a 0 de 0 categorías',
                infoFiltered: '(filtrado de _MAX_ categorías totales)',
                lengthMenu: 'Mostrar _MENU_ registros',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: 'Buscar:',
                zeroRecords: 'No se encontraron categorías coincidentes',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Todos']],
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, searchable: false, targets: [2, 3] }
            ],
            responsive: true,
            dom: "<'row mb-3 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-3 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
    }

    function cargarScriptSecuencial(urls, callback) {
        if (!urls.length) {
            callback();
            return;
        }
        const script = document.createElement('script');
        script.src = urls[0];
        script.onload = function() {
            cargarScriptSecuencial(urls.slice(1), callback);
        };
        document.body.appendChild(script);
    }

    if (typeof $.fn.DataTable === 'function') {
        inicializarTablaCategorias();
    } else {
        cargarScriptSecuencial([
            'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
            'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js',
            'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js',
            'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js'
        ], inicializarTablaCategorias);
    }
});