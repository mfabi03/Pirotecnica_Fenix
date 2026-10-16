<!-- ============================================================ -->
<!-- DATOS DE PRODUCTOS EN JSON + JAVASCRIPT ESPECÍFICO          -->
<!-- ============================================================ -->
<script id="productosData" type="application/json">
<?= json_encode(
    isset($productos) && is_array($productos) ? array_values($productos) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ============================================================
    // DATOS DE PRODUCTOS
    // ============================================================
    const productosData = JSON.parse(document.getElementById('productosData').textContent);
    const productosPorId = {};
    productosData.forEach(function(p) {
        productosPorId[parseInt(p.id_producto)] = p;
    });

    // ============================================================
    // VER DETALLE
    // ============================================================
    window.verDetalleProducto = function(id) {
        const p = productosPorId[id];
        if (!p) return;

        document.getElementById('ver-producto-nombre').textContent = p.descripcion || 'Sin nombre';
        document.getElementById('ver-producto-categoria').textContent = p.nombre_categoria || 'Sin categoría';
        document.getElementById('ver-producto-codigo').textContent = '#' + p.id_producto;
        document.getElementById('ver-producto-nombre-detalle').textContent = p.descripcion || 'N/A';
        document.getElementById('ver-producto-proveedor').textContent = p.proveedor || 'N/A';
        document.getElementById('ver-producto-stock').textContent = p.stock || 0;
        document.getElementById('ver-producto-stock-minimo').textContent = p.stock_minimo || 0;
        document.getElementById('ver-producto-costo').textContent = '$' + parseFloat(p.costo_unitario || 0).toFixed(2);
        document.getElementById('ver-producto-especificaciones').textContent = p.especificaciones || 'Sin especificaciones';

        new bootstrap.Modal(document.getElementById('modalVerProducto')).show();
    };

    // ============================================================
    // EDITAR
    // ============================================================
    window.editarProducto = function(id) {
        const p = productosPorId[id];
        if (!p) return;

        document.getElementById('editar-producto-nombre-header').textContent = p.descripcion || 'Producto';
        document.getElementById('editar-producto-id').value = p.id_producto;
        document.getElementById('editar-producto-descripcion').value = p.descripcion || '';
        document.getElementById('editar-producto-categoria').value = p.id_categoria || '';
        document.getElementById('editar-producto-stock-minimo').value = p.stock_minimo || 10;
        document.getElementById('editar-producto-especificaciones').value = p.especificaciones || '';

        const selectProv = document.getElementById('editar-producto-proveedor');
        if (selectProv && p.id_proveedor) {
            selectProv.value = p.id_proveedor;
        }

        new bootstrap.Modal(document.getElementById('modalEditarProducto')).show();
    };

    // ============================================================
    // ELIMINAR
    // ============================================================
    window.confirmarEliminarProducto = function(id, nombre) {
        document.getElementById('eliminar-producto-id').value = id;
        document.getElementById('eliminar-producto-nombre').textContent = nombre || 'Producto #' + id;
        new bootstrap.Modal(document.getElementById('modalEliminarProducto')).show();
    };
  // ============================================================
// RECARGAR CATEGORÍAS Y PROVEEDORES AL ABRIR EL MODAL
// ============================================================
const modalRegistrarProducto = document.getElementById('modalRegistrarProducto');
if (modalRegistrarProducto) {
    modalRegistrarProducto.addEventListener('show.bs.modal', function () {
        
        // --- RECARGAR CATEGORÍAS ---
        const selectCat = document.getElementById('id_categoria');
        if (selectCat) {
            // ⭐ LIMPIAR EL SELECT ANTES DE CARGAR
            selectCat.innerHTML = '<option value="" disabled selected>Seleccione una Categoría...</option>';
            
            fetch('?url=categorias&action=listar_ajax')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    
                    data.categorias.forEach(function(c) {
                        const opt = document.createElement('option');
                        opt.value = c.id_categoria;
                        opt.textContent = c.nombre_categoria;
                        selectCat.appendChild(opt);
                    });
                })
                .catch(err => console.error('Error al recargar categorías:', err));
        }
        
        // --- RECARGAR PROVEEDORES ---
        const selectProv = document.getElementById('id_proveedor');
        if (selectProv) {
            // ⭐ LIMPIAR EL SELECT ANTES DE CARGAR
            selectProv.innerHTML = '<option value="" disabled selected>Seleccione un Proveedor...</option>';
            
            fetch('?url=proveedores&type=listar_ajax')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    
                    data.proveedores.forEach(function(p) {
                        const opt = document.createElement('option');
                        opt.value = p.id_proveedor;
                        opt.textContent = p.razon_social;
                        selectProv.appendChild(opt);
                    });
                })
                .catch(err => console.error('Error al recargar proveedores:', err));
        }
    });
}

// ============================================================
// REGISTRO RÁPIDO: ABRIR MODALES
// ============================================================
const btnAbrirCat = document.getElementById('btnAbrirModalCategoria');
if (btnAbrirCat) {
    btnAbrirCat.addEventListener('click', function () {
        document.getElementById('formRapidoCategoria').reset();
        document.getElementById('modal-rapido-categoria-error').style.display = 'none';
        window.selectCategoriaObjetivo = 'id_categoria';
        new bootstrap.Modal(document.getElementById('modalRapidoCategoria')).show();
    });
}

