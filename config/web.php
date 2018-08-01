<?php

$config = include __DIR__ . '/common.php';

$web = [
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
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
    ]
];

return \yii\helpers\ArrayHelper::merge($config, $web);
