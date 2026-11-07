<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Pirotecnicafenix\Config\Connect\ConnectDB;

try {
    $db = (new ConnectDB())->getConnection();
    $sql = "SELECT u.id_usuario, p.nombre, p.apellido, p.cedula, p.telefono, p.correo_electronico, u.id_rol, r.nombre_rol AS rol, u.fecha_registro
            FROM usuario u
            INNER JOIN persona p ON u.id_usuario = p.id_usuario
            LEFT JOIN rol r ON u.id_rol = r.id_rol
            LIMIT 10";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $outPath = __DIR__ . '/test_query_output.json';
    file_put_contents($outPath, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "WROTE: " . $outPath . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
