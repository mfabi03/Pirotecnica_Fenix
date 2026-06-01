<?php
// lista para mostrar la tabla con todos los usuarios registrados (base_datos + Bootstrap)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios - Pirotecnia Fénix</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light"> 

<div class="container mt-5 mb-5">
<div class="row justify-content-center">
<div class="col-md-11"> 
            
<div class="card shadow border-0 rounded-lg">
                
<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
    <h2 class="mb-0 h4 font-weight-bold">👥 Listado General de Usuarios</h2>
        <a href="?url=user&type=register" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                <i class="bi bi-person-plus-fill"></i> + Nuevo Usuario
              </a>
                </div>
                
<div class="card-body p-4">
                    
<div class="table-responsive">
    <table id="userTable" class="table table-striped table-bordered table-hover align-middle text-center w-100">
         <thead class="table-secondary">
                <tr>
                    <th style="width: 15%;">Cédula / Id</th>
                    <th style="width: 25%;">Nombre</th>
                    <th style="width: 25%;">Apellido</th>
                    <th style="width: 20%;">Correo</th>
                    <th style="width: 15%;">Acciones</th>
                </tr>
        </thead>
        
<tbody>
                <tr>
                    <td class="font-weight-bold text-secondary">26123456</td>
                    <td>Juan Carlos</td>
                    <td>Pérez Mendoza</td>
                    <td>juan.perez@email.com</td>
                <td>
<div class="btn-group gap-1" role="group">
    <a href="#" class="btn btn-warning btn-sm text-dark shadow-sm" title="Editar">
        <i class="bi bi-pencil-square"></i>
     </a>
<a href="#" class="btn btn-danger btn-sm shadow-sm" title="Eliminar">
        <i class="bi bi-trash-fill"></i>
    </a>
</div>
     </td>
         </tr>
            </tbody>
                </table>
                    </div> 
                    
                    <div class="text-start mt-4">
                        <a href="?url=user" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left-short"></i> Volver al Menú Principal
                        </a>
                    </div>

                </div> 
            </div> 
            
        </div>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
