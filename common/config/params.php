<?php

$config = include __DIR__ . '/loader.php';
$params = [
    'yii.migrations' => [
        '@restcomponents/migrations',
    ]
];

if (isset($config['params'])) {
    $params = array_merge($params, $config['params']);
}

return $params;
