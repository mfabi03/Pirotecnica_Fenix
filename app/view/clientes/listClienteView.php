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

// Saber si estamos en la vista de eliminados (para cambiar el título)
$esVistaEliminados = (($estado_cliente ?? 'todos') === 'inactivos');
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
                    <i class="fas fa-users text-gold me-2"></i> 
                    <?= $esVistaEliminados ? 'Clientes Eliminados' : 'Lista de Clientes' ?>
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    <?= $esVistaEliminados ? 'Clientes inactivos en el sistema' : 'Gestiona los clientes registrados en el sistema' ?>
                </small>
            </div>
            <div class="col-auto">
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=clientes&type=list" class="btn btn-secondary" style="border-radius: 50px; padding: 8px 22px; font-weight: 600;">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Clientes Activos
                    </a>
                <?php else: ?>
                    <?php if ($puede_crear_cliente): ?>
                    <button type="button" class="btn btn-dark-gold" 
                            data-bs-toggle="modal" data-bs-target="#modalRegistrarCliente"
                            style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                        <i class="fas fa-plus me-1"></i> Registrar Cliente
                    </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FILTRO DE BÚSQUEDA -->
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form id="formFiltroClientes" method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="clientes">
            <input type="hidden" name="type" value="list">
            <input type="hidden" name="estado_cliente" value="<?= $esVistaEliminados ? 'inactivos' : 'activos' ?>">
            
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
                       list="listaClientes"
                       placeholder="Nombre, cédula, RIF o razón social..."
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

            <!-- 🔥 BOTÓN VER ELIMINADOS EN LOS FILTROS -->
            <div class="col-md-2">
                <?php if ($esVistaEliminados): ?>
                    <a href="?url=clientes&type=list" class="btn btn-success w-100" style="background: #28a745; border: none; color: #fff;">
                        <i class="fas fa-undo-alt me-1"></i> Ver Activos
                    </a>
                <?php else: ?>
                    <a href="?url=clientes&type=list&estado_cliente=inactivos" class="btn btn-danger w-100" style="background: #dc3545; border: none; color: #fff;">
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

    <!-- TABLA DE CLIENTES -->
    <div class="dark-card card shadow-sm dark-table-header">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0">
                <i class="fas fa-users me-2"></i> 
                <?= $esVistaEliminados ? 'Clientes Inactivos' : 'Clientes Registrados' ?>
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
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($clientes) && is_array($clientes) && count($clientes) > 0): ?>
                        <?php foreach ($clientes as $c): ?>
                            <?php 
                                $nombreCliente = ($c['tipo_cliente'] ?? '') === 'Jurídico' 
                                    ? ($c['razon_social'] ?? '') 
                                    : (($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                                $estaActivo = (($c['status'] ?? 'activo') === 'activo' && ($c['eliminado'] ?? 0) == 0);
                            ?>
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
                                
                                <!-- STATUS COMO BADGE (SIN SWITCH) -->
                                <td class="text-center">
                                    <?php if ($estaActivo): ?>
                                        <span class="badge" style="background: rgba(40,167,69,0.15); color: #28a745; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(220,53,69,0.15); color: #dc3545; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                            <i class="fas fa-times-circle me-1"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- ACCIONES -->
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Ver Detalle (siempre visible) -->
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                onclick="verDetalleCliente(<?= (int) $c['id_cliente'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($estaActivo): ?>
                                            <!-- ACTIVO: Editar y Eliminar -->
                                            <?php if ($puede_editar_cliente): ?>
                                                <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                        onclick="editarCliente(<?= (int) $c['id_cliente'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($puede_eliminar_cliente): ?>
                                                <button type="button" class="btn-action-circle btn-delete" title="Eliminar"
                                                        onclick="confirmarEliminarCliente(<?= (int) $c['id_cliente'] ?>, '<?= htmlspecialchars(addslashes($nombreCliente)) ?>')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- INACTIVO: Solo Reactivar -->
                                            <?php if ($puede_editar_cliente): ?>
                                                <button type="button" class="btn-action-circle btn-reactivar" title="Reactivar Cliente"
                                                        onclick="confirmarReactivarCliente(<?= (int) $c['id_cliente'] ?>, '<?= htmlspecialchars(addslashes($nombreCliente)) ?>')">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 dark-empty">
                                <div class="py-4">
                                    <i class="fas fa-users fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                    <p class="mb-0">
                                        <?= $esVistaEliminados ? 'No hay clientes inactivos' : 'No hay clientes registrados' ?>
                                    </p>
                                    <small><?= $esVistaEliminados ? 'Los clientes activos están en la lista principal' : 'Comienza registrando un nuevo cliente' ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-users me-1"></i> 
                Total: <?= $totalRegistros ?> clientes
            </span>
            <?php require_once dirname(__DIR__, 2) . "/view/partials/por_pagina_selector.php"; ?>
        </div>
    </div>
</div>


<?php // INCLUIR MODALES ?>
<?php require_once __DIR__ . '/registroClientView.php'; ?>
<?php require_once __DIR__ . '/detalleClientView.php'; ?>
<?php require_once __DIR__ . '/editarClientView.php'; ?>
<?php require_once __DIR__ . '/eliminarClientView.php'; ?>


<?php // REAPERTURA DE MODAL TRAS POST FALLIDO POR DUPLICADO ?>
<?php if (!empty($_SESSION['errores_campo']) && !empty($_SESSION['form_modal'])): 
    $erroresCampo = $_SESSION['errores_campo'];
    $formModal    = $_SESSION['form_modal'];
    $formData     = $_SESSION['form_data'] ?? [];
    unset($_SESSION['errores_campo'], $_SESSION['form_modal'], $_SESSION['form_data']);
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var formModal = <?= json_encode($formModal, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
    var formData  = <?= json_encode($formData,  JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;
    var errores   = <?= json_encode($erroresCampo, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;

    function setField(id, value) {
        var el = document.getElementById(id);
        if (el && value !== undefined && value !== null) el.value = value;
    }

    function pintarError(inputId, mensaje) {
        var input    = document.getElementById(inputId);
        var feedback = document.getElementById(inputId + '-feedback');
        if (input) {
            input.classList.remove('campo-valido', 'campo-verificando');
            input.classList.add('campo-invalido');
            input.dataset.unicoEstado = 'duplicado';
        }
        if (feedback) {
            feedback.classList.remove('info', 'success', 'checking');
            feedback.classList.add('error');
            feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i> ' + mensaje;
        }
    }

    if (formModal === 'registrar_natural' || formModal === 'registrar_juridico') {
        var modalEl = document.getElementById('modalRegistrarCliente');
        if (!modalEl) return;

        modalEl.addEventListener('shown.bs.modal', function onShown() {
            modalEl.removeEventListener('shown.bs.modal', onShown);

            var tabId = (formModal === 'registrar_natural') ? 'tab-natural-reg' : 'tab-juridico-reg';
            var tabEl = document.getElementById(tabId);
            if (tabEl) new bootstrap.Tab(tabEl).show();

            if (formModal === 'registrar_natural') {
                setField('reg-cedula',          formData.cedula);
                setField('reg-nombre',          formData.nombre);
                setField('reg-apellido',        formData.apellido);
                setField('reg-telefono',        formData.telefono);
                setField('reg-correo',          formData.correo_electronico);
                setField('reg-fecha-nac',       formData.fecha_de_nacimiento);
                setField('reg-direccion',       formData.direccion);
                if (errores.cedula) pintarError('reg-cedula', errores.cedula);
            } else {
                setField('reg-rif',             formData.rif);
                setField('reg-jur-razon',       formData.razon_social);
                setField('reg-jur-telefono',    formData.telefono);
                setField('reg-jur-correo',      formData.correo_electronico);
                setField('reg-jur-direccion',   formData.direccion);
                if (errores.rif) pintarError('reg-rif', errores.rif);
            }
        }, { once: true });

        new bootstrap.Modal(modalEl).show();
    }
});
</script>
<?php endif; ?>


<?php // DATOS DE CLIENTES EN JSON + JAVASCRIPT ESPECÍFICO ?>
<script id="clientesData" type="application/json">
<?= json_encode(
    isset($clientes) && is_array($clientes) ? array_values($clientes) : [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    function inicializarMapaClientes() {
        const scriptCliente = document.getElementById('clientesData');
        if (!scriptCliente) {
            window.clientesPorId = {};
            return;
        }

        try {
            const clientesData = JSON.parse(scriptCliente.textContent || '[]');
            const clientesPorId = {};
            clientesData.forEach(function(c) {
                clientesPorId[parseInt(c.id_cliente)] = c;
            });
            window.clientesPorId = clientesPorId;
        } catch (error) {
            console.error('No se pudieron cargar los datos de clientes:', error);
            window.clientesPorId = {};
        }
    }

    const formClientes = document.getElementById('formFiltroClientes');
    if (formClientes) {
        const enviarFiltroClientes = function () {
            const formData = new FormData(formClientes);
            formData.set('ajax', '1');

            fetch('?url=clientes&type=list', {
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
                inicializarMapaClientes();
            })
            .catch(() => {
                formClientes.submit();
            });
        };

        formClientes.addEventListener('submit', function (e) {
            e.preventDefault();
            enviarFiltroClientes();
        });

        formClientes.querySelectorAll('select').forEach(function (selectEl) {
            selectEl.addEventListener('change', function () {
                enviarFiltroClientes();
            });
        });
    }

    inicializarMapaClientes();

    <?php // CALLBACK DE MENOR DE EDAD ?>
    let modalMenorEdadMostrado = false;

    Validador.onMenorEdad(function(edad) {
        if (modalMenorEdadMostrado) return;

        const modalEl = document.getElementById('modalAlertaMenorEdad');
        if (!modalEl) {
            console.warn('No se encontró el modal de menor de edad');
            return;
        }

        modalMenorEdadMostrado = true;

        const edadEl = document.getElementById('alerta-edad-detectada');
        if (edadEl) edadEl.textContent = edad + ' años';

        new bootstrap.Modal(modalEl).show();

        modalEl.addEventListener('hidden.bs.modal', function onHidden() {
            modalMenorEdadMostrado = false;
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
        });
    });

    <?php // INICIALIZAR VALIDADOR EN LOS MODALES ?>
    const modalReg = document.getElementById('modalRegistrarCliente');
    const modalEdt = document.getElementById('modalEditarCliente');
    if (modalReg) Validador.init(modalReg);
    if (modalEdt) Validador.init(modalEdt);

    if (modalReg) {
        modalReg.addEventListener('show.bs.modal', function() {
            Validador.resetFormulario('formRegistrarNatural');
            Validador.resetFormulario('formRegistrarJuridico');
        });
    }

    <?php // VALIDAR AL ENVIAR FORMULARIOS ?>
    ['formRegistrarNatural', 'formRegistrarJuridico', 'formEditarNatural', 'formEditarJuridico'].forEach(function(id) {
        const form = document.getElementById(id);
        if (form) {
            form.addEventListener('submit', function(e) {
                if (id === 'formRegistrarNatural' || id === 'formEditarNatural') {
                    const inputFecha = id === 'formRegistrarNatural' 
                        ? document.getElementById('reg-fecha-nac') 
                        : document.getElementById('editar-natural-fecha');
                    if (inputFecha && inputFecha.value) {
                        const partes = inputFecha.value.split('-');
                        const fechaNac = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
                        const hoy = new Date();
                        let edad = hoy.getFullYear() - fechaNac.getFullYear();
                        const mes = hoy.getMonth() - fechaNac.getMonth();
                        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) edad--;
                        
                        if (edad < 18) {
                            e.preventDefault();
                            e.stopPropagation();

                            const modalEl = document.getElementById('modalAlertaMenorEdad');
                            if (modalEl) {
                                const edadEl = document.getElementById('alerta-edad-detectada');
                                if (edadEl) edadEl.textContent = (edad >= 0 ? edad : 0) + ' años';
                                new bootstrap.Modal(modalEl).show();
                            }

                            inputFecha.classList.add('campo-invalido');
                            inputFecha.focus();

                            return false;
                        } else {
                            inputFecha.classList.remove('campo-invalido');
                        }
                    }
                }

                if (!Validador.validarFormulario(id)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        }
    });

    <?php // VER DETALLE DEL CLIENTE ?>
    window.verDetalleCliente = function(id) {
        const c = clientesPorId[id];
        if (!c) return;

        const esJuridico = (c.tipo_cliente === 'Jurídico');
        const nombre = esJuridico 
            ? (c.razon_social || 'Sin razón social') 
            : ((c.nombre || '') + ' ' + (c.apellido || '')).trim();
        const documento = esJuridico ? (c.rif || 'N/A') : (c.cedula || 'N/A');

        document.getElementById('ver-cliente-titulo').querySelector('h4').textContent = nombre || 'Sin nombre';
        
        const badge = document.getElementById('ver-cliente-tipo-badge');
        if (esJuridico) {
            badge.textContent = 'Jurídico';
            badge.style.background = 'rgba(13,110,253,0.15)';
            badge.style.color = '#0d6efd';
        } else {
            badge.textContent = 'Natural';
            badge.style.background = 'rgba(40,167,69,0.15)';
            badge.style.color = '#28a745';
        }

        const idElem = document.getElementById('ver-cliente-id');
        if (idElem) idElem.textContent = '#' + c.id_cliente;

        const statusBadge = document.getElementById('ver-cliente-status-badge');
        if (statusBadge) {
            if (c.status === 'inactivo' || parseInt(c.eliminado) === 1) {
                statusBadge.textContent = 'Inactivo';
                statusBadge.style.background = 'rgba(220,53,69,0.15)';
                statusBadge.style.color = '#dc3545';
            } else {
                statusBadge.textContent = 'Activo';
                statusBadge.style.background = 'rgba(40,167,69,0.15)';
                statusBadge.style.color = '#28a745';
            }
        }

        document.getElementById('ver-cliente-documento').textContent = documento;
        document.getElementById('ver-cliente-nombre').textContent = nombre;
        document.getElementById('ver-cliente-telefono').textContent = c.telefono || 'N/A';
        document.getElementById('ver-cliente-correo').textContent = c.correo_electronico || 'N/A';
        document.getElementById('ver-cliente-direccion').textContent = c.direccion || 'Sin dirección registrada';

        const fechaWrap = document.getElementById('ver-cliente-fecha-wrap');
        if (esJuridico || !c.fecha_de_nacimiento) {
            fechaWrap.style.display = 'none';
        } else {
            fechaWrap.style.display = 'block';
            document.getElementById('ver-cliente-fecha').textContent = Validador.formatearFecha(c.fecha_de_nacimiento);
        }

        new bootstrap.Modal(document.getElementById('modalVerCliente')).show();
    };

    <?php // EDITAR CLIENTE ?>
    window.editarCliente = function(id) {
        const c = clientesPorId[id];
        if (!c) return;

        const esJuridico = (c.tipo_cliente === 'Jurídico');
        const nombre = esJuridico 
            ? (c.razon_social || '') 
            : ((c.nombre || '') + ' ' + (c.apellido || '')).trim();
        
        document.getElementById('editar-cliente-nombre-header').textContent = nombre || 'Cliente';

        const bloqueNatural = document.getElementById('editar-bloque-natural');
        const bloqueJuridico = document.getElementById('editar-bloque-juridico');

        if (esJuridico) {
            bloqueNatural.style.display = 'none';
            bloqueJuridico.style.display = 'block';

            document.getElementById('editar-juridico-id').value = c.id_cliente;
            document.getElementById('editar-juridico-rif').value = c.rif || '';
            document.getElementById('editar-juridico-telefono').value = c.telefono || '';
            document.getElementById('editar-juridico-razon').value = c.razon_social || '';
            document.getElementById('editar-juridico-correo').value = c.correo_electronico || '';
            document.getElementById('editar-juridico-direccion').value = c.direccion || '';
            
            setTimeout(function() {
                document.querySelectorAll('#editar-bloque-juridico input[data-validate]').forEach(function(input) {
                    Validador.validarCampo(input);
                });
            }, 100);
        } else {
            bloqueNatural.style.display = 'block';
            bloqueJuridico.style.display = 'none';

            document.getElementById('editar-natural-id').value = c.id_cliente;
            document.getElementById('editar-natural-cedula').value = c.cedula || '';
            document.getElementById('editar-natural-telefono').value = c.telefono || '';
            document.getElementById('editar-natural-nombre').value = c.nombre || '';
            document.getElementById('editar-natural-apellido').value = c.apellido || '';
            document.getElementById('editar-natural-correo').value = c.correo_electronico || '';
            document.getElementById('editar-natural-fecha').value = c.fecha_de_nacimiento || '';
            document.getElementById('editar-natural-direccion').value = c.direccion || '';
            
            setTimeout(function() {
                document.querySelectorAll('#editar-bloque-natural input[data-validate]').forEach(function(input) {
                    Validador.validarCampo(input);
                });
            }, 100);
        }

        new bootstrap.Modal(document.getElementById('modalEditarCliente')).show();
    };

    <?php // CONFIRMAR DESACTIVAR (SIN SWITCH) ?>
    window.confirmarEliminarCliente = function(id, nombre) {
        document.getElementById('eliminar-cliente-id').value = id;
        document.getElementById('eliminar-cliente-nombre').textContent = nombre || 'Cliente #' + id;
        new bootstrap.Modal(document.getElementById('modalEliminarCliente')).show();
    };

    <?php // CONFIRMAR ACTIVAR (SIN SWITCH) ?>
    window.confirmarReactivarCliente = function(id, nombre) {
        document.getElementById('reactivar-cliente-id').value = id;
        document.getElementById('reactivar-cliente-nombre').textContent = nombre || 'Cliente #' + id;
        new bootstrap.Modal(document.getElementById('modalReactivarCliente')).show();
    };

    <?php // ESTILO DE PESTAÑAS ?>
    document.querySelectorAll('#modalRegistrarCliente .nav-link').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function(e) {
            document.querySelectorAll('#modalRegistrarCliente .nav-link').forEach(function(t) {
                t.style.color = '#6c757d';
                t.style.borderBottom = 'none';
            });
            e.target.style.color = '#f39c12';
            e.target.style.borderBottom = '2px solid #f39c12';
        });
    });
});
</script>

<?php // AUTO-CIERRE DE ALERTAS ?>
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

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>