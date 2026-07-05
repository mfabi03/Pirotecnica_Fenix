<?php
// CAMBIO: Ajuste de nombre según BD - Vista de edición de nota de salida
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card shadow-sm p-4" style="border-radius: 16px; border-top: 4px solid #DAA520;">
        <h4><i class="fas fa-edit me-2" style="color: #DAA520;"></i> Editar Nota de Salida</h4>
        <hr>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-custom"><?= $error ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-custom"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form method="POST" action="?url=notasalida&type=update" id="notaSalidaForm">
            <input type="hidden" name="id_nota_salida" value="<?= $nota['id_nota_salida'] ?? '' ?>">

            <div class="row g-3">
                <!-- Cliente -->
                <div class="col-md-6">
                    <label class="form-label-custom">Cliente <span class="text-danger">*</span></label>
                    <select name="id_cliente" class="form-select" required>
                        <option value="">Seleccione un cliente...</option>
                        <?php if (!empty($clientes) && is_array($clientes)): ?>
                            <?php 
                            // 🔥 OBTENER EL ID DEL CLIENTE DESDE LA NOTA
                            $idClienteSeleccionado = $nota['id_persona'] ?? $nota['id_cliente'] ?? null;
                            foreach ($clientes as $c): 
                            ?>
                                <option value="<?= $c['id_cliente'] ?>" <?= ($c['id_cliente'] == $idClienteSeleccionado) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre_cliente'] ?? 'Cliente sin nombre') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay clientes disponibles</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Encargado -->
                <div class="col-md-6">
                    <label class="form-label-custom">Encargado</label>
                    <input type="text" class="form-control bg-light" 
                           value="<?= $_SESSION['usuario_nombre'] ?? 'Usuario actual' ?>" 
                           disabled readonly>
                    <small class="text-muted">La persona encargada se registra automáticamente</small>
                </div>
            </div>

            <hr>
            <h5><i class="fas fa-box me-2"></i> Productos</h5>

            <!-- Productos actuales -->
            <div class="table-responsive mb-3">
                <table class="table table-fenix table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="detallesBody">
                        <?php if (!empty($nota['detalles'])): ?>
                            <?php foreach ($nota['detalles'] as $index => $d): ?>
                                <tr data-index="<?= $index ?>">
                                    <td><?= htmlspecialchars($d['nombre_producto']) ?></td>
                                    <td>
                                        <input type="number" name="detalles[<?= $index ?>][cantidad]" 
                                               class="form-control form-control-sm" 
                                               value="<?= $d['cantidad'] ?>" min="1" style="width: 80px;">
                                        <input type="hidden" name="detalles[<?= $index ?>][id_producto]" 
                                               value="<?= $d['id_producto'] ?>">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="eliminarProducto(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay productos en esta nota</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Agregar producto -->
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <select id="productoSelect" class="form-select">
                        <option value="">Seleccione un producto...</option>
                        <?php if (!empty($productos) && is_array($productos)): ?>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id_producto'] ?>" 
                                        data-descripcion="<?= htmlspecialchars($p['descripcion']) ?>"
                                        data-stock="<?= $p['cantidad'] ?>">
                                    <?= htmlspecialchars($p['descripcion']) ?> (Stock: <?= $p['cantidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay productos disponibles</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" id="cantidadInput" class="form-control" min="1" value="1">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-gold" onclick="agregarProducto()">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                </div>
            </div>

            <input type="hidden" name="detalles_json" id="detallesJson" value="[]">

            <div class="mt-4">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
                <a href="?url=notasalida&type=list" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
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
            <td>${d.descripcion}</td>
            <td>
                <input type="number" name="detalles[${index}][cantidad]" 
                       class="form-control form-control-sm" 
                       value="${d.cantidad}" min="1" style="width: 80px;">
                <input type="hidden" name="detalles[${index}][id_producto]" value="${d.id_producto}">
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
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>