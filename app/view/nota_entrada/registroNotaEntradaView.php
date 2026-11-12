<?php
// app/view/notaentrada/registroNotaEntradaView.php
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8 col-lg-12">
            
            <!-- TARJETA DE TÍTULO - FONDO OSCURO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-sign-in-alt text-gold me-2"></i> Registrar Nota de Entrada
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Registra el ingreso de productos al stock
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=notaentrada&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-list me-1"></i> Ver Notas
                        </a>
                    </div>
                </div>
            </div>

            <!-- MENSAJES -->
            <?php if (isset($_SESSION['mensaje_rapido'])): ?>
                <div class="alert <?= ($_SESSION['tipo_rapido'] ?? 'success') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-<?= ($_SESSION['tipo_rapido'] ?? 'success') === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                        <?= htmlspecialchars($_SESSION['mensaje_rapido']) ?>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                </div>
                <?php unset($_SESSION['mensaje_rapido'], $_SESSION['tipo_rapido']); ?>
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

            <!-- FORMULARIO PRINCIPAL -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-edit me-2"></i> Datos de la Nota de Entrada
                    </h5>
                </div>
                
                <div class="card-body">
                    <?php
                        $idProveedorSeleccionado = isset($_GET['id_proveedor']) ? (int) $_GET['id_proveedor'] : (isset($_SESSION['nuevo_proveedor_id']) ? (int) $_SESSION['nuevo_proveedor_id'] : 0);
                        unset($_SESSION['nuevo_proveedor_id']);
                    ?>
                    <form method="POST" action="?url=notaentrada&type=store" id="notaEntradaForm">
                        
                        <div class="row g-4 mb-4">
                            <div class="col-lg-6">
                                <div class="card h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #f39c12;">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3" style="color: #f39c12;"><i class="fas fa-file-invoice me-2"></i>Datos de la Nota</h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">
                                                <i class="fas fa-calendar-alt me-1"></i> Fecha de Ingreso <span class="text-danger">*</span>
                                            </label>
                                            <input type="datetime-local" name="fecha_ingreso" class="form-control"
                                                   value="<?= date('Y-m-d\TH:i') ?>" required
                                                   style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 10px 16px;">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">
                                                <i class="fas fa-truck me-1"></i> Proveedor <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <select id="id_proveedor" name="id_proveedor" class="form-select" required
                                                        style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px 0 0 12px; color: #ffffff; padding: 10px 16px;">
                                                    <option value="">Seleccione un proveedor...</option>
                                                    <?php if (!empty($proveedores)): ?>
                                                        <?php foreach ($proveedores as $prov): ?>
                                                            <option value="<?= $prov['id_proveedor'] ?>"
                                                                <?= ($idProveedorSeleccionado > 0 && $idProveedorSeleccionado == (int) $prov['id_proveedor']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($prov['razon_social'] . ' - ' . $prov['rif']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="">No hay proveedores disponibles</option>
                                                    <?php endif; ?>
                                                </select>
                                                <button type="button" id="btnAbrirModalProveedor" class="btn"
                                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 16px;"
                                                        title="Registrar nuevo proveedor">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">
                                                <i class="fas fa-align-left me-1"></i> Descripción
                                            </label>
                                            <textarea name="descripcion" class="form-control" rows="3"
                                                      placeholder="Descripción de la nota de entrada (opcional)"
                                                      style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 12px 16px; resize: vertical;"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #28a745;">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3" style="color: #28a745;"><i class="fas fa-boxes me-2"></i>Agregar Productos</h6>
                                        <?php
                                            $idProductoHeredado = $_GET['id_producto'] ?? $_SESSION['nuevo_producto_id'] ?? '';
                                            $cantidadHeredada = $_GET['cantidad'] ?? '';
                                            $costoHeredado = $_GET['costo'] ?? '';
                                        ?>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Producto <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select id="productoSelect" class="form-select"
                                                        style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px 0 0 12px; color: #ffffff; padding: 10px 16px;">
                                                    <option value="">Seleccione un producto...</option>
                                                    <?php if (!empty($productos)): ?>
                                                        <?php foreach ($productos as $p): ?>
                                                            <option value="<?= $p['id_producto'] ?>"
                                                                    data-descripcion="<?= htmlspecialchars($p['descripcion']) ?>"
                                                                    data-costo="<?= $p['costo_unitario'] ?>"
                                                                    data-stock="<?= $p['cantidad'] ?>"
                                                                <?= ($idProductoHeredado != '' && $idProductoHeredado == $p['id_producto']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($p['descripcion']) ?> (Stock: <?= $p['cantidad'] ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="">No hay productos disponibles</option>
                                                    <?php endif; ?>
                                                </select>
                                                <button type="button" id="btnAbrirModalProducto" class="btn"
                                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 16px;"
                                                        title="Registrar nuevo producto">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php unset($_SESSION['nuevo_producto_id']); ?>
                                        <div class="row g-3 align-items-end">
                                            <div class="col-sm-6">
                                                <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Cantidad</label>
                                                <input type="number" id="cantidadInput" class="form-control" min="1"
                                                       value="<?= htmlspecialchars($cantidadHeredada !== '' ? $cantidadHeredada : '1') ?>"
                                                       style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 10px 16px;">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Costo Unitario</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-right: none; color: rgba(255,255,255,0.5); border-radius: 12px 0 0 12px;">$</span>
                                                    <input type="number" id="costoInput" class="form-control" step="0.01" min="0"
                                                           value="<?= htmlspecialchars($costoHeredado !== '' ? number_format((float) $costoHeredado, 2, '.', '') : '0.00') ?>"
                                                           style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-left: none; border-radius: 0 12px 12px 0; color: #ffffff; padding: 10px 16px;">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="button" class="btn btn-dark-gold w-100" id="btnAgregarProducto"
                                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px; border-radius: 12px;">
                                                    <i class="fas fa-plus me-1"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA DE DETALLES -->
                        <div class="card mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px;">
                            <div class="card-header" style="background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.04); border-radius: 12px 12px 0 0; padding: 12px 20px;">
                                <span style="color: #ffffff; font-weight: 600; font-size: 0.85rem;">
                                    <i class="fas fa-list me-2"></i> Productos Agregados
                                </span>
                            </div>
                            <div class="card-body" style="padding: 0;">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle m-0" id="tablaDetalles">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 py-2" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">ID</th>
                                                <th class="py-2" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Producto</th>
                                                <th class="py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Cantidad</th>
                                                <th class="py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Costo Unitario</th>
                                                <th class="py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Subtotal</th>
                                                <th class="pe-4 py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detallesBody">
                                            <tr>
                                                <td colspan="6" class="text-center py-3" style="color: rgba(255,255,255,0.2);">
                                                    <i class="fas fa-box-open me-2"></i> No hay productos agregados
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr style="border-top: 2px solid rgba(243,156,18,0.15);">
                                                <th colspan="4" class="ps-4 py-2 text-end" style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">Total:</th>
                                                <th class="pe-4 py-2 text-center" style="color: #f39c12; font-size: 1.1rem; font-weight: 700;" id="totalNota">$0.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- CAMPOS OCULTOS -->
                        <div id="detallesContainer"></div>

                        <!-- BOTONES DE ACCIÓN -->
                        <div class="mt-4 text-end" style="border-top: 1px solid rgba(255,255,255,0.04); padding-top: 20px;">
                            <a href="?url=notaentrada&type=list" class="btn" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                                <i class="fas fa-save me-2"></i> Guardar Nota de Entrada
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODAL 1: REGISTRO RÁPIDO DE PROVEEDOR (AJAX)                 -->
<!-- ============================================================ -->
<div class="modal fade" id="modalRapidoProveedor" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-truck me-2" style="color: #f39c12;"></i> Registrar Proveedor Rápido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <div id="modal-rapido-proveedor-error" class="alert" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="modal-rapido-proveedor-error-msg"></span>
                </div>

                <form id="formRapidoProveedor">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                            <input type="text" id="rapido-prov-rif" name="rif" class="form-control" required
                                   placeholder="Ej: J-123456789" data-validate="rif">
                            <small class="feedback-validacion info" id="rapido-prov-rif-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: J-123456789
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono *</label>
                            <input type="tel" id="rapido-prov-telefono" name="numero_contacto" class="form-control" required
                                   placeholder="Ej: 0412-5556677" data-validate="telefono">
                            <small class="feedback-validacion info" id="rapido-prov-telefono-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: 0412-1234567
                            </small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                            <input type="text" id="rapido-prov-razon" name="razon_social" class="form-control" required
                                   placeholder="Nombre de la empresa" data-validate="razon">
                            <small class="feedback-validacion info" id="rapido-prov-razon-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo</label>
                            <input type="email" id="rapido-prov-correo" name="correo_electronico" class="form-control"
                                   placeholder="proveedor@correo.com" data-validate="correo">
                            <small class="feedback-validacion info" id="rapido-prov-correo-feedback">
                                <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección *</label>
                            <input type="text" id="rapido-prov-direccion" name="direccion" class="form-control" required
                                   placeholder="Dirección del proveedor" data-validate="direccion">
                            <small class="feedback-validacion info" id="rapido-prov-direccion-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 5 caracteres
                            </small>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    Cancelar
                </button>
                <button type="button" class="btn" id="btnGuardarProveedorRapido"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                    <i class="fas fa-save me-1"></i> Guardar Proveedor
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODAL 2: REGISTRO RÁPIDO DE PRODUCTO (AJAX) SIN COSTO        -->
<!-- ============================================================ -->
<div class="modal fade" id="modalRapidoProducto" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-box me-2" style="color: #f39c12;"></i> Registrar Producto Rápido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <div id="modal-rapido-producto-error" class="alert" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="modal-rapido-producto-error-msg"></span>
                </div>

                <form id="formRapidoProducto">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Descripción del Producto *</label>
                            <input type="text" id="rapido-prod-descripcion" name="descripcion" class="form-control" required
                                   placeholder="Ej: Volcán grande" data-validate="razon">
                            <small class="feedback-validacion info" id="rapido-prod-descripcion-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Categoría *</label>
                            <select id="rapido-prod-categoria" name="id_categoria" class="form-select" required
                                    style="border-radius: 12px; padding: 10px 14px;">
                                <option value="">Seleccione categoría...</option>
                                <?php foreach (($categorias ?? []) as $c): ?>
                                    <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre_categoria']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Proveedor *</label>
                            <select id="rapido-prod-proveedor" name="id_proveedor" class="form-select" required
                                    style="border-radius: 12px; padding: 10px 14px;">
                                <option value="">Seleccione proveedor...</option>
                                <?php foreach (($proveedores ?? []) as $p): ?>
                                    <option value="<?= $p['id_proveedor'] ?>"><?= htmlspecialchars($p['razon_social']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Stock Mínimo</label>
                            <input type="number" id="rapido-prod-stockmin" name="stock_minimo" class="form-control" 
                                   min="1" value="10"
                                   style="border-radius: 12px; padding: 10px 14px;">
                            <small style="color: #6c757d; font-size: 0.7rem;">Alerta cuando el stock baje a este nivel</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Especificaciones (opcional)</label>
                            <textarea id="rapido-prod-especificaciones" name="especificaciones" class="form-control" rows="2"
                                      placeholder="Peso, color, material..." style="border-radius: 12px; resize: vertical;"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    Cancelar
                </button>
                <button type="button" class="btn" id="btnGuardarProductoRapido"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                    <i class="fas fa-save me-1"></i> Guardar Producto
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- SCRIPTS                                                       -->
<!-- ============================================================ -->
<?php $scriptsModulo = array_merge($scriptsModulo ?? [], ['nota_entrada.js']); ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>