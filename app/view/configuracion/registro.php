<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/welcome.css">




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
                        <h3 class="m-0 font-weight-bold text-dark">📋 Formulario de Registro</h3>
                
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
            <form action="?url=configuracion&action=guardar" method="POST">
    <div class="row g-3">
        
        <div class="col-md-6">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label for="apellido" class="form-label">Apellido</label>
            <input type="text" name="apellido" id="apellido" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label for="cedula" class="form-label">Cedula</label>
            <input type="text" name="cedula" id="cedula" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label for="telefono" class="form-label">Telefono</label>
            <input type="text" name="telefono" id="telefono" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label for="correo_electronico" class="form-label">Correo Electonico</label>
            <input type="text" name="correo_electronico" id="correo_electronico" class="form-control" required>
        </div>

        <div class="col-md-12">
            <label for="rol" class="form-label">Rol del Usuario</label>
            <select name="rol" id="rol" class="form-select">
                <option value="1">Administrador</option>
                <option value="2">Jefe de almacen</option>
                <option value="3">Cajero</option>
            </select>
        </div>

        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-dark w-100">Registrar Usuario</button>
        </div>
    </div>
</form>