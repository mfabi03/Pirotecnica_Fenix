<?php
// app/view/clientes/listClienteView.php
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
$puede_crear_cliente = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Clientes', 'crear') : false;
$puede_editar_cliente = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Clientes', 'actualizar') : false;
$puede_eliminar_cliente = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Clientes', 'eliminar') : false;

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($clientes) ? count($clientes) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
            
            <!-- TARJETA DE TÍTULO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-users text-gold me-2"></i> Lista de Clientes
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona los clientes registrados en el sistema
                        </small>
                    </div>
                    <div class="col-auto">
                        <?php if ($puede_crear_cliente): ?>
                        <a href="?url=clientes&type=register" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Cliente
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
                    <input type="hidden" name="url" value="clientes">
                    <input type="hidden" name="type" value="list">
                    
                    <!-- ✅ SELECT "MOSTRAR" ARRIBA -->
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
                               list="listaClientes"
                               placeholder="Buscar por nombre, cédula, RIF o razón social..."
                               value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        <datalist id="listaClientes">
                            <?php
                            $sugerencias = [];
                            if (!empty($clientes) && is_array($clientes)):
                                foreach ($clientes as $c):
                                    $razon  = trim($c['razon_social'] ?? '');
                                    $nombre = trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                                    $doc    = trim($c['cedula'] ?? $c['rif'] ?? '');
                                    if ($razon  !== '') $sugerencias[$razon]  = 1;
                                    if ($nombre !== '') $sugerencias[$nombre] = 1;
                                    if ($doc    !== '') $sugerencias[$doc]    = 1;
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
                        <a href="?url=clientes&type=list" class="btn btn-secondary w-100">
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

            <!-- TABLA DE CLIENTES -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-users me-2"></i> Clientes Registrados
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">Cédula / RIF</th>
                                <th class="py-3">Nombre / Razón Social</th>
                                <th class="py-3">Tipo</th>
                                <th class="py-3">Teléfono</th>
                                <th class="py-3">Correo</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($clientes) && is_array($clientes) && count($clientes) > 0): ?>
                                <?php foreach ($clientes as $c): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($c['cedula'] ?? $c['rif'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (($c['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                                <strong><?= htmlspecialchars($c['razon_social'] ?? 'N/A') ?></strong>
                                            <?php else: ?>
                                                <?= htmlspecialchars(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (($c['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                                <span class="badge" style="background: rgba(13,110,253,0.15); color: #0d6efd; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <i class="fas fa-building me-1"></i> Jurídico
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: rgba(40,167,69,0.15); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <i class="fas fa-user me-1"></i> Natural
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($c['telefono'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($c['correo_electronico'] ?? 'N/A') ?></td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="?url=clientes&type=view&id=<?= $c['id_cliente'] ?? 0 ?>" 
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($puede_editar_cliente): ?>
                                                <a href="?url=clientes&type=<?= ($c['tipo_cliente'] ?? '') === 'Jurídico' ? 'edit_juridico' : 'edit' ?>&id=<?= $c['id_cliente'] ?? 0 ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($puede_eliminar_cliente): ?>
                                                <form method="POST" action="?url=clientes&type=delete" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_cliente" value="<?= $c['id_cliente'] ?? 0 ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este cliente?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-users fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay clientes registrados</p>
                                            <small>Comienza registrando un nuevo cliente</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- ✅ PAGINACIÓN: TOTAL IZQ + BOTONES DER -->
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-users me-1"></i> 
                        Total: <?= $totalRegistros ?> clientes
                    </span>
                    <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
                </div>
            </div>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>