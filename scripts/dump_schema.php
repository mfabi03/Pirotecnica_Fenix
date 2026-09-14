<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Pirotecnicafenix\Config\Connect\ConnectDB;
try {
    $db = (new ConnectDB())->getConnection();
    $out = [];
    foreach (['usuario','persona'] as $table) {
        $rows = [];
        $stmt = $db->query("SHOW COLUMNS FROM `".$table."`");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $rows[] = $r;
        }
        $out[$table] = $rows;
        file_put_contents(__DIR__ . "/schema_{$table}.json", json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    file_put_contents(__DIR__ . '/schema_all.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "WROTE schema files\n";
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/schema_error.txt', $e->getMessage());
    echo "ERROR: wrote schema_error.txt\n";
}
