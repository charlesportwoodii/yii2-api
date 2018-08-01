<?php

$config = include __DIR__ . '/../loader.php';

return [
    'class'                 => 'yii\db\Connection',
    'dsn'                   => 'sqlite:/' . __DIR__ . '/../../runtime/db.sqlite',
    'charset'               => 'utf8',
    'enableSchemaCache'     => !YII_DEBUG,
    'schemaCacheDuration'   => YII_DEBUG ? 0 : 86400,
    'schemaCache'           => 'cache'
];