const btnAbrirProv = document.getElementById('btnAbrirModalProveedor');
if (btnAbrirProv) {
    btnAbrirProv.addEventListener('click', function () {
        document.getElementById('formRapidoProveedor').reset();
        document.getElementById('modal-rapido-proveedor-error').style.display = 'none';
        window.selectProveedorObjetivo = 'id_proveedor';
        new bootstrap.Modal(document.getElementById('modalRapidoProveedor')).show();
    });
}

const btnAbrirCatEditar = document.getElementById('btnAbrirModalCategoriaEditar');
if (btnAbrirCatEditar) {
    btnAbrirCatEditar.addEventListener('click', function () {
        document.getElementById('formRapidoCategoria').reset();
        document.getElementById('modal-rapido-categoria-error').style.display = 'none';
        window.selectCategoriaObjetivo = 'editar-producto-categoria';
        new bootstrap.Modal(document.getElementById('modalRapidoCategoria')).show();
    });
}

const btnAbrirProvEditar = document.getElementById('btnAbrirModalProveedorEditar');
if (btnAbrirProvEditar) {
    btnAbrirProvEditar.addEventListener('click', function () {
        document.getElementById('formRapidoProveedor').reset();
        document.getElementById('modal-rapido-proveedor-error').style.display = 'none';
        window.selectProveedorObjetivo = 'editar-producto-proveedor';
        new bootstrap.Modal(document.getElementById('modalRapidoProveedor')).show();
    });
}

// ============================================================
// REGISTRO RÁPIDO: GUARDAR CATEGORÍA (AJAX)
// ============================================================
const btnGuardarCat = document.getElementById('btnGuardarCategoriaRapido');
if (btnGuardarCat) {
    btnGuardarCat.addEventListener('click', function () {
        const formData = new FormData(document.getElementById('formRapidoCategoria'));
        
        btnGuardarCat.disabled = true;
        btnGuardarCat.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        
        fetch('?url=categorias&action=store_rapido_ajax', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            btnGuardarCat.disabled = false;
            btnGuardarCat.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Categoría';
            
            if (!data.success) {
                document.getElementById('modal-rapido-categoria-error-msg').textContent = data.error || 'Error al guardar';
                document.getElementById('modal-rapido-categoria-error').style.display = 'block';
                return;
            }
            
            const selectCat = document.getElementById(window.selectCategoriaObjetivo || 'id_categoria');
            if (selectCat) {
                let existe = false;
                for (let i = 0; i < selectCat.options.length; i++) {
                    if (selectCat.options[i].value == data.id_categoria) {
                        existe = true;
                        break;
                    }
                }
                
                if (!existe) {
                    const opt = document.createElement('option');
                    opt.value = data.id_categoria;
                    opt.textContent = data.nombre_categoria;
                    selectCat.appendChild(opt);
                }
                
                selectCat.value = data.id_categoria;
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalRapidoCategoria'));
            if (modal) modal.hide();
            refrescarTablaProductos();
        })
        
        .catch(err => {
            btnGuardarCat.disabled = false;
            btnGuardarCat.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Categoría';
            document.getElementById('modal-rapido-categoria-error-msg').textContent = 'Error: ' + err.message;
            document.getElementById('modal-rapido-categoria-error').style.display = 'block';
        });
    });
}

// ============================================================
// REGISTRO RÁPIDO: GUARDAR PROVEEDOR (AJAX)
// ============================================================
const btnGuardarProv = document.getElementById('btnGuardarProveedorRapido');
if (btnGuardarProv) {
    btnGuardarProv.addEventListener('click', function () {
        const formData = new FormData(document.getElementById('formRapidoProveedor'));
        
        btnGuardarProv.disabled = true;
        btnGuardarProv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        
        fetch('?url=proveedores&type=store_rapido_ajax', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            btnGuardarProv.disabled = false;
            btnGuardarProv.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Proveedor';
            
            if (!data.success) {
                document.getElementById('modal-rapido-proveedor-error-msg').textContent = data.error || 'Error al guardar';
                document.getElementById('modal-rapido-proveedor-error').style.display = 'block';
                return;
            }
            
            const selectProv = document.getElementById(window.selectProveedorObjetivo || 'id_proveedor');
            if (selectProv) {
                let existe = false;
                for (let i = 0; i < selectProv.options.length; i++) {
                    if (selectProv.options[i].value == data.id_proveedor) {
                        existe = true;
                        break;
                    }
                }
                
                if (!existe) {
                    const opt = document.createElement('option');
                    opt.value = data.id_proveedor;
                    opt.textContent = data.razon_social + ' - ' + data.rif;
                    selectProv.appendChild(opt);
                }
                
                selectProv.value = data.id_proveedor;
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalRapidoProveedor'));
            if (modal) modal.hide();
            refrescarTablaProductos();
        })
        .catch(err => {
            btnGuardarProv.disabled = false;
            btnGuardarProv.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Proveedor';
            document.getElementById('modal-rapido-proveedor-error-msg').textContent = 'Error: ' + err.message;
            document.getElementById('modal-rapido-proveedor-error').style.display = 'block';
        });
    });
}
});
</script>