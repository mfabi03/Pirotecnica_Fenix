<<<<<<< HEAD
=======
<?php
// ==========================================
// CARGAR PERMISOS DEL USUARIO ACTUAL
// ==========================================
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Helpers\PermisoHelper;

try {
    $db_menu = (new ConnectDB())->getConnection();
    $id_rol_actual = $_SESSION['id_rol'] ?? 0;
    $modulosVisibles = $id_rol_actual > 0
        ? PermisoHelper::modulosVisibles($db_menu, $id_rol_actual)
        : [];
} catch (Exception $e) {
    $modulosVisibles = [];
}

// Helper rápido para verificar si el usuario tiene acceso a un módulo
$tieneModulo = function($nombre) use ($modulosVisibles) {
    return in_array($nombre, $modulosVisibles);
};

$currentUrl = $_GET['url'] ?? 'main';
?>
<!DOCTYPE html>
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Pirotecnica Fénix</title>
    
    <base href="/pirotecnica_fenix/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/pirotecnica_fenix/assets/css/estilo.css">
</head>
<body>

<!-- ==========================================
<<<<<<< HEAD
NAVBAR - CORREGIDO
========================================== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" style="min-height: 60px; padding: 5px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 1050;">
    <div class="container-fluid">
        <!-- LOGO CON TAMAÑO CORRECTO -->
        <a class="navbar-brand d-flex align-items-center" href="?url=main" style="gap: 10px;">
            <img src="assets/imagenes/logo.png" alt="Logo" 
                 style="width: 40px !important; height: 40px !important; object-fit: contain; border-radius: 50%; background: rgba(255, 247, 22, 0.72); padding: 0.5px;">
=======
NAVBAR
========================================== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" style="min-height: 60px; padding: 5px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 1050;">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="?url=main" style="gap: 10px;">
            <img src="assets/imagenes/logo.png" alt="Logo" 
                 style="width: 40px !important; height: 40px !important; object-fit: contain; border-radius: 50%; background: #ffffff; padding: 4px;">
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
            <span style="font-size: 1.1rem; font-weight: 700; color: #ffffff; white-space: nowrap;">
                <span style="color: #DAA520;">Sistema</span> Pirotecnica Fénix
            </span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <span class="nav-link text-white" style="font-size: 0.9rem;">
                        <i class="fas fa-user me-1"></i> 
                        <?= $_SESSION['usuario_nombre'] ?? '' ?>
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
<<<<<<< HEAD
    <div class="container-fluid" style="margin-top: 60px; padding-left: 0; padding-right: 0;">
    <div class="row align-items-stretch" style="min-height: calc(100vh - 60px); margin-left: 0; margin-right: 0;">
        
 <!-- ==========================================
SIDEBAR - MENÚ DE NAVEGACIÓN CON ALTO COMPLETO
========================================== -->
<div class="col-md-3 col-lg-2" style="padding: 0; min-height: calc(100vh - 60px);">
    <div class="card shadow-sm border-0 rounded-0" style="background: #1a1a2e; border: 1px solid rgba(255,255,255,0.05); height: 100%; min-height: calc(100vh - 60px); border-radius: 0 !important;">
        <div class="card-body p-3" style="height: 100%; min-height: calc(100vh - 60px); overflow-y: auto;">
            <h6 class="text-uppercase small fw-bold mb-3" style="color: rgba(255,255,255,0.4); letter-spacing: 0.5px;">
                <i class="fas fa-compass me-2" style="color: #f39c12;"></i> Navegación
            </h6>
            <?php $currentUrl = $_GET['url'] ?? 'main'; ?>
            <div class="list-group list-group-flush" style="display: flex; flex-direction: column; gap: 2px;">
                
                <!-- ===== INICIO ===== -->
=======
<div class="container-fluid" style="margin-top: 70px;">
    <div class="row">
        
