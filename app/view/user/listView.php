<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de Usuarios</title>
  <link rel="stylesheet" href="../Practica_CRUD/assets/css/user.css">
</head>
<body>

  <div class="container">
    <h1>Lista de Usuarios</h1>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Correo</th>
        </tr>
      </thead>
      <tbody>
        <!-- Se realiza un bucle (foreach) para mostrar los usuarios -->
      <?php foreach($result as $users){?>
        <tr>
          <td><?php echo $users["id"]; ?></td>
          <td><?php echo $users["nombre"]; ?></td>
          <td><?php echo $users["apellido"]; ?></td>
          <td><?php echo $users["correo"]; ?></td>
        </tr>
        <?php }?>
        <!-- Se cierra el ciclo -->
      </tbody>
    </table>

    <div class="button-container">
      <a href="?url=user" class="btn">Menu Principal</a>
    </div>
  </div>

</body>
</html>

