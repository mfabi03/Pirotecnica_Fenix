<?php
// app/view/nota_salida/listNotasalidaView.php
require_once __DIR__ . '/../header.php';

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
$puede_crear_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Salida', 'crear') : false;
$puede_anular_nota = $db ? PermisoHelper::tienePermiso($db, $id_rol_actual, 'Notas de Salida', 'eliminar') : false;

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($notas) ? count($notas) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
            
    <!-- TARJETA DE TÍTULO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-sign-out-alt text-gold me-2"></i> Lista de Notas de Salida
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    Gestiona las notas de salida de productos
                </small>
            </div>
            <div class="col-auto">
                <?php if ($puede_crear_nota): ?>
                <a href="?url=notasalida&type=create" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                    <i class="fas fa-plus me-1"></i> Registrar Nota de Salida
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="notasalida">
            <input type="hidden" name="type" value="list">
            
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
                       list="listaNotasSalida"
                       placeholder="Buscar por cliente, encargado o ID..."
                       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                <datalist id="listaNotasSalida">
                    <?php if (isset($notas) && is_array($notas)): ?>
                        <?php foreach ($notas as $n): 
                            $nombreCliente = !empty($n['cliente_razon_social']) 
                                ? $n['cliente_razon_social'] 
                                : trim(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                            $nombreEncargado = trim(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? ''));
                        ?>
                            <option value="<?= htmlspecialchars($nombreCliente) ?>">
                            <option value="<?= htmlspecialchars($nombreEncargado) ?>">
                            <option value="<?= $n['id_nota_salida'] ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-gold w-100">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
            
            <div class="col-md-2">
                <a href="?url=notasalida&type=list" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- MENSAJES -->
    <?php if (isset($success)): ?>
        <div class="alert dark-alert-success alert-dismissible fade show shadow-sm border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <span><?= htmlspecialchars($success) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span><?= htmlspecialchars($error) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- CUADROS DE RESUMEN (MISMO ESTILO QUE NOTAS DE ENTRADA) -->
    <div class="row mb-4 g-3">
        
        <!-- TARJETA 1: TOTAL DE SALIDAS -->
        <div class="col-md-6">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(0, 0, 0); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-sign-out-alt me-1"></i> Total de Salidas
                        </h6>
                        <h2 style="color: #fdc304; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            <?= (int) ($resumen['total_salidas'] ?? $totalRegistros ?? 0) ?>
                        </h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(243,156,18,0.12); display: flex; align-items: center; justify-content: center; color: #f39c12; font-size: 1.5rem;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TARJETA 2: TOTAL ANULADAS -->
        <div class="col-md-6">
            <div class="dark-card card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center" style="padding: 20px 24px;">
                    <div>
                        <h6 class="card-title" style="color: rgb(10, 1, 1); font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">
                            <i class="fas fa-ban me-1"></i> Anuladas
                        </h6>
                        <h2 style="color: #fa0101; font-weight: 700; font-size: 2.2rem; margin: 0;">
                            <?= (int) ($resumen['total_anuladas'] ?? 0) ?>
                        </h2>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(220,53,69,0.12); display: flex; align-items: center; justify-content: center; color: #dc3545; font-size: 1.5rem;">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- TABLA DE NOTAS DE SALIDA -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-list me-2"></i> Notas Registradas
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="tablaNotas">
                <thead>
                    <tr>
                        <th class="ps-4 py-3">ID</th>
                        <th class="py-3">Fecha</th>
                        <th class="py-3">Cliente</th>
                        <th class="py-3">Tipo</th>
                        <th class="py-3">Productos</th>
                        <th class="py-3">Categorías</th>
                        <th class="py-3">Unidades</th>
                        <th class="py-3">Encargado</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyNotas">
                    <?php if (empty($notas)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">No hay notas de salida registradas</p>
                                    <small>Comienza registrando una nueva nota</small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notas as $n): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $n['id_nota_salida'] ?></td>
                                <td><?= formatoFechaVenezuela($n['fecha']) ?></td>
                                <td>
                                    <?php 
                                    if (!empty($n['cliente_razon_social'])) {
                                        echo htmlspecialchars($n['cliente_razon_social']);
                                    } else {
                                        echo htmlspecialchars(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? ''));
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?= ($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? 'rgba(13,110,253,0.15)' : 'rgba(40,167,69,0.15)' ?>; color: <?= ($n['tipo_cliente'] ?? 'Natural') === 'Jurídico' ? '#0d6efd' : '#28a745' ?>; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                        <?= $n['tipo_cliente'] ?? 'Natural' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($n['productos_lista'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($n['categorias_lista'] ?? 'N/A') ?></td>
                                <td class="fw-bold"><?= $n['total_unidades'] ?? 0 ?></td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars(($n['usuario_nombre'] ?? '') . ' ' . ($n['usuario_apellido'] ?? ''));
                                    ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- VER DETALLE (ABRE MODAL) -->
                                        <button type="button" class="btn-action-circle btn-view" 
                                                title="Ver Detalle"
                                                onclick="verDetalleNota(<?= (int) $n['id_nota_salida'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <!-- ANULAR (ABRE MODAL) -->
                                        <?php if ($puede_anular_nota): ?>
                                        <button type="button" class="btn-action-circle btn-delete" 
                                                title="Anular Nota"
                                                onclick="confirmarAnularNota(<?= (int) $n['id_nota_salida'] ?>, '<?php 
                                                    if (!empty($n['cliente_razon_social'])) {
                                                        echo htmlspecialchars(addslashes($n['cliente_razon_social']));
                                                    } else {
                                                        echo htmlspecialchars(addslashes(($n['cliente_nombre'] ?? '') . ' ' . ($n['cliente_apellido'] ?? '')));
                                                    }
                                                ?>')">
                                            <i class="fas fa-ban"></i>
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
                <i class="fas fa-file-invoice me-1"></i> 
                Total: <?= $totalRegistros ?> notas
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODAL 1: VER DETALLE DE NOTA (HEADER OSCURO + BODY BLANCO)   -->
<!-- ============================================================ -->
<div class="modal fade" id="modalVerNota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <!-- HEADER OSCURO -->
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-eye me-2" style="color: #f39c12;"></i> 
                    Detalle de Nota de Salida <span id="ver-nota-id" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <!-- ESTADO DE CARGA -->
                <div id="ver-nota-cargando" class="text-center py-5">
                    <div class="spinner-border" style="color: #f39c12;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3" style="color: #6c757d;">Cargando información de la nota...</p>
                </div>

                <!-- ESTADO DE ERROR -->
                <div id="ver-nota-error" class="text-center py-5" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3" style="color: #dc3545;"></i>
                    <p id="ver-nota-error-msg" style="color: #6c757d;">Error al cargar la nota</p>
                </div>

                <!-- CONTENIDO DE LA NOTA -->
                <div id="ver-nota-contenido" style="display: none;">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 14px;">
                                    <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos de la Nota
                                </h6>
                                <div class="mb-2">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Fecha</label>
                                    <p id="ver-nota-fecha" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                                </div>
                                <div class="mb-0">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Encargado</label>
                                    <p id="ver-nota-encargado" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8f9fa; border-radius: 12px; height: 100%;">
                                <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 14px;">
                                    <i class="fas fa-user me-2" style="color: #f39c12;"></i> Cliente
                                </h6>
                                <div class="mb-2">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Nombre</label>
                                    <p id="ver-nota-cliente" style="color: #1a1a2e; margin: 0; font-weight: 600;">—</p>
                                </div>
                                <div class="mb-0">
                                    <label style="color: #6c757d; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">Documento</label>
                                    <p id="ver-nota-documento" style="color: #1a1a2e; margin: 0;">—</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA DE PRODUCTOS -->
                    <div class="card" style="border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3" style="background: #1a1a2e; border: none;">
                            <h6 class="m-0" style="color: #ffffff; font-weight: 700;">
                                <i class="fas fa-box me-2" style="color: #f39c12;"></i> Productos de la Nota
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th class="ps-4 py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase;">ID</th>
                                        <th class="py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase;">Producto</th>
                                        <th class="py-3" style="color: #495057; font-size: 0.75rem; text-transform: uppercase;">Categoría</th>
                                        <th class="pe-4 py-3 text-center" style="color: #495057; font-size: 0.75rem; text-transform: uppercase;">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody id="ver-nota-productos-tbody">
                                </tbody>
                                <tfoot>
                                    <tr style="border-top: 2px solid rgba(243,156,18,0.2);">
                                        <th colspan="3" class="ps-4 py-2 text-end" style="color: #495057; font-size: 0.85rem;">Total Unidades:</th>
                                        <th class="pe-4 py-2 text-center" id="ver-nota-total-unidades" style="color: #f39c12; font-size: 1.1rem; font-weight: 700;">0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODAL 2: ANULAR NOTA (MISMO ESTILO QUE MODALES DE CLIENTES)  -->
<!-- ============================================================ -->
<?php if ($puede_anular_nota): ?>
<div class="modal fade" id="modalAnularNota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <!-- HEADER OSCURO CON ICONO ROJO -->
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(220,53,69,0.3); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i> Anular Nota de Salida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <form method="POST" action="?url=notasalida&type=eliminar">
                <input type="hidden" name="id_nota_salida" id="anular-nota-id">
                
                <!-- BODY BLANCO -->
                <div class="modal-body" style="padding: 20px 24px; color: #1a1a2e;">
                    
                    <p style="margin-bottom: 16px; font-weight: 500;">
                        ¿Estás seguro de anular esta nota de salida?
                    </p>
                    
                    <!-- PANEL DE INFO CON BORDE ROJO -->
                    <div class="p-3 mb-3" style="background: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 8px;">
                        <strong style="color: #6c757d; display: block; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;">
                            Cliente de la nota
                        </strong>
                        <span id="anular-nota-cliente" style="color: #1a1a2e; font-weight: 600; font-size: 1rem;">—</span>
                    </div>

                    <!-- ALERTA ROJA CLARA -->
                    <div class="alert" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px; margin-bottom: 16px;">
                        <i class="fas fa-info-circle me-2"></i>
                        Al anular, el stock de los productos se <strong>revertirá automáticamente</strong>.
                    </div>

                    <!-- CAMPO DE MOTIVO -->
                    <div class="mb-0">
                        <label class="form-label fw-bold" style="color: #1a1a2e; font-size: 0.85rem;">
                            Motivo de Anulación <span class="text-danger">*</span>
                        </label>
                        <textarea name="motivo_eliminacion" 
                                  class="form-control" 
                                  rows="3" 
                                  required 
                                  placeholder="Describe el motivo de la anulación..."
                                  style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); resize: vertical;"></textarea>
                        <small style="color: #6c757d; font-size: 0.7rem;">
                            Este motivo será registrado en el sistema para auditoría.
                        </small>
                    </div>
                </div>

                <!-- FOOTER CON BOTONES -->
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn"
                            style="background: #dc3545; border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                        <i class="fas fa-ban me-1"></i> Sí, anular
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ============================================================ -->
<!-- JAVASCRIPT                                                    -->
<!-- ============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ============================================================
    // VER DETALLE DE NOTA (AJAX)
    // ============================================================
    window.verDetalleNota = function(id) {
        // Resetear estados
        document.getElementById('ver-nota-cargando').style.display = 'block';
        document.getElementById('ver-nota-error').style.display = 'none';
        document.getElementById('ver-nota-contenido').style.display = 'none';
        document.getElementById('ver-nota-id').textContent = '#' + id;

        // Mostrar modal
        new bootstrap.Modal(document.getElementById('modalVerNota')).show();

        // Fetch al endpoint
        fetch('?url=notasalida&type=detalle_json&id=' + id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('ver-nota-cargando').style.display = 'none';
                
                if (!data.success) {
                    document.getElementById('ver-nota-error').style.display = 'block';
                    document.getElementById('ver-nota-error-msg').textContent = data.error || 'Error al cargar la nota';
                    return;
                }

                const nota = data.nota;
                document.getElementById('ver-nota-contenido').style.display = 'block';

                // Datos generales
                document.getElementById('ver-nota-fecha').textContent = Validador.formatearFecha(nota.fecha);
                document.getElementById('ver-nota-encargado').textContent = nota.usuario_responsable || 'N/A';
                document.getElementById('ver-nota-cliente').textContent = nota.cliente || 'N/A';
                document.getElementById('ver-nota-documento').textContent = nota.cedula || nota.rif || 'N/A';

                // Productos
                const tbody = document.getElementById('ver-nota-productos-tbody');
                tbody.innerHTML = '';
                let totalUnidades = 0;

                if (nota.detalles && nota.detalles.length > 0) {
                    nota.detalles.forEach(function(d) {
                        totalUnidades += parseInt(d.cantidad) || 0;
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="ps-4" style="color: #1a1a2e;">${d.id_producto}</td>
                            <td style="color: #1a1a2e;">${escapeHtml(d.nombre_producto)}</td>
                            <td style="color: #6c757d;">${escapeHtml(d.nombre_categoria || 'Sin categoría')}</td>
                            <td class="pe-4 text-center fw-bold" style="color: #1a1a2e;">${d.cantidad}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-3" style="color: #6c757d;">
                                <i class="fas fa-box-open me-2"></i> No hay productos en esta nota
                            </td>
                        </tr>
                    `;
                }

                document.getElementById('ver-nota-total-unidades').textContent = totalUnidades;
            })
            .catch(err => {
                document.getElementById('ver-nota-cargando').style.display = 'none';
                document.getElementById('ver-nota-error').style.display = 'block';
                document.getElementById('ver-nota-error-msg').textContent = 'Error de conexión: ' + err.message;
            });
    };

    // ============================================================
    // ANULAR NOTA
    // ============================================================
    window.confirmarAnularNota = function(id, cliente) {
        document.getElementById('anular-nota-id').value = id;
        document.getElementById('anular-nota-cliente').textContent = cliente || 'Nota #' + id;
        new bootstrap.Modal(document.getElementById('modalAnularNota')).show();
    };

    // ============================================================
    // HELPER: ESCAPAR HTML
    // ============================================================
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m];
        });
    }
});
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>