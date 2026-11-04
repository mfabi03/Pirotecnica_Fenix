<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idRol = isset($idRol) ? (int) $idRol : (int) ($_SESSION['id_rol'] ?? 0);
$esCajero = isset($esCajero) ? (bool) $esCajero : $idRol === 3;
$mostrarUsuarios = isset($mostrarUsuarios) ? (bool) $mostrarUsuarios : $idRol === 1;
$mostrarStockCritico = isset($mostrarStockCritico) ? (bool) $mostrarStockCritico : in_array($idRol, [1, 2], true);
$mostrarNotasEntrada = isset($mostrarNotasEntrada) ? (bool) $mostrarNotasEntrada : in_array($idRol, [1, 2], true);
$stats = isset($stats) && is_array($stats) ? $stats : [];

require_once dirname(__DIR__, 2) . "/view/header.php";

$e = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$tarjetas = [
    [
        'key' => 'total_productos', 'label' => 'Inventario Total',
        'descripcion' => 'Productos registrados', 'icono' => 'fa-boxes',
        'clase' => 'stat-card-primary', 'modal' => 'modalInventarioTotal'
    ],
    [
        'key' => 'total_usuarios', 'label' => 'Usuarios',
        'descripcion' => 'Usuarios registrados', 'icono' => 'fa-users',
        'clase' => 'stat-card-success', 'modal' => 'modalUsuarios', 'visible' => $mostrarUsuarios
    ],
    [
        'key' => 'productos_criticos', 'label' => 'Stock Crítico',
        'descripcion' => 'Productos con stock bajo', 'icono' => 'fa-exclamation-triangle',
        'clase' => 'stat-card-warning', 'modal' => 'modalStockCritico', 'visible' => $mostrarStockCritico
    ],
    [
        'key' => 'total_clientes', 'label' => 'Clientes',
        'descripcion' => 'Clientes registrados', 'icono' => 'fa-user-friends',
        'clase' => 'stat-card-info', 'modal' => 'modalClientes'
    ],
    [
        'key' => 'total_nota_salida', 'label' => $esCajero ? 'Mis Notas de Salida' : 'Notas de Salida',
        'descripcion' => $esCajero ? 'Salidas registradas por usted' : 'Salidas registradas',
        'icono' => 'fa-sign-out-alt', 'clase' => 'stat-card-purple', 'modal' => 'modalNotasSalida'
    ],
    [
        'key' => 'total_nota_entrada', 'label' => 'Notas de Entrada',
        'descripcion' => 'Entradas registradas', 'icono' => 'fa-sign-in-alt',
        'clase' => 'stat-card-info', 'modal' => 'modalNotasEntrada', 'visible' => $mostrarNotasEntrada
    ]
];

$notasEntradaDetalle = $notasEntradaDetalle ?? [];
$notasSalidaDetalle = $notasSalidaDetalle ?? [];
$clientesDetalle = $clientesDetalle ?? [];
$usuariosDetalle = $usuariosDetalle ?? [];
$productosCriticosDetalle = $productosCriticosDetalle ?? [];
$inventarioDetalle = $inventarioDetalle ?? [];

$modalesDashboard = [
    'inventario' => [
        'id' => 'modalInventarioTotal', 'titulo' => 'Inventario Total', 'icono' => 'fa-boxes',
        'columnas' => ['Código', 'Producto', 'Categoría', 'Stock actual', 'Precio'],
        'filas' => array_map(static function ($producto) {
            return [
                $producto['id_producto'] ?? '',
                $producto['descripcion'] ?? '',
                $producto['nombre_categoria'] ?? 'Sin categoría',
                $producto['cantidad'] ?? 0,
                '$' . number_format((float) ($producto['precio'] ?? 0), 2, ',', '.')
            ];
        }, $inventarioDetalle)
    ],
    'clientes' => [
        'id' => 'modalClientes', 'titulo' => 'Clientes Registrados', 'icono' => 'fa-user-friends',
        'columnas' => ['Cédula / RIF', 'Nombre / Razón Social', 'Tipo', 'Teléfono', 'Correo'],
        'filas' => array_map(static function ($cliente) {
            return [
                $cliente['documento'] ?? '',
                $cliente['nombre'] ?? '',
                $cliente['tipo_cliente'] ?? '',
                $cliente['telefono'] ?? '',
                $cliente['correo_electronico'] ?? ''
            ];
        }, $clientesDetalle)
    ],
    'salidas' => [
        'id' => 'modalNotasSalida', 'titulo' => 'Últimas Notas de Salida', 'icono' => 'fa-sign-out-alt',
        'columnas' => ['Número', 'Cliente', 'Fecha', 'Total', 'Estado'],
        'filas' => array_map(static function ($nota) {
            return [
                $nota['id_nota_salida'] ?? '',
                $nota['cliente'] ?? 'Sin cliente',
                $nota['fecha'] ?? '',
                number_format((int) ($nota['total'] ?? 0)) . ' unidades',
                $nota['estado'] ?? 'Activa'
            ];
        }, $notasSalidaDetalle),
        'tipo_notas' => 'salida'
    ]
];

