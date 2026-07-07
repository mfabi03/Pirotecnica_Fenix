<?php 
// app/View/configuracion/Dashboard.php
// Esto debe ir al principio de tu archivo de vista
require_once dirname(__DIR__, 2) . "/view/header.php"; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mensajes de error de permisos
if (!empty($_SESSION['error'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container-fluid px-4">
    <div class="row">
        
      

        <main class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>📊 Sistema de Gestión</h2>
                
            </div>
                    
            <!-- Estadísticas - Datos DINÁMICOS -->
            <div class="row g-4">
                <!-- Tarjeta 1: Productos -->
                <div class="col-12 col-md-3">
                    <div class="card text-white bg-primary h-100 shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-white-50">Inventario Total</h6>
                                    <p class="card-text display-5 fw-bold">
                                        <?= number_format($stats['total_productos'] ?? 0) ?>
                                    </p>
                                </div>
                                <div class="fs-1">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            </div>
                            <small class="text-white-50">
                                <i class="bi bi-arrow-up"></i> Productos registrados
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta 2: Usuarios -->
                <div class="col-12 col-md-3">
                    <div class="card text-white bg-success h-100 shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-white-50">Usuarios</h6>
                                    <p class="card-text display-5 fw-bold">
                                        <?= number_format($stats['total_usuarios'] ?? 0) ?>
                                    </p>
                                </div>
                                <div class="fs-1">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                            <small class="text-white-50">
                                <i class="bi bi-arrow-up"></i> Usuarios registrados
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta 3: Stock Crítico -->
                <div class="col-12 col-md-3">
                    <div class="card text-white bg-danger h-100 shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-white-50">Stock Crítico</h6>
                                    <p class="card-text display-5 fw-bold">
                                        <?= number_format($stats['productos_criticos'] ?? 0) ?>
                                    </p>
                                </div>
                                <div class="fs-1">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                            </div>
                            <small class="text-white-50">
                                <i class="bi bi-arrow-down"></i> Productos con stock bajo
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta 4: Clientes -->
                <div class="col-12 col-md-3">
                    <div class="card text-white bg-warning h-100 shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-dark">Clientes</h6>
                                    <p class="card-text display-5 fw-bold text-dark">
                                        <?= number_format($stats['total_clientes'] ?? 0) ?>
                                    </p>
                                </div>
                                <div class="fs-1 text-dark">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                            <small class="text-dark">
                                <i class="bi bi-arrow-up"></i> Clientes registrados
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda fila de tarjetas -->
            <div class="row g-4 mt-2">
                <!-- Tarjeta 5: Notas de Salida -->
                <div class="col-12 col-md-6">
                    <div class="card text-white bg-info h-100 shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-white-50">Notas de Salida</h6>
                                    <p class="card-text display-5 fw-bold">
                                        <?= number_format($stats['total_nota_salida'] ?? 0) ?>
                                    </p>
                                </div>
                                <div class="fs-1">
                                    <i class="bi bi-box-arrow-right"></i>
                                </div>
                            </div>
                            <small class="text-white-50">
                                <i class="bi bi-arrow-up"></i> Salidas registradas
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta 6: Notas de Entrada -->
                <div class="col-12 col-md-6">
                    <div class="card text-white bg-secondary h-100 shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-white-50">Notas de Entrada</h6>
                                    <p class="card-text display-5 fw-bold">
                                        <?= number_format($stats['total_nota_entrada'] ?? 0) ?>
                                    </p>
                                </div>
                                <div class="fs-1">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </div>
                            </div>
                            <small class="text-white-50">
                                <i class="bi bi-arrow-up"></i> Entradas registradas
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>
