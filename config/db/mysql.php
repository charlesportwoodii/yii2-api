<?php 

$config = require __DIR__ . '/../loader.php';

return [
    'class'                 => 'yii\db\Connection',
    'dsn'                   => 'mysql:host=' . $config['yii2']['database']['host'] . ';dbname=' . $config['yii2']['database']['database'],
    'username'              => $config['yii2']['database']['username'],
    'password'              => $config['yii2']['database']['password'],
    'charset'               => 'utf8',
    'enableSchemaCache'     => !YII_DEBUG,
    'schemaCacheDuration'   => YII_DEBUG ? 0 : 86400,
    'schemaCache'           => 'cache'
];