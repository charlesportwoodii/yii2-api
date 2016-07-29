<?php

$yaml = require __DIR__ . '/loader.php';

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
Yii::setAlias('@restcomponents', dirname(__DIR__) . '/vendor/charlesportwoodii/yii2-api-rest-components');

$config = [
    'id' => 'yii2-console',
    'name' => 'Yii2-Console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'enableCoreCommands' => YII_DEBUG,
    'controllerNamespace' => 'app\commands',
    'controllerMap' => [
        'migrate' => 'dmstr\console\controllers\MigrateController'
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'params' => require(__DIR__ . '/params.php')
];

if (YII_DEBUG) {
    error_reporting(-1);
    ini_set('display_errors', 'true');
    array_push($config['bootstrap'], 'gii');
    $config['modules'] = [
        'gii' => 'yii\gii\Module',
    ];
}

return $config;