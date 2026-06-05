<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Si entras en seco a la raíz, te redirige de inmediato a tu módulo
    if (!isset($_GET['url']) && !isset($_POST['url'])) {
        header('Location: ?url=reportes');
        exit();
    }

    use App\Pirotecnicafenix\Config\Connect\ConnectDB;
    require 'vendor/autoload.php';
    
    $conexion = new connectDB();
    $pdo = $conexion->getConnection();

    if ($pdo) {
        echo "Conexión exitosa a la base de datos.";
    }

    else {
        echo "Error al conectar a la base de datos.";
    }
    

    $partesRuta = isset($_GET["ruta"]) ? $_GET["ruta"] : "main";
    
    $ruta = explode("/", $partesRuta);
    $paginaActual = $ruta[0];
    
    include __DIR__."/app/view/header.php";
    switch ($paginaActual) {
        case "login":
            include __DIR__."/app/view/configuracion/login.php";
            break;
        case "home":
            include __DIR__."/app/view/configuracion/main.php";
            break;
        case "registro":
            include __DIR__."/app/view/configuracion/registro.php";
            break;
        default: 
            include __DIR__."/app/view/configuracion/main.php";
    }
?>
