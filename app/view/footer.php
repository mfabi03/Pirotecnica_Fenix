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
<footer class="footer-fenix text-center py-3">
    <div class="container">
        <p class="mb-0">
            <span class="text-gold fw-bold">✦</span> 
            <span class="text-gold fw-bold">Pirotecnia Fénix</span> 
            <span class="text-gold fw-bold">✦</span>
            <span class="text-muted ms-2 small">
                &copy; <?= date('Y') ?> - Todos los derechos reservados.
            </span>
        </p>
    </div>
</footer>

<!-- ==========================================
SCRIPTS GLOBALES
========================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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