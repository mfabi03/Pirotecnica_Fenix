<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Pirotecnicafenix\Config\Connect\ConnectDB;

try {
    $conn = (new ConnectDB())->getConnection();
    $rows = $conn->query('SELECT id_rol, nombre_rol FROM rol ORDER BY id_rol')->fetchAll(PDO::FETCH_ASSOC);
    $out = "ROL_TABLE_RESULTS\n";
    foreach ($rows as $row) {
        $out .= $row['id_rol'] . ':' . $row['nombre_rol'] . PHP_EOL;
    }
    file_put_contents(__DIR__ . '/rol_table_output.txt', $out);
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/rol_table_output.txt', 'ERROR: ' . $e->getMessage());
}
