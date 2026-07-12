<?php
// CAMBIO: Ajuste de enlace en menú - Footer centralizado
?>
            </div> <!-- Cierre p-3 -->
        </div> <!-- Cierre col-md-9 col-lg-10 -->
    </div> <!-- Cierre row -->
</div> <!-- Cierre container-fluid -->

<!-- ==========================================
FOOTER - VERSIÓN PEQUEÑA
========================================== -->
<footer class="footer-fenix py-0" style="background: radial-gradient(circle at center, #141423 0%, #08080f 100%); border-top: 1px solid rgba(212, 175, 55, 0.1);">
    <div class="container text-center">
        <div style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 4px 0;">
            <span style="color: #d4af37; font-size: 0.55rem; opacity: 0.5;">✦</span>
            <span style="color: #d4af37; letter-spacing: 1.5px; font-family: 'Cinzel', 'Times New Roman', serif; font-size: 0.65rem; font-weight: 700;">
                PIROTECNICA FÉNIX
            </span>
            <span style="color: #d4af37; font-size: 0.55rem; opacity: 0.5;">✦</span>
            <span style="color: rgba(255,255,255,0.06);">|</span>
            <span style="color: #e5c158; font-size: 0.5rem; opacity: 0.4;">&copy; <?= date('Y') ?></span>
        </div>
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