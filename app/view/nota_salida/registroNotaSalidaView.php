<?php
require_once dirname(__DIR__, 2) . "/view/header.php";
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-8 col-lg-12">
            
            <!-- ==========================================
                 TARJETA DE TÍTULO - FONDO OSCURO
                 ========================================== -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-sign-out-alt text-gold me-2"></i> Registrar Nota de Salida
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Complete los campos para registrar una nueva nota de salida
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=notasalida&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 MENSAJES
                 ========================================== -->
            <?php if (isset($_SESSION['mensaje_rapido'])): ?>
                <div class="alert <?= htmlspecialchars((string) (($_SESSION['tipo_rapido'] ?? 'success') === 'success' ? 'dark-alert-success' : 'dark-alert-danger'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> alert-dismissible fade show shadow-sm border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-<?= htmlspecialchars((string) (($_SESSION['tipo_rapido'] ?? 'success') === 'success' ? 'check-circle' : 'exclamation-circle'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> me-2"></i>
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

            <!-- ==========================================
                 FORMULARIO PRINCIPAL
                 ========================================== -->
            <div class="dark-card card shadow-sm">
                <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
                    <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                        <i class="fas fa-edit me-2"></i> Datos de la Nota de Salida
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="?url=notasalida&type=store" id="formNotaSalida">
                        
                        <!-- ==========================================
                        DATOS DEL CLIENTE Y ENCARGADO
                        ========================================== -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #f39c12;">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3" style="color: #f39c12;">
                                            <i class="fas fa-user me-2"></i> Cliente
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Cliente <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <?php 
                                                    $idClienteHeredado = $_GET['id_cliente'] ?? $_SESSION['nuevo_cliente_id'] ?? '';
                                                    $clientes = $clientes ?? [];
                                                ?>
                                                <select name="id_cliente" id="id_cliente" class="form-select" required style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px 0 0 12px; color: #ffffff; padding: 10px 16px;">
                                                    <option value="">Seleccione un cliente...</option>
                                                    <?php foreach ($clientes as $c): ?>
                                                        <?php
                                                            $nombreCliente = ($c['tipo_cliente'] ?? '') === 'Jurídico'
                                                                ? trim($c['razon_social'] ?? '')
                                                                : trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                                                            $doc = !empty($c['cedula']) ? $c['cedula'] : (!empty($c['rif']) ? $c['rif'] : '');
                                                            $etiqueta = ($nombreCliente !== '' ? $nombreCliente : 'Cliente #' . $c['id_cliente']) . ($doc !== '' ? " ($doc)" : '');
                                                        ?>
                                                        <option value="<?= htmlspecialchars((string) ($c['id_cliente']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                            <?= htmlspecialchars((string) (($idClienteHeredado != '' && $idClienteHeredado == $c['id_cliente']) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>
                                                            <?= htmlspecialchars($etiqueta) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <!-- BOTÓN "+" QUE ABRE EL MODAL RÁPIDO -->
                                                <button type="button" 
                                                        id="btnAbrirModalCliente"
                                                        class="btn" 
                                                        title="Registrar nuevo cliente rápido"
                                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 16px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <?php unset($_SESSION['nuevo_cliente_id']); ?>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Encargado</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?>" disabled style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: rgba(255,255,255,0.4); padding: 10px 16px;">
                                            <small style="color: rgba(255,255,255,0.2); font-size: 0.7rem;">La persona encargada se registra automáticamente</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; border-left: 4px solid #28a745;">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3" style="color: #28a745;">
                                            <i class="fas fa-boxes me-2"></i> Agregar Productos
                                        </h6>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Producto <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <?php 
                                                    $idProductoHeredado = $_GET['id_producto'] ?? $_SESSION['nuevo_producto_id'] ?? '';
                                                    $productos = $productos ?? [];
                                                ?>
                                                <!-- SE QUITÓ EL REQUIRED AQUÍ -->
                                                <select id="producto" class="form-select" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px 0 0 12px; color: #ffffff; padding: 10px 16px;">
                                                    <option value="">Seleccione un producto...</option>
                                                    <?php foreach ($productos as $p): ?>
                                                        <option value="<?= htmlspecialchars((string) ($p['id_producto']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" 
                                                                data-categoria="<?= htmlspecialchars($p['nombre_categoria'] ?? 'Sin categoría') ?>"
                                                                data-stock="<?= htmlspecialchars((string) ($p['stock']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                            <?= htmlspecialchars((string) (($idProductoHeredado != '' && $idProductoHeredado == $p['id_producto']) ? 'selected' : ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>>
                                                            <?= htmlspecialchars($p['descripcion']) ?> 
                                                            (Stock: <?= htmlspecialchars((string) ($p['stock']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php unset($_SESSION['nuevo_producto_id']); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Categoría</label>
                                            <input type="text" id="categoria" class="form-control" value="" readonly style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: rgba(255,255,255,0.3); padding: 10px 16px;">
                                        </div>
                                        <div class="row">
                                            <div class="col-8">
                                                <label class="form-label fw-bold" style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Cantidad <span class="text-danger">*</span></label>
                                                <input type="number" id="cantidad" class="form-control" min="1" value="1" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; color: #ffffff; padding: 10px 16px;">
                                            </div>
                                            <div class="col-4 d-flex align-items-end">
                                                <button type="button" id="btnAgregar" class="btn w-100" style="background: #28a745; border: none; color: #fff; border-radius: 12px; padding: 10px; font-weight: 600; transition: all 0.3s ease;">
                                                    <i class="fas fa-plus me-1"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                        <small style="color: rgba(255,255,255,0.2); font-size: 0.7rem;">Stock disponible: <span id="stockActual" style="color: rgba(255,255,255,0.4);">0</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                        TABLA DE PRODUCTOS AGREGADOS
                        ========================================== -->
                        <div class="card mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px;">
                            <div class="card-header" style="background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.04); border-radius: 12px 12px 0 0; padding: 12px 20px;">
                                <span style="color: #ffffff; font-weight: 600; font-size: 0.85rem;">
                                    <i class="fas fa-list me-2"></i> Productos agregados
                                </span>
                            </div>
                            <div class="card-body" style="padding: 0;">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle m-0" id="detallesTabla">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 py-2" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Producto</th>
                                                <th class="py-2" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Categoría</th>
                                                <th class="py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Cantidad</th>
                                                <th class="pe-4 py-2 text-center" style="color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyDetalles">
                                            <tr id="filaVacia">
                                                <td colspan="4" class="text-center py-3" style="color: rgba(255,255,255,0.2);">
                                                    <i class="fas fa-box-open me-2"></i> No hay productos agregados
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                        CAMPOS OCULTOS PARA DETALLES
                        ========================================== -->
                        <div id="detallesContainer"></div>

                        <!-- ==========================================
                        BOTONES DE ACCIÓN
                        ========================================== -->
                        <div class="mt-4 text-end" style="border-top: 1px solid rgba(255,255,255,0.04); padding-top: 20px;">
                            <a href="?url=notasalida&type=list" class="btn" style="background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                                <i class="fas fa-save me-2"></i> Guardar Nota de Salida
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================ -->
<!-- MODAL: REGISTRO RÁPIDO DE CLIENTE (AJAX)                     -->
<!-- ============================================================ -->
<div class="modal fade" id="modalRapidoCliente" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-user-plus me-2" style="color: #f39c12;"></i> Registrar Cliente Rápido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <!-- MENSAJE DE ERROR -->
                <div id="modal-rapido-cliente-error" class="alert" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="modal-rapido-cliente-error-msg"></span>
                </div>

                <!-- PESTAÑAS -->
                <ul class="nav nav-tabs mb-4" style="border-bottom: 1px solid #e9ecef;">
                    <li class="nav-item">
                        <button class="nav-link active" id="tab-rapido-natural" data-bs-toggle="tab" data-bs-target="#pane-rapido-natural" type="button"
                                style="color: #f39c12; background: transparent; border: none; border-bottom: 2px solid #f39c12; font-weight: 600;">
                            <i class="fas fa-user me-1"></i> Cliente Natural
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-rapido-juridico" data-bs-toggle="tab" data-bs-target="#pane-rapido-juridico" type="button"
                                style="color: #6c757d; background: transparent; border: none; font-weight: 600;">
                            <i class="fas fa-building me-1"></i> Cliente Jurídico
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- PESTAÑA NATURAL -->
                    <div class="tab-pane fade show active" id="pane-rapido-natural">
                        <form id="formRapidoClienteNatural">
                            <input type="hidden" name="tipo_cliente" value="natural">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Cédula *</label>
                                    <input type="text" id="rapido-cedula" name="cedula" class="form-control" required
                                           placeholder="Ej: V-12345678" data-validate="cedula">
                                    <small class="feedback-validacion info" id="rapido-cedula-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: V-12345678
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono</label>
                                    <input type="tel" id="rapido-telefono" name="telefono" class="form-control"
                                           placeholder="Ej: 0412-5556677" data-validate="telefono">
                                    <small class="feedback-validacion info" id="rapido-telefono-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: 0412-1234567
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombres *</label>
                                    <input type="text" id="rapido-nombre" name="nombre" class="form-control" required
                                           placeholder="Solo letras" data-validate="nombre">
                                    <small class="feedback-validacion info" id="rapido-nombre-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 2 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Apellidos *</label>
                                    <input type="text" id="rapido-apellido" name="apellido" class="form-control" required
                                           placeholder="Solo letras" data-validate="nombre">
                                    <small class="feedback-validacion info" id="rapido-apellido-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 2 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo</label>
                                    <input type="email" id="rapido-correo" name="correo_electronico" class="form-control"
                                           placeholder="ejemplo@correo.com" data-validate="correo">
                                    <small class="feedback-validacion info" id="rapido-correo-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Fecha Nacimiento *</label>
                                    <input type="date" id="rapido-fecha" name="fecha_de_nacimiento" class="form-control" required data-validate="fecha">
                                    <small class="feedback-validacion info" id="rapido-fecha-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Debe ser mayor de 18 años
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección</label>
                                    <input type="text" id="rapido-direccion" name="direccion" class="form-control"
                                           placeholder="Dirección completa" data-validate="direccion">
                                    <small class="feedback-validacion info" id="rapido-direccion-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 5 caracteres
                                    </small>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- PESTAÑA JURÍDICO -->
                    <div class="tab-pane fade" id="pane-rapido-juridico">
                        <form id="formRapidoClienteJuridico">
                            <input type="hidden" name="tipo_cliente" value="juridico">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">RIF *</label>
                                    <input type="text" id="rapido-rif" name="rif" class="form-control" required
                                           placeholder="Ej: J-123456789" data-validate="rif">
                                    <small class="feedback-validacion info" id="rapido-rif-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: J-123456789
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Teléfono</label>
                                    <input type="tel" id="rapido-jur-telefono" name="telefono" class="form-control"
                                           placeholder="Ej: 0251-1234567" data-validate="telefono">
                                    <small class="feedback-validacion info" id="rapido-jur-telefono-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: 0251-1234567
                                    </small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Razón Social *</label>
                                    <input type="text" id="rapido-razon" name="razon_social" class="form-control" required
                                           placeholder="Nombre de la empresa" data-validate="razon">
                                    <small class="feedback-validacion info" id="rapido-razon-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Correo</label>
                                    <input type="email" id="rapido-jur-correo" name="correo_electronico" class="form-control"
                                           placeholder="empresa@correo.com" data-validate="correo">
                                    <small class="feedback-validacion info" id="rapido-jur-correo-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Formato: usuario@dominio.com
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Dirección Fiscal</label>
                                    <input type="text" id="rapido-jur-direccion" name="direccion" class="form-control"
                                           placeholder="Dirección fiscal" data-validate="direccion">
                                    <small class="feedback-validacion info" id="rapido-jur-direccion-feedback">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 5 caracteres
                                    </small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    Cancelar
                </button>
                <button type="button" class="btn" id="btnGuardarClienteRapido"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                    <i class="fas fa-save me-1"></i> Guardar Cliente
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ==========================================
SCRIPTS
========================================== -->


<?php $scriptsModulo = ['nota_salida_registro.js']; ?>
<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>