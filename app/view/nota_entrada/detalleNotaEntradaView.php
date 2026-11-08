<?php
// app/view/nota_entrada/detalleNotaEntradaView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

$nota = $nota ?? null;
$anulada = (int) ($nota['eliminado'] ?? 0) === 1;
$fechaIngreso = $nota['fecha_ingreso'] ?? '';
$fechaNota = $fechaIngreso !== '' ? formatoFechaVenezuela($fechaIngreso) : 'N/A';
$timestampNota = $fechaIngreso !== '' ? strtotime($fechaIngreso) : false;
$horaNota = $timestampNota !== false ? date('H:i', $timestampNota) : 'N/A';
$detalles = $nota['detalles'] ?? [];
$totalUnidades = 0;
$totalDinero = 0;

foreach ($detalles as $d) {
    $cantidad = (int) ($d['cantidad'] ?? 0);
    $costo = (float) ($d['costo_unitario'] ?? 0);
    $totalUnidades += $cantidad;
    $totalDinero += $cantidad * $costo;
}
?>

<div class="container-fluid py-4">
    <?php if (!$nota): ?>
        <div class="alert alert-danger" role="alert">Nota de entrada no encontrada.</div>
    <?php else: ?>
        <main class="bg-white p-4 p-md-5 shadow-sm border rounded mx-auto text-dark" style="max-width: 900px;">
            <header class="d-flex justify-content-between align-items-center gap-3 pb-4 mb-4 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <img src="assets/imagenes/logo.png" alt="Logo de Pirotecnica Fenix" style="width: 68px; height: 68px; object-fit: contain;">
                    <div>
                        <h1 class="h4 fw-bold mb-1">PIROTECNICA FENIX C.A.</h1>
                        <p class="small text-muted mb-0">Sistema de gestión - Comprobante</p>
                    </div>
                </div>
                <div class="p-3 text-center fw-bold" style="background-color: #f39c12; color: #212529;">
                    NOTA DE ENTRADA
                </div>
            </header>

            <section class="row g-4 mb-4">
                <div class="col-md-6">
                    <h2 class="h6 fw-bold text-uppercase text-secondary mb-2">DATOS DE LA NOTA</h2>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th scope="row" class="fw-normal text-secondary">Nota #</th>
                                <td><?= htmlspecialchars((string) ($nota['id_nota_entrada'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="fw-normal text-secondary">Fecha</th>
                                <td><?= htmlspecialchars($fechaNota, ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="fw-normal text-secondary">Hora</th>
                                <td><?= htmlspecialchars($horaNota, ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="fw-normal text-secondary">Encargado</th>
                                <td><?= htmlspecialchars($nota['encargado_nombre'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <h2 class="h6 fw-bold text-uppercase text-secondary mb-2">DATOS DEL PROVEEDOR</h2>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th scope="row" class="fw-normal text-secondary">Proveedor</th>
                                <td><?= htmlspecialchars($nota['razon_social'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="fw-normal text-secondary">Documento (RIF)</th>
                                <td><?= htmlspecialchars($nota['rif'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="fw-normal text-secondary">Estatus</th>
                                <td class="<?= $anulada ? 'text-danger' : 'text-success' ?> fw-bold">
                                    <?= $anulada ? 'ANULADA' : 'ACTIVA' ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="h6 fw-bold text-uppercase text-secondary mb-2">DETALLE DE PRODUCTOS</h2>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th scope="col">ID</th>
                                <th scope="col">Producto</th>
                                <th scope="col">Categoría</th>
                                <th scope="col" class="text-end">Cant.</th>
                                <th scope="col" class="text-end">Costo</th>
                                <th scope="col" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($detalles)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No hay productos en esta nota.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($detalles as $d): ?>
                                    <?php
                                    $cantidad = (int) ($d['cantidad'] ?? 0);
                                    $costo = (float) ($d['costo_unitario'] ?? 0);
                                    $subtotal = $cantidad * $costo;
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($d['id_producto'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($d['nombre_producto'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($d['nombre_categoria'] ?? 'Sin categoría', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-end"><?= number_format($cantidad) ?></td>
                                        <td class="text-end">$<?= number_format($costo, 2) ?></td>
                                        <td class="text-end">$<?= number_format($subtotal, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">TOTALES:</th>
                                <td class="text-end fw-bold"><?= number_format($totalUnidades) ?></td>
                                <td></td>
                                <td class="text-end fw-bold">$<?= number_format($totalDinero, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <footer class="mt-4 pt-3 border-top text-center small text-muted">
                Gracias por su preferencia - Pirotecnia Fenix C.A.
                <span class="d-block"><?= htmlspecialchars(date('d/m/Y'), ENT_QUOTES, 'UTF-8') ?></span>
            </footer>
        </main>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>
