<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Pirotecnica Fénix</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>

<!-- ==========================================
NAVBAR - CORREGIDO
========================================== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top navbar-fenix">
    <div class="container-fluid">
        <!-- 🔥 LOGO CON TAMAÑO CORRECTO -->
        <a class="navbar-brand d-flex align-items-center navbar-logo-group" href="?url=main">
            <img src="assets/imagenes/productos/imgs/logo.png" alt="Logo" class="logo-navbar">
            <span class="brand-title">
                <span class="brand-orange">Sistema</span> Pirotecnica Fénix
            </span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <span class="nav-link text-white small">
                        <i class="fas fa-user me-1"></i> 
                        <?= $_SESSION['usuario_nombre'] ?? 'Invitado' ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="?url=logout" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ==========================================
CONTENEDOR PRINCIPAL
========================================== -->
<div class="container-fluid main-container">
    <div class="row">
        
        <!-- ==========================================
        SIDEBAR - MENÚ DE NAVEGACIÓN CON ESTILO BOOTSTRAP
        ========================================== -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-3">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Navegación</h6>
                    
                    <div class="list-group list-group-flush">
                        
                        <a href="?url=main" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= ($_GET['url'] ?? 'main') == 'main' ? 'active' : '' ?>">
                            <i class="fas fa-home w-20px"></i> Inicio
                        </a>
                        
                        <a href="?url=proveedores" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= ($_GET['url'] ?? '') == 'proveedores' ? 'active' : '' ?>">
                            <i class="fas fa-truck w-20px"></i> Proveedores
                        </a>
                        
                        <a href="?url=clientes" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= ($_GET['url'] ?? '') == 'clientes' ? 'active' : '' ?>">
                            <i class="fas fa-users w-20px"></i> Clientes
                        </a>
                        
                        <a href="?url=productos" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= ($_GET['url'] ?? '') == 'productos' ? 'active' : '' ?>">
                            <i class="fas fa-box w-20px"></i> Productos
                        </a>
                        
                        <!-- NOTAS -->
                        <div class="dropdown w-100">
                            <a class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 text-decoration-none" 
                               href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-file-invoice w-20px"></i> Notas
                            </a>
                            <ul class="dropdown-menu w-100 border-0 shadow-sm rounded-3">
                                <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=notaentrada"><i class="fas fa-sign-in-alt me-2"></i> Nota de Entrada</a></li>
                                <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=notasalida"><i class="fas fa-sign-out-alt me-2"></i> Nota de Salida</a></li>
                            </ul>
                        </div>
                        
                        <!-- CONFIGURACIÓN -->
                        <div class="dropdown w-100">
                            <a class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 text-decoration-none" 
                               href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-cog w-20px"></i> Configuración
                            </a>
                            <ul class="dropdown-menu w-100 border-0 shadow-sm rounded-3">
                                <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=configuracion&type=list"><i class="fas fa-tags me-2"></i> Categorías</a></li>
                            </ul>
                        </div>
                        
                        <!-- SEGURIDAD -->
                        <div class="dropdown w-100">
                            <a class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 text-decoration-none" 
                               href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-lock w-20px"></i> Seguridad
                            </a>
                            <ul class="dropdown-menu w-100 border-0 shadow-sm rounded-3">
                                <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=seguridad&type=usuarios"><i class="fas fa-users-cog me-2"></i> Usuarios</a></li>
                                <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=seguridad&type=roles"><i class="fas fa-user-shield me-2"></i> Roles / Permisos</a></li>
                            </ul>
                        </div>
                        
                        <a href="?url=reportes" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= ($_GET['url'] ?? '') == 'reportes' ? 'active' : '' ?>">
                            <i class="fas fa-file-alt w-20px"></i> Reportes
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-md-9 col-lg-10">
            <div class="content-wrapper p-3">
                <!-- Las vistas se inyectan aquí -->