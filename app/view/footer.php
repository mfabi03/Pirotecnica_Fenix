<?php
// CAMBIO: Ajuste de enlace en menú - Footer centralizado
?>
            </div> <!-- Cierre p-3 -->
        </div> <!-- Cierre col-md-9 col-lg-10 -->
    </div> <!-- Cierre row -->
</div> <!-- Cierre container-fluid -->

<!-- ==========================================
FOOTER
========================================== -->
<footer class="footer-fenix py-2" style="background: radial-gradient(circle at center, #141423 0%, #08080f 100%); border-top: 1px solid rgba(212, 175, 55, 0.25);">
    <div class="container text-center">
        <h3 class="fw-bold mb-2" style="color: #d4af37; letter-spacing: 3px; font-family: 'Cinzel', 'Times New Roman', serif; font-size: 1.6rem;">
            <span style="opacity: 0.7; font-size: 1.2rem; vertical-align: middle;" class="me-2">✦</span>
            PIROTECNICA FÉNIX
            <span style="opacity: 0.7; font-size: 1.2rem; vertical-align: middle;" class="ms-2">✦</span>
        </h3>

        <p class="mb-0" style="color: #e5c158; letter-spacing: 1px; font-size: 0.85rem; opacity: 0.85;">
            &copy; <?= date('Y') ?> &middot; Todos los derechos reservados
        </p>
    </div>
</footer>

<!-- ==========================================
SCRIPTS GLOBALES
========================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar dropdowns en caso de que la API de datos no se haya activado automáticamente.
        const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdownToggles.forEach(function(toggle) {
            if (!bootstrap.Dropdown.getInstance(toggle)) {
                new bootstrap.Dropdown(toggle);
            }
        });

        // Auto-cierre de alertas
        const alertElement = document.querySelector('.alert');
        if (alertElement) {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getInstance(alertElement);
                if (bsAlert) bsAlert.close();
            }, 5000);
        }
    });

    // Funciones globales
    window.confirmarEliminacion = function(mensaje) {
        return confirm(mensaje || '¿Estás seguro de eliminar este registro?');
    };

    window.formatearCedula = function(input) {
        let value = input.value.trim().toUpperCase();
        if (/^\d+$/.test(value)) {
            input.value = 'V-' + value;
        } else if (/^V\d+$/.test(value)) {
            input.value = 'V-' + value.substring(1);
        }
    };

    window.formatearRif = function(input) {
        let value = input.value.trim().toUpperCase();
        if (/^\d+$/.test(value)) {
            input.value = 'J-' + value;
        } else if (/^J\d+$/.test(value)) {
            input.value = 'J-' + value.substring(1);
        }
    };
</script>

</body>
</html>