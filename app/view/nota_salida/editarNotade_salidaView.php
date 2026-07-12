<?php
// app/view/notasalida/editarNotaSalida.php
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
                            <i class="fas fa-edit text-gold me-2"></i> Editar Nota de Salida
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Modifique los datos de la nota de salida
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
            <?php if (isset($error)): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span><?= $error ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span><?= htmlspecialchars($_GET['error']) ?></span>
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
                        <i class="fas fa-edit me-2"></i> Editar Nota de Salida
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="?url=notasalida&type=update" id="notaSalidaForm">
                        <input type="hidden" name="id_nota_salida" value="<?= $nota['id_nota_salida'] ?? '' ?>">

                        <!-- ==========================================
                        DATOS DEL CLIENTE Y ENCARGADO
                        ========================================== -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #f39c12;">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3" style="color: #f39c12;">
                                            <i class="fas fa-user me-2"></i> Cliente
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Cliente <span class="text-danger">*</span></label>
                                            <select name="id_cliente" class="form-select" required style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 10px 16px;">
                                                <option value="">Seleccione un cliente...</option>
                                                <?php if (!empty($clientes) && is_array($clientes)): ?>
                                                    <?php 
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
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #28a745;">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3" style="color: #28a745;">
                                            <i class="fas fa-user-tie me-2"></i> Encargado
                                        </h6>
                                        <input type="text" class="form-control" 
                                               value="<?= $_SESSION['usuario_nombre'] ?? 'Usuario actual' ?>" 
                                               disabled readonly
                                               style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: rgba(255,255,255,0.3); padding: 10px 16px;">
                                        <small style="color: rgba(255,255,255,0.2); font-size: 0.7rem;">La persona encargada se registra automáticamente</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                        TABLA DE PRODUCTOS - CORREGIDA
                        ========================================== -->
                        <div class="card mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px;">
                            <div class="card-header" style="background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.04); border-radius: 12px 12px 0 0; padding: 12px 20px;">
                                <span style="color: rgb(5, 5, 5); font-weight: 600; font-size: 0.85rem;">
                                    <i class="fas fa-box me-2"></i> Productos
                                </span>
                            </div>
                            <div class="card-body" style="padding: 0;">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle m-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 py-2" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Producto</th>
                                                <th class="py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Cantidad</th>
                                                <th class="pe-4 py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detallesBody">
                                            <?php if (!empty($nota['detalles']) && is_array($nota['detalles'])): ?>
                                                <?php foreach ($nota['detalles'] as $index => $d): ?>
                                                    <tr data-index="<?= $index ?>">
                                                        <td class="ps-4" style="color: rgba(255,255,255,0.8);">
                                                            <?php 
                                                                // ✅ CORREGIDO: Usar el campo correcto para el nombre del producto
                                                                $nombreProducto = $d['descripcion'] ?? $d['nombre_producto'] ?? $d['producto_descripcion'] ?? 'Producto sin nombre';
                                                                echo htmlspecialchars($nombreProducto);
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="number" name="detalles[<?= $index ?>][cantidad]" 
                                                                   class="form-control form-control-sm" 
                                                                   value="<?= $d['cantidad'] ?>" min="1" 
                                                                   style="width: 80px; display: inline-block; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 6px 12px; text-align: center;">
                                                            <input type="hidden" name="detalles[<?= $index ?>][id_producto]" 
                                                                   value="<?= $d['id_producto'] ?>">
                                                        </td>
                                                        <td class="pe-4 text-center">
                                                            <button type="button" class="btn-action-circle btn-delete" 
                                                                    onclick="eliminarProducto(this)" title="Eliminar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center py-3" style="color: rgba(255,255,255,0.2);">
                                                        <i class="fas fa-box-open me-2"></i> No hay productos en esta nota
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                        AGREGAR PRODUCTO - CORREGIDO
                        ========================================== -->
                        <div class="card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #28a745;">
                            <div class="card-body">
                                <h6 style="color: #28a745; font-weight: 600; font-size: 0.85rem; margin-bottom: 12px;">
                                    <i class="fas fa-plus-circle me-2"></i> Agregar Producto
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <select id="productoSelect" class="form-select" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 10px 16px;">
                                            <option value="">Seleccione un producto...</option>
                                            <?php if (!empty($productos) && is_array($productos)): ?>
                                                <?php foreach ($productos as $p): ?>
                                                    <option value="<?= $p['id_producto'] ?>" 
                                                            data-descripcion="<?= htmlspecialchars($p['descripcion'] ?? $p['nombre_producto'] ?? 'Producto sin nombre') ?>"
                                                            data-stock="<?= $p['cantidad'] ?? 0 ?>">
                                                        <?= htmlspecialchars($p['descripcion'] ?? $p['nombre_producto'] ?? 'Producto sin nombre') ?> 
                                                        (Stock: <?= $p['cantidad'] ?? 0 ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="">No hay productos disponibles</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" id="cantidadInput" class="form-control" min="1" value="1" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 10px 16px;">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-dark-gold" onclick="agregarProducto()" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 20px; border-radius: 50px; transition: all 0.3s ease;">
                                            <i class="fas fa-plus me-1"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="detalles_json" id="detallesJson" value="[]">

                        <!-- ==========================================
                        BOTONES DE ACCIÓN
                        ========================================== -->
                        <div class="mt-4 text-end" style="border-top: 1px solid rgba(255,255,255,0.04); padding-top: 20px;">
                            <a href="?url=notasalida&type=list" class="btn" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
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
    
    // ✅ OBTENER DESCRIPCIÓN CORRECTAMENTE
    let descripcion = option.dataset.descripcion;
    
    // Si no tiene data-descripcion, usar el texto del option
    if (!descripcion || descripcion === 'Producto sin nombre') {
        const textoCompleto = option.text;
        descripcion = textoCompleto.replace(/\s*\(Stock:.*\)\s*$/, '').trim();
    }
    
    const producto = {
        id_producto: parseInt(select.value),
        descripcion: descripcion,
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
    
    // Eliminar fila vacía si existe
    const filaVacia = tbody.querySelector('td[colspan="3"]');
    if (filaVacia) {
        filaVacia.closest('tr').remove();
    }
    
    detalles.forEach((d, index) => {
        // Verificar si el producto ya existe
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
        
        // Crear nueva fila
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-4" style="color: rgba(255,255,255,0.8);">${d.descripcion}</td>
            <td class="text-center">
                <input type="number" name="detalles[${index}][cantidad]" 
                       class="form-control form-control-sm" 
                       value="${d.cantidad}" min="1" 
                       style="width: 80px; display: inline-block; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 6px 12px; text-align: center;">
                <input type="hidden" name="detalles[${index}][id_producto]" value="${d.id_producto}">
            </td>
            <td class="pe-4 text-center">
                <button type="button" class="btn-action-circle btn-delete" onclick="eliminarProducto(this)" title="Eliminar" style="width: 32px; height: 32px; font-size: 0.75rem;">
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

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>