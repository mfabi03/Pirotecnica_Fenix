<!-- ============================================================ -->
<!-- MODAL 3: EDITAR PRODUCTO                                     -->
<!-- ============================================================ -->
<?php if ($puede_editar_producto): ?>
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden;">
            
            <!-- HEADER OSCURO -->
            <div class="modal-header" style="background: #1a1a2e; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 16px 24px;">
                <h5 class="modal-title" style="color: #ffffff; font-weight: 700;">
                    <i class="fas fa-edit me-2" style="color: #f39c12;"></i> Editar Producto: <span id="editar-producto-nombre-header" style="color: #f39c12;">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1); opacity: 0.7;"></button>
            </div>

            <!-- BODY BLANCO -->
            <div class="modal-body" style="padding: 20px 24px;">
                <form method="POST" action="?url=productos&type=update" id="formEditarProducto">
                    <input type="hidden" name="id_producto" id="editar-producto-id">
                    <input type="hidden" name="accion" value="update">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Nombre del Producto *</label>
                            <input type="text" name="descripcion" id="editar-producto-descripcion" class="form-control" required
                                   style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                        </div>

                    <!-- CATEGORÍA CON BOTÓN DE REGISTRO RÁPIDO -->
                     <!-- CATEGORÍA CON BOTÓN DE REGISTRO RÁPIDO -->
<div class="col-md-6">
    <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Categoría *</label>
    <div class="input-group">
        <select name="id_categoria" id="editar-producto-categoria" class="form-select" required
                style="border-radius: 12px 0 0 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
            <option value="">Seleccione una Categoría...</option>
            <?php foreach (($categorias ?? []) as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" 
                id="btnAbrirModalCategoriaEditar"
                class="btn" 
                style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 15px; display: flex; align-items: center; justify-content: center;"
                title="Registrar nueva categoría">
            <i class="fas fa-plus"></i>
        </button>
    </div>
</div>
                        <!-- PROVEEDOR CON BOTÓN DE REGISTRO RÁPIDO -->
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Proveedor *</label>
                            <div class="input-group">
                                <select name="id_proveedor" id="editar-producto-proveedor" class="form-select" required
                                        style="border-radius: 12px 0 0 12px; padding: 12px 160px; border: 1.5px solid rgba(222, 18, 18, 0.08);">
                                    <option value="">Seleccione un proveedor...</option>
                                    <?php foreach (($proveedores ?? []) as $prov): ?>
                                        <option value="<?= $prov['id_proveedor'] ?>"><?= htmlspecialchars($prov['razon_social']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" 
                                        id="btnAbrirModalProveedorEditar"
                                        class="btn" 
                                        style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; border-radius: 0 12px 12px 0; padding: 0 15px; display: flex; align-items: center; justify-content: center;"
                                        title="Registrar nuevo proveedor">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Stock Mínimo *</label>
                            <input type="number" name="stock_minimo" id="editar-producto-stock-minimo" class="form-control" min="1" required
                                   style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color: #1a1a2e; font-weight: 600; font-size: 0.85rem;">Especificaciones</label>
                            <textarea name="especificaciones" id="editar-producto-especificaciones" class="form-control" rows="2"
                                      style="border-radius: 12px; padding: 12px 16px; border: 1.5px solid rgba(0,0,0,0.08);"
                                      placeholder="Ej: Peso: 2kg, Color: Rojo"></textarea>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                style="border-radius: 50px; padding: 10px 25px; font-weight: 600;">
                            Cancelar
                        </button>
                        <button type="submit" class="btn"
                                style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 50px;">
                            <i class="fas fa-save me-1"></i> Actualizar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
