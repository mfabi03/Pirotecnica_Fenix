<?php
require __DIR__ . '/../vendor/autoload.php';

session_start();

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\ProveedoresModel;

$db = (new ConnectDB())->getConnection();
$model = new ProveedoresModel($db);

$rif = 'J-' . time() . '-TEST';
$datos = [
    'rif' => $rif,
    'razon_social' => 'Proveedor QA ' . time(),
    'numero_contacto' => '0412-0000000',
    'direccion' => 'Dir. prueba QA',
    'correo_electronico' => 'qa' . time() . '@test.com',
    'nombre_contacto' => 'QA',
    'apellido_contacto' => 'Test'
];

$id = $model->registrarProveedor($datos);
$id = (int) $id;

echo 'ID_PROVEEDOR=' . $id . PHP_EOL;
echo 'REDIRECT=' . http_build_query([
    'url' => 'notaentrada',
    'type' => 'create',
    'id_proveedor' => $id,
]) . PHP_EOL;

$stmt = $db->prepare('SELECT id_persona FROM proveedor WHERE id_proveedor = :id');
$stmt->execute([':id' => $id]);
$personaId = $stmt->fetchColumn();

if ($personaId) {
    $db->prepare('DELETE FROM proveedor WHERE id_proveedor = :id')->execute([':id' => $id]);
    $db->prepare('DELETE FROM persona WHERE id_persona = :id')->execute([':id' => $personaId]);
    echo 'CLEANUP=OK' . PHP_EOL;
}
