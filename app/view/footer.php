<?php
// app/view/footer.php
require_once dirname(__DIR__) . '/config/timezone.php';
?>
            </div> <!-- Cierre content-wrapper -->
        </div> <!-- Cierre col-lg-10 col-12 -->
    </div> <!-- Cierre row align-items-stretch -->
</div> <!-- Cierre container-fluid -->

<!-- ==========================================
FOOTER
========================================== -->
<footer class="footer-fenix py-0" style="background: radial-gradient(circle at center, #141423 0%, #08080f 100%); border-top: 1px solid rgba(212, 175, 55, 0.1);">
    <div class="container text-center">
        <div style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 4px 0;">
            <span style="color: #d4af37; font-size: 0.55rem; opacity: 0.5;">✦</span>
            <span class="fenix-wordmark" style="color: #d4af37;">PIROTECNICA FÉNIX</span>
            <span style="color: #d4af37; font-size: 0.55rem; opacity: 0.5;">✦</span>
            <span style="color: rgba(255,255,255,0.06);">|</span>
            <span style="color: #e5c158; font-size: 0.5rem; opacity: 0.4;">&copy; <?= htmlspecialchars((string) (anioActualVenezuela()), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
        </div>
    </div>
</footer>

<!-- CONTENEDOR DE TOASTS -->
<div id="toast-container" class="toast-container position-fixed top-50 start-50 translate-middle p-3" style="z-index: 9999;"></div>

<!-- ==========================================
SCRIPTS GLOBALES - ORDEN CRÍTICO
========================================== -->

<!-- 1. jQuery PRIMERO (obligatorio) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- 2. Bootstrap DESPUÉS de jQuery (obligatorio para collapse, modals, dropdowns) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. Validador global -->
<script src="/pirotecnica_fenix/assets/js/validaciones.jquery.js"></script>

<!-- 4. Búsqueda en selects -->
<script src="/pirotecnica_fenix/assets/js/select-search.js"></script>

<!-- 5. Scripts del módulo (los declara cada vista con $scriptsModulo) -->
<?php if (!empty($scriptsModulo) && is_array($scriptsModulo)): ?>
    <?php foreach ($scriptsModulo as $script): ?>
        <script src="/pirotecnica_fenix/assets/js/<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- 6. Toasts / alertas / confirmación global -->
<script src="/pirotecnica_fenix/assets/js/global.js?v=<?= htmlspecialchars((string) (filemtime(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'global.js')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>


</body>
</html>