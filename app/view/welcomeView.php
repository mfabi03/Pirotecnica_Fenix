<?php
// CAMBIO: Ajuste de enlace en menú - Panel de control de usuarios
// Vista de bienvenida para el módulo de seguridad/usuarios
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="col-md-9 col-lg-10">
    <div class="card card-custom p-4 mb-4 bg-white shadow-sm rounded-4 border-0">
        <div class="row align-items-center g-3">
            <div class="col-md-12 text-center">
                <h1 class="display-4 fw-bold mb-2">
                    <span style="color: #DAA520;">💥</span> 
                    <span style="color: #000000;">Sistema Pirotecnia Fénix</span>
                </h1>
                <p class="lead text-warning fw-bold" style="color: #DAA520 !important;">
                    ¡Bienvenido al Panel de Control de Usuarios!
                </p>
                <p class="text-muted">Selecciona a continuación la acción que deseas ejecutar en el sistema.</p>
            </div>
        </div>
    </div>

    <!-- ==========================================
    TARJETAS DE ACCIONES
    ========================================== -->
    <div class="row justify-content-center g-4">
        
        <!-- Tarjeta 1: Listado Global -->
        <div class="col-md-4">
            <div class="card card-custom shadow border-0 p-3 text-center h-100">
                <div class="card-body">
                    <div class="text-primary display-4 mb-3">
                        <i class="fas fa-table"></i>
                    </div>
                    <h3 class="h5 fw-bold card-title">Listado Global</h3>
                    <p class="card-text text-muted small mb-4">
                        Visualiza, edita o elimina los registros existentes de usuarios.
                    </p>
                    <!-- CAMBIO: Ajuste de enlace en menú - URL correcta para usuarios -->
                    <a href="?url=seguridad&type=list" class="btn btn-primary w-100 fw-bold shadow-sm">
                        <i class="fas fa-search me-1"></i> Consultar Lista
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Nuevo Registro -->
        <div class="col-md-4">
            <div class="card card-custom shadow border-0 p-3 text-center h-100">
                <div class="card-body">
                    <div class="text-success display-4 mb-3">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3 class="h5 fw-bold card-title">Nuevo Registro</h3>
                    <p class="card-text text-muted small mb-4">
                        Abre el formulario para ingresar un nuevo usuario al sistema.
                    </p>
                    <a href="?url=seguridad&type=register" class="btn btn-success w-100 fw-bold shadow-sm">
                        <i class="fas fa-plus me-1"></i> Registrar Nuevo
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Búsqueda Avanzada -->
        <div class="col-md-4">
            <div class="card card-custom shadow border-0 p-3 text-center h-100">
                <div class="card-body">
                    <div class="text-warning display-4 mb-3">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="h5 fw-bold card-title">Búsqueda Avanzada</h3>
                    <p class="card-text text-muted small mb-4">
                        Localiza un registro específico de manera inmediata mediante filtros.
                    </p>
                    <a href="?url=seguridad&type=search" class="btn btn-gold w-100 fw-bold shadow-sm">
                        <i class="fas fa-search me-1"></i> Buscar Usuario
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>