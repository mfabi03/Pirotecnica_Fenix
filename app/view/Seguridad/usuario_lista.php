<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/welcome.css">


<nav class="navbar bg-dark navbar-dark px-4 py-3 mb-4 shadow">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <span class="navbar-brand mb-0 h1 text-white display-5">
            🔥 Pirotecnia Fénix
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
                    <a href="?url=productos" class="nav-link nav-link-custom"><i class="fas fa-box me-2"></i> Stock</a>
                    <a href="?url=salidas" class="nav-link nav-link-custom"><i class="fas fa-shopping-cart me-2"></i> Registro de Salidas</a>
                    <a href="?url=clientes" class="nav-link nav-link-custom"><i class="fas fa-users me-2"></i> Clientes</a>
                    <a href="?url=proveedor" class="nav-link nav-link-custom"><i class="fas fa-truck me-2"></i> Proveedores</a>
                    <a href="?url=usuarios" class="nav-link nav-link-custom"><i class="fas fa-user-cog me-2"></i> Reportes</a>
                    <a href="?url=reportes" class="nav-link nav-link-custom active bg-dark text-white"><i class="fas fa-file-alt me-2"></i> Configuración</a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            
            <div class="card card-custom p-4 mb-4 bg-white">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 font-weight-bold text-dark">📋 Registro de Usuarios</h3>
                
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
                    <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-user text-muted me-2"></i></i>Usuarios Registrados</h5>
                    <a href="?url=configuracion&action=registrar" class="btn btn-sm btn-warning fw-bold text-dark"><i class="fas fa-plus me-1"></i> Registrar</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-fenix m-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cedula</th>
                                <th>Telefono</th>
                                <th>Correo Electronico</th>
                                <th>Rol</th>
                                <th>Fecha de Registro</th>   
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($result)): ?>
                                <?php foreach ($result as $usuario): ?>
                                    <tr>
                                        <td class="ps-4"><?= htmlspecialchars($usuario['id_usuario'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                        <td><?= htmlspecialchars($usuario['apellido']) ?></td>
                                        <td><?= htmlspecialchars($usuario['cedula']) ?></td>
                                        <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                                        <td><?= htmlspecialchars($usuario['correo_electronico']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($usuario['rol']) ?></span></td>
                                        <td class="text-center">
                                            <a href="?url=configuracion&action=editar&id=<?= $usuario['id_usuario'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?url=configuracion&action=eliminar&id=<?= $usuario['id_usuario'] ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Seguro?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No hay registros encontrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>