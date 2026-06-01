<?php
//Este formulario permite registrar un nuevo proveedor, ingresando datos como el RIF de la empresa, 
// razón social, teléfono, correo electrónico y dirección fiscal. Además, se incluyen campos para registrar
//  los datos del encargado o contacto principal del proveedor. El formulario cuenta con validaciones para asegurar que se ingresen datos correctos y completos antes de procesar el registro.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Proveedor - Pirotecnia Fénix</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light"> <div class="container mt-5 mb-4">
<div class="row justify-content-center">
<div class="col-md-7"> <div class="card shadow border-info rounded-lg">
                
<div class="card-header bg-info text-dark text-center py-3">
        <h2 class="mb-0 h4 font-weight-bold">🏢 Registrar Nuevo Proveedor</h2>
</div>
                
<div class="card-body p-4">
                    
    <form method="post" class="form-box">
                        
        <h5 class="text-info font-weight-bold mb-3">🏢 Datos de la Empresa</h5>
                        
<div class="row">
<div class="col-md-6 mb-3">
    <label for="rif_proveedor" class="form-label font-weight-bold text-secondary">RIF de la Empresa</label>
            <input type="text" id="rif_proveedor" name="rif_proveedor" class="form-control" placeholder="Ej: J-12345678-9" required>
</div>
                            
<div class="col-md-6 mb-3">
    <label for="razon_social" class="form-label font-weight-bold text-secondary">Razón Social</label>
            <input type="text" id="razon_social" name="razon_social" class="form-control" placeholder="Ej: Fuegos Artificiales C.A." required>
</div>
        </div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="telefono_empresa" class="form-label font-weight-bold text-secondary">Teléfono de la Empresa</label>
                <input type="tel" id="telefono_empresa" name="telefono_empresa" class="form-control" placeholder="Ej: 02512345678" required>
</div>
                            
<div class="col-md-6 mb-3">
    <label for="correo_empresa" class="form-label font-weight-bold text-secondary">Correo Electrónico</label>
            <input type="email" id="correo_empresa" name="correo_empresa" class="form-control" placeholder="proveedor@correo.com" required>
</div>
      </div>

<div class="mb-3">
    <label for="direccion_empresa" class="form-label font-weight-bold text-secondary">Dirección Fiscal</label>
            <textarea id="direccion_empresa" name="direccion_empresa" class="form-control" rows="2" placeholder="Dirección detallada de la empresa..." required></textarea>
</div>

<hr class="my-4"> <h5 class="text-secondary font-weight-bold mb-3">👤 Datos del Encargado (Contacto)</h5>
                        
<div class="row mb-4">
<div class="col-md-6 mb-3">
    <label for="nombre_encargado" class="form-label font-weight-bold text-secondary">Nombre del Encargado</label>
            <input type="text" id="nombre_encargado" name="nombre_encargado" class="form-control" placeholder="Ej: Carlos Mendoza" required>
</div>
                            
<div class="col-md-6 mb-3">
    <label for="telefono_encargado" class="form-label font-weight-bold text-secondary">Teléfono del Encargado</label>
            <input type="tel" id="telefono_encargado" name="telefono_encargado" class="form-control" placeholder="Ej: 04141234567" required>
</div>
        </div>

<div class="d-grid gap-2">
    <button type="submit" class="btn btn-info btn-lg font-weight-bold text-dark shadow-sm">
            💾 Registrar Proveedor
    </button>
                            
<a href="?url=user" class="btn btn-outline-secondary btn-sm mt-2">
            Menú Principal
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