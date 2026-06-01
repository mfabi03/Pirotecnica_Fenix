
<?php
// Formulario de Registro de Usuarios del Sistema ,PERSONA y USUARIO, con validaciones de patrones para cada campo y mensajes de error personalizados.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body>

<div class="form-container">
    <h2>Registro de Usuario</h2>
    <form method="post" class="form-box">

    //cambo de cedula , nombre , apellido y correo con validaciones de patrones para cada campo y mensajes de error personalizados.
       
    <div class="form-group">
            <label for="cedula">Cédula</label>
            <input type="text" id="cedula" pattern="[0-9]{5,15}" title="Solo números. Mínimo 5 y máximo 15 dígitos" name="cedula"  placeholder="12345678" required>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" pattern="[A-Za-z]{3,30}" title="Solo letras. Entre 2 y 30 caracteres" name="nombre" placeholder="Pedro" required>
        </div>

        <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" pattern="[A-Za-z]{3,30}" title="Solo letras. Entre 2 y 30 caracteres" name="apellido" placeholder="Perez" required>
        </div>

        <div class="form-group">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" placeholder="ejemplo@correo.com" required title="Debe tener el formato correcto: ejemplo@dominio.com">
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn primary">Registrar Usuario</button>
            <a href="?url=user" class="btn secondary"> Volver alMenú Principal</a>
        </div>
    </form>
</div>

<?php if(isset($result)) {echo $result;} else{echo "";}?>
</body>
</html>

