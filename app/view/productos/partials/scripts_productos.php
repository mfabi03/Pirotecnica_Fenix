<script id="productosData" type="application/json">
<?= json_encode(
    isset($productos) && is_array($productos) ? array_values($productos) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    var productosDataRaw = document.getElementById('productosData');
    if (!productosDataRaw) return;
    
    var productosData = JSON.parse(productosDataRaw.textContent);
    var productosPorId = {};
    
    productosData.forEach(function(p) {
        productosPorId[parseInt(p.id_producto)] = p;
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
        
        setVal('editar-producto-categoria-texto', p.nombre_categoria || 'Sin categoría');
        setVal('editar-producto-categoria-id', p.id_categoria || '');
        
        var proveedorSelect = document.getElementById('editar-producto-proveedor-id');
        if (proveedorSelect) {
            proveedorSelect.value = p.id_proveedor || '';
            proveedorSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }

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
            var formData = new FormData(document.getElementById('formRapidoCategoria'));
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
                    // 1. Agregar la nueva opción al select
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
                    
                    // 2. Seleccionarla automáticamente
                    selectCat.value = data.id_categoria;
                    
                    // 3. Actualizar el buscador visual
                    var ssWrapper = selectCat.closest('.ss-wrapper');
                    if (ssWrapper) {
                        var ssInput = ssWrapper.querySelector('.ss-input');
                        if (ssInput) ssInput.value = data.nombre_categoria;
                    }
                }
                
                // 4. Cerrar el modal
                bootstrap.Modal.getInstance(document.getElementById('modalRapidoCategoria')).hide();
            });
        });
    }

    // ============================================================
    // REGISTRO RÁPIDO DE PROVEEDOR (NUEVO - CON AJAX)
    // ============================================================
    var btnAbrirProv = document.getElementById('btnAbrirModalProveedor');
    if (btnAbrirProv) {
        btnAbrirProv.addEventListener('click', function () {
            document.getElementById('formRapidoProveedorProducto').reset();
            document.getElementById('modal-rapido-proveedor-error').style.display = 'none';
            
            // Abrimos el modal con un z-index mayor
            new bootstrap.Modal(document.getElementById('modalRapidoProveedorProducto')).show();
        });
    }

    var btnGuardarProv = document.getElementById('btnGuardarProveedorRapidoProd');
    if (btnGuardarProv) {
        btnGuardarProv.addEventListener('click', function () {
            var formId = 'formRapidoProveedorProducto';
            
            if (typeof Validador !== 'undefined') {
                Validador.init(document.getElementById('modalRapidoProveedorProducto'));
                if (!Validador.validarFormulario(formId)) return;
            }
            
            var formData = new FormData(document.getElementById(formId));
            btnGuardarProv.disabled = true;
            btnGuardarProv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
            
            fetch('?url=proveedores&type=store_rapido_ajax', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btnGuardarProv.disabled = false;
                btnGuardarProv.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Proveedor';
                
                if (!data.success) {
                    document.getElementById('modal-rapido-proveedor-error-msg').textContent = data.error || 'Error';
                    document.getElementById('modal-rapido-proveedor-error').style.display = 'block';
                    return;
                }
                
                var selectProv = document.getElementById('id_proveedor');
                if (selectProv) {
                    var existe = false;
                    for (var i = 0; i < selectProv.options.length; i++) {
                        if (selectProv.options[i].value == data.id_proveedor) { existe = true; break; }
                    }
                    if (!existe) {
                        var opt = document.createElement('option');
                        opt.value = data.id_proveedor;
                        opt.textContent = data.razon_social + ' - ' + data.rif;
                        selectProv.appendChild(opt);
                    }
                    selectProv.value = data.id_proveedor;
                    
                    // Actualizar el buscador
                    var ssWrapper = selectProv.closest('.ss-wrapper');
                    if (ssWrapper) {
                        var ssInput = ssWrapper.querySelector('.ss-input');
                        if (ssInput) ssInput.value = data.razon_social + ' - ' + data.rif;
                    }
                }
                
                bootstrap.Modal.getInstance(document.getElementById('modalRapidoProveedorProducto')).hide();
            })
            .catch(function(err) {
                btnGuardarProv.disabled = false;
                btnGuardarProv.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Proveedor';
                document.getElementById('modal-rapido-proveedor-error-msg').textContent = 'Error: ' + err.message;
                document.getElementById('modal-rapido-proveedor-error').style.display = 'block';
            });
        });
    }

});
</script>