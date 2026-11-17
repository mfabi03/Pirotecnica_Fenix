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
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
                <?= htmlspecialchars((string) ($idRol === 1 ? 'Resumen General' : ($idRol === 2 ? 'Resumen de Almacén' : 'Resumen Personal')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
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
                        <span class="stat-card-number" data-stat-key="<?= $e($tarjeta['key']) ?>"><?= htmlspecialchars((string) (number_format((int) ($stats[$tarjeta['key']] ?? 0))), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
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
                                            <td>$<?= htmlspecialchars((string) (number_format((float) ($nota['costo_total'] ?? 0), 2, ',', '.')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
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

        <div class="col-12 <?= htmlspecialchars((string) ($mostrarNotasEntrada ? 'col-xl-6' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <section class="dashboard-table-card h-100">
                <div class="dashboard-table-heading">
                    <h5><i class="fas fa-sign-out-alt me-2 text-gold"></i><?= htmlspecialchars((string) ($esCajero ? 'Mis Últimas Notas de Salida' : 'Últimas Notas de Salida'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h5>
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
                                        <td><?= htmlspecialchars((string) (number_format((int) ($nota['total'] ?? 0))), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> unidades</td>
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
                                    id="<?= htmlspecialchars((string) ($modal['tipo_notas'] === 'entrada' ? 'modalEntradasBody' : 'modalSalidasBody'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    data-modal-notes="<?= $e($modal['tipo_notas']) ?>"
                                <?php endif; ?>>
                                <?php if (!empty($modal['filas'])): ?>
                                    <?php foreach ($modal['filas'] as $fila): ?>
                                        <tr><?php foreach ($fila as $valor): ?><td><?= $e($valor) ?></td><?php endforeach; ?></tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="<?= htmlspecialchars((string) (count($modal['columnas'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-center text-secondary py-4">No hay registros para mostrar.</td></tr>
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

<script id="dashboardConfig" type="application/json">
<?= json_encode(
    [
        'lastNotaEntradaId' => (int) ($notasEntradaDetalle[0]['id_nota_entrada'] ?? 0),
        'lastNotaSalidaId' => (int) ($notasSalidaDetalle[0]['id_nota_salida'] ?? 0),
        'puedeVerEntradas' => (bool) $mostrarNotasEntrada,
    ],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?>
</script>
<?php $scriptsModulo = ['dashboard.js']; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>
