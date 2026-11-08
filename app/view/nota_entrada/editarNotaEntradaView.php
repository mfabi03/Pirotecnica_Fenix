<?php
require_once dirname(__DIR__, 2) . '/view/header.php';
$fechaNota = strtotime($nota['fecha_ingreso'] ?? '');
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-edit text-gold me-2"></i> Editar Nota de Entrada #<?= (int) $nota['id_nota_entrada'] ?>
                        </h3>
                        <small class="text-white-50">Modifica los datos y productos de la nota</small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=notaentrada&type=list" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="alert alert-warning d-none" id="editarNotaError" role="alert"></div>

            <form method="POST" action="?url=notaentrada&type=update" id="editarNotaEntradaForm">
                <input type="hidden" name="id_nota_entrada" value="<?= (int) $nota['id_nota_entrada'] ?>">
                <div class="dark-card card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="m-0"><i class="fas fa-file-invoice me-2"></i>Datos de la Nota</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="fecha_ingreso" class="form-label">Fecha y hora</label>
                                <input type="datetime-local" class="form-control" id="fecha_ingreso" name="fecha_ingreso"
                                       value="<?= $fechaNota ? date('Y-m-d\TH:i', $fechaNota) : '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="id_proveedor" class="form-label">Proveedor</label>
                                <select class="form-select" id="id_proveedor" name="id_proveedor" required>
                                    <option value="">Seleccione un proveedor</option>
                                    <?php foreach ($proveedores as $proveedor): ?>
                                        <option value="<?= (int) $proveedor['id_proveedor'] ?>"
                                            <?= (int) $proveedor['id_proveedor'] === (int) $nota['id_proveedor'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($proveedor['razon_social'] . ' - ' . $proveedor['rif']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="descripcion" name="descripcion"
                                       value="<?= htmlspecialchars($nota['descripcion'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dark-card card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="m-0"><i class="fas fa-boxes me-2"></i>Productos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-md-6">
                                <label for="productoSelect" class="form-label">Producto</label>
                                <select class="form-select" id="productoSelect">
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <option value="<?= (int) $producto['id_producto'] ?>"
                                                data-costo="<?= htmlspecialchars((string) $producto['costo_unitario']) ?>">
                                            <?= htmlspecialchars($producto['descripcion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="cantidadInput" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidadInput" min="1" value="1">
                            </div>
                            <div class="col-md-2">
                                <label for="costoInput" class="form-label">Costo unitario</label>
                                <input type="number" class="form-control" id="costoInput" min="0" step="0.01" value="0.00">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-warning w-100" id="agregarProductoNota">
                                    <i class="fas fa-plus me-1"></i> Agregar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Costo</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="detallesBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total</th>
                                        <th id="totalNota" class="text-end">$0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="detallesContainer"></div>
                    </div>
                </div>

                <div class="text-end mb-4">
                    <a href="?url=notaentrada&type=list" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script id="notaEdicionData" type="application/json">
<?= json_encode(['detalles' => $nota['detalles'] ?? []], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?>
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/pirotecnica_fenix/assets/js/editar_nota_entrada.js"></script>

<?php require_once dirname(__DIR__, 2) . '/view/footer.php'; ?>
