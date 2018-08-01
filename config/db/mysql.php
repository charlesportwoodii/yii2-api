<?php

$config = include __DIR__ . '/../loader.php';

return [
    'class'                 => 'yii\db\Connection',
    'dsn'                   => 'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['database'],
    'username'              => $config['database']['username'],
    'password'              => $config['database']['password'],
    'charset'               => 'utf8',
    'enableSchemaCache'     => !YII_DEBUG,
    'schemaCacheDuration'   => YII_DEBUG ? 0 : 86400,
    'schemaCache'           => 'cache'
];
