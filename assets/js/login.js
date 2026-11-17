document.addEventListener('DOMContentLoaded', function() {
            const config = document.getElementById('loginContainer');
            const bloqueado = config.dataset.bloqueado === 'true';
            const segundosRestantes = Number(config.dataset.segundosRestantes);

            if (bloqueado && segundosRestantes > 0) {
                // Bloquear el envío del formulario (incluso con Enter)
                const form = document.getElementById('formLogin');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        return false;
                    });
                }

                // Conteo regresivo
                let tiempo = segundosRestantes;
                const alerta = document.getElementById('alerta-error');
                
                const intervalo = setInterval(() => {
                    tiempo--;
                    if (alerta) {
                        alerta.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> Demasiados intentos fallidos. Intenta de nuevo en ${window.escapeHtml(tiempo)} segundos.`;
                    }
                    if (tiempo <= 0) {
                        clearInterval(intervalo);
                        window.location.reload();
                    }
                }, 1000);
            }
        });
