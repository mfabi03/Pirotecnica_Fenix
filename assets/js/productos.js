document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    var productosDataRaw = document.getElementById('productosData');
    if (!productosDataRaw) return;
    
    var productosData = JSON.parse(productosDataRaw.textContent);
    var productosPorId = {};
    
    productosData.forEach(function(p) {
        productosPorId[parseInt(p.id_producto)] = p;
    });

    function validarYEnviarFormulario(form) {
        var $form = $(form);
        var $camposUnicos = $form.find('[data-unico-endpoint]');
        $camposUnicos.data('unicoEstado', '');

        if (typeof Validador !== 'undefined' && !Validador.validarFormulario(form.id)) {
            if (typeof window.mostrarAlerta === 'function') {
                window.mostrarAlerta('Revisa los campos marcados en rojo.', 'warning');
            }
            return;
        }

        if (!$camposUnicos.length) {
            form.submit();
            return;
        }

        var $boton = $form.find('[type="submit"]');
        var htmlOriginal = $boton.html();
        var valores = $camposUnicos.map(function() { return $(this).val(); }).get();
        var verificaciones = $camposUnicos.map(function() {
            return Validador.validarUnico(this).then(function(disponible) {
                return disponible;
            }, function() {
                return false;
            });
        }).get();

        $boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Verificando...');
        Promise.all(verificaciones).then(function(resultados) {
            var sinCambios = true;
            $camposUnicos.each(function(index) {
                if ($(this).val() !== valores[index]) sinCambios = false;
            });

            if (sinCambios && resultados.every(Boolean)) {
                form.submit();
                return;
            }

            $boton.prop('disabled', false).html(htmlOriginal);
            if (typeof window.mostrarAlerta === 'function') {
                var duplicado = $camposUnicos.filter(function() {
                    return $(this).data('unicoEstado') === 'duplicado';
                }).length > 0;
                window.mostrarAlerta(
                    duplicado ? 'Ya existe un producto activo con ese nombre.' : 'No se pudo verificar la disponibilidad del producto.',
                    duplicado ? 'warning' : 'danger'
                );
            }
        }).catch(function() {
            $boton.prop('disabled', false).html(htmlOriginal);
            if (typeof window.mostrarAlerta === 'function') {
                window.mostrarAlerta('No se pudo verificar la disponibilidad del producto.', 'danger');
            }
        });
    }

    ['formRegistrarProducto', 'formEditarProducto'].forEach(function(formId) {
        var form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                validarYEnviarFormulario(form);
            });
        }
    });

    // EDITAR
    window.editarProducto = function(id) {
        var p = productosPorId[id];
        if (!p) return;

        function setVal(elementId, val) {
            var el = document.getElementById(elementId);
            if (el) el.value = val;
        }

        setVal('editar-producto-id', p.id_producto);
        setVal('editar-producto-descripcion', p.descripcion || '');
        setVal('editar-producto-stock-minimo', p.stock_minimo || 10);
        setVal('editar-producto-especificaciones', p.especificaciones || '');
        var descripcionInput = document.getElementById('editar-producto-descripcion');
        if (descripcionInput) $(descripcionInput).data('excluir-id', id);
        
        setVal('editar-producto-categoria-texto', p.nombre_categoria || 'Sin categoría');
        setVal('editar-producto-categoria-id', p.id_categoria || '');
        
        setVal('editar-producto-proveedor-texto', p.nombre_proveedor || 'Sin proveedor');
        setVal('editar-producto-proveedor-id', p.id_proveedor || '');

        var header = document.getElementById('editar-producto-nombre-header');
        if (header) header.textContent = p.descripcion || 'Producto';

        var modal = document.getElementById('modalEditarProducto');
        if (modal) {
            new bootstrap.Modal(modal).show();
        }
    };

    // VER DETALLE
    window.verDetalleProducto = function(id) {
        var p = productosPorId[id];
        if (!p) {
            alert('No se encontró la información del producto.');
            return;
        }

        document.getElementById('ver-producto-nombre').textContent = p.descripcion || 'Sin nombre';
        
        // ESTATUS
        var badgeEstatus = document.getElementById('ver-producto-estatus');
        if (badgeEstatus) {
            if (p.eliminado == 1) {
                badgeEstatus.textContent = 'Inactivo';
                badgeEstatus.style.color = '#dc3545';
                badgeEstatus.style.background = 'rgba(220,53,69,0.15)';
            } else {
                badgeEstatus.textContent = 'Activo';
                badgeEstatus.style.color = '#28a745';
                badgeEstatus.style.background = 'rgba(40,167,69,0.15)';
            }
        }

        document.getElementById('ver-producto-codigo').textContent = '#' + p.id_producto;
        document.getElementById('ver-producto-nombre-detalle').textContent = p.descripcion || 'N/A';
        document.getElementById('ver-producto-proveedor').textContent = p.nombre_proveedor || 'Sin proveedor asignado';
        document.getElementById('ver-producto-stock').textContent = p.stock || 0;
        document.getElementById('ver-producto-stock-minimo').textContent = p.stock_minimo || 0;
        document.getElementById('ver-producto-costo').textContent = '$' + parseFloat(p.costo_unitario || 0).toFixed(2);
        document.getElementById('ver-producto-especificaciones').textContent = p.especificaciones || 'Sin especificaciones';

        var imagenProducto = document.getElementById('ver-producto-imagen');
        var contenedorImagen = document.getElementById('ver-producto-imagen-container');
        if (imagenProducto && contenedorImagen) {
            imagenProducto.onerror = function() {
                this.removeAttribute('src');
                contenedorImagen.classList.add('d-none');
            };
            if (p.imagen) {
                imagenProducto.src = p.imagen;
                imagenProducto.alt = 'Imagen de ' + (p.descripcion || 'producto');
                contenedorImagen.classList.remove('d-none');
            } else {
                imagenProducto.removeAttribute('src');
                imagenProducto.alt = '';
                contenedorImagen.classList.add('d-none');
            }
        }

        var modal = document.getElementById('modalVerProducto');
        if (modal) {
            new bootstrap.Modal(modal).show();
        }
    };

    // ELIMINAR
    window.confirmarEliminarProducto = function(id, nombre) {
        document.getElementById('eliminar-producto-id').value = id;
        document.getElementById('eliminar-producto-nombre').textContent = nombre || 'Producto #' + id;
        
        var modalEliminar = document.getElementById('modalEliminarProducto');
        if (modalEliminar) {
            new bootstrap.Modal(modalEliminar).show();
        }
    };

    // REACTIVAR
    window.confirmarReactivarProducto = function(id, nombre) {
        document.getElementById('reactivar-producto-id').value = id;
        document.getElementById('reactivar-producto-nombre').textContent = nombre || 'Producto #' + id;
        
        var modalReactivar = document.getElementById('modalReactivarProducto');
        if (modalReactivar) {
            new bootstrap.Modal(modalReactivar).show();
        }
    };

    // REGISTRO RÁPIDO: ABRIR MODAL CATEGORÍA
    var btnAbrirCat = document.getElementById('btnAbrirModalCategoria');
    if (btnAbrirCat) {
        btnAbrirCat.addEventListener('click', function () {
            document.getElementById('formRapidoCategoria').reset();
            document.getElementById('modal-rapido-categoria-error').style.display = 'none';
            window.selectCategoriaObjetivo = 'id_categoria';
            new bootstrap.Modal(document.getElementById('modalRapidoCategoria')).show();
        });
    }

    // GUARDAR CATEGORÍA RÁPIDA
       var btnGuardarCat = document.getElementById('btnGuardarCategoriaRapido');
    if (btnGuardarCat) {
        btnGuardarCat.addEventListener('click', function () {
            var form = document.getElementById('formRapidoCategoria');
            var inputNombre = document.getElementById('rapido-cat-nombre');
            var $inputNombre = $(inputNombre);
            $inputNombre.data('unicoEstado', '');

            if (typeof Validador !== 'undefined' && !Validador.validarFormulario('formRapidoCategoria')) {
                return;
            }

            if (typeof Validador === 'undefined') {
                if (form.reportValidity()) guardarCategoriaRapida();
                return;
            }

            btnGuardarCat.disabled = true;
            btnGuardarCat.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Verificando...';
            Validador.validarUnico(inputNombre).then(function (disponible) {
                if (!disponible) {
                    btnGuardarCat.disabled = false;
                    btnGuardarCat.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Categoría';
                    var errorMsg = $inputNombre.data('unicoEstado') === 'duplicado'
                        ? 'Ya existe una categoría activa con ese nombre.'
                        : 'No se pudo verificar la disponibilidad de la categoría.';
                    document.getElementById('modal-rapido-categoria-error-msg').textContent = errorMsg;
                    document.getElementById('modal-rapido-categoria-error').style.display = 'block';
                    return;
                }
                guardarCategoriaRapida();
            }).catch(function () {
                btnGuardarCat.disabled = false;
                btnGuardarCat.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Categoría';
                document.getElementById('modal-rapido-categoria-error-msg').textContent = 'No se pudo verificar la disponibilidad de la categoría.';
                document.getElementById('modal-rapido-categoria-error').style.display = 'block';
            });

            function guardarCategoriaRapida() {
                var formData = new FormData(form);
                btnGuardarCat.disabled = true;
                btnGuardarCat.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

                fetch('?url=categorias&action=store_rapido_ajax', { method: 'POST', body: formData })
                .then(function(r) { return r.json(); }).then(function(data) {
                    btnGuardarCat.disabled = false;
                    btnGuardarCat.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Categoría';

                    if (!data.success) {
                        document.getElementById('modal-rapido-categoria-error-msg').textContent = data.error || 'Error';
                        document.getElementById('modal-rapido-categoria-error').style.display = 'block';
                        return;
                    }
                
                    var selectCat = document.getElementById(window.selectCategoriaObjetivo || 'id_categoria');
                    if (selectCat) {
                        var existe = false;
                        for (var i = 0; i < selectCat.options.length; i++) {
                            if (selectCat.options[i].value == data.id_categoria) { existe = true; break; }
                        }
                        if (!existe) {
                            var opt = document.createElement('option');
                            opt.value = data.id_categoria;
                            opt.textContent = data.nombre_categoria;
                            selectCat.appendChild(opt);
                        }
                    
                        selectCat.value = data.id_categoria;
                    
                        var ssWrapper = selectCat.closest('.ss-wrapper');
                        if (ssWrapper) {
                            var ssInput = ssWrapper.querySelector('.ss-input');
                            if (ssInput) ssInput.value = data.nombre_categoria;
                        }
                    }
                
                    bootstrap.Modal.getInstance(document.getElementById('modalRapidoCategoria')).hide();
                })
                .catch(function () {
                    btnGuardarCat.disabled = false;
                    btnGuardarCat.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Categoría';
                    document.getElementById('modal-rapido-categoria-error-msg').textContent = 'No se pudo guardar la categoría.';
                    document.getElementById('modal-rapido-categoria-error').style.display = 'block';
                });
            }
        });
    }

    // ============================================================
    // REGISTRO RÁPIDO DE PROVEEDOR (NUEVO - CON AJAX)
    // ============================================================
    var btnAbrirProv = document.getElementById('btnAbrirModalProveedor');
    if (btnAbrirProv) {
        btnAbrirProv.addEventListener('click', function () {
            var form = document.getElementById('formRapidoProveedorProducto');
            var modalProveedor = document.getElementById('modalRapidoProveedorProducto');
            form.reset();
            document.getElementById('modal-rapido-proveedor-error').style.display = 'none';
            if (typeof Validador !== 'undefined') {
                Validador.init(modalProveedor);
                Validador.resetEstadoVisual(form);
            }

            // Abrimos el modal con un z-index mayor
            new bootstrap.Modal(modalProveedor).show();
        });
    }

    var btnGuardarProv = document.getElementById('btnGuardarProveedorRapidoProd');
    if (btnGuardarProv) {
        btnGuardarProv.addEventListener('click', function () {
            var formId = 'formRapidoProveedorProducto';
            var form = document.getElementById(formId);
            var modalProveedor = document.getElementById('modalRapidoProveedorProducto');
            var error = document.getElementById('modal-rapido-proveedor-error');
            var errorMsg = document.getElementById('modal-rapido-proveedor-error-msg');
            var htmlOriginal = btnGuardarProv.innerHTML;
            var camposUnicos = $(form).find('[data-unico-endpoint]');
            camposUnicos.data('unicoEstado', '');
            
            if (typeof Validador !== 'undefined') {
                Validador.init(modalProveedor);
            }
            if (!form.reportValidity()) return;
            if (typeof Validador !== 'undefined' && !Validador.validarFormulario(formId)) {
                return;
            }

            var verificacion = camposUnicos.length && typeof Validador !== 'undefined'
                ? Validador.validarFormularioCompleto(formId)
                : Promise.resolve({ valido: true });

            btnGuardarProv.disabled = true;
            btnGuardarProv.innerHTML = camposUnicos.length
                ? '<i class="fas fa-spinner fa-spin me-1"></i> Verificando...'
                : '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

            verificacion.then(function(resultado) {
                    if (!resultado.valido) {
                        if (resultado.tipo === 'disponibilidad') {
                            errorMsg.textContent = 'No se pudo verificar la disponibilidad del RIF o teléfono.';
                            error.style.display = 'block';
                    }
                        return;
                    }

                    btnGuardarProv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
                    return fetch('?url=proveedores&type=store_rapido_ajax', {
                        method: 'POST',
                        body: new FormData(form)
                    })
                    .then(function(response) {
                        return response.json().then(function(data) {
                            if (!response.ok || !data.success) {
                                throw new Error(data.error || 'No se pudo registrar el proveedor.');
                        }
                            return data;
                        });
                    })
                    .then(function(data) {
                        var selectProv = document.getElementById('id_proveedor');
                        if (selectProv) {
                            var opcion = Array.prototype.find.call(selectProv.options, function(item) {
                                return item.value === String(data.id_proveedor);
                            });
                            if (!opcion) {
                                opcion = new Option(data.razon_social + ' - ' + data.rif, data.id_proveedor);
                                selectProv.add(opcion);
                            }
                            opcion.textContent = data.razon_social + ' - ' + data.rif;
                            selectProv.dispatchEvent(new Event('refresh-search'));
                            selectProv.value = String(data.id_proveedor);
                            selectProv.dispatchEvent(new Event('change', { bubbles: true }));

                            var buscador = selectProv.closest('.ss-wrapper');
                            if (buscador) {
                                var input = buscador.querySelector('.ss-input');
                                if (input) input.value = opcion.textContent;
                            }
                        }

                        bootstrap.Modal.getInstance(modalProveedor).hide();
                    });
                }).catch(function(err) {
                    errorMsg.textContent = err.message || 'No se pudo verificar la disponibilidad.';
                    error.style.display = 'block';
                }).finally(function() {
                    btnGuardarProv.disabled = false;
                    btnGuardarProv.innerHTML = htmlOriginal;
                });
        });
    }

});

document.addEventListener('DOMContentLoaded', function () {
    var selectorPorPagina = document.querySelector('select[name="por_pagina"][data-auto-submit]');
    if (selectorPorPagina) {
        selectorPorPagina.addEventListener('change', function () {
            selectorPorPagina.form.submit();
        });
    }

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

