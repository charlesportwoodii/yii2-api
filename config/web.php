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
        'yrc' => [
            'class' => 'yrc\components\YRC',
            'userClass' => $yaml['yii2']['user'],
            'fromEmail' => $yaml['yii2']['swiftmailer']['origin_email'],
            'fromName' => $yaml['yii2']['swiftmailer']['origin_email_name'],
            'accessHeader' => $yaml['yii2']['access_control']['header'],
            'accessHeaderSecret' => $yaml['yii2']['access_control']['secret']
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
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class'         => 'Swift_SmtpTransport',
                'host'          => $yaml['yii2']['swiftmailer']['host'],
                'username'      => $yaml['yii2']['swiftmailer']['username'],
                'password'      => $yaml['yii2']['swiftmailer']['password'],
                'port'          => $yaml['yii2']['swiftmailer']['port'],
                'encryption'    => $yaml['yii2']['swiftmailer']['encryption'],
            ],
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis'
        ],
        'redis' => [
            'class'     => 'yii\redis\Connection',
            'hostname'  => $yaml['yii2']['redis']['host'],
            'port'      => $yaml['yii2']['redis']['port'],
            'database'  => $yaml['yii2']['redis']['database'],
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
                    'globals' => ['html' => 'yii\helpers\Html'],
                ],
            ],
        ],
        'request' => [
            'enableCookieValidation'    => false,
            'enableCsrfValidation'      => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'application/vnd.25519+json' => 'yrc\web\Json25519Parser'
            ]
        ],
        'response' => [
            'class'      => 'yrc\web\Response',
            'format'     => \yrc\web\Response::FORMAT_JSON,
            'charset'    => 'UTF-8',
            'formatters' => [
                \yrc\web\Response::FORMAT_JSON25519 => [
                    'class'         => 'yrc\web\Json25519ResponseFormatter',
                    'prettyPrint'   => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
                ],
                \yrc\web\Response::FORMAT_JSON => [
                    'class'         => 'yrc\web\JsonResponseFormatter',
                    'prettyPrint'   => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
                ]
            ],
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
            'class' => 'yrc\web\User',
            'identityClass' => $yaml['yii2']['user'],
            'enableSession' => false
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                'graylog' => [
                    'class' => 'nex\graylog\GraylogTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => [],
                    'host' => $yaml['yii2']['graylog']['host'],
                    'additionalFields' => [
                        'user-ip' => function ($yii) {
                            return $yii->request->getUserIP();
                        }
                    ]
                ]
            ]
        ],
        'db' => require(__DIR__ . '/db.php')
    ],
    'params' => require(__DIR__ . '/params.php')
];

return $config;