<?php 
// app/view/configuracion/usuario_editar.php
if (!isset($usuario) || empty($usuario)) {
    die('Usuario no encontrado');
}
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="container-fluid px-4">
    <div class="row">
        <!-- Contenido Principal -->
        <div class="col-md-9 col-lg-10">
            
            <div class="card card-custom p-4 mb-4 bg-white">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 font-weight-bold text-dark">✏️ Editar Usuario</h3>
                        <small class="text-muted">Modifique los datos del usuario: <?= htmlspecialchars($usuario['nombre'] ?? '') ?></small>
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

            <!-- Formulario de Edición -->
            <form action="?url=usuarios" method="POST">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario'] ?? '') ?>">
                
                <div class="row g-3">
                    
                    <!-- Datos Personales -->
                    <div class="col-12">
                        <div class="card card-body mb-4">
                            <h5 class="mb-4"><i class="fas fa-user me-2"></i> Datos personales</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre *</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label">Apellido *</label>
                                    <input type="text" name="apellido" id="apellido" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cedula" class="form-label">Cédula *</label>
                                    <input type="text" name="cedula" id="cedula" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['cedula'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono *</label>
                                    <input type="text" name="telefono" id="telefono" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="correo_electronico" class="form-label">Correo electrónico *</label>
                                    <input type="email" name="correo_electronico" id="correo_electronico" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['correo_electronico'] ?? '') ?>" required>
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
                                    <input type="text" name="usuario" id="usuario" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['usuario'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="clave" class="form-label">Nueva Contraseña</label>
                                    <input type="password" name="clave" id="clave" class="form-control" 
                                           placeholder="Dejar vacío para no cambiar">
                                    <small class="text-muted">Solo si desea cambiar la contraseña</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="id_rol" class="form-label">Rol del usuario *</label>
                                    <select name="id_rol" id="id_rol" class="form-select" required>
                                        <option value="">Seleccione un rol</option>
                                        <?php if (!empty($roles)): ?>
                                            <?php foreach ($roles as $rol): ?>
                                                <option value="<?= htmlspecialchars($rol['id_rol']) ?>"
                                                    <?= ($rol['id_rol'] == ($usuario['id_rol'] ?? 0)) ? 'selected' : '' ?>>
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
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Actualizar usuario
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>