<!-- ==========================================
SIDEBAR - MENÚ DE NAVEGACIÓN DINÁMICO
========================================== -->
<div class="col-md-3 col-lg-2" style="padding: 0; height: 100%;">
    <div class="card shadow-sm border-0 rounded-0" style="background: #1a1a2e; border: 1px solid rgba(255,255,255,0.05); height: 100%; min-height: calc(100vh - 70px); border-radius: 0 !important;">
        <div class="card-body p-3" style="height: 100%; overflow-y: auto;">
            <h6 class="text-uppercase small fw-bold mb-3" style="color: rgba(255,255,255,0.4); letter-spacing: 0.5px;">
                <i class="fas fa-compass me-2" style="color: #f39c12;"></i> Navegación
            </h6>
            
            <div class="list-group list-group-flush" style="display: flex; flex-direction: column; gap: 2px;">
                
                <!-- ===== INICIO (SIEMPRE VISIBLE) ===== -->
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
                <a href="?url=main" 
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $currentUrl == 'main' ? 'active' : '' ?>"
                   style="border: none; background: <?= $currentUrl == 'main' ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $currentUrl == 'main' ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $currentUrl == 'main' ? '600' : '400' ?>; transition: all 0.3s ease;">
                    <i class="fas fa-home" style="width: 20px; color: <?= $currentUrl == 'main' ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
                    <span>Inicio</span>
                </a>
                
                <!-- ===== PROVEEDORES ===== -->
<<<<<<< HEAD
=======
                <?php if ($tieneModulo('Proveedores')): ?>
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
                <a href="?url=proveedores" 
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $currentUrl == 'proveedores' ? 'active' : '' ?>"
                   style="border: none; background: <?= $currentUrl == 'proveedores' ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $currentUrl == 'proveedores' ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $currentUrl == 'proveedores' ? '600' : '400' ?>; transition: all 0.3s ease;">
                    <i class="fas fa-truck" style="width: 20px; color: <?= $currentUrl == 'proveedores' ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
                    <span>Proveedores</span>
                </a>
<<<<<<< HEAD
                
                <!-- ===== CLIENTES ===== -->
=======
                <?php endif; ?>
                
                <!-- ===== CLIENTES ===== -->
                <?php if ($tieneModulo('Clientes')): ?>
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
                <a href="?url=clientes" 
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $currentUrl == 'clientes' ? 'active' : '' ?>"
                   style="border: none; background: <?= $currentUrl == 'clientes' ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $currentUrl == 'clientes' ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $currentUrl == 'clientes' ? '600' : '400' ?>; transition: all 0.3s ease;">
                    <i class="fas fa-users" style="width: 20px; color: <?= $currentUrl == 'clientes' ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
                    <span>Clientes</span>
                </a>
<<<<<<< HEAD
                
                <!-- ===== PRODUCTOS ===== -->
=======
                <?php endif; ?>
                
                <!-- ===== PRODUCTOS ===== -->
                <?php if ($tieneModulo('Productos')): ?>
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
                <a href="?url=productos" 
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $currentUrl == 'productos' ? 'active' : '' ?>"
                   style="border: none; background: <?= $currentUrl == 'productos' ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $currentUrl == 'productos' ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $currentUrl == 'productos' ? '600' : '400' ?>; transition: all 0.3s ease;">
                    <i class="fas fa-box" style="width: 20px; color: <?= $currentUrl == 'productos' ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
                    <span>Productos</span>
                </a>
<<<<<<< HEAD
                
                <!-- ===== NOTAS (DROPDOWN) ===== -->
 <?php $isNotasActive = in_array($currentUrl, ['notaentrada', 'notasalida']); ?>
<div class="w-100">
    <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $isNotasActive ? 'active' : '' ?>" 
       data-bs-toggle="collapse" href="#menuNotas" role="button" aria-expanded="false" aria-controls="menuNotas"
       style="border: none; width: 100%; background: <?= $isNotasActive ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $isNotasActive ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $isNotasActive ? '600' : '400' ?>; text-decoration: none; transition: all 0.3s ease; cursor: pointer; padding: 8px 12px;">
        <i class="fas fa-file-invoice" style="width: 20px; color: <?= $isNotasActive ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
        <span style="flex: 1;">Notas</span>
        <i class="fas fa-chevron-down" style="font-size: 0.7rem; opacity: 0.5; transition: transform 0.3s ease;"></i>
    </a>
    
    <!-- Menú desplegable que empuja hacia abajo -->
    <div class="collapse" id="menuNotas">
        <div class="d-flex flex-column ps-3 mt-1" style="background: #0D0D1A; border-radius: 8px; padding: 6px;">
            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=notaentrada" 
               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                <i class="fas fa-sign-in-alt me-2" style="color: rgba(255,255,255,0.3);"></i> Nota de Entrada
            </a>
            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=notasalida" 
               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                <i class="fas fa-sign-out-alt me-2" style="color: rgba(255,255,255,0.3);"></i> Nota de Salida
            </a>
        </div>
    </div>
