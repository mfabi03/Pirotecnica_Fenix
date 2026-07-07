<?php
$isAdmin = isset($_SESSION['id_rol']) && (int) $_SESSION['id_rol'] === 1;
$currentUrl = $_GET['url'] ?? 'main';
?>
<div class="container-fluid px-4">
    <div class="row">
        
       <div class="col-md-3 col-lg-2 mb-4">
    <div class="card card-custom p-3">
        <h6 class="text-muted text-uppercase font-weight-bold mb-3 small">Navegación</h6>
        <div class="d-flex flex-column">
            <a href="?url=productos" class="nav-link"><i class="fas fa-box me-2"></i> Stock</a>
            <a href="?url=salidas" class="nav-link"><i class="fas fa-shopping-cart me-2"></i> Registro de Salidas</a>
            <a href="?url=clientes" class="nav-link"><i class="fas fa-users me-2"></i> Clientes</a>
            <a href="?url=proveedor" class="nav-link"><i class="fas fa-truck me-2"></i> Proveedores</a>

            <a class="nav-link" data-bs-toggle="collapse" href="#menuConfiguracion" role="button" aria-expanded="false">
                <i class="fas fa-user-cog me-2"></i> Configuración <i class="fas fa-chevron-down float-end"></i>
            </a>

            <div class="collapse" id="menuConfiguracion">
                <div class="d-flex flex-column ps-3 mt-1">
                    <?php if ($isAdmin): ?>
                        <a href="?url=usuarios" class="nav-link small"><i class="fas fa-users-cog me-2"></i> Usuarios</a>
                        <a href="?url=roles" class="nav-link small"><i class="fas fa-user-shield me-2"></i> Roles</a>
                    <?php endif; ?>
                    <a href="?url=categorias" class="nav-link small"><i class="fas fa-tags me-2"></i> Categorías</a>
                </div>
            </div>
        </div>
    </div>
</div>
    
