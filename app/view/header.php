<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Pirotecnica Fénix</title>
    
    <base href="/pirotecnica_fenix/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php
    // Versionado automático del CSS usando filemtime para cache-busting controlado
    $cssPath = dirname(__DIR__, 2) . '/assets/css/estilo.css';
    $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
    ?>
    <link rel="stylesheet" href="/pirotecnica_fenix/assets/css/estilo.css?v=<?= $cssVersion ?>">
</head>
<body>

<!-- ==========================================
NAVBAR - CORREGIDO
========================================== -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="min-height: 70px; padding: 5px 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6); z-index: 1050; background: linear-gradient(180deg, #12121f 0%, #08080f 100%); border-bottom: 2px solid rgba(218, 165, 32, 0.4);">
    <div class="container-fluid">
        
        <a class="navbar-brand d-flex align-items-center" href="?url=main" style="gap: 12px; transition: transform 0.2s ease;">
            <div style="width: 44px; height: 44px; background: #111116; border: 1.5px solid #DAA520; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 12px rgba(218, 165, 32, 0.25); padding: 4px;">
                <img src="assets/imagenes/productos/imgs/logo.png" alt="Logo" 
                    style="width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0px 0px 1px rgba(255,255,255,0.1));">
            </div>
            
            <span style="font-size: 1.15rem; font-weight: 700; letter-spacing: 0.8px; color: #ffffff; white-space: nowrap; font-family: 'Poppins', 'Segoe UI', sans-serif;">
                <span style="color: #DAA520; font-weight: 800;">SISTEMA</span> PIROTECNICA FÉNIX
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <span class="nav-link text-white" style="font-size: 0.9rem;">
                        <i class="fas fa-user me-2" style="color: #DAA520;"></i> 
                        <?= $_SESSION['usuario_nombre'] ?? 'Invitado' ?>
                    </span>
                </li>
                <li class="nav-item d-flex align-items-center ms-lg-3">
                    <a href="?url=logout" class="btn btn-sm" style="color: #DAA520; border: 1px solid rgba(218, 165, 32, 0.5); background-color: rgba(218, 165, 32, 0.03); font-weight: 500; letter-spacing: 0.5px; padding: 6px 16px; border-radius: 4px; transition: all 0.3s ease;">
                        <i class="fas fa-sign-out-alt me-2" style="font-size: 0.9rem;"></i>Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ==========================================
CONTENEDOR PRINCIPAL
========================================== -->
<div class="container-fluid" style="margin-top: 70px;">
    <div class="row">
        
        <!-- ==========================================
        SIDEBAR - MENÚ DE NAVEGACIÓN CON ESTILO BOOTSTRAP
        ========================================== -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-3">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Navegación</h6>
                    <?php $currentUrl = $_GET['url'] ?? 'main'; ?>
                    <div class="list-group list-group-flush">
                        
                        <a href="?url=main" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= $currentUrl == 'main' ? 'active' : '' ?>"
                           style="border: none; <?= $currentUrl == 'main' ? 'background: #000; color: #DAA520;' : '' ?>">
                            <i class="fas fa-home" style="width: 20px;"></i> Inicio
                        </a>
                        
                        <a href="?url=proveedores" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= $currentUrl == 'proveedores' ? 'active' : '' ?>"
                           style="border: none; <?= $currentUrl == 'proveedores' ? 'background: #000; color: #DAA520;' : '' ?>">
                            <i class="fas fa-truck" style="width: 20px;"></i> Proveedores
                        </a>
                        
                        <a href="?url=clientes" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= $currentUrl == 'clientes' ? 'active' : '' ?>"
                           style="border: none; <?= $currentUrl == 'clientes' ? 'background: #000; color: #DAA520;' : '' ?>">
                            <i class="fas fa-users" style="width: 20px;"></i> Clientes
                        </a>
                        
                        <a href="?url=productos" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= $currentUrl == 'productos' ? 'active' : '' ?>"
                           style="border: none; <?= $currentUrl == 'productos' ? 'background: #000; color: #DAA520;' : '' ?>">
                            <i class="fas fa-box" style="width: 20px;"></i> Productos
                        </a>
                        
                        <!-- NOTAS -->
                        <?php $isNotasActive = in_array($currentUrl, ['notaentrada', 'notasalida']); ?>
                        <div class="dropdown w-100">
                            <a class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= $isNotasActive ? 'active' : '' ?>" 
                               href="#" data-bs-toggle="dropdown" style="border: none; width: 100%; background: <?= $isNotasActive ? '#000' : 'transparent' ?>; color: <?= $isNotasActive ? '#DAA520' : '#000' ?>; font-weight: 500; text-decoration: none;">
                                <i class="fas fa-file-invoice" style="width: 20px;"></i> Notas
                            </a>
                            <ul class="dropdown-menu w-100 border-0 shadow-sm rounded-3">
                                <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=notaentrada"><i class="fas fa-sign-in-alt me-2"></i> Nota de Entrada</a></li>
                                <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=notasalida"><i class="fas fa-sign-out-alt me-2"></i> Nota de Salida</a></li>
                            </ul>
                        </div>
                        
                        <!-- CONFIGURACIÓN -->
                        <?php $isConfigActive = $currentUrl === 'categorias'; ?>
                        <div class="dropdown w-100">
                            <a class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= $isConfigActive ? 'active' : '' ?>" 
                               href="#" role="button" aria-haspopup="true" aria-expanded="false" data-bs-toggle="dropdown" style="border: none; width: 100%; background: <?= $isConfigActive ? '#000' : 'transparent' ?>; color: <?= $isConfigActive ? '#DAA520' : '#000' ?>; font-weight: 500; text-decoration: none;" onclick="return false;">
                                <i class="fas fa-cog" style="width: 20px;"></i> Configuración
                            </a>
                            <ul class="dropdown-menu w-100 border-0 shadow-sm rounded-3">
                                <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=categorias"><i class="fas fa-tags me-2"></i> Categorías</a></li>
                            </ul>
                        </div>

                        <!-- SEGURIDAD (solo admin) -->
                        <?php $isAdmin = isset($_SESSION['id_rol']) && (int)$_SESSION['id_rol'] === 1; ?>
                        <?php if ($isAdmin): ?>
                            <?php $isSecurityActive = in_array($currentUrl, ['usuarios', 'roles']); ?>
                            <div class="dropdown w-100">
                                <a class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= $isSecurityActive ? 'active' : '' ?>" 
                                   href="#" data-bs-toggle="dropdown" style="border: none; width: 100%; background: <?= $isSecurityActive ? '#000' : 'transparent' ?>; color: <?= $isSecurityActive ? '#DAA520' : '#000' ?>; font-weight: 500; text-decoration: none;">
                                    <i class="fas fa-shield-alt" style="width: 20px;"></i> Seguridad
                                </a>
                                <ul class="dropdown-menu w-100 border-0 shadow-sm rounded-3">
                                    <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=usuarios"><i class="fas fa-users-cog me-2"></i> Usuarios</a></li>
                                    <li><a class="dropdown-item py-2 px-3 rounded-2" href="?url=roles"><i class="fas fa-user-shield me-2"></i> Roles</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <a href="?url=reportes" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-1 <?= $currentUrl == 'reportes' ? 'active' : '' ?>"
                           style="border: none; <?= $currentUrl == 'reportes' ? 'background: #000; color: #DAA520;' : '' ?>">
                            <i class="fas fa-file-alt" style="width: 20px;"></i> Reportes
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-md-9 col-lg-10">
            <div class="content-wrapper p-3">
                <!-- Las vistas se inyectan aquí -->