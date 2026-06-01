<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lista de Usuarios</title>
  <link rel="stylesheet" href="../Practica_CRUD/assets/css/user.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="..\Practica_CRUD\assets\css\DataTables\css\jquery.dataTables.min.css">
  <link rel="stylesheet" href="..\Practica_CRUD\assets\css\DataTables\css\dataTables.bootstrap5.min.css">
</head>
<body>

  <div class="container">
    <h1>Lista de Usuarios</h1>
    <table id="userTable">
      <thead>
        <tr style="margin-top: 10%;">
            <th class="tabla">Id</th>
            <th class="tabla">Nombre</th>
            <th class="tabla">Apellido</th>
            <th class="tabla">Correo</th>
            <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>

    <div class="button-container">
      <a href="?url=user" class="btn-user">Menu Principal</a>
    </div>
  </div>
  <!--JQUERY-->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <!--Archivo-->
  <script type='module' src="..\Practica_CRUD\assets\js\user.js"></script>
  <script type='module' src="..\Practica_CRUD\assets\js\prueba.js"></script>
  <!--DataTables-->
  <script src="../Practica_CRUD/assets/js/DataTables/datatables.min.js"></script>
  <script type="text/javascript" src="../Practica_CRUD/assets/js/DataTables/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>