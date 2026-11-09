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

// Saber si estamos en la vista de eliminados
$esVistaEliminados = (($estado_categoria ?? 'todos') === 'inactivos');
?>

<!-- ESTILOS PARA LOS BOTONES DE ACCIÓN CIRCULARES -->
<style>
.btn-action-circle {
    width: 36px; height: 36px; border-radius: 50%; border: none;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s ease;
    padding: 0; font-size: 0.85rem; color: #ffffff;
    text-decoration: none;
}
.btn-action-circle:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); filter: brightness(1.1); }
.btn-action-circle.btn-view { background: rgba(13,110,253,0.15); color: #0d6efd; }
.btn-action-circle.btn-view:hover { background: #0d6efd; color: #fff; }
.btn-action-circle.btn-edit { background: rgba(243,156,18,0.15); color: #f39c12; }
.btn-action-circle.btn-edit:hover { background: #f39c12; color: #fff; }
.btn-action-circle.btn-delete { background: rgba(220,53,69,0.15); color: #dc3545; }
.btn-action-circle.btn-delete:hover { background: #dc3545; color: #fff; }
.btn-action-circle.btn-reactivar { background: rgba(40,167,69,0.15); color: #28a745; }
.btn-action-circle.btn-reactivar:hover { background: #28a745; color: #fff; }
</style>

<div class="col-md-8 col-lg-12">
            
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-tags text-gold me-2"></i> 
                    <?= $esVistaEliminados ? 'Categorías Eliminadas' : 'Lista de Categorías' ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= $esVistaEliminados ? 'Categorías inactivas en el sistema' : 'Gestiona las categorías de productos' ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=categorias&action=lista" class="btn btn-secondary" style="border-radius: 50px; padding: 8px 22px; font-weight: 600;">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Categorías Activas
                    </a>
                <?php else: ?>
                    <?php if ($puede_crear_categoria): ?>
                    <button type="button" class="btn btn-dark-gold" 
                            data-bs-toggle="modal" data-bs-target="#modalRegistrarCategoria"
                            style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                        <i class="fas fa-plus me-1"></i> Registrar Categoría
                    </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form id="formFiltroCategorias" method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="categorias">
            <input type="hidden" name="action" value="lista">
            <input type="hidden" name="estado_categoria" value="<?= $esVistaEliminados ? 'inactivos' : 'activos' ?>">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-no-search="true">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small text-dark mb-0">Buscar</label>
                <input type="text" name="busqueda" class="form-control form-control-sm" 
                       list="listaCategorias"
                       placeholder="Nombre o ID..."
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

            <!-- 🔥 BOTÓN VER ELIMINADOS EN LOS FILTROS -->
            <div class="col-md-2">
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=categorias&action=lista" class="btn btn-success w-100" style="background: #28a745; border: none; color: #fff;">
                        <i class="fas fa-undo-alt me-1"></i> Ver Activos
                    </a>
                <?php else: ?>
                    <a href="?url=categorias&action=lista&estado_categoria=inactivos" class="btn btn-danger w-100" style="background: #dc3545; border: none; color: #fff;">
                        <i class="fas fa-trash-alt me-1"></i> Ver Eliminados
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- MENSAJES -->
    <?php
    $mapaTipos = [
        'success' => ['clase' => 'dark-alert-success', 'icono' => 'fa-check-circle'],
        'danger'  => ['clase' => 'dark-alert-danger',  'icono' => 'fa-exclamation-circle'],
        'error'   => ['clase' => 'dark-alert-danger',  'icono' => 'fa-exclamation-circle'],
        'warning' => ['clase' => 'dark-alert-warning', 'icono' => 'fa-exclamation-triangle'],
        'info'    => ['clase' => 'dark-alert-info',    'icono' => 'fa-info-circle'],
    ];
    $estiloMsg = $mapaTipos[$tipo_mensaje ?? 'info'] ?? $mapaTipos['info'];
    ?>
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?= $estiloMsg['clase'] ?> alert-dismissible fade show border-0" role="alert" data-auto-dismiss="5000">
            <div class="d-flex align-items-center">
                <i class="fas <?= $estiloMsg['icono'] ?> me-3"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- TABLA DE CATEGORÍAS -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-tags me-2"></i> 
                <?= $esVistaEliminados ? 'Categorías Inactivas' : 'Categorías Registradas' ?>
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">NOMBRE</th>
                        <th class="py-3">DESCRIPCIÓN</th>
                        <th class="py-3 text-center">STATUS</th>
                        <th class="pe-4 py-3 text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-tags fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">
                                        <?= $esVistaEliminados ? 'No hay categorías inactivas' : 'No hay categorías registradas' ?>
                                    </p>
                                    <small><?= $esVistaEliminados ? 'Las categorías activas están en la lista principal' : 'Comienza registrando una nueva categoría' ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $cat): ?>
                            <?php $estaActiva = (($cat['eliminado'] ?? 0) == 0); ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($cat['nombre_categoria'] ?? '') ?></td>
                                <td><?= htmlspecialchars($cat['descripcion'] ?? 'N/A') ?></td>
                                
                                <td class="text-center">
                                    <?php if (!$estaActiva): ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.12); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-times-circle me-1"></i> Inactivo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.12); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- VER -->
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                onclick="verDetalleCategoria(<?= (int) $cat['id_categoria'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if (!$estaActiva): ?>
                                            <!-- INACTIVA: SOLO REACTIVAR -->
                                            <?php if ($puede_editar_categoria): ?>
                                            <button type="button" class="btn-action-circle btn-reactivar" title="Reactivar Categoría"
                                                    onclick="confirmarReactivarCategoria(<?= (int) $cat['id_categoria'] ?>, '<?= htmlspecialchars(addslashes($cat['nombre_categoria'] ?? '')) ?>')">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- ACTIVA: EDITAR Y ELIMINAR -->
                                            <?php if ($puede_editar_categoria): ?>
                                            <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                    onclick="editarCategoria(<?= (int) $cat['id_categoria'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($puede_eliminar_categoria): ?>
                                            <button type="button" class="btn-action-circle btn-delete" title="Eliminar"
                                                    onclick="confirmarEliminarCategoria(<?= (int) $cat['id_categoria'] ?>, '<?= htmlspecialchars(addslashes($cat['nombre_categoria'] ?? '')) ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <?php endif; ?>
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

<!-- MODALES -->
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_registrar_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_ver_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_editar_categoria.php'; ?>
<?php require_once __DIR__ . '/subcarpetas_categoria/modal_eliminar_categoria.php'; ?>

<!-- MODAL REACTIVAR -->
<?php if ($puede_editar_categoria): ?>
<div class="modal fade" id="modalReactivarCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(40,167,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-undo-alt me-2" style="color: #28a745;"></i> Reactivar Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <form method="POST" action="?url=categorias&action=reactivar">
                <input type="hidden" name="accion" value="reactivar">
                <input type="hidden" name="id_categoria" id="reactivar-categoria-id">
                
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    <p style="margin-bottom: 12px;">¿Deseas reactivar esta categoría?</p>
                    
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px;">Categoría a reactivar</strong>
                        <span id="reactivar-categoria-nombre" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>

                    <div class="alert" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 12px; padding: 12px 16px; margin-bottom: 0;">
                        <i class="fas fa-check-circle me-2"></i>
                        La categoría volverá a estar <strong>Activa</strong> y disponible para asignarse a productos.
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn"
                            style="background: #28a745; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-check me-1"></i> Sí, reactivar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- DATOS EN JSON + JAVASCRIPT -->
<script id="categoriasData" type="application/json">
<?= json_encode(
    isset($categorias) && is_array($categorias) ? array_values($categorias) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // 🔥 AJAX para el filtro (Igual que en Clientes/Productos)
    const formCategorias = document.getElementById('formFiltroCategorias');
    if (formCategorias) {
        const enviarFiltroCategorias = function () {
            const formData = new FormData(formCategorias);
            formData.set('ajax', '1');

            fetch('?url=categorias&action=lista', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.html) return;
                const wrap = document.createElement('div');
                wrap.innerHTML = data.html;
                const nuevaVista = wrap.querySelector('.col-md-8.col-lg-12');
                if (!nuevaVista) return;
                const contenedorActual = document.querySelector('.col-md-8.col-lg-12');
                if (contenedorActual) contenedorActual.replaceWith(nuevaVista);
            })
            .catch(() => {
                formCategorias.submit();
            });
        };

        formCategorias.addEventListener('submit', function (e) {
            e.preventDefault();
            enviarFiltroCategorias();
        });

        formCategorias.querySelectorAll('select').forEach(function (selectEl) {
            selectEl.addEventListener('change', function () {
                enviarFiltroCategorias();
            });
        });
    }
    
    // Lógica de los modales
    const categoriasData = JSON.parse(document.getElementById('categoriasData').textContent);
    const categoriasPorId = {};
    categoriasData.forEach(function(c) {
        categoriasPorId[parseInt(c.id_categoria)] = c;
    });

    // VER DETALLE
    window.verDetalleCategoria = function(id) {
        const c = categoriasPorId[id];
        if (!c) return;

        document.getElementById('ver-categoria-nombre').textContent = c.nombre_categoria || 'Sin nombre';
        
        var badgeEstatus = document.getElementById('ver-categoria-estatus');
        if (badgeEstatus) {
            if (c.eliminado == 1) {
                badgeEstatus.textContent = 'Inactivo';
                badgeEstatus.style.background = 'rgba(220,53,69,0.15)';
                badgeEstatus.style.color = '#dc3545';
            } else {
                badgeEstatus.textContent = 'Activo';
                badgeEstatus.style.background = 'rgba(40,167,69,0.15)';
                badgeEstatus.style.color = '#28a745';
            }
        }

        document.getElementById('ver-categoria-nombre-detalle').textContent = c.nombre_categoria || 'N/A';
        document.getElementById('ver-categoria-descripcion').textContent = c.descripcion || 'Sin descripción';

        new bootstrap.Modal(document.getElementById('modalVerCategoria')).show();
    };

    // EDITAR
    window.editarCategoria = function(id) {
        const c = categoriasPorId[id];
        if (!c) return;

        document.getElementById('editar-categoria-nombre-header').textContent = c.nombre_categoria || 'Categoría';
        document.getElementById('editar-categoria-id').value = c.id_categoria;
        document.getElementById('editar-categoria-nombre').value = c.nombre_categoria || '';
        document.getElementById('editar-categoria-descripcion').value = c.descripcion || '';

        new bootstrap.Modal(document.getElementById('modalEditarCategoria')).show();
    };

    // ELIMINAR
    window.confirmarEliminarCategoria = function(id, nombre) {
        document.getElementById('eliminar-categoria-id').value = id;
        document.getElementById('eliminar-categoria-nombre').textContent = nombre || 'Categoría #' + id;
        new bootstrap.Modal(document.getElementById('modalEliminarCategoria')).show();
    };

    // REACTIVAR
    window.confirmarReactivarCategoria = function(id, nombre) {
        document.getElementById('reactivar-categoria-id').value = id;
        document.getElementById('reactivar-categoria-nombre').textContent = nombre || 'Categoría #' + id;
        new bootstrap.Modal(document.getElementById('modalReactivarCategoria')).show();
    };
});
</script>

<script>
// Auto-cierre de alertas
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

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>