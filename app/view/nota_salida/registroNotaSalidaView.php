<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card shadow-sm p-4" style="border-radius: 16px; border-top: 4px solid #DAA520;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 style="color: #000000; font-weight: 700;">
                <i class="fas fa-sign-out-alt" style="color: #DAA520;"></i> Registrar Nota de Salida
            </h4>
            <a href="?url=notasalida&type=list" class="btn btn-secondary" style="border-radius: 50px; padding: 8px 25px;">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <!-- Mostrar mensajes de éxito/error -->
        <?php if (isset($_SESSION['mensaje_rapido'])): ?>
            <div class="alert alert-<?= $_SESSION['tipo_rapido'] ?? 'success' ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= ($_SESSION['tipo_rapido'] ?? 'success') == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($_SESSION['mensaje_rapido']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje_rapido'], $_SESSION['tipo_rapido']); ?>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="?url=notasalida&type=store" id="formNotaSalida">
            
            <!-- ==========================================
            DATOS DEL CLIENTE Y ENCARGADO
            ========================================== -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card" style="border-radius: 12px; border-left: 4px solid #DAA520;">
                        <div class="card-body">
                            <h6 class="card-title fw-bold mb-3"><i class="fas fa-user me-2" style="color: #DAA520;"></i> Cliente</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="id_cliente" id="id_cliente" class="form-select" required>
                                        <option value="">Seleccione un cliente...</option>
                                        <?php foreach ($clientes as $c): ?>
                                            <?php
                                                $nombreCliente = ($c['tipo_cliente'] ?? '') === 'Jurídico'
                                                    ? trim($c['razon_social'] ?? '')
                                                    : trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                                            ?>
                                            <option value="<?= $c['id_cliente'] ?>"
                                                <?= (isset($_SESSION['nuevo_cliente_id']) && $_SESSION['nuevo_cliente_id'] == $c['id_cliente']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($nombreCliente !== '' ? $nombreCliente : 'Cliente #' . $c['id_cliente']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- 🔥 REDIRECCIÓN EN LUGAR DE MODAL -->
                                    <a href="?url=clientes&type=register&return=notasalida" 
                                       class="btn btn-gold" 
                                       title="Registrar nuevo cliente">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                                <?php unset($_SESSION['nuevo_cliente_id']); ?>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold">Encargado</label>
                                <input type="text" class="form-control" value="<?= $_SESSION['usuario_nombre'] ?? 'Usuario actual' ?>" disabled>
                                <small class="text-muted">La persona encargada se registra automáticamente</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card" style="border-radius: 12px; border-left: 4px solid #28a745;">
                        <div class="card-body">
                            <h6 class="card-title fw-bold mb-3"><i class="fas fa-boxes me-2" style="color: #28a745;"></i> Agregar Productos</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Producto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select id="producto" class="form-select" required>
                                        <option value="">Seleccione un producto...</option>
                                        <?php foreach ($productos as $p): ?>
                                            <option value="<?= $p['id_producto'] ?>" 
                                                    data-categoria="<?= htmlspecialchars($p['nombre_categoria'] ?? 'Sin categoría') ?>"
                                                    data-stock="<?= $p['stock'] ?>"
                                                <?= (isset($_SESSION['nuevo_producto_id']) && $_SESSION['nuevo_producto_id'] == $p['id_producto']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['descripcion']) ?> 
                                                (Stock: <?= $p['stock'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- 🔥 REDIRECCIÓN EN LUGAR DE MODAL -->
                                    <a href="?url=productos&type=create&return=notasalida" 
                                       class="btn btn-gold" 
                                       title="Registrar nuevo producto">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                                <?php unset($_SESSION['nuevo_producto_id']); ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Categoría</label>
                                <input type="text" id="categoria" class="form-control" value="" readonly style="background-color: #e9ecef;">
                            </div>
                            <div class="row">
                                <div class="col-8">
                                    <label class="form-label fw-bold">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" id="cantidad" class="form-control" min="1" value="1" required>
                                </div>
                                <div class="col-4 d-flex align-items-end">
                                    <button type="button" id="btnAgregar" class="btn btn-success w-100">
                                        <i class="fas fa-plus me-1"></i> Agregar
                                    </button>
                                </div>
                            </div>
                            <small id="stockDisponible" class="text-muted">Stock disponible: <span id="stockActual">0</span></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
            TABLA DE PRODUCTOS AGREGADOS
            ========================================== -->
            <div class="card mb-4" style="border-radius: 12px;">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-list me-2"></i> Productos agregados
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="detallesTabla">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalles">
                                <tr id="filaVacia">
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <i class="fas fa-box-open me-2"></i> No hay productos agregados
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ==========================================
            CAMPOS OCULTOS PARA DETALLES (SIN JSON)
            ========================================== -->
            <div id="detallesContainer"></div>

            <!-- ==========================================
            BOTONES DE ACCIÓN
            ========================================== -->
            <div class="d-flex justify-content-end gap-2">
                <a href="?url=notasalida&type=list" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-save me-1"></i> Guardar Nota de Salida
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
SCRIPTS
========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productoSelect = document.getElementById('producto');
    const categoriaInput = document.getElementById('categoria');
    const cantidadInput = document.getElementById('cantidad');
    const btnAgregar = document.getElementById('btnAgregar');
    const tbody = document.getElementById('tbodyDetalles');
    const filaVacia = document.getElementById('filaVacia');
    const detallesContainer = document.getElementById('detallesContainer');
    const stockActual = document.getElementById('stockActual');
    const detalles = [];

    // ==========================================
    // 1. MOSTRAR CATEGORÍA AL SELECCIONAR PRODUCTO
    // ==========================================
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

    // Disparar evento inicial
    productoSelect.dispatchEvent(new Event('change'));

    // ==========================================
    // 2. AGREGAR PRODUCTO
    // ==========================================
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

        // Verificar si el producto ya está agregado
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

        // Eliminar fila vacía
        if (filaVacia) {
            filaVacia.remove();
        }

        // Agregar fila
        const row = document.createElement('tr');
        row.setAttribute('data-id', idProducto);
        row.innerHTML = `
            <td>${nombreProducto}</td>
            <td>${categoria}</td>
            <td class="text-center">${cantidad}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm eliminar-producto" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);

        // Guardar en array
        detalles.push({
            id_producto: parseInt(idProducto),
            cantidad: cantidad
        });

        actualizarCamposOcultos();

        // Limpiar cantidad
        cantidadInput.value = 1;
    });

    // ==========================================
    // 3. ELIMINAR PRODUCTO
    // ==========================================
    tbody.addEventListener('click', function(e) {
        const btn = e.target.closest('.eliminar-producto');
        if (btn) {
            const row = btn.closest('tr');
            const idProducto = parseInt(row.getAttribute('data-id'));
            
            // Eliminar del array
            const index = detalles.findIndex(d => d.id_producto === idProducto);
            if (index !== -1) {
                detalles.splice(index, 1);
            }
            
            row.remove();
            actualizarCamposOcultos();

            // Si no quedan productos
            if (tbody.querySelectorAll('tr').length === 0) {
                tbody.innerHTML = `
                    <tr id="filaVacia">
                        <td colspan="4" class="text-center text-muted py-3">
                            <i class="fas fa-box-open me-2"></i> No hay productos agregados
                        </td>
                    </tr>
                `;
            }
        }
    });

    // ==========================================
    // 4. ACTUALIZAR CAMPOS OCULTOS (SIN JSON)
    // ==========================================
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

    // ==========================================
    // 5. VALIDAR ANTES DE GUARDAR
    // ==========================================
    document.getElementById('formNotaSalida').addEventListener('submit', function(e) {
        if (detalles.length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un producto.');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>