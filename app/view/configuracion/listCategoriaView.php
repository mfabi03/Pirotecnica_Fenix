<?php
// app/view/configuracion/listCategoriaView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

use App\Pirotecnicafenix\Helpers\PermisoHelper;
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

// Crear conexión si no existe
if (!isset($db) || $db === null) {
    try {
        $db = (new ConnectDB())->getConnection();
    } catch (Exception $e) {
        $db = null;
    }
}

// Obtener permisos del usuario actual
$id_rol_actual = $_SESSION['id_rol'] ?? 0;
$puede_crear_categoria = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Categorias', 'crear') : false;
$puede_editar_categoria = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Categorias', 'actualizar') : false;
$puede_eliminar_categoria = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Categorias', 'eliminar') : false;
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-9 col-lg-10">
            
            <!-- TARJETA DE TÍTULO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-tags text-gold me-2"></i> lista de Categorías
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona las categorías de productos
                        </small>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <form method="GET" class="me-3">
                            <input type="hidden" name="url" value="categorias">
                            <input type="hidden" name="action" value="lista">
                            <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                        </form>
                        <?php if ($puede_crear_categoria): ?>
                        <a href="?url=categorias&action=registrar" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Categoría
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-center" autocomplete="off">
                    <input type="hidden" name="url" value="categorias">
                    <input type="hidden" name="action" value="lista">
                    
                    <div class="col-md-8">
                        <input type="text" name="busqueda" class="form-control" 
                               list="listaCategorias"
                               placeholder="Buscar por nombre o ID..."
                               value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        <datalist id="listaCategorias">
                            <?php
                            $sugerencias = [];
                            if (!empty($categorias) && is_array($categorias)):
                                foreach ($categorias as $cat):
                                    $nombre = trim($cat['nombre_categoria'] ?? '');
                                    $id     = trim((string)($cat['id_categoria'] ?? ''));
                                    if ($nombre !== '') $sugerencias[$nombre]   = 1;
                                    if ($id     !== '') $sugerencias['#' . $id] = 1;
                                endforeach;
                            endif;

                            foreach (array_keys($sugerencias) as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gold w-100">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                    
                    <div class="col-md-2">
                        <a href="?url=categorias&action=lista" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- MENSAJES -->
            <?php if (isset($mensaje) && !empty($mensaje)): ?>
                <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                        <span><?= htmlspecialchars($mensaje) ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABLA DE CATEGORÍAS -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-tags me-2"></i> Categorías Registradas
                    </h5>
                    <span class="text-muted small" style="color: rgba(255,255,255,0.3) !important; font-size: 0.75rem;">
                        <i class="fas fa-database me-1"></i> 
                        <?= isset($categorias) ? count($categorias) : 0 ?> registros
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Nombre</th>
                                <th class="py-3">Descripción</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categorias)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-tags fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay categorías registradas</p>
                                            <small>Comienza registrando una nueva categoría</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categorias as $cat): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($cat['id_categoria'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($cat['nombre_categoria'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($cat['descripcion'] ?? 'N/A') ?></td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <!-- Ver: siempre visible -->
                                                <a href="?url=categorias&action=ver&id=<?= $cat['id_categoria'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <!-- Editar: solo si tiene permiso -->
                                                <?php if ($puede_editar_categoria): ?>
                                                <a href="?url=categorias&action=editar&id=<?= $cat['id_categoria'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <!-- Eliminar: solo si tiene permiso -->
                                                <?php if ($puede_eliminar_categoria): ?>
                                                <form method="POST" action="?url=categorias&action=eliminar" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_categoria" value="<?= $cat['id_categoria'] ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete" title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-2 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-tags me-1"></i> 
                        Total: <?= isset($categorias) ? count($categorias) : 0 ?> categorías
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>