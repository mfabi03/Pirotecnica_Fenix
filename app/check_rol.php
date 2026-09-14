<?php
require __DIR__ . '/Model/RolModel.php';
echo 'file_exists: ' . (file_exists(__DIR__ . '/Model/RolModel.php') ? 'yes' : 'no') . PHP_EOL;
var_dump(class_exists('App\\Pirotecnicafenix\\Model\\RolModel'));
echo "Declared classes containing 'RolModel':\n";
foreach (get_declared_classes() as $c) {
    if (stripos($c, 'rolmodel') !== false) {
        echo $c . PHP_EOL;
    }
}
