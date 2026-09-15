<?php
// Partial: por_pagina_selector.php
// SOLO botones ◀ ▶ (sin números)

$por_pagina    = isset($por_pagina) && (int)$por_pagina > 0 ? (int)$por_pagina : (int)($_GET['por_pagina'] ?? 10);
$pagina_actual = isset($pagina_actual) && (int)$pagina_actual > 0 ? (int)$pagina_actual : (int)($_GET['pagina'] ?? 1);
$totalRegistros= isset($totalRegistros) ? (int)$totalRegistros : 0;
$totalPaginas  = isset($totalPaginas) ? (int)$totalPaginas : ($totalRegistros > 0 ? (int)ceil($totalRegistros / max(1, $por_pagina)) : 1);

function build_page_url($overrides = []) {
    $q = $_GET;
    foreach ($overrides as $k => $v) {
        $q[$k] = $v;
    }
    return '?' . http_build_query($q);
}
?>

<?php if ($totalPaginas > 1): ?>
    <nav aria-label="Paginación">
        <ul class="pagination pagination-sm mb-0">
            <?php
            $prevDisabled = ($pagina_actual <= 1) ? 'disabled' : '';
            $nextDisabled = ($pagina_actual >= $totalPaginas) ? 'disabled' : '';
            $prevPage = max(1, $pagina_actual - 1);
            $nextPage = min($totalPaginas, $pagina_actual + 1);
            ?>
            <li class="page-item <?= $prevDisabled ?>">
                <a class="page-link" href="<?= build_page_url(['pagina' => $prevPage, 'por_pagina' => $por_pagina]) ?>" aria-label="Anterior">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            <li class="page-item disabled">
                <span class="page-link bg-white text-dark border-0">
                    <?= $pagina_actual ?> / <?= $totalPaginas ?>
                </span>
            </li>
            <li class="page-item <?= $nextDisabled ?>">
                <a class="page-link" href="<?= build_page_url(['pagina' => $nextPage, 'por_pagina' => $por_pagina]) ?>" aria-label="Siguiente">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>