<?php
require_once __DIR__ . '/../header.php';
?>

<div class="container-fluid px-4">
    <div class="row">       
        <div class="col-md-9 col-lg-10">
            
            <div class="card card-custom p-4 mb-4 bg-white">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 font-weight-bold text-dark">📋 Formulario de Registro</h3>
                        <small class="text-muted">Complete todos los campos para registrar un nuevo usuario</small>
                    </div>
                    <div class="col-xl-6 text-end">
                        <a href="?url=usuarios" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?? 'info' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form action="?url=usuarios" method="POST">
                <input type="hidden" name="accion" value="guardar">
                <div class="row g-3">
                    
                    <!-- Datos Personales -->
                    <div class="col-12">
                        <div class="card card-body mb-4">
                            <h5 class="mb-4"><i class="fas fa-user me-2"></i> Datos personales</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre *</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label">Apellido *</label>
                                    <input type="text" name="apellido" id="apellido" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cedula" class="form-label">Cédula *</label>
                                    <input type="text" name="cedula" id="cedula" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono *</label>
                                    <input type="text" name="telefono" id="telefono" class="form-control" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="correo_electronico" class="form-label">Correo electrónico *</label>
                                    <input type="email" name="correo_electronico" id="correo_electronico" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cuenta de Usuario -->
                    <div class="col-12">
                        <div class="card card-body mb-4">
                            <h5 class="mb-4"><i class="fas fa-lock me-2"></i> Cuenta de usuario</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="usuario" class="form-label">Usuario *</label>
                                    <input type="text" name="usuario" id="usuario" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="clave" class="form-label">Contraseña *</label>
                                    <input type="password" name="clave" id="clave" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="id_rol" class="form-label">Rol del usuario *</label>
                                    <select name="id_rol" id="id_rol" class="form-select" required>
                                        <option value="">Seleccione un rol</option>
                                        <?php if (!empty($roles)): ?>
                                            <?php foreach ($roles as $rol): ?>
                                                <option value="<?= htmlspecialchars($rol['id_rol']) ?>">
                                                    <?= htmlspecialchars($rol['nombre_rol']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">No hay roles disponibles</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-save me-2"></i> Registrar usuario
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>