if ($mostrarUsuarios) {
    $modalesDashboard['usuarios'] = [
        'id' => 'modalUsuarios', 'titulo' => 'Usuarios Registrados', 'icono' => 'fa-users',
        'columnas' => ['ID', 'Nombre completo', 'Correo electrónico', 'Rol', 'Estado'],
        'filas' => array_map(static function ($usuario) {
            return [
                $usuario['id_usuario'] ?? '',
                trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')),
                $usuario['correo_electronico'] ?? '',
                $usuario['rol'] ?? 'Sin rol',
                $usuario['estado'] ?? 'Activo'
            ];
        }, $usuariosDetalle)
    ];
}

if ($mostrarStockCritico) {
    $modalesDashboard['stock'] = [
        'id' => 'modalStockCritico', 'titulo' => 'Productos con Stock Crítico', 'icono' => 'fa-exclamation-triangle',
        'columnas' => ['Código', 'Producto', 'Stock actual', 'Stock mínimo', 'Proveedor'],
        'filas' => array_map(static function ($producto) {
            return [
                $producto['id_producto'] ?? '',
                $producto['descripcion'] ?? '',
                $producto['cantidad'] ?? 0,
                $producto['stock_minimo'] ?? 0,
                $producto['proveedor'] ?? 'Sin proveedor'
            ];
        }, $productosCriticosDetalle)
    ];
}

if ($mostrarNotasEntrada) {
    $modalesDashboard['entradas'] = [
        'id' => 'modalNotasEntrada', 'titulo' => 'Últimas Notas de Entrada', 'icono' => 'fa-sign-in-alt',
        'columnas' => ['Número', 'Proveedor', 'Fecha', 'Total', 'Estado'],
        'filas' => array_map(static function ($nota) {
            return [
                $nota['id_nota_entrada'] ?? '',
                $nota['proveedor'] ?? 'Sin proveedor',
                $nota['fecha_ingreso'] ?? '',
                '$' . number_format((float) ($nota['costo_total'] ?? 0), 2, ',', '.'),
                $nota['estado'] ?? 'Activa'
            ];
        }, $notasEntradaDetalle),
        'tipo_notas' => 'entrada'
    ];
}
?>

