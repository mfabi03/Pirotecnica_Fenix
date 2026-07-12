<?php 
// app/view/configuracion/dashboard.php
require_once dirname(__DIR__, 2) . "/view/header.php"; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mensajes de error de permisos
if (!empty($_SESSION['error'])): ?>
    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-md-9 col-lg-10">
                <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-chart-pie text-gold me-2"></i> Panel de Control
                        </h3>
                    
                    </div>
                    <div class="col-auto">
                        <span class="badge" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); padding: 6px 14px; border-radius: 50px;">
                            <i class="fas fa-database me-1"></i> Resumen General
                        </span>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 TARJETAS DE ESTADÍSTICAS - ESTILO DARK
                 ========================================== -->
            <div class="row g-4 mb-4">
                <!-- Tarjeta 1: Productos -->
                <div class="col-12 col-md-3">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-card-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Inventario Total</span>
                            <span class="stat-card-number"><?= number_format($stats['total_productos'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Productos registrados
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta 2: Usuarios -->
                <div class="col-12 col-md-3">
                    <div class="stat-card stat-card-success">
                        <div class="stat-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Usuarios</span>
                            <span class="stat-card-number"><?= number_format($stats['total_usuarios'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Usuarios registrados
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta 3: Stock Crítico -->
                <div class="col-12 col-md-3">
                    <div class="stat-card stat-card-warning">
                        <div class="stat-card-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Stock Crítico</span>
                            <span class="stat-card-number"><?= number_format($stats['productos_criticos'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-down">
                                <i class="fas fa-arrow-down"></i> Productos con stock bajo
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta 4: Clientes -->
                <div class="col-12 col-md-3">
                    <div class="stat-card stat-card-info">
                        <div class="stat-card-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Clientes</span>
                            <span class="stat-card-number"><?= number_format($stats['total_clientes'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Clientes registrados
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 SEGUNDA FILA DE TARJETAS
                 ========================================== -->
            <div class="row g-4 mb-4">
                <!-- Tarjeta 5: Notas de Salida -->
                <div class="col-12 col-md-6">
                    <div class="stat-card stat-card-purple">
                        <div class="stat-card-icon">
                            <i class="fas fa-sign-out-alt"></i> <!-- Ícono corregido -->
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Notas de Salida</span>
                            <span class="stat-card-number"><?= number_format($stats['total_nota_salida'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Salidas registradas
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta 6: Notas de Entrada -->
                <div class="col-12 col-md-6">
                    <div class="stat-card" style="border-color: rgba(108, 117, 125, 0.3);">
                        <div class="stat-card-icon" style="background: rgba(108, 117, 125, 0.12); color: #adb5bd;">
                            <i class="fas fa-sign-in-alt"></i> <!-- Ícono corregido -->
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Notas de Entrada</span>
                            <span class="stat-card-number"><?= number_format($stats['total_nota_entrada'] ?? 0) ?></span>
                        </div>
                        <div class="stat-card-footer">
                            <span class="stat-card-change stat-card-change-up">
                                <i class="fas fa-arrow-up"></i> Entradas registradas
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 ACCIONES RÁPIDAS
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header py-3">
                    <h5 class="m-0">
                        <i class="fas fa-bolt me-2" style="color: #f39c12;"></i> Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="?url=productos&type=create" class="btn w-100" style="background: rgba(141, 189, 28, 0.17); color: #a78528; border: 1px solid rgba(40, 167, 69, 0.2); border-radius: 50px; padding: 12px 10px; font-size: 0.85rem; text-decoration: none; transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(255, 188, 4, 0.37)';"
                               onmouseout="this.style.background='rgba(255, 188, 4, 0.37)';">
                                <i class="fas fa-user-plus me-1"></i> Nuevo Cliente
                            </a>
                        </div>    
                        <div class="col-md-3 col-6">
                            <a href="?url=clientes&type=register" class="btn w-100" style="background: rgba(40, 167, 69, 0.15); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.2); border-radius: 50px; padding: 12px 10px; font-size: 0.85rem; text-decoration: none; transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(40,167,69,0.25)';"
                               onmouseout="this.style.background='rgba(40,167,69,0.15)';">
                                <i class="fas fa-user-plus me-1"></i> Nuevo Cliente
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="?url=notas&type=create" class="btn w-100" style="background: rgba(111, 66, 193, 0.15); color: #6f42c1; border: 1px solid rgba(111, 66, 193, 0.2); border-radius: 50px; padding: 12px 10px; font-size: 0.85rem; text-decoration: none; transition: all 0.3s ease;"
                               onmouseover="this.style.background='rgba(111,66,193,0.25)';"
                               onmouseout="this.style.background='rgba(111,66,193,0.15)';">
                                <i class="fas fa-file-plus me-1"></i> Nueva Nota
                            </a>
                        </div>
                    </div>
                </div>
            </div>

           
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>