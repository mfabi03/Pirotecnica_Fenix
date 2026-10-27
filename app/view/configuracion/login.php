<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pirotecnia Fénix</title>
    
    <link rel="stylesheet" href="../../pirotecnica_fenix/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../pirotecnica_fenix/assets/css/estiloInicio.css">
    <link rel="stylesheet" href="../../pirotecnica_fenix/assets/css/fuegos-artificiales.css?v=transparent-fireworks-1">
</head>
<body data-login-error="<?= isset($_GET['error']) ? 'true' : 'false' ?>">
    <img src="../../pirotecnica_fenix/assets/imagenes/fondo.jpg" 
         alt="Fondo" 
         class="bg-image">
    <div class="overlay"></div>
    <div class="login-container" id="loginContainer">
        <div class="login-card">
            <div class="login-header">
                <span class="logo-icon"></span>
                <h2><span>Pirotécnica Fénix</span> Inicio de Sesión</h2>
            </div>
            <?php if (isset($_GET['error'])): ?>
                <?php 
                    $errorMsg = '';
                    $errorType = $_GET['error'];
                    switch ($errorType) {
                        case '1':
                            $errorMsg = 'Usuario o contraseña incorrectos.';
                            break;
                        case 'empty':
                            $errorMsg = 'Por favor, complete todos los campos.';
                            break;
                        case 'session':
                            $errorMsg = 'Tu sesión ha expirado. Inicia sesión nuevamente.';
                            break;
                        default:
                            $errorMsg = 'Error al iniciar sesión.';
                    }
                ?>
                <div class="alert alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errorMsg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" 
                            style="filter: invert(1); opacity: 0.5;"></button>
                </div>
            <?php endif; ?>
            <form action="?url=login" method="POST">
                <div class="form-group">
                    <input type="text" class="form-control" 
                           id="usuario" 
                           name="usuario" 
                           placeholder="Ingresa tu usuario"
                           value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                           required>
                    <span class="input-icon">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" 
                           id="clave" 
                           name="clave" 
                           placeholder="Ingresa tu contraseña"
                           required>
                    <span class="input-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                </div><button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
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
    <script src="https://cdn.jsdelivr.net/npm/@tsparticles/fireworks@4.4.0/tsparticles.fireworks.bundle.min.js" defer></script>
    <script src="../../pirotecnica_fenix/assets/js/fuegos-artificiales.js?v=transparent-fireworks-2" defer></script>
</body>
</html>