<?php

$yaml = include __DIR__ . '/loader.php';

Yii::setAlias('@root', ROOT);
Yii::setAlias('@console', '@root/console');
Yii::setAlias('@common', '@root/common');
Yii::setAlias('@api', '@root/api');
Yii::setAlias('@vendor', '@root/vendor');
Yii::setAlias('@runtime', '@root/runtime');

Yii::setAlias('@yrc', ROOT . '/vendor/charlesportwoodii/yii2-api-rest-components');

$config = [
    'id' => $yaml['app']['id'],
    'name' => $yaml['app']['name'],
    'basePath' => ROOT,
    'bootstrap' => [ 'log' ],
    'language' => 'en-US',
    'sourceLanguage' => 'en-US',
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager'
        ],
        'yrc' => [
            'class' => 'yrc\components\YRC',
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
                    'basePath' => '@root/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                    'on missingTranslation' => ['yrc\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'yrc*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@root/vendor/charlesportwoodii/yii2-api-reset-components/messages',
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
            'targets' => [
                'file' => [
                    'enabled' => true,
                    'class' => '\yrc\components\log\PsrTarget',
                    'logger' => require $yaml['log']['logger'],
                    'levels' => \array_merge([
                        'info', 'error', 'warning'
                    ], YII_DEBUG ? ['trace'] : []),
                    'logVars' => [],
                    'except' => [
                        'yii\web\HttpException:400',
                        'yii\web\HttpException:401',
                        'yii\web\HttpException:403',
                        'yii\web\HttpException:404',
                        'yii\web\HttpException:408',
                        'yii\web\HttpException:428',
                        'yii\db\Command:*',
                        'yrc\filters\auth\HMACSignatureAuth:*',
                        'yii\db\Connection:*',
                        'yii\filters\RateLimiter:*',
                        'yii\mail\BaseMailer::send',
                        'yii\web\User::login',
                        'yii\web\Session::open'
                    ]
                ],
            ]
        ],
        'rpq' => [
            'class' => 'yrc\components\RPQComponent',
            'redis' => $yaml['redis'],
            'queues' => $yaml['queue']
        ],
        'db' => include __DIR__ . '/database.php'
    ],
    'params' => include __DIR__ . '/params.php'
];

return $config;
