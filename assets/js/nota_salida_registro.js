document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================================
    // MÓDULO PRINCIPAL DE NOTA DE SALIDA
    // ============================================================
    const productoSelect = document.getElementById('producto');
    const categoriaInput = document.getElementById('categoria');
    const cantidadInput = document.getElementById('cantidad');
    const btnAgregar = document.getElementById('btnAgregar');
    const tbody = document.getElementById('tbodyDetalles');
    const filaVacia = document.getElementById('filaVacia');
    const detallesContainer = document.getElementById('detallesContainer');
    const stockActual = document.getElementById('stockActual');
    const detalles = [];

    // 1. MOSTRAR CATEGORÍA AL SELECCIONAR PRODUCTO
    productoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const categoria = selectedOption.getAttribute('data-categoria');
        const stock = selectedOption.getAttribute('data-stock');
        
        categoriaInput.value = categoria || 'Sin categoría';
        stockActual.textContent = stock || 0;
        
        if (cantidadInput.value > stock) {
            cantidadInput.value = stock;
        }
    });

    productoSelect.dispatchEvent(new Event('change'));

    // 2. AGREGAR PRODUCTO
    btnAgregar.addEventListener('click', function(e) {
        e.preventDefault();

        const idProducto = productoSelect.value;
        const nombreProducto = productoSelect.options[productoSelect.selectedIndex].text.split(' (Stock:')[0];
        const categoria = categoriaInput.value || 'Sin categoría';
        const cantidad = parseInt(cantidadInput.value);
        const stock = parseInt(productoSelect.options[productoSelect.selectedIndex].getAttribute('data-stock') || 0);

        if (!idProducto) {
            alert('Seleccione un producto.');
            return;
        }

        if (isNaN(cantidad) || cantidad <= 0) {
            alert('Ingrese una cantidad válida (mayor a 0).');
            return;
        }

        if (cantidad > stock) {
            alert('Stock insuficiente. Stock disponible: ' + stock);
            return;
        }

        const filasExistentes = tbody.querySelectorAll('tr:not(#filaVacia)');
        let productoExistente = false;
        filasExistentes.forEach(row => {
            const nombreCelda = row.querySelector('td:first-child');
            if (nombreCelda && nombreCelda.textContent.trim() === nombreProducto) {
                productoExistente = true;
            }
        });

        if (productoExistente) {
            alert('Este producto ya está agregado.');
            return;
        }

        if (filaVacia) {
            filaVacia.remove();
        }

        const row = document.createElement('tr');
        row.setAttribute('data-id', idProducto);
        row.innerHTML = `
            <td class="ps-4" style="color: rgba(255,255,255,0.8);">${window.escapeHtml(nombreProducto)}</td>
            <td style="color: rgba(255,255,255,0.5);">${window.escapeHtml(categoria)}</td>
            <td class="text-center" style="color: rgba(255,255,255,0.8);">${window.escapeHtml(cantidad)}</td>
            <td class="pe-4 text-center">
                <button type="button" class="btn-action-circle btn-delete eliminar-producto" title="Eliminar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);

        detalles.push({
            id_producto: parseInt(idProducto),
            cantidad: cantidad
        });

        actualizarCamposOcultos();

        // ============================================================
        // AQUÍ ESTÁ LA CORRECCIÓN PARA LIMPIAR LOS CAMPOS
        // ============================================================
        
        // 1. Limpiar el select real
        if (productoSelect) productoSelect.value = '';

        // 2. Limpiar el input visible de select-search.js
        const ssWrapper = productoSelect.closest('.ss-wrapper');
        if (ssWrapper) {
            const ssInput = ssWrapper.querySelector('.ss-input');
            if (ssInput) ssInput.value = '';
        }

        // 3. Limpiar los otros campos
        if (categoriaInput) categoriaInput.value = '';
        if (cantidadInput) cantidadInput.value = '';
        if (stockActual) stockActual.textContent = '0';

        // 4. Devolver el foco
        if (ssWrapper) {
            const ssInput = ssWrapper.querySelector('.ss-input');
            if (ssInput) ssInput.focus();
        } else if (productoSelect) {
            productoSelect.focus();
        }
    });

    // 3. ELIMINAR PRODUCTO
    tbody.addEventListener('click', function(e) {
        const btn = e.target.closest('.eliminar-producto');
        if (btn) {
            const row = btn.closest('tr');
            const idProducto = parseInt(row.getAttribute('data-id'));
            
            const index = detalles.findIndex(d => d.id_producto === idProducto);
            if (index !== -1) {
                detalles.splice(index, 1);
            }
            
            row.remove();
            actualizarCamposOcultos();

            if (tbody.querySelectorAll('tr').length === 0) {
                tbody.innerHTML = `
                    <tr id="filaVacia">
                        <td colspan="4" class="text-center py-3" style="color: rgba(255,255,255,0.2);">
                            <i class="fas fa-box-open me-2"></i> No hay productos agregados
                        </td>
                    </tr>
                `;
            }
        }
    });

    // 4. ACTUALIZAR CAMPOS OCULTOS
    function actualizarCamposOcultos() {
        detallesContainer.innerHTML = '';
        
        detalles.forEach((d) => {
            const inputProducto = document.createElement('input');
            inputProducto.type = 'hidden';
            inputProducto.name = 'detalle_producto[]';
            inputProducto.value = d.id_producto;
            detallesContainer.appendChild(inputProducto);
            
            const inputCantidad = document.createElement('input');
            inputCantidad.type = 'hidden';
            inputCantidad.name = 'detalle_cantidad[]';
            inputCantidad.value = d.cantidad;
            detallesContainer.appendChild(inputCantidad);
        });
    }

    function reconstruirDetallesDesdeTabla() {
        const filas = tbody.querySelectorAll('tr[data-id]');
        const reconstruido = [];

        filas.forEach((row) => {
            const idProducto = parseInt(row.getAttribute('data-id'));
            const celdas = row.querySelectorAll('td');
            const cantidad = celdas.length > 2 ? parseInt(celdas[2].textContent.trim()) : 0;

            if (!Number.isNaN(idProducto) && idProducto > 0 && !Number.isNaN(cantidad) && cantidad > 0) {
                reconstruido.push({ id_producto: idProducto, cantidad: cantidad });
            }
        });

        detalles.length = 0;
        reconstruido.forEach((item) => detalles.push(item));
        actualizarCamposOcultos();
        return detalles;
    }

    // 5. VALIDAR ANTES DE GUARDAR
    const formNotaSalida = document.getElementById('formNotaSalida');
    formNotaSalida.addEventListener('submit', function(e) {
        const filasActuales = tbody.querySelectorAll('tr[data-id]');
        if (filasActuales.length > 0 && detalles.length === 0) {
            reconstruirDetallesDesdeTabla();
        }

        if (detalles.length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un producto.');
            return;
        }

        actualizarCamposOcultos();
    });


    // ============================================================
    // MÓDULO DE REGISTRO RÁPIDO DE CLIENTE (MODAL + AJAX)
    // ============================================================

    const btnAbrirModal = document.getElementById('btnAbrirModalCliente');
    if (btnAbrirModal) {
        btnAbrirModal.addEventListener('click', function () {
            document.getElementById('formRapidoClienteNatural').reset();
            document.getElementById('formRapidoClienteJuridico').reset();
            document.getElementById('modal-rapido-cliente-error').style.display = 'none';
            
            if (typeof Validador !== 'undefined') {
                Validador.resetEstadoVisual(document.getElementById('modalRapidoCliente'));
            }
            
            new bootstrap.Modal(document.getElementById('modalRapidoCliente')).show();
        });
    }

    const btnGuardar = document.getElementById('btnGuardarClienteRapido');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', function () {
            const tabActiva = document.querySelector('#modalRapidoCliente .nav-link.active');
            const esJuridico = tabActiva && tabActiva.id === 'tab-rapido-juridico';
            
            const formId = esJuridico ? 'formRapidoClienteJuridico' : 'formRapidoClienteNatural';
            const form = document.getElementById(formId);
            
            if (typeof Validador !== 'undefined') {
                Validador.init(document.getElementById('modalRapidoCliente'));
                if (!Validador.validarFormulario(formId)) {
                    return;
                }
            }
            
            const formData = new FormData(form);
            
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
            
            fetch('?url=notasalida&type=store_rapido_cliente_ajax', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Cliente';
                
                if (!data.success) {
                    document.getElementById('modal-rapido-cliente-error-msg').textContent = data.error || 'Error al guardar el cliente';
                    document.getElementById('modal-rapido-cliente-error').style.display = 'block';
                    return;
                }
                
                const selectCliente = document.getElementById('id_cliente');
                if (selectCliente) {
                    let existe = false;
                    for (let i = 0; i < selectCliente.options.length; i++) {
                        if (selectCliente.options[i].value == data.id_cliente) {
                            existe = true;
                            break;
                        }
                    }
                    
                    if (!existe) {
                        const nuevaOpcion = document.createElement('option');
                        nuevaOpcion.value = data.id_cliente;
                        nuevaOpcion.textContent = data.nombre_cliente + ' - ' + data.documento;
                        selectCliente.appendChild(nuevaOpcion);
                    }
                    
                    selectCliente.value = data.id_cliente;
                }
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalRapidoCliente'));
                if (modal) modal.hide();
                
                mostrarMensajeExito('✅ Cliente "' + data.nombre_cliente + '" registrado exitosamente');
                
            })
            .catch(err => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Cliente';
                document.getElementById('modal-rapido-cliente-error-msg').textContent = 'Error de conexión: ' + err.message;
                document.getElementById('modal-rapido-cliente-error').style.display = 'block';
            });
        });
    }

    function mostrarMensajeExito(mensaje) {
        const contenedor = document.getElementById('formNotaSalida');
        if (!contenedor) return;
        
        const alerta = document.createElement('div');
        alerta.className = 'alert dark-alert-success alert-dismissible fade show shadow-sm border-0';
        alerta.style.marginBottom = '16px';
        alerta.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <span>${window.escapeHtml(mensaje)}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        contenedor.parentNode.insertBefore(alerta, contenedor);
        
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.remove();
            }
        }, 4000);
    }

    document.querySelectorAll('#modalRapidoCliente .nav-link').forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function (e) {
            document.querySelectorAll('#modalRapidoCliente .nav-link').forEach(function (t) {
                t.style.color = '#6c757d';
                t.style.borderBottom = 'none';
            });
            e.target.style.color = '#f39c12';
            e.target.style.borderBottom = '2px solid #f39c12';
        });
    });
});
