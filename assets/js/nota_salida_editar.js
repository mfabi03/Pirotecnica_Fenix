let detalles = [];

function agregarProducto() {
    const select = document.getElementById('productoSelect');
    const cantidad = parseInt(document.getElementById('cantidadInput').value);
    
    if (!select.value) {
        alert('Seleccione un producto');
        return;
    }
    if (!cantidad || cantidad < 1) {
        alert('Ingrese una cantidad válida');
        return;
    }
    
    const option = select.options[select.selectedIndex];
    const stock = parseInt(option.dataset.stock);
    
    if (cantidad > stock) {
        alert('Stock insuficiente. Disponible: ' + stock);
        return;
    }
    
    const producto = {
        id_producto: parseInt(select.value),
        descripcion: option.dataset.descripcion,
        cantidad: cantidad
    };
    
    detalles.push(producto);
    actualizarTabla();
    select.value = '';
    document.getElementById('cantidadInput').value = 1;
}

function eliminarProducto(button) {
    const row = button.closest('tr');
    row.remove();
}

function actualizarTabla() {
    const tbody = document.getElementById('detallesBody');
    
    if (detalles.length === 0) {
        return;
    }
    
    detalles.forEach((d) => {
        const existingRows = tbody.querySelectorAll('tr');
        for (let row of existingRows) {
            const hiddenInput = row.querySelector('input[type="hidden"]');
            if (hiddenInput && parseInt(hiddenInput.value) === d.id_producto) {
                const cantInput = row.querySelector('input[type="number"]');
                if (cantInput) {
                    cantInput.value = parseInt(cantInput.value) + d.cantidad;
                }
                return;
            }
        }
        
        const tr = document.createElement('tr');
        const index = tbody.children.length;
        tr.innerHTML = `
            <td>${window.escapeHtml(d.descripcion)}</td>
            <td>
                <input type="number" name="detalles[${window.escapeHtml(index)}][cantidad]" 
                       class="form-control form-control-sm" 
                       value="${window.escapeHtml(d.cantidad)}" min="1" style="width: 80px;">
                <input type="hidden" name="detalles[${window.escapeHtml(index)}][id_producto]" value="${window.escapeHtml(d.id_producto)}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    detalles = [];
}

document.getElementById('notaSalidaForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#detallesBody tr');
    const productos = [];
    
    rows.forEach(row => {
        const idInput = row.querySelector('input[name*="id_producto"]');
        const cantInput = row.querySelector('input[name*="cantidad"]');
        if (idInput && cantInput) {
            productos.push({
                id_producto: parseInt(idInput.value),
                cantidad: parseInt(cantInput.value)
            });
        }
    });
    
    if (productos.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un producto.');
        return;
    }
    
    document.getElementById('detallesJson').value = JSON.stringify(productos);
});
