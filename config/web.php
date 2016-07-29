<?php

$yaml = require __DIR__ . '/loader.php';

Yii::setAlias('@restcomponents', dirname(__DIR__) . '/vendor/charlesportwoodii/yii2-api-rest-components');

$config = [
    'id' => 'mmxp-server',
    'name' => 'Modern Messenger Exchange Server',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class'     => 'Swift_SmtpTransport',
                'host'          => $config['yii2']['swiftmailer']['host'],
                'username'      => $config['yii2']['swiftmailer']['username'],
                'password'      => $config['yii2']['swiftmailer']['password'],
                'port'          => $config['yii2']['swiftmailer']['port'],
                'encryption'    => $config['yii2']['swiftmailer']['encryption'],
            ],
        ],
        'view' => [
            'class' => 'yii\web\View',
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    'cachePath' => '@runtime/Twig/cache',
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'globals' => ['html' => '\yii\helpers\Html'],
                ],
            ],
        ],
        'request' => [
            'enableCookieValidation'    => false,
            'enableCsrfValidation'      => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'format'         => yii\web\Response::FORMAT_JSON,
            'charset'        => 'UTF-8'
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'class'                 => 'yii\web\UrlManager',
            'showScriptName'        => false,
            'enableStrictParsing'   => true,
            'enablePrettyUrl'       => true,
            'rules' => [
                [
                    'pattern'   => '/api/v1/<controller>/<action>',
                    'route'     => 'api/v1/<controller>/<action>'
                ]
            ]
        ],
        'user' => [
            'identityClass' => $yaml['user']['class']
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ]
            ]
        ],
        'db' => require(__DIR__ . '/db.php')
    ],
    'params' => require(__DIR__ . '/params.php')
];

return $config;