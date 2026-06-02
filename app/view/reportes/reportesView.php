<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar-fenix {
        background: linear-gradient(135deg, #1e1e24 0%, #0f0f12 100%);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .brand-title {
        color: #fff;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .brand-orange {
        color: #f25c05;
    }
    .sidebar-mock {
        background-color: #ffffff;
        border-right: 1px solid #e3e6f0;
        min-height: 100vh;
    }
    .nav-link-custom {
        color: #4e73df;
        font-weight: 600;
        padding: 0.8rem 1rem;
        border-radius: 5px;
        margin-bottom: 0.3rem;
        transition: all 0.2s;
    }
    .nav-link-custom:hover {
        background-color: #f1f3f9;
        color: #224abe;
    }
    .card-custom {
        border: none;
        border-radius: 12px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    }
    .table-fenix thead {
        background-color: #1e1e24;
        color: white;
    }
    .badge-entrada { background-color: #2e7d32; color: white; }
    .badge-salida { background-color: #c62828; color: white; }
</style>

<nav class="navbar navbar-dark navbar-fenix px-4 py-3 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <span class="navbar-brand m-0 h1 brand-title">
            🔥 <span class="brand-orange">Sistema</span> Pirotecnia Fénix
        </span>
        <span class="text-white-50 small">Panel de Control General</span>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="row">
        
        <div class="col-md-3 col-lg-2 mb-4">
            <div class="card card-custom p-3">
                <h6 class="text-muted text-uppercase font-weight-bold mb-3 small">Navegación</h6>
                <div class="d-flex flex-column">
                    <a href="?url=productos" class="nav-link nav-link-custom"><i class="fas fa-box me-2"></i> Productos</a>
                    <a href="?url=salidas" class="nav-link nav-link-custom"><i class="fas fa-shopping-cart me-2"></i> Ventas</a>
                    <a href="?url=clientes" class="nav-link nav-link-custom"><i class="fas fa-users me-2"></i> Clientes</a>
                    <a href="?url=proveedor" class="nav-link nav-link-custom"><i class="fas fa-truck me-2"></i> Proveedores</a>
                    <a href="?url=usuarios" class="nav-link nav-link-custom"><i class="fas fa-user-cog me-2"></i> Usuarios</a>
                    <a href="?url=reportes" class="nav-link nav-link-custom active bg-dark text-white"><i class="fas fa-file-alt me-2"></i> Reportes</a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            
            <div class="card card-custom p-4 mb-4 bg-white">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 font-weight-bold text-dark">📋 Módulo de Soporte y Reportes</h3>
                
                    </div>
                    <div class="col-xl-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                            <input type="date" class="form-control" title="Filtrar por Fecha">
                            <input type="text" class="form-control w-25" placeholder="Buscar">
                            <button class="btn btn-dark" type="button"><i class="fas fa-search"></i> Buscar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-history text-muted me-2"></i>Historial de Movimientos</h5>
                    <button class="btn btn-sm btn-warning fw-bold text-dark"><i class="fas fa-plus me-1"></i> Registrar</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-fenix m-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Precio</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                        </thead>
                       <tbody>
    <?php if (isset($result) && !empty($result)): ?>
        <?php foreach ($result as $reporte): ?>
            <tr>
                <td class="ps-4 font-weight-bold text-primary"><?= $reporte['codigo']; ?></td>
                <td><?= $reporte['nombre']; ?></td>
                <td>
                    <?php if ($reporte['tipo'] == 'Entrada'): ?>
                        <span class="badge badge-entrada px-2 py-1"><i class="fas fa-arrow-down me-1"></i>Entrada</span>
                    <?php else: ?>
                        <span class="badge badge-salida px-2 py-1"><i class="fas fa-arrow-up me-1"></i>Salida</span>
                    <?php endif; ?>
                </td>
                <td class="fw-bold">$<?= number_format($reporte['precio'], 2); ?></td>
                <td class="pe-4 text-end">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" title="Detalle">D</button>
                        <button class="btn btn-outline-warning" title="Modificar">M</button>
                        <button class="btn btn-outline-danger" title="Eliminar">E</button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">
                <i class="fas fa-database mb-2" style="font-size: 2rem; display: block;"></i>
                No hay movimientos registrados en la base de datos.
            </td>
        </tr>
    <?php endif; ?>
</tbody>
                    </table>
                </div>
            </div>

            <div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card card-custom border-start border-danger border-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-shield-alt me-2"></i>🛡️ Validación de Integridad de Datos</h6>
            </div>
            <div class="card-body pt-0">
                
                
                <?php 
                // Inicializamos las variables en 0 por si la base de datos está vacía
                $totalEntradas = 0;
                $totalVentas = 0;

                // Si hay datos reales en la variable $result, sumamos los movimientos automáticamente
                if (isset($result) && !empty($result)) {
                    foreach ($result as $reporte) {
                        if ($reporte['tipo'] == 'Entrada') {
                            $totalEntradas += $reporte['cantidad']; // Suma las unidades que entraron
                        } elseif ($reporte['tipo'] == 'Salida') {
                            $totalVentas += $reporte['cantidad'];   // Suma las unidades vendidas
                        }
                    }
                }
                // El stock real es la resta matemática de las entradas menos las salidas
                $stockRealCalculado = $totalEntradas - $totalVentas;
                ?>

                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted">Total Entradas:</span>
                    <strong class="text-success">+ <?= $totalEntradas; ?> unidades</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted">Total Ventas:</span>
                    <strong class="text-danger">- <?= $totalVentas; ?> unidades</strong>
                </div>
                <div class="d-flex justify-content-between pt-2">
                    <span class="fw-bold text-dark">Stock Real:</span>
                    <strong class="text-primary" style="font-size: 1.1rem;"><?= $stockRealCalculado; ?> unidades</strong>
                </div>
            </div>
        </div>
    </div>
</div>