<div class="container-fluid px-3 px-lg-4 dashboard-home">
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-3"></i>
                <span><?= $e($_SESSION['error']) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="dark-header-card card dashboard-heading mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h3 class="m-0 dark-title">
                <i class="fas fa-chart-pie text-gold me-2"></i>Panel de Control
            </h3>
            <span class="badge dashboard-summary">
                <i class="fas fa-database me-1"></i>
                <?= $idRol === 1 ? 'Resumen General' : ($idRol === 2 ? 'Resumen de Almacén' : 'Resumen Personal') ?>
            </span>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <?php foreach ($tarjetas as $tarjeta): ?>
            <?php if (array_key_exists('visible', $tarjeta) && !$tarjeta['visible']) continue; ?>
            <div class="col-6 col-md-4 col-xl">
                <div class="stat-card <?= $e($tarjeta['clase']) ?> dashboard-stat-interactive"
                     role="button" tabindex="0" aria-haspopup="dialog"
                     aria-label="<?= $e('Ver detalle de ' . $tarjeta['label']) ?>"
                     data-bs-toggle="modal" data-bs-target="#<?= $e($tarjeta['modal']) ?>">
                    <div class="stat-card-icon"><i class="fas <?= $e($tarjeta['icono']) ?>"></i></div>
                    <div class="stat-card-content">
                        <span class="stat-card-label"><?= $e($tarjeta['label']) ?></span>
                        <span class="stat-card-number" data-stat-key="<?= $e($tarjeta['key']) ?>"><?= number_format((int) ($stats[$tarjeta['key']] ?? 0)) ?></span>
                    </div>
                    <div class="stat-card-footer">
                        <span class="stat-card-change"><?= $e($tarjeta['descripcion']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <?php if ($mostrarNotasEntrada): ?>
            <div class="col-12 col-xl-6">
                <section class="dashboard-table-card h-100">
                    <div class="dashboard-table-heading">
                        <h5><i class="fas fa-sign-in-alt me-2 text-gold"></i>Últimas Notas de Entrada</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-sm align-middle mb-0">
                            <thead><tr><th>Número</th><th>Proveedor</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
                            <tbody id="notasEntradaBody">
                                <?php if ($notasEntradaDetalle): ?>
                                    <?php foreach ($notasEntradaDetalle as $nota): ?>
                                        <tr>
                                            <td>#<?= $e($nota['id_nota_entrada'] ?? '') ?></td>
                                            <td><?= $e($nota['proveedor'] ?? 'Sin proveedor') ?></td>
                                            <td><?= $e($nota['fecha_ingreso'] ?? '') ?></td>
                                            <td>$<?= number_format((float) ($nota['costo_total'] ?? 0), 2, ',', '.') ?></td>
                                            <td><span class="badge bg-success"><?= $e($nota['estado'] ?? 'Activa') ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-secondary py-3">No hay notas de entrada recientes.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        <?php endif; ?>

        <div class="col-12 <?= $mostrarNotasEntrada ? 'col-xl-6' : '' ?>">
            <section class="dashboard-table-card h-100">
                <div class="dashboard-table-heading">
                    <h5><i class="fas fa-sign-out-alt me-2 text-gold"></i><?= $esCajero ? 'Mis Últimas Notas de Salida' : 'Últimas Notas de Salida' ?></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-sm align-middle mb-0">
                        <thead><tr><th>Número</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
                        <tbody id="notasSalidaBody">
                            <?php if ($notasSalidaDetalle): ?>
                                <?php foreach ($notasSalidaDetalle as $nota): ?>
                                    <tr>
                                        <td>#<?= $e($nota['id_nota_salida'] ?? '') ?></td>
                                        <td><?= $e($nota['cliente'] ?? 'Sin cliente') ?></td>
                                        <td><?= $e($nota['fecha'] ?? '') ?></td>
                                        <td><?= number_format((int) ($nota['total'] ?? 0)) ?> unidades</td>
                                        <td><span class="badge bg-success"><?= $e($nota['estado'] ?? 'Activa') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-secondary py-3">No hay notas de salida recientes.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

<?php foreach ($modalesDashboard as $modal): ?>
    <div class="modal fade" id="<?= $e($modal['id']) ?>" tabindex="-1" aria-hidden="true" aria-labelledby="<?= $e($modal['id']) ?>Label">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content dashboard-modal">
                <div class="modal-header dashboard-modal-header">
                    <h5 class="modal-title" id="<?= $e($modal['id']) ?>Label">
                        <i class="fas <?= $e($modal['icono']) ?> me-2 text-gold"></i><?= $e($modal['titulo']) ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead><tr>
                                <?php foreach ($modal['columnas'] as $columna): ?>
                                    <th scope="col" class="text-nowrap"><?= $e($columna) ?></th>
                                <?php endforeach; ?>
                            </tr></thead>
                            <tbody
                                <?php if (isset($modal['tipo_notas'])): ?>
                                    id="<?= $modal['tipo_notas'] === 'entrada' ? 'modalEntradasBody' : 'modalSalidasBody' ?>"
                                    data-modal-notes="<?= $e($modal['tipo_notas']) ?>"
                                <?php endif; ?>>
                                <?php if (!empty($modal['filas'])): ?>
                                    <?php foreach ($modal['filas'] as $fila): ?>
                                        <tr><?php foreach ($fila as $valor): ?><td><?= $e($valor) ?></td><?php endforeach; ?></tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="<?= count($modal['columnas']) ?>" class="text-center text-secondary py-4">No hay registros para mostrar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<style>
.dashboard-home { padding-bottom: 1rem; }
.dashboard-heading { padding: .8rem 1rem; }
.dashboard-summary {
    background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.72);
    padding: .4rem .8rem;
    border-radius: 50px;
}
.dashboard-stat-interactive {
    min-height: 132px;
    padding: .8rem !important;
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease;
}
.dashboard-stat-interactive:hover { transform: translateY(-2px); }
.dashboard-stat-interactive:focus-visible { outline: 3px solid #f39c12; outline-offset: 2px; }
.dashboard-stat-interactive .stat-card-icon { font-size: 1.25rem; margin-bottom: .35rem; }
.dashboard-stat-interactive .stat-card-label { font-size: .82rem; }
.dashboard-stat-interactive .stat-card-number { font-size: 1.55rem; }
.dashboard-stat-interactive .stat-card-footer { margin-top: .35rem; font-size: .72rem; }
.dashboard-table-card {
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px;
    background: #151521;
}
.dashboard-table-heading { padding: .8rem 1rem; border-bottom: 1px solid rgba(255,255,255,.08); }
.dashboard-table-heading h5 { margin: 0; color: #f1f1f5; font-size: .98rem; }
.dashboard-table-card .table { --bs-table-bg: #151521; --bs-table-hover-bg: rgba(255,255,255,.05); font-size: .84rem; }
.dashboard-table-card .table thead th { color: #d7bd6a; white-space: nowrap; }
.dashboard-modal { color: #f1f1f5; background: #151521; border: 1px solid rgba(255,255,255,.1); }
.dashboard-modal-header { background: #1a1a2e; border-bottom-color: rgba(255,255,255,.08); }
.dashboard-modal .modal-footer { border-top-color: rgba(255,255,255,.08); }
.dashboard-modal .table { --bs-table-bg: #151521; --bs-table-hover-bg: rgba(255,255,255,.05); }
.dashboard-modal .table thead th { color: #d7bd6a; }
@media (min-width: 1200px) {
    .dashboard-stat-interactive { min-height: 125px; }
}
</style>

<script>
(function () {
    const formatNumber = new Intl.NumberFormat('es-VE');
    let lastNotaEntradaId = <?= (int) ($notasEntradaDetalle[0]['id_nota_entrada'] ?? 0) ?>;
    let lastNotaSalidaId = <?= (int) ($notasSalidaDetalle[0]['id_nota_salida'] ?? 0) ?>;
    const puedeVerEntradas = <?= $mostrarNotasEntrada ? 'true' : 'false' ?>;

    document.querySelectorAll('.dashboard-stat-interactive').forEach(function (card) {
        card.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            const modalElement = document.querySelector(card.getAttribute('data-bs-target'));
            if (modalElement) bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    });

    function renderNotas(tbody, notas, tipo) {
        if (!tbody) return;
        tbody.replaceChildren();

        if (!Array.isArray(notas) || notas.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 5;
            cell.className = 'text-center text-secondary py-3';
            cell.textContent = tipo === 'entrada'
                ? 'No hay notas de entrada recientes.'
                : 'No hay notas de salida recientes.';
            row.appendChild(cell);
            tbody.appendChild(row);
            return;
        }

        notas.forEach(function (nota) {
            const row = document.createElement('tr');
            const values = tipo === 'entrada'
                ? [
                    '#' + (nota.id_nota_entrada || ''),
                    nota.proveedor || 'Sin proveedor',
                    nota.fecha_ingreso || '',
                    '$' + Number(nota.costo_total || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    nota.estado || 'Activa'
                ]
                : [
                    '#' + (nota.id_nota_salida || ''),
                    nota.cliente || 'Sin cliente',
                    nota.fecha || '',
                    formatNumber.format(Number(nota.total || 0)) + ' unidades',
                    nota.estado || 'Activa'
                ];

            values.forEach(function (value, index) {
                const cell = document.createElement('td');
                if (index === 4) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-success';
                    badge.textContent = String(value);
                    cell.appendChild(badge);
                } else {
                    cell.textContent = String(value);
                }
                row.appendChild(cell);
            });
            tbody.appendChild(row);
        });
    }

    function actualizarDashboard() {
        const url = new URL(window.location.href);
        url.searchParams.set('url', 'dashboard');
        url.searchParams.set('action', 'ajax_stats');

        fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Respuesta HTTP ' + response.status);
                return response.json();
            })
            .then(function (data) {
                if (data.error) throw new Error(data.error);

                Object.keys(data.stats || {}).forEach(function (key) {
                    const element = document.querySelector('[data-stat-key="' + key + '"]');
                    if (element) element.textContent = formatNumber.format(Number(data.stats[key] || 0));
                });

                if (puedeVerEntradas) {
                    renderNotas(document.getElementById('notasEntradaBody'), data.notas_entrada, 'entrada');
                    renderNotas(document.getElementById('modalEntradasBody'), data.notas_entrada, 'entrada');
                }
                renderNotas(document.getElementById('notasSalidaBody'), data.notas_salida, 'salida');
                renderNotas(document.getElementById('modalSalidasBody'), data.notas_salida, 'salida');

                const newEntradaId = Number(data.ultimo_id_nota_entrada || 0);
                const newSalidaId = Number(data.ultimo_id_nota_salida || 0);
                const hayNuevaNota = newEntradaId > lastNotaEntradaId || newSalidaId > lastNotaSalidaId;
                lastNotaEntradaId = Math.max(lastNotaEntradaId, newEntradaId);
                lastNotaSalidaId = Math.max(lastNotaSalidaId, newSalidaId);

                if (hayNuevaNota && typeof window.mostrarToast === 'function') {
                    window.mostrarToast('¡Nueva nota registrada!', 'success');
                }
            })
            .catch(function (error) {
                console.error('No se pudo actualizar el dashboard:', error);
            });
    }

    window.setInterval(actualizarDashboard, 60000);
})();
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>
