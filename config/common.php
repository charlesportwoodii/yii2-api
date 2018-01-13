<?php

$yaml = require __DIR__ . '/loader.php';

Yii::setAlias('@restcomponents', dirname(__DIR__) . '/vendor/charlesportwoodii/yii2-api-rest-components');

$config = [
    'id' => $yaml['app']['id'],
    'name' => $yaml['app']['name'],
    'basePath' => dirname(__DIR__),
    'bootstrap' => [ 'log' ],
    'language' => 'en-US',
    'sourceLanguage' => 'en-US',
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager'
        ],
        'yrc' => [
            'class' => 'yrc\components\YRC',
            'userClass' => $yaml['user'],
            'accessHeader' => $yaml['access_control']['header'],
            'accessHeaderSecret' => $yaml['access_control']['secret']
        ],
        'httpclient' => [
            'class' => 'yrc\components\HttpClientComponent',
            'transport' => 'yii\httpclient\CurlTransport',
            'options' => [
                'timeout' => 5
            ]
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@app/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                    'on missingTranslation' => ['yrc\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'yrc*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@app/vendor/charlesportwoodii/yii2-api-reset-components/messages',
                    'fileMap' => [
                        'yrc' => 'yrc.php',
                    ],
                    'on missingTranslation' => ['yrc\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis'
        ],
        'redis' => [
            'class'     => 'yii\redis\Connection',
            'hostname'  => $yaml['redis']['host'],
            'port'      => $yaml['redis']['port'],
            'database'  => $yaml['redis']['database'],
        ],
        'user' => [
            'class' => 'yrc\web\User',
            'identityClass' => $yaml['user'],
            'enableSession' => false
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => require(__DIR__ . '/logs.php'),
        ],
        'rpq' => [
            'class' => 'yrc\components\RPQComponent',
            'redis' => $yaml['redis'],
            'queues' => $yaml['queue']
        ],
        'db' => require(__DIR__ . '/database.php')
    ],
    'params' => require(__DIR__ . '/params.php')
];

return $config;