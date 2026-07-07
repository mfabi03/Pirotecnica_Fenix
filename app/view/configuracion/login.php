<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pirotecnia Fénix</title>
    
    <!-- ✅ RUTA RELATIVA: Bootstrap CSS -->
    <link rel="stylesheet" href="../../pirotecnica_fenix/assets/bootstrap/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body class="bg-light">
    <div class="container-fluid min-vh-100 d-flex justify-content-center align-items-center bg-light">
        <div class="card p-4 shadow" style="width: 350px;">
            <h3 class="text-center"> Inicio de Sesión</h3>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php 
                        $msg = $_GET['error'] == '1' ? 'Usuario o contraseña incorrectos.' : 'Error al iniciar sesión.';
                        echo htmlspecialchars($msg);
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- ✅ RUTA RELATIVA: action="?url=login" -->
            <form action="?url=login" method="POST">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" 
                           value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" 
                           placeholder="Ingresa tu usuario" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <!-- ✅ CAMBIADO: name="clave" para que coincida con LoginController -->
                    <input type="password" name="clave" class="form-control" 
                           placeholder="Ingresa tu contraseña" required>
                </div>
                <div class="d-grid gap-3 d-md-block text-center">
                    <button type="submit" class="btn btn-warning btn-lg mx-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Entrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>