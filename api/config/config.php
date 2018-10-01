<?php

$yaml = require ROOT . '/common/config/loader.php';
$common = require ROOT . '/common/config/config.php';

return \yii\helpers\ArrayHelper::merge($common, [
    'controllerNamespace' => 'api\controllers',
    'homeUrl' => '/api',
    'basePath' => dirname(__DIR__),
    'language' => 'en-US',
    'sourceLanguage' => 'en-US',
    'components' => [
        'request' => [
            'class' => 'yrc\web\Request',
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'enableCsrfCookie' => false,
            'baseUrl' => '/api'
        ],
        'response' => [
            'class'      => \yrc\web\Response::class,
            'format'     => \yrc\web\Response::FORMAT_JSON,
            'charset'    => 'UTF-8'
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
                    'pattern'   => '/v1/<controller>/<action>',
                    'route'     => 'v1/<controller>/<action>'
                ],
                [
                    'pattern' => '/VERSION',
                    'route' => 'v1/info/version'
                ]
            ]
        ]
    ]
]);
