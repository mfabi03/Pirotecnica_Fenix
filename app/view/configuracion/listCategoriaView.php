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

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($categorias) ? count($categorias) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
            
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-tags text-gold me-2"></i> Lista de Categorías
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    Gestiona las categorías de productos
                </small>
            </div>
            <div class="col-auto">
                <?php if ($puede_crear_categoria): ?>
                <button type="button" class="btn btn-dark-gold" 
                        data-bs-toggle="modal" data-bs-target="#modalRegistrarCategoria"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                    <i class="fas fa-plus me-1"></i> Registrar Categoría
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="categorias">
            <input type="hidden" name="action" value="lista">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
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

    <?php if (isset($mensaje) && !empty($mensaje)): ?>
    <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
        <div class="d-flex align-items-center">
            <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
            <span><?= htmlspecialchars($mensaje) ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    </div>
<?php endif; ?>

    <!-- TABLA DE CATEGORÍAS -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-tags me-2"></i> Categorías Registradas
            </h5>
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
                                        <!-- VER -->
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                onclick="verDetalleCategoria(<?= (int) $cat['id_categoria'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <!-- EDITAR -->
                                        <?php if ($puede_editar_categoria): ?>
                                        <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                onclick="editarCategoria(<?= (int) $cat['id_categoria'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <!-- ELIMINAR -->
                                        <?php if ($puede_eliminar_categoria): ?>
                                        <button type="button" class="btn-action-circle btn-delete" title="Eliminar"
                                                onclick="confirmarEliminarCategoria(<?= (int) $cat['id_categoria'] ?>, '<?= htmlspecialchars(addslashes($cat['nombre_categoria'] ?? '')) ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-tags me-1"></i> 
                Total: <?= $totalRegistros ?> categorías
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODALES                                                      -->
<!-- ============================================================ -->
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_registrar_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_ver_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_editar_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_eliminar_categoria.php'; ?>

<!-- ============================================================ -->
<!-- DATOS EN JSON + JAVASCRIPT                                   -->
<!-- ============================================================ -->
<script id="categoriasData" type="application/json">
<?= json_encode(
    isset($categorias) && is_array($categorias) ? array_values($categorias) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ============================================================
    // INICIALIZAR VALIDADOR EN LOS MODALES
    // ============================================================
    if (typeof Validador !== 'undefined') {
        Validador.init(document.getElementById('modalRegistrarCategoria'));
        Validador.init(document.getElementById('modalEditarCategoria'));
    }

    // ============================================================
    // VALIDAR AL ENVIAR FORMULARIOS
    // ============================================================
    ['formRegistrarCategoria', 'formEditarCategoria'].forEach(function(id) {
        const form = document.getElementById(id);
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!Validador.validarFormulario(id)) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });

    // ... (resto del código: categoriasData, verDetalleCategoria, etc.) ...
});
    // ============================================================
    // DATOS DE CATEGORÍAS
    // ============================================================
    const categoriasData = JSON.parse(document.getElementById('categoriasData').textContent);
    const categoriasPorId = {};
    categoriasData.forEach(function(c) {
        categoriasPorId[parseInt(c.id_categoria)] = c;
    });

    // ============================================================
    // VER DETALLE
    // ============================================================
    window.verDetalleCategoria = function(id) {
        const c = categoriasPorId[id];
        if (!c) return;

        document.getElementById('ver-categoria-nombre').textContent = c.nombre_categoria || 'Sin nombre';
        document.getElementById('ver-categoria-id').textContent = c.id_categoria;
        document.getElementById('ver-categoria-nombre-detalle').textContent = c.nombre_categoria || 'N/A';
        document.getElementById('ver-categoria-descripcion').textContent = c.descripcion || 'Sin descripción';

        new bootstrap.Modal(document.getElementById('modalVerCategoria')).show();
    };

    // ============================================================
    // EDITAR
    // ============================================================
    window.editarCategoria = function(id) {
        const c = categoriasPorId[id];
        if (!c) return;

        document.getElementById('editar-categoria-nombre-header').textContent = c.nombre_categoria || 'Categoría';
        document.getElementById('editar-categoria-id').value = c.id_categoria;
        document.getElementById('editar-categoria-nombre').value = c.nombre_categoria || '';
        document.getElementById('editar-categoria-descripcion').value = c.descripcion || '';

        new bootstrap.Modal(document.getElementById('modalEditarCategoria')).show();
    };

    // ============================================================
    // ELIMINAR
    // ============================================================
    window.confirmarEliminarCategoria = function(id, nombre) {
        document.getElementById('eliminar-categoria-id').value = id;
        document.getElementById('eliminar-categoria-nombre').textContent = nombre || 'Categoría #' + id;
        new bootstrap.Modal(document.getElementById('modalEliminarCategoria')).show();
    };
});
</script>
<!-- ============================================================ -->
<!-- AUTO-CIERRE DE ALERTAS                                       -->
<!-- ============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
        var ms = parseInt(el.dataset.autoDismiss, 10) || 5000;
        setTimeout(function () {
            if (!el.parentNode) return;
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function () { el.remove(); }, 400);
        }, ms);
    });
});
</script>
<?php require_once __DIR__ . '/../footer.php'; ?>