
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida - Pirotecnia Fénix</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/welcome.css">
</head>
<body>

    <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100">
        
        <div class="card shadow w-100" style="max-width: 600px;">
            <div class="card-body text-center py-5">
                
                <img src="http://localhost:3000/assets/imagenes/logo.png" alt="Logo de Pirotecnia Fénix" class="mb-4" style="width: 150px;">
                <h1 class="display-5 text-dark mb-3">¡Pirotecnia Fénix!</h1>
                <p class="lead text-muted">Sistema de gestión integral para control de mercancia.</p>
                
                <hr class="my-4">
                
                
                
                <div class="d-grid gap-3 d-md-block">
                    <a href="?url=configuracion&type=usuarios" class="btn btn-warning btn-lg mx-2">Inicio de Sesión</a>
                    <a href="?url=configuracion&type=roles" class="btn btn-secondary btn-lg mx-2">Salir</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>