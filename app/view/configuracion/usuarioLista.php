<?php
require_once __DIR__ . '/../header.php';
?>

<style>
    /* ==========================================
       ESTILOS - TEXTO NEGRO, ICONOS FUERTES
       ========================================== */
    .dark-card {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08) !important;
    }

    .dark-card .card-header {
        background: #f8f9fa !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06) !important;
    }

    .dark-card .card-header h5 {
        color: #1a1a2e !important;
        font-weight: 700 !important;
    }

    .dark-card .card-footer {
        background: #f8f9fa !important;
        border-top: 1px solid rgba(0, 0, 0, 0.06) !important;
        color: #6c757d !important;
    }

    /* ===== TABLA CON TEXTO NEGRO ===== */
    .dark-card .table {
        color: #1a1a2e !important;
    }

    .dark-card .table thead {
        background: #f0f0f5 !important;
    }

    .dark-card .table thead th {
        color: #1a1a2e !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px;
        border-bottom: 2px solid rgba(243, 156, 18, 0.2) !important;
        padding: 16px 12px !important;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .dark-card .table tbody tr {
        border-bottom: 1px solid rgba(0, 0, 0, 0.04) !important;
        transition: all 0.3s ease;
    }

    .dark-card .table tbody tr:hover {
        background: rgba(243, 156, 18, 0.06) !important;
    }

    .dark-card .table tbody td {
        color: #1a1a2e !important;
        padding: 14px 12px !important;
        vertical-align: middle !important;
        font-weight: 500 !important;
    }

    .dark-card .table tbody td.fw-bold {
        color: #000000 !important;
        font-weight: 700 !important;
    }

    .dark-card .table tbody td.text-muted {
        color: #4a4a5a !important;
    }

    /* ===== BOTONES DE ACCIÓN - COLORES FUERTES ===== */
    .btn-action-circle {
        width: 36px;
        height: 36px;
        border-radius: 50% !important;
        border: none !important;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        font-size: 0.85rem;
        color: #fff !important;
    }

    .btn-action-circle:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-action-circle.btn-view {
        background: #0dcaf0 !important;
        color: #fff !important;
    }

    .btn-action-circle.btn-view:hover {
        background: #0bb5d8 !important;
        box-shadow: 0 6px 20px rgba(13, 202, 240, 0.4);
    }

    .btn-action-circle.btn-edit {
        background: #f39c12 !important;
        color: #fff !important;
    }

    .btn-action-circle.btn-edit:hover {
        background: #d68910 !important;
        box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
    }

    .btn-action-circle.btn-delete {
        background: #dc3545 !important;
        color: #fff !important;
    }

    .btn-action-circle.btn-delete:hover {
        background: #c82333 !important;
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }

    .btn-action-circle:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    /* ===== BADGES ===== */
    .badge-dark-admin {
        background: linear-gradient(135deg, #dc3545, #b02a37) !important;
        color: #ffffff !important;
        padding: 5px 14px !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        font-size: 0.7rem !important;
        letter-spacing: 0.3px;
        display: inline-block;
    }

    .badge-dark-user {
        background: #e9ecef !important;
        color: #495057 !important;
        padding: 5px 14px !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        font-size: 0.7rem !important;
        letter-spacing: 0.3px;
        display: inline-block;
    }

    /* ==========================================
       ===== NUEVOS ESTILOS PARA CABECERAS OSCURAS =====
       ========================================== */

    /* ===== TARJETA DE TÍTULO - FONDO OSCURO ===== */
    .dark-header-card {
        background: #1a1a2e !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
    }

    .dark-header-card .dark-title {
        color: #ffffff !important;
    }

    .dark-header-card .dark-title .text-gold {
        color: #f39c12 !important;
    }

    .dark-header-card .dark-title small {
        color: rgba(255, 255, 255, 0.4) !important;
    }

    .dark-header-card .dark-title small i {
        color: rgba(255, 255, 255, 0.2);
    }

    /* ===== BÚSQUEDA EN FONDO OSCURO ===== */
    .dark-header-card .dark-search .input-group-text {
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        border-right: none !important;
        color: rgba(255, 255, 255, 0.3) !important;
        border-radius: 12px 0 0 12px !important;
    }

    .dark-header-card .dark-search .form-control {
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        border-left: none !important;
        color: #ffffff !important;
        border-radius: 0 12px 12px 0 !important;
    }

    .dark-header-card .dark-search .form-control::placeholder {
        color: rgba(255, 255, 255, 0.25) !important;
    }

    .dark-header-card .dark-search .form-control:focus {
        border-color: #f39c12 !important;
        box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.08) !important;
        background: rgba(255, 255, 255, 0.12) !important;
    }

    /* ===== BOTÓN BUSCAR EN FONDO OSCURO ===== */
    .dark-header-card .btn-dark-search {
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        color: rgba(255, 255, 255, 0.5) !important;
        border-radius: 12px !important;
        transition: all 0.3s ease;
        font-weight: 500;
        padding: 8px 0;
    }

    .dark-header-card .btn-dark-search:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
    }

    /* ===== TABLA DE USUARIOS - CABECERA OSCURA ===== */
    .dark-table-header .card-header {
        background: #1a1a2e !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        border-radius: 16px 16px 0 0 !important;
    }

    .dark-table-header .card-header h5 {
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    .dark-table-header .card-header .btn-dark-gold {
        background: linear-gradient(135deg, #f39c12, #e67e22) !important;
        border: none !important;
        color: #fff !important;
        font-weight: 600 !important;
        padding: 8px 22px !important;
        border-radius: 50px !important;
        transition: all 0.3s ease;
    }

    .dark-table-header .card-header .btn-dark-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(243, 156, 18, 0.3);
        color: #fff !important;
    }

    /* ===== BADGES ===== */
    .badge-dark-admin {
        background: linear-gradient(135deg, #dc3545, #b02a37) !important;
        color: #ffffff !important;
        padding: 5px 14px !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        font-size: 0.7rem !important;
        letter-spacing: 0.3px;
        display: inline-block;
    }

    .badge-dark-user {
        background: #e9ecef !important;
        color: #495057 !important;
        padding: 5px 14px !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        font-size: 0.7rem !important;
        letter-spacing: 0.3px;
        display: inline-block;
    }
</style>

<div class="container-fluid px-4">
    <div class="row">
        <!-- Contenido Principal -->
        <div class="col-md-9 col-lg-10">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-xl-6">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-users text-gold me-2"></i> Registro de Usuarios
                        </h3>
                       <small style="color: #ffffff !important;">
                            <i class="fas fa-database me-2"></i> 
                            <?= isset($usuarios) ? count($usuarios) : 0 ?> usuarios registrados
                       </small>
                    </div>
                    <div class="col-xl-6">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="url" value="usuarios">
                            <input type="hidden" name="action" value="lista">
                            <div class="col-8">
                                <div class="dark-search input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="busqueda" class="form-control shadow-none" 
                                           placeholder="Buscar por nombre o cédula..."
                                           value="<?= htmlspecialchars($busqueda ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-dark-search w-100" type="submit">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ==========================================
                 TABLA DE USUARIOS - CABECERA OSCURA
                 ========================================== -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-user me-2"></i> Usuarios Registrados
                    </h5>
                    <a href="?url=usuarios&action=registrar" class="btn btn-dark-gold">
                        <i class="fas fa-plus me-1"></i> Registrar
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Nombre</th>
                                <th class="py-3">Apellido</th>
                                <th class="py-3">Cédula</th>
                                <th class="py-3">Teléfono</th>
                                <th class="py-3">Correo</th>
                                <th class="py-3">Rol</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($usuarios) && is_array($usuarios) && count($usuarios) > 0): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($usuario['id_usuario'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($usuario['nombre'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['apellido'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['cedula'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['telefono'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['correo_electronico'] ?? '') ?></td>
                                        <td>
                                            <?php 
                                                $rol = $usuario['rol'] ?? 0;
                                                if ($rol == 1):
                                            ?>
                                                <span class="badge-dark-admin">
                                                    <i class="fas fa-crown me-1"></i> Administrador
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-dark-user">
                                                    <i class="fas fa-user me-1"></i> Usuario
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <!-- Ver -->
                                                <a href="?url=usuarios&action=ver&id=<?= $usuario['id_usuario'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <!-- Editar -->
                                                <a href="?url=usuarios&action=editar&id=<?= $usuario['id_usuario'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <!-- Eliminar -->
                                                <form method="POST" action="?url=usuarios" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                                            <?= ($usuario['id_usuario'] ?? 0) == $_SESSION['id_usuario'] ? 'disabled' : '' ?>>
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                            <p class="mb-0">No hay usuarios registrados</p>
                                            <small>Comienza registrando un nuevo usuario</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>