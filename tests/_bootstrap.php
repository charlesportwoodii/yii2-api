<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

foreach (glob(__DIR__ . '/codeception/*') as $file) {
    require_once $file;
}

// Don't deep clone...
\Codeception\Specify\Config::setDeepClone(false);

Yii::setAlias('@tests', __DIR__);
