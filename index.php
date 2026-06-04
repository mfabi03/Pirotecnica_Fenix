<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require 'vendor/autoload.php';

    $partesRuta = isset($_GET["ruta"]) ? $_GET["ruta"] : "main";
    
    $ruta = explode("/", $partesRuta);
    $paginaActual = $ruta[0];
    
    include __DIR__."/app/view/header.php";
    switch ($paginaActual) {
        case "login":
            include __DIR__."/app/view/clientes/listClienteView.php";
            break;
        case "home":
            include __DIR__."/app/view/configuracion/main.php";
            break;
        case "hola":
            echo "aaaaaaa";
            break;
        default: 
            include __DIR__."/app/view/configuracion/main.php";
    }
?>
