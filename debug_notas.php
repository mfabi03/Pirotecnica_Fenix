<?php
// Script de debug para verificar las notas de salida
session_start();

require_once 'app/config/connect/ConnectDB.php';

$db = (new App\Pirotecnicafenix\Config\Connect\ConnectDB())->getConnection();

echo "=== DEBUG NOTAS DE SALIDA ===<br><br>";

echo "Sesión ID usuario: " . ($_SESSION['id_usuario'] ?? 'NO DEFINIDO') . "<br><br>";

// Ver todas las notas de salida
echo "--- TODAS LAS NOTAS DE SALIDA ---<br>";
$stmt = $db->query("SELECT id_nota_salida, fecha, id_usuario, eliminado FROM nota_de_salida ORDER BY id_nota_salida DESC LIMIT 20");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id_nota_salida'] . " | Fecha: " . $row['fecha'] . " | ID Usuario: " . $row['id_usuario'] . " | Eliminado: " . $row['eliminado'] . "<br>";
}

echo "<br>--- NOTAS DE HOY (usuario específico) ---<br>";
$id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
$fecha_hoy = date('Y-m-d');

if ($id_usuario > 0) {
    $stmt = $db->prepare("SELECT id_nota_salida, fecha, id_usuario FROM nota_de_salida WHERE DATE(fecha) = ? AND id_usuario = ? ORDER BY id_nota_salida DESC");
    $stmt->execute([$fecha_hoy, $id_usuario]);
    $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($notas)) {
        echo "No hay notas para hoy del usuario $id_usuario<br>";
    } else {
        foreach ($notas as $n) {
            echo "ID: " . $n['id_nota_salida'] . " | Fecha: " . $n['fecha'] . " | ID Usuario: " . $n['id_usuario'] . "<br>";
        }
    }
} else {
    echo "ERROR: No hay usuario en sesión (ID = 0)<br>";
}

echo "<br>--- NOTAS DE HOY (todas) ---<br>";
$stmt = $db->prepare("SELECT id_nota_salida, fecha, id_usuario FROM nota_de_salida WHERE DATE(fecha) = ? ORDER BY id_nota_salida DESC");
$stmt->execute([$fecha_hoy]);
$notas_hoy = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($notas_hoy)) {
    echo "No hay notas de hoy en la BD<br>";
} else {
    foreach ($notas_hoy as $n) {
        echo "ID: " . $n['id_nota_salida'] . " | Fecha: " . $n['fecha'] . " | ID Usuario: " . $n['id_usuario'] . "<br>";
    }
}
?>