<?php

$config = require __DIR__ . '/loader.php';
$params = [
    'yii.migrations' => [
        "@restcomponents/api/migrations",
    ]
];

if (isset($config['params'])) {
    $params = array_merge($params, $config['params']);
}


return $params;