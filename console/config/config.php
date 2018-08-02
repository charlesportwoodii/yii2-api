<?php

$yaml = require ROOT . '/common/config/loader.php';
$common = require ROOT . '/common/config/config.php';

// Only load tests if the environment is set to test
if (YII_ENV === 'test' || YII_ENV === 'dev') {
    Yii::setAlias('@tests', ROOT . '/tests');
}

$config = \yii\helpers\ArrayHelper::merge($common, [
    'id' => $config['id'] . '-console',
    'name' => $config['name'] . '-console',
    'enableCoreCommands' => YII_DEBUG,
    'controllerNamespace' => 'console\commands',
    'controllerMap' => \yii\helpers\ArrayHelper::merge([
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [
                '@yrc/migrations',
                '@console/migrations',
                '@yii/rbac/migrations'
            ]
        ]], YII_DEBUG ? [
            'fixture' => [
                'class' => 'yii\console\controllers\FixtureController',
                'namespace' => 'tests\\fixtures'
            ],
            'shell' => [
                'class' => 'yii\shell\ShellController'
            ]
        ] : []
    ),
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath'      => '@console/views/email',
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
                    'globals' => [
                        'DEBUG' => YII_DEBUG
                    ],
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'extensions' => [
                        '\Twig_Extension_Debug',
                    ],
                    'globals' => [
                        'html' => ['class' => '\yii\helpers\Html'],
                    ],
                    'functions' => [
                        't' => '\Yii::t'
                    ]
                ],
            ],
        ]
    ]
]);

if (YII_DEBUG) {
    error_reporting(-1);
    ini_set('display_errors', 'true');
    
    if (\class_exists('yii\gii\Module')) {
        $config['bootstrap'][] = 'gii';
        $config['modules']['gii'] = ['class' => 'yii\gii\Module'];
    }
}

return $config;
