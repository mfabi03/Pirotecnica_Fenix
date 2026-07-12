<?php
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-sign-out-alt text-gold me-2"></i> Registrar Nota de Salida
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Complete los campos para registrar una nueva nota de salida
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=notasalida&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($_SESSION['mensaje_rapido'])): ?>
                <div class="alert <?= ($_SESSION['tipo_rapido'] ?? 'success') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-<?= ($_SESSION['tipo_rapido'] ?? 'success') === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                        <?= htmlspecialchars($_SESSION['mensaje_rapido']) ?>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                <?php unset($_SESSION['mensaje_rapido'], $_SESSION['tipo_rapido']); ?>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span><?= $error ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ==========================================
                 FORMULARIO PRINCIPAL
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-edit me-2"></i> Datos de la Nota de Salida
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="?url=notasalida&type=store" id="formNotaSalida">
                        
                        <!-- ==========================================
                        DATOS DEL CLIENTE Y ENCARGADO
                        ========================================== -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #f39c12;">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3" style="color: #f39c12;">
                                            <i class="fas fa-user me-2"></i> Cliente
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Cliente <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select name="id_cliente" id="id_cliente" class="form-select" required style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px 0 0 12px; color: #ffffff; padding: 10px 16px;">
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
                                                <a href="?url=clientes&type=register&return=notasalida" 
                                                   class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 16px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                                                   title="Registrar nuevo cliente">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            </div>
                                            <?php unset($_SESSION['nuevo_cliente_id']); ?>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Encargado</label>
                                            <input type="text" class="form-control" value="<?= $_SESSION['usuario_nombre'] ?? 'Usuario actual' ?>" disabled style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: rgba(255,255,255,0.4); padding: 10px 16px;">
                                            <small style="color: rgba(255,255,255,0.2); font-size: 0.7rem;">La persona encargada se registra automáticamente</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #28a745;">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3" style="color: #28a745;">
                                            <i class="fas fa-boxes me-2"></i> Agregar Productos
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Producto <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select id="producto" class="form-select" required style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px 0 0 12px; color: #ffffff; padding: 10px 16px;">
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
                                                <a href="?url=productos&type=create&return=notasalida" 
                                                   class="btn" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 16px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                                                   title="Registrar nuevo producto">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            </div>
                                            <?php unset($_SESSION['nuevo_producto_id']); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Categoría</label>
                                            <input type="text" id="categoria" class="form-control" value="" readonly style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: rgba(255,255,255,0.3); padding: 10px 16px;">
                                        </div>
                                        <div class="row">
                                            <div class="col-8">
                                                <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Cantidad <span class="text-danger">*</span></label>
                                                <input type="number" id="cantidad" class="form-control" min="1" value="1" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 10px 16px;">
                                            </div>
                                            <div class="col-4 d-flex align-items-end">
                                                <button type="button" id="btnAgregar" class="btn w-100" style="background: #28a745; border: none; color: #fff; border-radius: 12px; padding: 10px; font-weight: 600; transition: all 0.3s ease;">
                                                    <i class="fas fa-plus me-1"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                        <small style="color: rgba(255,255,255,0.2); font-size: 0.7rem;">Stock disponible: <span id="stockActual" style="color: rgba(255,255,255,0.4);">0</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                        TABLA DE PRODUCTOS AGREGADOS
                        ========================================== -->
                        <div class="card mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px;">
                            <div class="card-header" style="background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.04); border-radius: 12px 12px 0 0; padding: 12px 20px;">
                                <span style="color: rgb(12, 2, 2); font-weight: 600; font-size: 0.85rem;">
                                    <i class="fas fa-list me-2"></i> Productos agregados
                                </span>
                            </div>
                            <div class="card-body" style="padding: 0;">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle m-0" id="detallesTabla">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 py-2" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Producto</th>
                                                <th class="py-2" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Categoría</th>
                                                <th class="py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Cantidad</th>
                                                <th class="pe-4 py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyDetalles">
                                            <tr id="filaVacia">
                                                <td colspan="4" class="text-center py-3" style="color: rgba(255,255,255,0.2);">
                                                    <i class="fas fa-box-open me-2"></i> No hay productos agregados
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                        CAMPOS OCULTOS PARA DETALLES
                        ========================================== -->
                        <div id="detallesContainer"></div>

                        <!-- ==========================================
                        BOTONES DE ACCIÓN
                        ========================================== -->
                        <div class="mt-4 text-end" style="border-top: 1px solid rgba(255,255,255,0.04); padding-top: 20px;">
                            <a href="?url=notasalida&type=list" class="btn" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                                <i class="fas fa-save me-2"></i> Guardar Nota de Salida
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
            <td class="ps-4" style="color: rgba(255,255,255,0.8);">${nombreProducto}</td>
            <td style="color: rgba(255,255,255,0.5);">${categoria}</td>
            <td class="text-center" style="color: rgba(255,255,255,0.8);">${cantidad}</td>
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

    // ==========================================
    // 4. ACTUALIZAR CAMPOS OCULTOS
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

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>