<?php

$config = include __DIR__ . '/common.php';

$console = [
    'id' => $config['id'] . '-console',
    'name' => $config['name'] . '-console',
    'enableCoreCommands' => YII_DEBUG,
    'controllerNamespace' => 'app\commands',
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [
                '@yrc/migrations',
                '@app/migrations',
                '@yii/rbac/migrations'
            ]
        ],
    ],
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath'      => '@app/views/email',
            'htmlLayout'    => 'email/html.twig',
            'textLayout'    => 'email/text.twig',
            'transport' => [
                'class'         => 'Swift_SmtpTransport',
                'host'          => $yaml['swiftmailer']['host'],
                'username'      => $yaml['swiftmailer']['username'],
                'password'      => $yaml['swiftmailer']['password'],
                'port'          => $yaml['swiftmailer']['port'],
                'encryption'    => $yaml['swiftmailer']['encryption'],
            ],
        ],
        'view' => [
            'class' => 'yii\web\View',
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    'cachePath' => YII_DEBUG ? false : '@runtime/Twig/cache',
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'extensions' => [
                        '\Twig_Extension_Debug',
                    ],
                    'globals' => [
                        'html' => ['class' => '\yii\helpers\Html'],
                    ],
                ],
            ],
        ]
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
