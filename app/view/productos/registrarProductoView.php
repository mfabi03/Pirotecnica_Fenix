<?php
// app/view/productos/registrarProductoView.php
require_once dirname(__DIR__, 2) . "/view/header.php";

// Recuperar el id_proveedor desde GET, SESSION o POST
$idProveedorSelected = isset($_GET['id_proveedor']) ? (int) $_GET['id_proveedor'] : (isset($_SESSION['nuevo_proveedor_id']) ? (int) $_SESSION['nuevo_proveedor_id'] : (isset($_POST['id_proveedor']) ? (int) $_POST['id_proveedor'] : 0));

// Recuperar el id_categoria desde GET, SESSION o POST
$idCategoriaSelected = isset($_GET['id_categoria']) ? (int) $_GET['id_categoria'] : (isset($_SESSION['nueva_categoria_id']) ? (int) $_SESSION['nueva_categoria_id'] : (isset($_POST['id_categoria']) ? (int) $_POST['id_categoria'] : 0));

unset($_SESSION['nuevo_proveedor_id']);
unset($_SESSION['nueva_categoria_id']);
?>

<div class="col-md-8 col-lg-12">
            
    <!-- TARJETA DE TÍTULO - FONDO OSCURO -->
    <div class="dark-header-card card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="m-0 dark-title">
                    <i class="fas fa-box text-gold me-2"></i> Registro de Productos
                </h3>
                <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                    Complete todos los campos para registrar un nuevo producto
                </small>
            </div>
            <div class="col-auto">
                <a href="?url=productos&type=list" class="btn" style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 50px; padding: 8px 20px; text-decoration: none; transition: all 0.3s ease;">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- MENSAJES -->
    <?php if (isset($mensaje) && !empty($mensaje)): ?>
        <div class="alert <?= ($tipo_mensaje ?? '') === 'success' ? 'dark-alert-success' : 'dark-alert-danger' ?> alert-dismissible fade show shadow-sm border-0">
            <div class="d-flex align-items-center">
                <i class="fas <?= ($tipo_mensaje ?? '') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-3 fs-4"></i>
                <span><?= htmlspecialchars($mensaje) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert dark-alert-danger alert-dismissible fade show shadow-sm border-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                <span><?= htmlspecialchars($error) ?></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- FORMULARIO -->
    <div class="dark-card card shadow-sm">
        <div class="card-header" style="background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; border-radius: 16px 16px 0 0 !important; padding: 16px 20px !important;">
            <h5 class="m-0" style="color: #ffffff !important; font-weight: 700 !important;">
                <i class="fas fa-box me-2"></i> Nuevo Producto
            </h5>
        </div>
        
        <div class="card-body">
            <form method="POST" action="?url=productos&type=store<?= isset($_GET['return']) ? '&return=' . urlencode($_GET['return']) : '' ?>" id="formProducto">
                
                <?php if (isset($_GET['return'])): ?>
                    <input type="hidden" name="registro_rapido" value="1">
                    <input type="hidden" name="return" value="<?= htmlspecialchars($_GET['return']) ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    
                    <!-- DATOS DEL PRODUCTO -->
                    <div class="col-12">
                        <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                            <i class="fas fa-info-circle me-2" style="color: #f39c12;"></i> Datos del Producto
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="descripcion" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre del Producto *</label>
                                <input type="text" name="descripcion" id="descripcion" class="form-control" 
                                       style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);"
                                       placeholder="Ej: Pólvora" required
                                       value="<?= htmlspecialchars($_POST['descripcion'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="id_categoria" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Categoría *</label>
                                <div class="input-group">
                                    <select name="id_categoria" id="id_categoria" class="form-select" required
                                            style="border-radius: 12px 0 0 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                                        <option value="">Seleccione una categoría...</option>
                                        <?php foreach (($categorias ?? []) as $c): ?>
                                            <option value="<?= $c['id_categoria'] ?>"
                                                <?= ($idCategoriaSelected == $c['id_categoria']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['nombre_categoria']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" 
                                            id="btnAbrirModalCategoria"
                                            class="btn" 
                                            style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 15px; display: flex; align-items: center; justify-content: center;"
                                            title="Registrar nueva categoría">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="id_proveedor" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Proveedor *</label>
                                <div class="input-group">
                                    <select name="id_proveedor" id="id_proveedor" class="form-select" required
                                            style="border-radius: 12px 0 0 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                                        <option value="">Seleccione un proveedor...</option>
                                        <?php foreach (($proveedores ?? []) as $p): ?>
                                            <option value="<?= $p['id_proveedor'] ?>"
                                                <?= ($idProveedorSelected == $p['id_proveedor']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['razon_social']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" 
                                            id="btnAbrirModalProveedor"
                                            class="btn" 
                                            style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 15px; display: flex; align-items: center; justify-content: center;"
                                            title="Registrar nuevo proveedor">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DATOS DE INVENTARIO -->
                    <div class="col-12">
                        <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                            <i class="fas fa-chart-line me-2" style="color: #f39c12;"></i> Datos de Inventario
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="stock_minimo" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Stock Mínimo *</label>
                                <input type="number" name="stock_minimo" id="stock_minimo" class="form-control" min="1"
                                       style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);"
                                       value="<?= htmlspecialchars($_POST['stock_minimo'] ?? 10) ?>">
                                <small style="color: #6c757d; font-size: 0.7rem;">
                                    <i class="fas fa-info-circle me-1"></i> Alerta cuando el stock baje a este nivel
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- ESPECIFICACIONES -->
                    <div class="col-12">
                        <h6 style="color: #1a1a2e; font-weight: 700; border-bottom: 2px solid rgba(243,156,18,0.2); padding-bottom: 8px; margin-bottom: 16px;">
                            <i class="fas fa-list me-2" style="color: #f39c12;"></i> Especificaciones Técnicas
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="especificaciones" class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Especificaciones</label>
                                <textarea name="especificaciones" id="especificaciones" class="form-control" rows="4" 
                                          style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08); resize: vertical;"
                                          placeholder="Ej: Peso: 2kg, Color: Rojo, Material: Plástico"><?= htmlspecialchars($_POST['especificaciones'] ?? '') ?></textarea>
                                <small style="color: #6c757d; font-size: 0.7rem;">Describe las características técnicas del producto</small>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div class="col-12 text-end" style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 20px; margin-top: 10px;">
                        <?php if (isset($_GET['return'])): ?>
                            <a href="?url=<?= htmlspecialchars($_GET['return']) ?>&type=create" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                        <?php else: ?>
                            <a href="?url=productos&type=list" class="btn" style="background: rgba(0,0,0,0.04); color: #1a1a2e; border-radius: 50px; padding: 10px 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; margin-right: 10px;">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px; transition: all 0.3s ease;">
                            <i class="fas fa-save me-2"></i> Registrar Producto
                        </button>
                    </div>
                </div>
            </form>
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
<!-- MODAL 2: REGISTRO RÁPIDO DE CATEGORÍA (AJAX)                 -->
<!-- ============================================================ -->
<div class="modal fade" id="modalRapidoCategoria" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-tags me-2" style="color: #f39c12;"></i> Registrar Categoría Rápida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <div class="modal-body" style="padding: 20px 24px;">
                
                <div id="modal-rapido-categoria-error" class="alert" style="display: none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 12px; padding: 12px 16px;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="modal-rapido-categoria-error-msg"></span>
                </div>

                <form id="formRapidoCategoria">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre de la Categoría *</label>
                            <input type="text" id="rapido-cat-nombre" name="nombre_categoria" class="form-control" required
                                   placeholder="Ej: Volcanes, Estrellitas..." data-validate="razon">
                            <small class="feedback-validacion info" id="rapido-cat-nombre-feedback">
                                <i class="fas fa-info-circle me-1"></i> Mínimo 3 caracteres
                            </small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Descripción</label>
                            <textarea id="rapido-cat-desc" name="descripcion" class="form-control" rows="3"
                                      placeholder="Descripción de la categoría (opcional)" style="resize: vertical;"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 16px 24px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                    Cancelar
                </button>
                <button type="button" class="btn" id="btnGuardarCategoriaRapido"
                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                    <i class="fas fa-save me-1"></i> Guardar Categoría
                </button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ============================================================
    // MÓDULO: REGISTRO RÁPIDO DE PROVEEDOR
    // ============================================================
    const btnAbrirProv = document.getElementById('btnAbrirModalProveedor');
    if (btnAbrirProv) {
        btnAbrirProv.addEventListener('click', function () {
            document.getElementById('formRapidoProveedor').reset();
            document.getElementById('modal-rapido-proveedor-error').style.display = 'none';
            
            document.querySelectorAll('#modalRapidoProveedor input[data-validate]').forEach(function (input) {
                input.classList.remove('campo-valido', 'campo-invalido');
                input.style.borderColor = '';
                input.style.boxShadow = '';
                const feedback = document.getElementById(input.id + '-feedback');
                if (feedback) {
                    feedback.classList.remove('error', 'success');
                    feedback.classList.add('info');
                }
            });
            
            new bootstrap.Modal(document.getElementById('modalRapidoProveedor')).show();
        });
    }

    const btnGuardarProv = document.getElementById('btnGuardarProveedorRapido');
    if (btnGuardarProv) {
        btnGuardarProv.addEventListener('click', function () {
            const formId = 'formRapidoProveedor';
            
            if (typeof Validador !== 'undefined') {
                Validador.init(document.getElementById('modalRapidoProveedor'));
                if (!Validador.validarFormulario(formId)) return;
            }
            
            const formData = new FormData(document.getElementById(formId));
            
            btnGuardarProv.disabled = true;
            btnGuardarProv.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
            
            fetch('?url=proveedores&type=store_rapido_ajax', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                btnGuardarProv.disabled = false;
                btnGuardarProv.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Proveedor';
                
                if (!data.success) {
                    document.getElementById('modal-rapido-proveedor-error-msg').textContent = data.error || 'Error al guardar';
                    document.getElementById('modal-rapido-proveedor-error').style.display = 'block';
                    return;
                }
                
                const selectProv = document.getElementById('id_proveedor');
                if (selectProv) {
                    let existe = false;
                    for (let i = 0; i < selectProv.options.length; i++) {
                        if (selectProv.options[i].value == data.id_proveedor) {
                            existe = true;
                            break;
                        }
                    }
                    
                    if (!existe) {
                        const opt = document.createElement('option');
                        opt.value = data.id_proveedor;
                        opt.textContent = data.razon_social + ' - ' + data.rif;
                        selectProv.appendChild(opt);
                    }
                    
                    selectProv.value = data.id_proveedor;
                }
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalRapidoProveedor'));
                if (modal) modal.hide();
                
                mostrarMensajeExito('✅ Proveedor "' + data.razon_social + '" registrado exitosamente');
            })
            .catch(err => {
                btnGuardarProv.disabled = false;
                btnGuardarProv.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Proveedor';
                document.getElementById('modal-rapido-proveedor-error-msg').textContent = 'Error: ' + err.message;
                document.getElementById('modal-rapido-proveedor-error').style.display = 'block';
            });
        });
    }

    // ============================================================
    // MÓDULO: REGISTRO RÁPIDO DE CATEGORÍA
    // ============================================================
    const btnAbrirCat = document.getElementById('btnAbrirModalCategoria');
    if (btnAbrirCat) {
        btnAbrirCat.addEventListener('click', function () {
            document.getElementById('formRapidoCategoria').reset();
            document.getElementById('modal-rapido-categoria-error').style.display = 'none';
            
            document.querySelectorAll('#modalRapidoCategoria input[data-validate]').forEach(function (input) {
                input.classList.remove('campo-valido', 'campo-invalido');
                input.style.borderColor = '';
                input.style.boxShadow = '';
                const feedback = document.getElementById(input.id + '-feedback');
                if (feedback) {
                    feedback.classList.remove('error', 'success');
                    feedback.classList.add('info');
                }
            });
            
            new bootstrap.Modal(document.getElementById('modalRapidoCategoria')).show();
        });
    }

    const btnGuardarCat = document.getElementById('btnGuardarCategoriaRapido');
    if (btnGuardarCat) {
        btnGuardarCat.addEventListener('click', function () {
            const formId = 'formRapidoCategoria';
            
            if (typeof Validador !== 'undefined') {
                Validador.init(document.getElementById('modalRapidoCategoria'));
                if (!Validador.validarFormulario(formId)) return;
            }
            
            const formData = new FormData(document.getElementById(formId));
            
            btnGuardarCat.disabled = true;
            btnGuardarCat.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
            
            fetch('?url=categorias&action=store_rapido_ajax', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                btnGuardarCat.disabled = false;
                btnGuardarCat.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Categoría';
                
                if (!data.success) {
                    document.getElementById('modal-rapido-categoria-error-msg').textContent = data.error || 'Error al guardar';
                    document.getElementById('modal-rapido-categoria-error').style.display = 'block';
                    return;
                }
                
                const selectCat = document.getElementById('id_categoria');
                if (selectCat) {
                    let existe = false;
                    for (let i = 0; i < selectCat.options.length; i++) {
                        if (selectCat.options[i].value == data.id_categoria) {
                            existe = true;
                            break;
                        }
                    }
                    
                    if (!existe) {
                        const opt = document.createElement('option');
                        opt.value = data.id_categoria;
                        opt.textContent = data.nombre_categoria;
                        selectCat.appendChild(opt);
                    }
                    
                    selectCat.value = data.id_categoria;
                }
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalRapidoCategoria'));
                if (modal) modal.hide();
                
                mostrarMensajeExito('✅ Categoría "' + data.nombre_categoria + '" registrada exitosamente');
            })
            .catch(err => {
                btnGuardarCat.disabled = false;
                btnGuardarCat.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Categoría';
                document.getElementById('modal-rapido-categoria-error-msg').textContent = 'Error: ' + err.message;
                document.getElementById('modal-rapido-categoria-error').style.display = 'block';
            });
        });
    }

    // ============================================================
    // HELPER: MOSTRAR MENSAJE DE ÉXITO
    // ============================================================
    function mostrarMensajeExito(mensaje) {
        const contenedor = document.getElementById('formProducto');
        if (!contenedor) return;
        
        const alerta = document.createElement('div');
        alerta.className = 'alert dark-alert-success alert-dismissible fade show shadow-sm border-0';
        alerta.style.marginBottom = '16px';
        alerta.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <span>${mensaje}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        contenedor.parentNode.insertBefore(alerta, contenedor);
        
        setTimeout(() => {
            if (alerta.parentNode) alerta.remove();
        }, 4000);
    }
});
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>