</div>
                
                <!-- ===== CONFIGURACIÓN (COLLAPSE) ===== -->
<?php $isConfigActive = in_array($currentUrl, ['categorias', 'usuarios', 'roles']); ?>
<div class="w-100">
    <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $isConfigActive ? 'active' : '' ?>" 
       data-bs-toggle="collapse" href="#menuConfiguracion" role="button" aria-expanded="false" aria-controls="menuConfiguracion"
       style="border: none; width: 100%; background: <?= $isConfigActive ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $isConfigActive ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $isConfigActive ? '600' : '400' ?>; text-decoration: none; transition: all 0.3s ease; cursor: pointer; padding: 8px 12px;">
        <i class="fas fa-cog" style="width: 20px; color: <?= $isConfigActive ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
        <span style="flex: 1;">Configuración</span>
        <i class="fas fa-chevron-down" style="font-size: 0.7rem; opacity: 0.5; transition: transform 0.3s ease;"></i>
    </a>
    
    <!-- Menú desplegable que empuja hacia abajo -->
    <div class="collapse" id="menuConfiguracion">
        <div class="d-flex flex-column ps-3 mt-1" style="background: #0D0D1A; border-radius: 8px; padding: 6px;">
            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=categorias" 
               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                <i class="fas fa-tags me-2" style="color: rgba(255,255,255,0.3);"></i> Categorías
            </a>
            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=usuarios" 
               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                <i class="fas fa-users-cog me-2" style="color: rgba(255,255,255,0.3);"></i> Usuarios
            </a>
            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=roles" 
               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                <i class="fas fa-user-shield me-2" style="color: rgba(255,255,255,0.3);"></i> Roles
            </a>
        </div>
    </div>
</div>
                
                <!-- ===== REPORTES ===== -->
