<?php

defined('ROOT') or define('ROOT', realpath(__DIR__ . '/../../'));

require ROOT .'/vendor/autoload.php';
$loader = require ROOT . '/common/config/loader.php';

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', $loader['app']['debug']);
defined('YII_ENV') or define('YII_ENV', $loader['app']['env'] === 'prod' ? 'prod' : 'dev');

require ROOT . '/vendor/yiisoft/yii2/Yii.php';
$config = require ROOT . '/api/config/config.php';
(new yii\web\Application($config))->run();
