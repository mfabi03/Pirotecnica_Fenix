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

<?php // CSS DEL SWITCH CON TEXTO "ACTIVO" / "INACTIVO" DENTRO ?>
<style>
.switch-estado {
    position: relative;
    display: inline-block;
    width: 110px;
    height: 34px;
    margin: 0;
    vertical-align: middle;
    cursor: pointer;
}

.switch-estado input {
    opacity: 0;
    width: 0;
    height: 0;
}

.switch-estado .slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #dc3545; 
    transition: .3s;
    border-radius: 34px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
}

<?php // Círculo deslizante ?>
.switch-estado .slider::before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 2;
}

<?php // Texto cuando está INACTIVO ?>
.switch-estado .slider::after {
    content: 'INACTIVO';
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: white;
    transition: .3s;
    pointer-events: none;
    z-index: 1;
}

<?php // Cuando está ACTIVO ?>
.switch-estado input:checked + .slider {
    background-color: #28a745; 
}

.switch-estado input:checked + .slider::before {
    transform: translateX(76px);
}

.switch-estado input:checked + .slider::after {
    content: 'ACTIVO';
    left: 14px;
    right: auto;
    color: white;
}
</style>

<div class="col-md-8 col-lg-12">
    
    <?php // TARJETA DE TÍTULO ?>
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
                <button type="button" class="btn btn-dark-gold" 
                        data-bs-toggle="modal" data-bs-target="#modalRegistrarCliente"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px;">
                    <i class="fas fa-plus me-1"></i> Registrar Cliente
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php // FILTRO DE BÚSQUEDA ?>
    <div class="card shadow-sm p-3 mb-4 bg-white">
        <form id="formFiltroClientes" method="GET" action="" class="row g-3 align-items-end" autocomplete="off">
            <input type="hidden" name="url" value="clientes">
            <input type="hidden" name="type" value="list">
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                <select name="por_pagina" class="form-select form-select-sm" data-no-search="true">
                    <?php foreach ([5, 10, 25, 50] as $o): ?>
                        <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark mb-0">Estado</label>
                <select name="estado_cliente" class="form-select form-select-sm" data-no-search="true">
                    <option value="todos" <?= (($estado_cliente ?? 'todos') === 'todos') ? 'selected' : '' ?>>Todos</option>
                    <option value="activos" <?= (($estado_cliente ?? 'todos') === 'activos') ? 'selected' : '' ?>>Activos</option>
                    <option value="inactivos" <?= (($estado_cliente ?? 'todos') === 'inactivos') ? 'selected' : '' ?>>Inactivos</option>
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
        </form>
    </div>

    <?php // MENSAJES ?>
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

    <?php // TABLA DE CLIENTES ?>
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
                                
                                <?php // COLUMNA STATUS: SWITCH CON TEXTO DENTRO ?>
                                <td class="text-center">
                                    <?php if ($puede_eliminar_cliente || $puede_editar_cliente): ?>
                                        <label class="switch-estado" title="<?= $estaActivo ? 'Desactivar Cliente' : 'Activar Cliente' ?>">
                                            <input type="checkbox" 
                                                   <?= $estaActivo ? 'checked' : '' ?>
                                                   onchange="<?= $estaActivo 
                                                       ? 'confirmarEliminarCliente(' . (int)$c['id_cliente'] . ', \'' . htmlspecialchars(addslashes($nombreCliente)) . '\', this)' 
                                                       : 'confirmarReactivarCliente(' . (int)$c['id_cliente'] . ', \'' . htmlspecialchars(addslashes($nombreCliente)) . '\', this)' ?>">
                                            <span class="slider"></span>
                                        </label>
                                    <?php else: ?>
                                        <?php // Sin permisos: solo mostramos el estado como badge ?>
                                        <span class="badge <?= $estaActivo ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $estaActivo ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <?php // COLUMNA ACCIONES: SOLO VER Y EDITAR ?>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        
                                        <?php // Botón Ver Detalle (siempre visible) ?>
                                        <button type="button" class="btn-action-circle btn-view" title="Ver Detalle"
                                                onclick="verDetalleCliente(<?= (int) $c['id_cliente'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php // Botón Editar (solo si está activo y tiene permiso) ?>
                                        <?php if ($estaActivo && $puede_editar_cliente): ?>
                                            <button type="button" class="btn-action-circle btn-edit" title="Editar"
                                                    onclick="editarCliente(<?= (int) $c['id_cliente'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
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
                                    <p class="mb-0">No hay clientes registrados</p>
                                    <small>Comienza registrando un nuevo cliente</small>
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

    <?php // CONFIRMAR DESACTIVAR (desde el switch) ?>
    window.confirmarEliminarCliente = function(id, nombre, checkbox) {
        document.getElementById('eliminar-cliente-id').value = id;
        document.getElementById('eliminar-cliente-nombre').textContent = nombre || 'Cliente #' + id;
        
        const modalEl = document.getElementById('modalEliminarCliente');
        
        // Si el usuario cierra el modal (porque canceló), devolvemos el switch a su estado original
        modalEl.addEventListener('hidden.bs.modal', function onHidden() {
            if (checkbox) checkbox.checked = true; // Lo volvemos a encender
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
        }, { once: true });

        new bootstrap.Modal(modalEl).show();
    };

    <?php // CONFIRMAR ACTIVAR (desde el switch) ?>
    window.confirmarReactivarCliente = function(id, nombre, checkbox) {
        document.getElementById('reactivar-cliente-id').value = id;
        document.getElementById('reactivar-cliente-nombre').textContent = nombre || 'Cliente #' + id;
        
        const modalEl = document.getElementById('modalReactivarCliente');

        // Si el usuario cierra el modal (porque canceló), devolvemos el switch a su estado original
        modalEl.addEventListener('hidden.bs.modal', function onHidden() {
            if (checkbox) checkbox.checked = false; // Lo volvemos a apagar
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
        }, { once: true });

        new bootstrap.Modal(modalEl).show();
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