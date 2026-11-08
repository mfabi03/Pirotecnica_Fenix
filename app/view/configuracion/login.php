<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pirotecnia Fénix</title>
    
    <link rel="stylesheet" href="../../pirotecnica_fenix/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../pirotecnica_fenix/assets/css/estiloInicio.css">
    <link rel="stylesheet" href="../../pirotecnica_fenix/assets/css/fuegos-artificiales.css?v=fullscreen-fireworks-2">
    
    <style>
        /* 🔥 REGLA DE ORO: Ignorar el CSS de la plantilla si está deshabilitado */
        .form-control:disabled, .form-control[disabled] {
            background-color: #2b2b36 !important;
            color: #888 !important;
            border-color: #444 !important;
            cursor: not-allowed !important;
            opacity: 0.7 !important;
        }
        .btn-login:disabled, .btn-login[disabled] {
            background: #444 !important;
            color: #888 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body>
    <img src="../../pirotecnica_fenix/assets/imagenes/fondo.jpg" 
         alt="Fondo" 
         class="bg-image">
    <div class="overlay"></div>
    <div id="tsparticles" class="fireworks-overlay" aria-hidden="true"></div>
    <div class="login-container" id="loginContainer">
        <div class="login-card">
            <div class="login-header">
                <span class="logo-icon"></span>
                <h2><span>Pirotécnica Fénix</span> Inicio de Sesión</h2>
            </div>
            
            <!-- BLOQUE DE ERROR DINÁMICO -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-dismissible fade show" role="alert" style="background-color: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); color: #dc3545; border-radius: 10px; margin-bottom: 20px;" id="alerta-error">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" 
                            style="filter: invert(1); opacity: 0.5;"></button>
                </div>
            <?php endif; ?>

            <form action="?url=login" method="POST" id="formLogin">
                <div class="form-group">
                    <input type="text" class="form-control" 
                           id="usuario" 
                           name="usuario" 
                           placeholder="Ingresa tu usuario"
                           value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                           <?= (isset($bloqueado) && $bloqueado) ? 'disabled' : 'required' ?>>
                    <span class="input-icon">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" 
                           id="clave" 
                           name="clave" 
                           placeholder="Ingresa tu contraseña"
                           <?= (isset($bloqueado) && $bloqueado) ? 'disabled' : 'required' ?>>
                    <span class="input-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>
                
                <button type="submit" class="btn-login" id="btnLogin"
                        <?= (isset($bloqueado) && $bloqueado) ? 'disabled' : '' ?>>
                    <i class="fas fa-sign-in-alt"></i> 
                    <?= (isset($bloqueado) && $bloqueado) ? 'Bloqueado' : 'Iniciar Sesión' ?>
                </button>
            </form>
            
            <div class="divider">
                <span class="line"></span>
                <span class="text">Sistema seguro</span>
                <span class="line"></span>
            </div>
            <div class="login-footer">
                <i class="fas fa-shield-alt"></i> v1.0.0
            </div>
        </div>
    </div>

    <script src="../../pirotecnica_fenix/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tsparticles/preset-fireworks@4/tsparticles.preset.fireworks.bundle.min.js" defer></script>
    <script src="../../pirotecnica_fenix/assets/js/fuegos-artificiales.js?v=fullscreen-fireworks-2" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bloqueado = <?= (isset($bloqueado) && $bloqueado) ? 'true' : 'false' ?>;
            const segundosRestantes = <?= isset($segundos_restantes) ? (int)$segundos_restantes : 0 ?>;

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
                        alerta.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> Demasiados intentos fallidos. Intenta de nuevo en ${tiempo} segundos.`;
                    }
                    if (tiempo <= 0) {
                        clearInterval(intervalo);
                        window.location.reload();
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>