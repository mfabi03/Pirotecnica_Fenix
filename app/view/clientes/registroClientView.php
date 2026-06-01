<?php
// Formulario de Registro de Clientes del Sistema , con validaciones de patrones para cada campo y mensajes de error personalizados.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente - Pirotecnia Fénix</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light"> <div class="container mt-5 mb-4">
<div class="row justify-content-center">
<div class="col-md-7"> <div class="card shadow border-danger rounded-lg">
    
<div class="card-header bg-danger text-white text-center py-3">
     <h2 class="mb-0 h4 font-weight-bold">➕ Registrar Nuevo Cliente</h2>
</div>
                
<div class="card-body p-4">
                    
    <form method="post" class="form-box">
                        
<div class="row">
<div class="col-md-6 mb-3">
    <label for="cedula_rif" class="form-label font-weight-bold text-secondary">Cédula o RIF</label>
        <input type="text" id="cedula_rif" name="cedula_rif" class="form-control" placeholder="Ej: V26123456" required>
</div>
                            
<div class="col-md-6 mb-3">
    <label for="numero_telefono" class="form-label font-weight-bold text-secondary">Número de Teléfono</label>
        <input type="tel" id="numero_telefono" name="numero_telefono" class="form-control" placeholder="Ej: 04125556677" required>
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3"> 
    <label for="nombre_cliente" class="form-label font-weight-bold text-secondary">Nombres</label>
        <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control" pattern="[A-Za-z ]{3,30}" title="Solo letras." required placeholder="Nombres del cliente">
</div>
                            
<div class="col-md-6 mb-3">
    <label for="apellido_cliente" class="form-label font-weight-bold text-secondary">Apellidos</label>
        <input type="text" id="apellido_cliente" name="apellido_cliente" class="form-control" pattern="[A-Za-z ]{3,30}" title="Solo letras." required placeholder="Apellidos del cliente">
</div>
</div>

<div class="mb-3">
    <label for="fecha_nac" class="form-label font-weight-bold text-secondary">Fecha de Nacimiento</label>
        <input type="date" id="fecha_nac" name="fecha_nac" class="form-control" required>
<div class="form-text text-muted">Nota: El sistema verificará que el cliente sea mayor de 18 años.</div>
</div>

<div class="mb-4">
    <label for="direccion" class="form-label font-weight-bold text-secondary">Dirección de Habitación</label>
        <textarea id="direccion" name="direccion" class="form-control" rows="2" placeholder="Dirección detallada..." required></textarea>
</div>

<div class="d-grid gap-2">
        <button type="submit" class="btn btn-dark btn-lg font-weight-bold shadow-sm">
             Registrar Cliente
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