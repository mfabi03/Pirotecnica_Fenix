<?php
//Formulario de Registro de Nota de Salida. Este formulario permite registrar una nueva nota de salida, asociándola a un 
//cliente existente y seleccionando los productos que se están vendiendo, con sus respectivas cantidades.
//El formulario incluye validaciones para asegurar que se ingresen datos correctos y completos antes de procesar la venta.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Salida - Pirotecnia Fénix</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light"> <div class="container mt-5 mb-5">
<div class="row justify-content-center">
<div class="col-md-9"> <div class="card shadow border-dark rounded-lg">
                
<div class="card-header bg-dark text-white text-center py-3">
    <h2 class="mb-0 h4 font-weight-bold">🧾 Registrar Nota de Salida </h2>
</div>
                
<div class="card-body p-4">
    <form method="post" class="form-box">
                        
    <h5 class="text-primary font-weight-bold mb-3">📋 Datos del Comprobante</h5>
                        
<div class="row mb-4">
<div class="col-md-4 mb-2">
    <label for="fecha_venta" class="form-label font-weight-bold text-secondary">Fecha de Emisión</label>
            <input type="date" id="fecha_venta" name="fecha_venta" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
</div>
                            
<div class="col-md-8 mb-2">
    <label for="cliente_venta" class="form-label font-weight-bold text-secondary">Cliente (Comprador)</label>
            <select id="cliente_venta" name="cliente_venta" class="form-select" required>
                    <option value="" selected disabled>Seleccione el cliente...</option>
                    <option value="1">V-12345678 - Juan Pérez</option>
                    <option value="2">V-87654321 - María Rodríguez</option>
                    <option value="3">J-99999999 - Inversiones Fénix C.A.</option>
             </select>
</div>
        </div>
                        
<hr class="my-4"> <h5 class="text-danger font-weight-bold mb-3">🔥 Productos a cancelar </h5>
                        
<div class="row mb-3 bg-white p-3 rounded border mx-1 shadow-sm">
<div class="col-md-6 mb-2">
        <label for="producto_venta" class="form-label font-weight-bold text-secondary">Seleccione Producto</label>
                <select id="producto_venta" class="form-select">
                        <option value="" selected disabled>Buscar producto...</option>
                        <option value="10.50">Torta Misil 100 Tiros ($10.50)</option>
                        <option value="2.00">Caja de Cebollitas x12 ($2.00)</option>
                        <option value="5.00">Luces de Bengala Grandes ($5.00)</option>
                </select>
</div>
                            
<div class="col-md-3 mb-2">
    <label for="cantidad_venta" class="form-label font-weight-bold text-secondary">Cantidad</label>
            <input type="number" id="cantidad_venta" class="form-control" min="1" value="1">
</div>
                            
<div class="col-md-3 mb-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger w-100 font-weight-bold">➕ Agregar</button>
</div>
        </div>

<div class="table-responsive mb-4">
        <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-secondary">
                        <tr>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
</thead>
        <tbody>
                        <tr>
                            <td class="text-start">Torta Misil 100 Tiros</td>
                            <td>$10.50</td>
                            <td>2</td>
                            <td class="font-weight-bold">$21.00</td>
                        </tr>
<tr class="table-light">
    <td colspan="3" class="text-end font-weight-bold text-dark h5">Total a Pagar:</td>
        <td class="font-weight-bold text-danger h5">$21.00</td>
</tr>
</tbody>
    </table>
        </div>

<div class="d-grid gap-2">
    <button type="submit" class="btn btn-dark btn-lg font-weight-bold shadow-sm">
            💾 Procesar y Guardar Nota de Salida
    </button>
                            
<a href="?url=user" class="btn btn-outline-secondary btn-sm mt-2">
            Volver al Menú Principal
</a>
        </div>

                    </form> </div> </div> </div> 
    </div> 
</div>

<div class="container text-center mb-5 col-md-9">

    <?php 
    // Si la variable $result tiene información la muestra aquí abajo
    if(isset($result)) { 
        echo '<div class="alert alert-secondary shadow-sm py-2"><strong>Resultado:</strong> ' . $result . '</div>'; 
    } else { 
        echo ""; 
    } 
    ?>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>