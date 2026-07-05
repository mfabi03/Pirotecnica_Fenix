<?php
// CAMBIO: Ajuste de nombre según BD - Vista de registro de nota de entrada CON BOOTSTRAP
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <!-- Tarjeta de título -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-sign-in-alt me-2"></i> Registrar Nota de Entrada
                </h3>
                <p class="text-muted mb-0">Registra el ingreso de productos al stock.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=notaentrada&type=list" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-list me-1"></i> Ver Notas
                </a>
            </div>
        </div>
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
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4 mb-4 bg-white">
        <form method="POST" action="?url=notaentrada&type=store" id="notaEntradaForm">
            <div class="row g-3">
                <!-- Fecha de Ingreso -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">
                        Fecha de Ingreso <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" name="fecha_ingreso" class="form-control" 
                           value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>

                <!-- 🔥 PROVEEDOR CON BOTÓN DE REGISTRO RÁPIDO (REDIRECCIÓN) -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">
                        Proveedor <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <select id="id_proveedor" name="id_proveedor" class="form-select" required>
                            <option value="">Seleccione un proveedor...</option>
                            <?php if (!empty($proveedores)): ?>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= $prov['id_proveedor'] ?>"
                                        <?= (isset($_SESSION['nuevo_proveedor_id']) && $_SESSION['nuevo_proveedor_id'] == $prov['id_proveedor']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prov['razon_social'] . ' - ' . $prov['rif']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No hay proveedores disponibles</option>
                            <?php endif; ?>
                        </select>
                        <!-- 🔥 REDIRECCIÓN EN LUGAR DE MODAL -->
                        <a href="?url=proveedores&type=create&return=notaentrada" 
                           class="btn btn-gold" 
                           title="Registrar nuevo proveedor">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                    <?php unset($_SESSION['nuevo_proveedor_id']); ?>
                </div>

                <!-- Descripción -->
                <div class="col-md-12">
                    <label class="form-label">
                        Descripción
                    </label>
                    <textarea name="descripcion" class="form-control" rows="2" 
                              placeholder="Descripción de la nota de entrada (opcional)"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>
            </div>

            <hr>
            <h5><i class="fas fa-box me-2"></i> Productos</h5>

            <!-- Selector de productos -->
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label class="form-label">Producto</label>
                    <div class="input-group">
                        <select id="productoSelect" class="form-select">
                            <option value="">Seleccione un producto...</option>
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $p): ?>
                                    <option value="<?= $p['id_producto'] ?>" 
                                            data-descripcion="<?= htmlspecialchars($p['descripcion']) ?>"
                                            data-costo="<?= $p['costo_unitario'] ?>"
                                            data-stock="<?= $p['cantidad'] ?>"
                                        <?= (isset($_SESSION['nuevo_producto_id']) && $_SESSION['nuevo_producto_id'] == $p['id_producto']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['descripcion']) ?> (Stock: <?= $p['cantidad'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No hay productos disponibles</option>
                            <?php endif; ?>
                        </select>
                        <!-- 🔥 REDIRECCIÓN EN LUGAR DE MODAL -->
                        <a href="?url=productos&type=create&return=notaentrada" 
                           class="btn btn-gold" 
                           title="Registrar nuevo producto">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                    <?php unset($_SESSION['nuevo_producto_id']); ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" id="cantidadInput" class="form-control" 
                           min="1" value="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Costo Unitario</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="costoInput" class="form-control" 
                               step="0.01" min="0" value="0.00">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-gold w-100" onclick="agregarProducto()">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                </div>
            </div>

            <!-- Tabla de detalles -->
            <div class="table-responsive">
                <table class="table table-hover" id="tablaDetalles">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Costo Unitario</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="detallesBody">
                        <tr>
                            <td colspan="6" class="text-center">No hay productos agregados</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total:</th>
                            <th id="totalNota">$0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- 🔥 CAMPOS OCULTOS PARA DETALLES (SIN JSON) -->
            <div id="detallesContainer"></div>

            <div class="mt-4">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Guardar Nota de Entrada
                </button>
                <a href="?url=notaentrada&type=list" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ============================================ -->
<!-- SCRIPTS -->
<!-- ============================================ -->
<script>
// ==========================================
// FUNCIONES DE LA NOTA DE ENTRADA
// ==========================================
let detalles = [];

function agregarProducto() {
    const select = document.getElementById('productoSelect');
    const cantidad = parseInt(document.getElementById('cantidadInput').value);
    const costo = parseFloat(document.getElementById('costoInput').value);
    
    if (!select.value) {
        alert('Seleccione un producto');
        return;
    }
    if (!cantidad || cantidad < 1) {
        alert('Ingrese una cantidad válida');
        return;
    }
    if (!costo || costo < 0) {
        alert('Ingrese un costo unitario válido');
        return;
    }
    
    const option = select.options[select.selectedIndex];
    const producto = {
        id_producto: parseInt(select.value),
        descripcion: option.dataset.descripcion,
        cantidad: cantidad,
        costo_unitario: costo,
        subtotal: cantidad * costo
    };
    
    detalles.push(producto);
    actualizarTabla();
    actualizarCamposOcultos();
    select.value = '';
    document.getElementById('cantidadInput').value = 1;
    document.getElementById('costoInput').value = '0.00';
}

function eliminarProducto(index) {
    detalles.splice(index, 1);
    actualizarTabla();
    actualizarCamposOcultos();
}

function actualizarTabla() {
    const tbody = document.getElementById('detallesBody');
    let total = 0;
    
    if (detalles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay productos agregados</td></tr>';
        document.getElementById('totalNota').textContent = '$0.00';
        return;
    }
    
    let html = '';
    detalles.forEach((d, i) => {
        total += d.subtotal;
        html += `
            <tr>
                <td>${d.id_producto}</td>
                <td>${d.descripcion}</td>
                <td>${d.cantidad}</td>
                <td>$${d.costo_unitario.toFixed(2)}</td>
                <td>$${d.subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${i})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalNota').textContent = '$' + total.toFixed(2);
}

// ==========================================
// ACTUALIZAR CAMPOS OCULTOS 
// ==========================================
function actualizarCamposOcultos() {
    const container = document.getElementById('detallesContainer');
    container.innerHTML = '';
    
    detalles.forEach((d) => {
        const inputProducto = document.createElement('input');
        inputProducto.type = 'hidden';
        inputProducto.name = 'detalle_producto[]';
        inputProducto.value = d.id_producto;
        container.appendChild(inputProducto);
        
        const inputCantidad = document.createElement('input');
        inputCantidad.type = 'hidden';
        inputCantidad.name = 'detalle_cantidad[]';
        inputCantidad.value = d.cantidad;
        container.appendChild(inputCantidad);
        
        const inputCosto = document.createElement('input');
        inputCosto.type = 'hidden';
        inputCosto.name = 'detalle_costo[]';
        inputCosto.value = d.costo_unitario;
        container.appendChild(inputCosto);
    });
}

document.getElementById('productoSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const costo = selected.getAttribute('data-costo') || 0;
    document.getElementById('costoInput').value = parseFloat(costo).toFixed(2);
});

document.getElementById('notaEntradaForm').addEventListener('submit', function(e) {
    if (detalles.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un producto.');
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>