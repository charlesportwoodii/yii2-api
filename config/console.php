<?php

$config = require __DIR__ . '/common.php';

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$console = [
    'id' => $config['id'] . '-console',
    'name' => $config['name'] . '-console',
    'enableCoreCommands' => YII_DEBUG,
    'controllerNamespace' => 'app\commands',
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [
                '@restcomponents/migrations',
                '@app/migrations',
                '@yii/rbac/migrations'
            ]
        ],
    ],
    'components' => [
        'rpq' => [
            'class' => 'yrc\components\RPQComponent',
            'redis' => $yaml['redis'],
            'queues' => $yaml['queue']
        ],
    ]
];

$config = \yii\helpers\ArrayHelper::merge($config, $console);

if (YII_DEBUG) {
    error_reporting(-1);
    ini_set('display_errors', 'true');
    
    if (\class_exists('yii\gii\Module')) {
        $config['bootstrap'][] = 'gii';
        $config['modules']['gii'] = ['class' => 'yii\gii\Module'];
    }
}

return $config;