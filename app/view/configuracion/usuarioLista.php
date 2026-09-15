<?php
// app/view/configuracion/usuarioLista.php
require_once __DIR__ . '/../header.php';

// Paginación
$por_pagina = isset($por_pagina) ? $por_pagina : (int)($_GET['por_pagina'] ?? 10);
$totalRegistros = isset($totalRegistros) ? $totalRegistros : (isset($usuarios) ? count($usuarios) : 0);
$pagina_actual = isset($pagina_actual) ? $pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalPaginas = isset($totalPaginas) ? $totalPaginas : 1;
?>

<div class="col-md-8 col-lg-12">
            
            <!-- TARJETA DE TÍTULO -->
            <div class="dark-header-card card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="m-0 dark-title">
                            <i class="fas fa-users text-gold me-2"></i> Lista de Usuarios
                        </h3>
                        <small style="color: rgba(255, 255, 255, 0.6) !important; display: block; margin-top: 4px;">
                            Gestiona los usuarios del sistema
                        </small>
                    </div>
                    <div class="col-auto">
                        <a href="?url=usuarios&action=registrar" class="btn btn-dark-gold" style="background: linear-gradient(135deg, #f39c12, #e67e22); border: none; color: #fff; font-weight: 600; padding: 8px 22px; border-radius: 50px; transition: all 0.3s ease; text-decoration: none; display: inline-block;">
                            <i class="fas fa-plus me-1"></i> Registrar Usuario
                        </a>
                    </div>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="card shadow-sm p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2 align-items-end" autocomplete="off">
                    <input type="hidden" name="url" value="usuarios">
                    <input type="hidden" name="action" value="lista">
                    
                    <!-- ✅ SELECT "MOSTRAR" ARRIBA -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-dark mb-0">Mostrar</label>
                        <select name="por_pagina" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ([5, 10, 25, 50] as $o): ?>
                                <option value="<?= $o ?>" <?= ($por_pagina == $o) ? 'selected' : '' ?>><?= $o ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <input type="text" name="busqueda" class="form-control" 
                               list="listaUsuarios"
                               placeholder="Buscar por nombre, apellido, cédula o correo..."
                               value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        <datalist id="listaUsuarios">
                            <?php
                            $sugerencias = [];
                            if (!empty($usuarios) && is_array($usuarios)):
                                foreach ($usuarios as $u):
                                    $nombre  = trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''));
                                    $cedula  = trim($u['cedula'] ?? '');
                                    $correo  = trim($u['correo_electronico'] ?? '');
                                    if ($nombre !== '') $sugerencias[$nombre] = 1;
                                    if ($cedula !== '') $sugerencias[$cedula] = 1;
                                    if ($correo !== '') $sugerencias[$correo] = 1;
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
                        <a href="?url=usuarios&action=lista" class="btn btn-secondary w-100">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
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

            <!-- TABLA DE USUARIOS -->
            <div class="dark-card card shadow-sm dark-table-header">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0">
                        <i class="fas fa-user me-2"></i> Usuarios Registrados
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">ID</th>
                                <th class="py-3">Nombre</th>
                                <th class="py-3">Apellido</th>
                                <th class="py-3">Cédula</th>
                                <th class="py-3">Teléfono</th>
                                <th class="py-3">Correo</th>
                                <th class="py-3">Rol</th>
                                <th class="pe-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($usuarios) && is_array($usuarios) && count($usuarios) > 0): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($usuario['id_usuario'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($usuario['nombre'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['apellido'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['cedula'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['telefono'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($usuario['correo_electronico'] ?? '') ?></td>
                                        <td>
                                            <?php 
                                                $rol = $usuario['rol'] ?? 0;
                                                if ($rol == 1):
                                            ?>
                                                <span class="badge" style="background: rgba(243,156,18,0.15); color: #f39c12; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <i class="fas fa-crown me-1"></i> Administrador
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: rgba(13,110,253,0.15); color: #0d6efd; padding: 4px 12px; border-radius: 50px; font-weight: 600; font-size: 0.7rem;">
                                                    <i class="fas fa-user me-1"></i> Usuario
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="?url=usuarios&action=ver&id=<?= $usuario['id_usuario'] ?>" 
                                                   class="btn-action-circle btn-view" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?url=usuarios&action=editar&id=<?= $usuario['id_usuario'] ?>" 
                                                   class="btn-action-circle btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="?url=usuarios" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                                                    <button type="submit" class="btn-action-circle btn-delete"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                                            <?= ($usuario['id_usuario'] ?? 0) == $_SESSION['id_usuario'] ? 'disabled' : '' ?>>
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 dark-empty">
                                        <div class="py-4">
                                            <i class="fas fa-users fa-3x d-block mb-3" style="opacity: 0.3;"></i>
                                            <p class="mb-0">No hay usuarios registrados</p>
                                            <small>Comienza registrando un nuevo usuario</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- ✅ PAGINACIÓN: TOTAL IZQ + BOTONES DER -->
                <div class="card-footer py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-users me-1"></i> 
                        Total: <?= $totalRegistros ?> usuarios
                    </span>
                    <?php require_once __DIR__ . '/../partials/por_pagina_selector.php'; ?>
                </div>
            </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>