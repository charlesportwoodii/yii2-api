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
            'class' => 'yii\web\Request',
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'enableCsrfCookie' => false,
            'baseUrl' => '/api',
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
