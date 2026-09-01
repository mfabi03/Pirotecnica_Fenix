<?php
// Partial: por_pagina_selector.php
// Este partial renderiza el selector "por página" y una paginación reutilizable.
// Variables opcionales que puede usar el partial: $por_pagina, $pagina_actual, $totalRegistros, $totalPaginas

// Valores por defecto desde GET
$por_pagina = isset($por_pagina) && (int)$por_pagina > 0 ? (int)$por_pagina : (int)($_GET['por_pagina'] ?? 10);
$pagina_actual = isset($pagina_actual) && (int)$pagina_actual > 0 ? (int)$pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalRegistros = isset($totalRegistros) ? (int)$totalRegistros : (int)($_GET['total_registros'] ?? 0);
$totalPaginas = isset($totalPaginas) ? (int)$totalPaginas : ($totalRegistros > 0 ? (int)ceil($totalRegistros / max(1, $por_pagina)) : 1);

$opts = [5,10,20,30,40,50,60,70];
for ($i = 80; $i <= 200; $i += 10) $opts[] = $i;

// Construir función auxiliar para mantener query params
function build_page_url($overrides = []) {
    $q = $_GET;
    foreach ($overrides as $k => $v) {
        $q[$k] = $v;
    }
    return '?' . http_build_query($q);
}

?>
<div class="d-flex align-items-center gap-2">
    <div>
        <select name="por_pagina" class="form-select shadow-none" onchange="this.form.submit()">
            <?php foreach ($opts as $o): ?>
                <option value="<?= $o ?>" <?= ($por_pagina === (int)$o) ? 'selected' : '' ?>>Mostrar <?= $o ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($totalPaginas > 1): ?>
        <nav aria-label="Paginación" class="ms-3">
            <ul class="pagination pagination-sm mb-0">
                <?php
                $prevDisabled = ($pagina_actual <= 1) ? 'disabled' : '';
                $nextDisabled = ($pagina_actual >= $totalPaginas) ? 'disabled' : '';
                $prevPage = max(1, $pagina_actual - 1);
                $nextPage = min($totalPaginas, $pagina_actual + 1);
                ?>
                <li class="page-item <?= $prevDisabled ?>">
                    <a class="page-link" href="<?= build_page_url(['pagina' => $prevPage, 'por_pagina' => $por_pagina]) ?>">Anterior</a>
                </li>

                <?php
                // Ventana de páginas (mostrar un rango alrededor de la página actual)
                $window = 5;
                $start = max(1, $pagina_actual - floor($window/2));
                $end = min($totalPaginas, $start + $window - 1);
                if ($end - $start + 1 < $window) {
                    $start = max(1, $end - $window + 1);
                }

                if ($start > 1):
                ?>
                    <li class="page-item"><a class="page-link" href="<?= build_page_url(['pagina' => 1, 'por_pagina' => $por_pagina]) ?>">1</a></li>
                    <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                        <a class="page-link <?= ($i == $pagina_actual) ? 'bg-dark border-dark text-white' : '' ?>" href="<?= build_page_url(['pagina' => $i, 'por_pagina' => $por_pagina]) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $totalPaginas): ?>
                    <?php if ($end < $totalPaginas - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                    <li class="page-item"><a class="page-link" href="<?= build_page_url(['pagina' => $totalPaginas, 'por_pagina' => $por_pagina]) ?>"><?= $totalPaginas ?></a></li>
                <?php endif; ?>

                <li class="page-item <?= $nextDisabled ?>">
                    <a class="page-link" href="<?= build_page_url(['pagina' => $nextPage, 'por_pagina' => $por_pagina]) ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
