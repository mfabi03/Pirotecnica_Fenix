<?php
// lista para mostrar la tabla con todos los Proveedores 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Proveedores - Pirotecnia Fénix</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light"> 

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-11"> 
            
            <div class="card shadow border-0 rounded-lg">
                
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-3">
                    <h2 class="mb-0 h4 font-weight-bold">🏢 Proveedores Autorizados</h2>
                    <a href="?url=proveedor&type=register" class="btn btn-dark btn-sm font-weight-bold shadow-sm">
                        <i class="bi bi-building-fill-add"></i> + Nuevo Proveedor
                    </a>
                </div>
                
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover align-middle text-center w-100">
                            <thead class="table-secondary">
                                <tr>
                                    <th>RIF</th>
                                    <th>Razón Social</th>
                                    <th>Contacto (Encargado)</th>
                                    <th>Teléfono</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-weight-bold text-secondary">J-40000123-4</td>
                                    <td>Fuegos Artificiales del Centro C.A.</td>
                                    <td>Carlos Mendoza</td>
                                    <td>0412-7654321</td>
                                    <td>
                                        <div class="btn-group gap-1" role="group">
                                            <a href="#" class="btn btn-warning btn-sm text-dark shadow-sm"><i class="bi bi-pencil-square"></i></a>
                                            <a href="#" class="btn btn-danger btn-sm shadow-sm"><i class="bi bi-trash-fill"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                        </table>
                    </div> 
                    
                    <div class="text-start mt-4">
                        <a href="?url=user" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left-short"></i> Menú Principal
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