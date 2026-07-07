<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pirotecnia Fénix</title>
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="../../pirotecnica_fenix/assets/bootstrap/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
    <!-- ✅ RUTA RELATIVA: fondo.jpg -->
    <img src="../../pirotecnica_fenix/assets/imagenes/fondo.jpg" 
         alt="Fondo de Pirotecnia Fénix" 
         class="position-fixed top-0 start-0 w-100 h-100 object-fit-cover opacity-100">
    
    <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100">
        
        <div class="card shadow w-100" style="max-width: 600px; background-color: rgba(0, 0, 0, 0.7); backdrop-filter: blur(0px);">
            <div class="card-body text-center py-5">
                
                <!-- ✅ RUTA RELATIVA: logo.png -->
                <img src="../../pirotecnica_fenix/assets/imagenes/logo.png" 
                     alt="Logo de Pirotecnia Fénix" 
                    class="rounded-circle" 
                style="width: 150px; height: 150px; object-fit: cover">
                <h1 class="display-5 text-white mb-3">¡Pirotecnica Fénix!</h1>
                <p class="lead text-white">Sistema de gestión integral para control de mercancia.</p>

                <hr class="my-4">
                
                <!-- ✅ RUTA RELATIVA: login -->
                <div class="d-grid gap-3 d-md-block">
                    <a href="?url=login" class="btn btn-warning btn-lg mx-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Inicio de Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>