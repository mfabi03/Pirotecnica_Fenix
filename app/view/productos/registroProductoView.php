<?php
// Formulario de Registro de Productos en el stock.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Producto - Pirotecnia Fénix</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light"> <div class="container mt-5 mb-4">
<div class="row justify-content-center">
<div class="col-md-7"> <div class="card shadow border-warning rounded-lg">
                
<div class="card-header bg-warning text-dark text-center py-3">
    <h2 class="mb-0 h4 font-weight-bold">📦 Registrar Nuevo Producto (Pirotecnia)</h2>
</div>
<div class="card-body p-4">
    <form method="post" class="form-box">
<div class="row">
<div class="col-md-6 mb-3">
     <label for="codigo_producto" class="form-label font-weight-bold text-secondary">Código del Producto</label>
            <input type="text" id="codigo_producto" name="codigo_producto" class="form-control" placeholder="Ej: ART-001" required>
</div>
                            
<div class="col-md-6 mb-3">
     <label for="categoria" class="form-label font-weight-bold text-secondary">Categoría</label>
            <select id="categoria" name="categoria" class="form-select" required>
                <option value="" selected disabled>Seleccione una opción...</option>
                <option value="Tortas">Tortas / traquitraquis</option>
                <option value="Luces">Luces de Bengala</option>
                <option value="Cohetes">Cohetes / Voladores</option>
                <option value="Frenos">Cebollitas / Menudencias</option>
</select>
        </div>
                </div>

<div class="mb-3">
        <label for="nombre_producto" class="form-label font-weight-bold text-secondary">Nombre / Descripción del Producto</label>
                <input type="text" id="nombre_producto" name="nombre_producto" class="form-control" placeholder="Ej: Torta Misil de 100 Tiros" required>
</div>

<div class="row">
<div class="col-md-6 mb-4">
        <label for="precio" class="form-label font-weight-bold text-secondary">Precio de Venta ($)</label>
                <input type="number" id="precio" name="precio" class="form-control" step="0.01" min="0" placeholder="Ej: 15.50" required>
 </div>
                            
<div class="col-md-6 mb-4">
        <label for="stock" class="form-label font-weight-bold text-secondary">Cantidad Inicial (Stock)</label>
                <input type="number" id="stock" name="stock" class="form-control" min="0" placeholder="Ej: 50" required>
</div>
   </div>

<div class="d-grid gap-2">
            <button type="submit" class="btn btn-warning btn-lg font-weight-bold text-dark shadow-sm">
                  💾 Registrar Producto
            </button>
                            
<a href="?url=user" class="btn btn-outline-secondary btn-sm mt-2">
           Volver al Menú Principal
</a>
    </div>

          </form> </div> </div> </div> 
    </div> 
</div>

<div class="container text-center mb-5 col-md-7">
    <?php 
    // Si la variable $result tiene información la muestra aquí de forma ordenada
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