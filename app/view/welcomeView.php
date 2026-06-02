<?php 
//  Menú Principal / Panel de Control de Usuarios del Sistema  Pirotecnia Fénix,
//  con un diseño moderno y atractivo utilizando Bootstrap. La vista presenta tres opciones principales : 

// Consultar Lista, Registrar Nuevo y Buscar Usuario, cada una representada por una tarjeta con íconos y descripciones claras. El diseño es responsivo 

//y utiliza colores llamativos para destacar cada opción, invitando al usuario a interactuar con el sistema de manera intuitiva.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Pirotecnia Fénix</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light"> <div class="container mt-5"> 
    
    <div class="row justify-content-center mb-5">
        <div class="col-md-10 text-center bg-dark text-white p-5 rounded shadow-lg">
            <h1 class="display-5 font-weight-bold mb-2">💥 Sistema Pirotecnia Fénix</h1>
            <p class="lead text-warning font-weight-bold">¡Bienvenido al Panel de Control de Usuarios!</p>
            <div class="text-muted small">Selecciona a continuación la acción que deseas ejecutar en el sistema.</div>
        </div>
    </div>

    <div class="row justify-content-center gap-4">
        
        <div class="col-md-3 card shadow border-0 p-3 text-center">
            <div class="card-body">
                <div class="text-primary display-4 mb-3">
                    <i class="bi bi-table"></i>
                </div>
                <h3 class="h5 font-weight-bold card-title">Listado Global</h3>
                <p class="card-text text-muted small mb-4">Visualiza, edita o elimina los registros existentes </p>
                <a href="?url=user&type=list" class="btn btn-primary w-100 font-weight-bold shadow-sm">
                    🔍 Consultar Lista
                </a>
            </div>
        </div>

        <div class="col-md-3 card shadow border-0 p-3 text-center">
            <div class="card-body">
                <div class="text-success display-4 mb-3">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <h3 class="h5 font-weight-bold card-title">Nuevo Registro</h3>
                <p class="card-text text-muted small mb-4">Abre el formulario para ingresar un nuevo usuario al sistema.</p>
                <a href="?url=user&type=register" class="btn btn-success w-100 font-weight-bold shadow-sm">
                    ➕ Registrar Nuevo
                </a>
            </div>
        </div>

        <div class="col-md-3 card shadow border-0 p-3 text-center">
            <div class="card-body">
                <div class="text-warning display-4 mb-3">
                    <i class="bi bi-search-heart"></i>
                </div>
                <h3 class="h5 font-weight-bold card-title">Búsqueda Avanzada</h3>
                <p class="card-text text-muted small mb-4">Localiza un registro específico de manera inmediata mediante filtros.</p>
                <a href="?url=user&type=main" class="btn btn-warning text-dark w-100 font-weight-bold shadow-sm">
                    🔎 Buscar Usuario
                </a>
            </div>
        </div>

    </div> <div class="text-center text-muted mt-5 mb-4 small">
        © 2026 Pirotecnia Fénix C.A. - Todos los derechos reservados.
    </div>

</div> <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    