=======
                <?php endif; ?>
                
                <!-- ===== NOTAS (DROPDOWN DINÁMICO) ===== -->
                <?php 
                $isNotasActive = in_array($currentUrl, ['notaentrada', 'notasalida']);
                $verNotaEntrada = $tieneModulo('Notas de Entrada');
                $verNotaSalida = $tieneModulo('Notas de Salida');
                $verNotas = $verNotaEntrada || $verNotaSalida;
                ?>
                
                <?php if ($verNotas): ?>
                <div class="dropdown w-100">
                    <a class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $isNotasActive ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown" 
                       style="border: none; width: 100%; background: <?= $isNotasActive ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $isNotasActive ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $isNotasActive ? '600' : '400' ?>; text-decoration: none; transition: all 0.3s ease; cursor: pointer; padding: 8px 12px;">
                        <i class="fas fa-file-invoice" style="width: 20px; color: <?= $isNotasActive ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
                        <span style="flex: 1;">Notas</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem; opacity: 0.5; transition: transform 0.3s ease;"></i>
                    </a>
                    <ul class="dropdown-menu w-100 border-0 shadow-sm rounded-3" style="background: #0D0D1A; border: 1px solid rgba(255,255,255,0.05); margin-top: 4px; padding: 6px;">
                        <?php if ($verNotaEntrada): ?>
                        <li>
                            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=notaentrada" 
                               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
                               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                                <i class="fas fa-sign-in-alt me-2" style="color: rgba(255,255,255,0.3);"></i> Nota de Entrada
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($verNotaSalida): ?>
                        <li>
                            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=notasalida" 
                               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
                               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                                <i class="fas fa-sign-out-alt me-2" style="color: rgba(255,255,255,0.3);"></i> Nota de Salida
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- ===== CONFIGURACIÓN (DROPDOWN DINÁMICO) ===== -->
                <?php 
                $isConfigActive = in_array($currentUrl, ['categorias', 'usuarios', 'roles']);
                $verCategorias = $tieneModulo('Categorias');
                $verUsuarios = $tieneModulo('Usuarios');
                $verRoles = $tieneModulo('Roles');
                $verConfig = $verCategorias || $verUsuarios || $verRoles;
                ?>
                
                <?php if ($verConfig): ?>
                <div class="dropdown w-100">
                    <a class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $isConfigActive ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown" 
                       style="border: none; width: 100%; background: <?= $isConfigActive ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $isConfigActive ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $isConfigActive ? '600' : '400' ?>; text-decoration: none; transition: all 0.3s ease; cursor: pointer; padding: 8px 12px;">
                        <i class="fas fa-cog" style="width: 20px; color: <?= $isConfigActive ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
                        <span style="flex: 1;">Configuración</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem; opacity: 0.5; transition: transform 0.3s ease;"></i>
                    </a>
                    <ul class="dropdown-menu w-100 border-0 shadow-sm rounded-3" style="background: #0D0D1A; border: 1px solid rgba(255,255,255,0.05); margin-top: 4px; padding: 6px;">
                        <?php if ($verCategorias): ?>
                        <li>
                            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=categorias" 
                               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
                               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                                <i class="fas fa-tags me-2" style="color: rgba(255,255,255,0.3);"></i> Categorías
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($verUsuarios): ?>
                        <li>
                            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=usuarios" 
                               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
                               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                                <i class="fas fa-users-cog me-2" style="color: rgba(255,255,255,0.3);"></i> Usuarios
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($verRoles): ?>
                        <li>
                            <a class="dropdown-item py-2 px-3 rounded-2" href="?url=roles" 
                               style="color: rgba(255,255,255,0.6); transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(243,156,18,0.12)'; this.style.color='#f39c12';"
                               onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.6)';">
                                <i class="fas fa-user-shield me-2" style="color: rgba(255,255,255,0.3);"></i> Roles
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- ===== REPORTES ===== -->
                <?php if ($tieneModulo('Reportes')): ?>
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
                <a href="?url=reportes" 
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3 <?= $currentUrl == 'reportes' ? 'active' : '' ?>"
                   style="border: none; background: <?= $currentUrl == 'reportes' ? 'rgba(243,156,18,0.12)' : 'transparent' ?>; color: <?= $currentUrl == 'reportes' ? '#f39c12' : 'rgba(255,255,255,0.6)' ?>; font-weight: <?= $currentUrl == 'reportes' ? '600' : '400' ?>; transition: all 0.3s ease;">
                    <i class="fas fa-file-alt" style="width: 20px; color: <?= $currentUrl == 'reportes' ? '#f39c12' : 'rgba(255,255,255,0.3)' ?>;"></i> 
                    <span>Reportes</span>
                </a>
<<<<<<< HEAD
                
                <!-- ===== ESPACIO EMPUJA EL CONTENIDO HACIA ABAJO ===== -->
                <div style="flex: 1;"></div>
                
                <!-- ===== VERSIÓN DEL SISTEMA ===== -->
=======
                <?php endif; ?>
                
                <!-- ===== ESPACIO ===== -->
                <div style="flex: 1;"></div>
                
                <!-- ===== VERSIÓN ===== -->
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
                <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    <small style="color: rgba(255,255,255,0.2); font-size: 0.65rem; display: block; text-align: center;">
                        <i class="fas fa-code-branch me-1"></i> v1.0.0
                    </small>
                    <small style="color: rgba(255,255,255,0.15); font-size: 0.6rem; display: block; text-align: center;">
                        Pirotecnia Fénix
                    </small>
                </div>
                
            </div>
        </div>
    </div>
</div>
        <!-- CONTENIDO PRINCIPAL -->
<<<<<<< HEAD
        <div class="col-md-9 col-lg-10" style="padding-left: 0; padding-right: 0;">
        <div class="content-wrapper px-3 py-3">
=======
        <div class="col-md-9 col-lg-10">
            <div class="content-wrapper p-3">
>>>>>>> ad45ea0e9124a6b1afc884906470819cabf7986d
                <!-- Las vistas se inyectan aquí -->