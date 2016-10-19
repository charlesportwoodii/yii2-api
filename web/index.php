<?php

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/loader.php';

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', $config['app']['debug']);
defined('YII_ENV') or define('YII_ENV', $config['app']['env'] ?? 'prod');

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require(__DIR__ . '/../config/web.php');
(new yii\web\Application($config))->run();
