<?php
// Script simple de debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config/connect/ConnectDB.php';

use App\Pirotecnicafenix\Config\Connect\ConnectDB;

session_start();

$db = (new ConnectDB())->getConnection();

$id_usuario = (int)($_SESSION['id_usuario'] ?? 0);
$fecha = date('Y-m-d'); // HOY

echo "ID Usuario en sesión: $id_usuario<br>";
echo "Fecha: $fecha<br><br>";

// Consulta directa a la base de datos
$sql = "SELECT 
            ns.id_nota_salida AS id_documento,
            ns.fecha,
            DATE(ns.fecha) as fecha_solo,
            ns.id_usuario,
            SUM(ds.cantidad) as cantidad
        FROM nota_de_salida ns
        INNER JOIN detalle_salida ds ON ns.id_nota_salida = ds.id_nota_salida
        WHERE ns.eliminado = 0 
        AND ns.id_usuario = :id_usuario
        AND DATE(ns.fecha) = :fecha
        GROUP BY ns.id_nota_salida, ns.fecha, ns.id_usuario
        ORDER BY ns.fecha DESC
        LIMIT 10";

$stmt = $db->prepare($sql);
$stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt->bindValue(':fecha', $fecha);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Resultados encontrados: " . count($results) . "<br><br>";

if (empty($results)) {
    // Verificar si hay notas en la BD
    echo "--- Notas existentes en BD ---<br>";
    $stmt2 = $db->query("SELECT id_nota_salida, fecha, id_usuario FROM nota_de_salida ORDER BY id_nota_salida DESC LIMIT 10");
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id_nota_salida'] . " | Fecha: " . $row['fecha'] . " | Usuario: " . $row['id_usuario'] . "<br>";
    }
} else {
    foreach ($results as $r) {
        echo "ID: " . $r['id_documento'] . " | Fecha: " . $r['fecha'] . " | Cantidad: " . $r['cantidad'] . "<br>";
    }
}
?>