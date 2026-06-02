<?php

// Evaluamos el tipo de vista que se solicita, igualito a como hace el UserController de tus compañeros
if (isset($_GET['type'])) {

    // Vista 1: Listado general de reportes e integridad
    if ($_GET['type'] == 'list') {
        include 'app/view/reportes/reportesView.php';
    } 
    
    // Vista 2: Por si acaso
    elseif ($_GET['type'] == 'register') {
        include 'app/view/reportes/registerView.php';
    }
    
    // Si pasan cualquier otra cosa
    else {
        include 'app/view/reportes/reportesView.php';
    }

} else {
    // Si entras directo a ?url=reportes sin poner tipo, cargamos tu vista por defecto
    include 'app/view/reportes/reportesView.php';
}

?>