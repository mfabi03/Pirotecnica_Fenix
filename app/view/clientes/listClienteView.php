<?php
require_once dirname(__DIR__, 2) . "/view/header.php"; 
?>

<div class="col-md-8 col-lg-12">
    <div class="card shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="mb-1 fw-bold"><i class="fas fa-users me-2"></i> Clientes</h4>
                <p class="text-muted mb-0">Lista de clientes naturales y jurídicos.</p>
            </div>
            <a href="?url=clientes&type=register" class="btn btn-gold btn-sm fw-bold">
                <i class="fas fa-user-plus me-1"></i> Nuevo Cliente
            </a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?> alert-custom">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="GET" action="/Pirotecnica_Fenix/index.php" class="row g-3 mb-4">
            <input type="hidden" name="url" value="clientes">
            <input type="hidden" name="type" value="list">
            <div class="col-md-5">
                <input type="text" name="busqueda" class="form-control" 
                       placeholder="Buscar por nombre, cédula, teléfono..." 
                       value="<?= htmlspecialchars($busqueda ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="tipo" class="form-select">
                    <option value="todos" <?= ($tipo ?? '') === 'todos' ? 'selected' : '' ?>>Todos los tipos</option>
                    <option value="Natural" <?= ($tipo ?? '') === 'Natural' ? 'selected' : '' ?>>Natural</option>
                    <option value="Jurídico" <?= ($tipo ?? '') === 'Jurídico' ? 'selected' : '' ?>>Jurídico</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-gold w-100">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
            <div class="col-md-2">
                <a href="?url=clientes&type=list" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-fenix table-hover m-0">
                <thead>
                    <tr>
                        <th class="ps-4">Cédula / RIF</th>
                        <th>Nombre / Razón Social</th>
                        <th>Tipo</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-database mb-2" style="font-size: 2rem; display: block;"></i>
                                No hay clientes registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td class="ps-4"><?= htmlspecialchars($c['cedula'] ?? $c['rif'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (($c['tipo_cliente'] ?? '') === 'Jurídico'): ?>
                                        <strong><?= htmlspecialchars($c['razon_social'] ?? 'N/A') ?></strong>
                                    <?php else: ?>
                                        <?= htmlspecialchars(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')) ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= ($c['tipo_cliente'] ?? '') === 'Jurídico' ? 'bg-primary' : 'bg-success' ?>"><?= htmlspecialchars($c['tipo_cliente'] ?? 'N/A') ?></span></td>
                                <td><?= htmlspecialchars($c['telefono'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($c['correo_electronico'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="?url=clientes&type=view&id=<?= $c['id_cliente'] ?? 0 ?>" 
                                           class="btn btn-outline-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?url=clientes&type=<?= ($c['tipo_cliente'] ?? '') === 'Jurídico' ? 'edit_juridico' : 'edit' ?>&id=<?= $c['id_cliente'] ?? 0 ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                            <form method="POST" action="?url=clientes&type=delete" style="display:inline;" 
                                              onsubmit="return confirm('¿Eliminar este cliente?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_cliente" value="<?= $c['id_cliente'] ?? 0 ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Búsqueda en tiempo real con debounce y envío por tipo/Enter
    (function(){
        const input = document.querySelector('input[name="busqueda"]');
        const form = input ? input.closest('form') : null;
        const tipoSelect = form ? form.querySelector('select[name="tipo"]') : null;
        if (!input || !form) return;

        let timeout = null;
        function submitFormDelayed() {
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                if (input.value.trim() === '') {
                    window.location.href = '?url=clientes&type=list';
                    return;
                }
                form.submit();
            }, 800);
        }

        input.addEventListener('input', submitFormDelayed);
        input.addEventListener('keydown', function(e){
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timeout);
                form.submit();
            }
        });

        if (tipoSelect) {
            tipoSelect.addEventListener('change', function(){
                form.submit();
            });
        }
    })();
</script>

<?php require_once dirname(__DIR__, 2) . "/view/footer.php"; ?>