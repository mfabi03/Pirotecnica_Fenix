<?php
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Helpers\PermisoHelper;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db_menu = (new ConnectDB())->getConnection();
    $id_rol_actual = (int) ($_SESSION['id_rol'] ?? 0);
    $modulosVisibles = $id_rol_actual > 0
        ? PermisoHelper::modulosVisibles($db_menu, $id_rol_actual)
        : [];
} catch (Exception $e) {
    error_log('Error al cargar permisos del menú: ' . $e->getMessage());
    $modulosVisibles = [];
}

$tieneModulo = static function ($nombre) use (&$modulosVisibles): bool {
    return in_array($nombre, $modulosVisibles, true);
};

$currentUrl = $_GET['url'] ?? 'main';
$nombreUsuario = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));

$renderMenu = static function (string $suffix) use ($tieneModulo, $currentUrl): void {
    $itemStyle = static function (string $url) use ($currentUrl): string {
        $active = $currentUrl === $url;
        return 'border:none;background:' . ($active ? 'rgba(243,156,18,0.12)' : 'transparent')
            . ';color:' . ($active ? '#f39c12' : 'rgba(255,255,255,0.72)')
            . ';font-weight:' . ($active ? '600' : '400');
    };

    $linkStyle = static function (string $url) use ($currentUrl): string {
        $active = $currentUrl === $url;
        return 'color:' . ($active ? '#f39c12' : 'rgba(255,255,255,0.72)')
            . ';background:' . ($active ? 'rgba(243,156,18,0.12)' : 'transparent');
    };

    $isNotasActive = in_array($currentUrl, ['notaentrada', 'notasalida'], true);
    $verNotaEntrada = $tieneModulo('Notas de Entrada') && (int) ($_SESSION['id_rol'] ?? 0) !== 3;
    $verNotaSalida = $tieneModulo('Notas de Salida');
    $verCategorias = $tieneModulo('Categorias');
    $verUsuarios = $tieneModulo('Usuarios');
    $verRoles = $tieneModulo('Roles');
    $verReporteMovimiento = $tieneModulo('Reportes');
    $verReporteCaja = $tieneModulo('ReporteCaja');
    $isConfigActive = in_array($currentUrl, ['categorias', 'usuarios', 'roles'], true);
    $isReportesActive = in_array($currentUrl, ['reportes', 'reportecaja'], true);
    ?>
    <div class="d-flex flex-column gap-1">
        <h6 class="text-uppercase small fw-bold mb-2 px-3" style="color:rgba(255,255,255,.45);letter-spacing:.5px">
            <i class="fas fa-compass me-2 text-warning"></i>Navegación
        </h6>

        <a href="?url=main" class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="<?= htmlspecialchars($itemStyle('main'), ENT_QUOTES, 'UTF-8') ?>">
            <i class="fas fa-home" style="width:20px"></i><span>Inicio</span>
        </a>

        <?php if ($tieneModulo('Proveedores')): ?>
            <a href="?url=proveedores" class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="<?= htmlspecialchars($itemStyle('proveedores'), ENT_QUOTES, 'UTF-8') ?>">
                <i class="fas fa-truck" style="width:20px"></i><span>Proveedores</span>
            </a>
        <?php endif; ?>

        <?php if ($tieneModulo('Clientes')): ?>
            <a href="?url=clientes" class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="<?= htmlspecialchars($itemStyle('clientes'), ENT_QUOTES, 'UTF-8') ?>">
                <i class="fas fa-users" style="width:20px"></i><span>Clientes</span>
            </a>
        <?php endif; ?>

        <?php if ($tieneModulo('Productos')): ?>
            <a href="?url=productos" class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="<?= htmlspecialchars($itemStyle('productos'), ENT_QUOTES, 'UTF-8') ?>">
                <i class="fas fa-box" style="width:20px"></i><span>Productos</span>
            </a>
        <?php endif; ?>

        <?php if ($verNotaEntrada || $verNotaSalida): ?>
            <div class="w-100">
                <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none"
                   data-bs-toggle="collapse" href="#menuNotas<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>"
                   role="button" aria-expanded="<?= $isNotasActive ? 'true' : 'false' ?>"
                   aria-controls="menuNotas<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>"
                   style="background:<?= $isNotasActive ? 'rgba(243,156,18,0.12)' : 'transparent' ?>;color:<?= $isNotasActive ? '#f39c12' : 'rgba(255,255,255,0.72)' ?>">
                    <i class="fas fa-file-invoice" style="width:20px"></i><span class="flex-grow-1">Notas</span><i class="fas fa-chevron-down small"></i>
                </a>
                <div class="collapse <?= $isNotasActive ? 'show' : '' ?>" id="menuNotas<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="d-flex flex-column ps-3 mt-1 rounded-3 p-2" style="background:#0D0D1A">
                        <?php if ($verNotaEntrada): ?>
                            <a class="dropdown-item py-2 px-2 rounded-2" href="?url=notaentrada" style="<?= htmlspecialchars($linkStyle('notaentrada'), ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fas fa-sign-in-alt me-2"></i>Nota de Entrada
                            </a>
                        <?php endif; ?>
                        <?php if ($verNotaSalida): ?>
                            <a class="dropdown-item py-2 px-2 rounded-2" href="?url=notasalida" style="<?= htmlspecialchars($linkStyle('notasalida'), ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Nota de Salida
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($verCategorias || $verUsuarios || $verRoles): ?>
            <div class="w-100">
                <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none"
                   data-bs-toggle="collapse" href="#menuConfiguracion<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>"
                   role="button" aria-expanded="<?= $isConfigActive ? 'true' : 'false' ?>"
                   aria-controls="menuConfiguracion<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>"
                   style="background:<?= $isConfigActive ? 'rgba(243,156,18,0.12)' : 'transparent' ?>;color:<?= $isConfigActive ? '#f39c12' : 'rgba(255,255,255,0.72)' ?>">
                    <i class="fas fa-cog" style="width:20px"></i><span class="flex-grow-1">Configuración</span><i class="fas fa-chevron-down small"></i>
                </a>
                <div class="collapse <?= $isConfigActive ? 'show' : '' ?>" id="menuConfiguracion<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="d-flex flex-column ps-3 mt-1 rounded-3 p-2" style="background:#0D0D1A">
                        <?php if ($verCategorias): ?>
                            <a class="dropdown-item py-2 px-2 rounded-2" href="?url=categorias" style="<?= htmlspecialchars($linkStyle('categorias'), ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-tags me-2"></i>Categorías</a>
                        <?php endif; ?>
                        <?php if ($verUsuarios): ?>
                            <a class="dropdown-item py-2 px-2 rounded-2" href="?url=usuarios" style="<?= htmlspecialchars($linkStyle('usuarios'), ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-users-cog me-2"></i>Usuarios</a>
                        <?php endif; ?>
                        <?php if ($verRoles): ?>
                            <a class="dropdown-item py-2 px-2 rounded-2" href="?url=roles" style="<?= htmlspecialchars($linkStyle('roles'), ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-user-shield me-2"></i>Roles</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($verReporteMovimiento || $verReporteCaja): ?>
            <div class="w-100">
                <a class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none"
                   data-bs-toggle="collapse" href="#menuReportes<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>"
                   role="button" aria-expanded="<?= $isReportesActive ? 'true' : 'false' ?>"
                   aria-controls="menuReportes<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>"
                   style="background:<?= $isReportesActive ? 'rgba(243,156,18,0.12)' : 'transparent' ?>;color:<?= $isReportesActive ? '#f39c12' : 'rgba(255,255,255,0.72)' ?>">
                    <i class="fas fa-file-alt" style="width:20px"></i><span class="flex-grow-1">Reportes</span><i class="fas fa-chevron-down small"></i>
                </a>
                <div class="collapse <?= $isReportesActive ? 'show' : '' ?>" id="menuReportes<?= htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="d-flex flex-column ps-3 mt-1 rounded-3 p-2" style="background:#0D0D1A">
                        <?php if ($verReporteMovimiento): ?>
                            <a class="dropdown-item py-2 px-2 rounded-2" href="?url=reportes" style="<?= htmlspecialchars($linkStyle('reportes'), ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-boxes-stacked me-2"></i>Reporte de Movimiento</a>
                        <?php endif; ?>
                        <?php if ($verReporteCaja): ?>
                            <a class="dropdown-item py-2 px-2 rounded-2" href="?url=reportecaja" style="<?= htmlspecialchars($linkStyle('reportecaja'), ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-cash-register me-2"></i>Reporte de Caja</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Pirotecnica Fénix</title>
    <base href="/pirotecnica_fenix/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/pirotecnica_fenix/assets/css/estilo.css">
    <link rel="stylesheet" href="/pirotecnica_fenix/assets/css/validaciones.css">
    <script src="/pirotecnica_fenix/assets/js/validaciones.js" defer></script>
    <style>
        body { padding-top: 60px; background: #0e0e18; }
        .app-navbar { 
            min-height: 60px; 
            background: #1a1a2e; 
            z-index: 1050; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .app-sidebar {
            position: sticky;
            top: 60px;
            height: calc(100vh - 60px);
            overflow-y: auto;
            background: #1a1a2e;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }
        .app-offcanvas { 
            background: #1a1a2e; 
            color: #fff; 
            z-index: 1060; 
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }
        @media (max-width: 991.98px) {
            .content-wrapper { min-height: calc(100vh - 110px); }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark fixed-top app-navbar shadow">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="?url=main">
            <img src="assets/imagenes/logo.png" alt="Logo" class="rounded-circle" style="width:40px;height:40px;object-fit:contain;background:rgba(255,247,22,.72)">
            <span class="d-none d-sm-inline"><span class="text-warning">Sistema</span> Pirotecnica Fénix</span>
            <span class="d-sm-none"><span class="text-warning">Pirotecnica</span> Fénix</span>
        </a>

        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuMovil" aria-controls="menuMovil" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <span class="navbar-text text-white d-none d-md-inline">
                <i class="fas fa-user me-1"></i><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?>
            </span>
            <a href="?url=logout" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i><span class="d-none d-sm-inline ms-1">Salir</span>
            </a>
        </div>
    </div>
</nav>

<div class="offcanvas offcanvas-start app-offcanvas d-lg-none" tabindex="-1" id="menuMovil" aria-labelledby="menuMovilLabel">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title" id="menuMovilLabel"><i class="fas fa-compass text-warning me-2"></i>Menú</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <?php $renderMenu('Movil'); ?>
        <div class="mt-auto pt-3 text-center border-top border-secondary">
            <small class="text-white-50">Pirotecnia Fénix · v1.0.0</small>
        </div>
    </div>
</div>

<div class="container-fluid px-0">
    <div class="row align-items-stretch g-0">
        <aside class="col-lg-2 d-none d-lg-block app-sidebar">
            <div class="p-3 h-100 d-flex flex-column">
                <?php $renderMenu('Pc'); ?>
                <div class="mt-auto pt-3 text-center border-top border-secondary">
                    <small class="text-white-50">Pirotecnia Fénix · v1.0.0</small>
                </div>
            </div>
        </aside>
        <div class="col-lg-10 col-12">
            <div class="content-wrapper p-3">
