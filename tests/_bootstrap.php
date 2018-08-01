<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
defined('ROOT') or define('ROOT', dirname(__DIR__ . '/../../'));
defined('APPLICATION_ENV') or define('APPLICATION_ENV', 'test');
defined('SIMPLE_APPLICATION_ENV') or define('SIMPLE_APPLICATION_ENV', 'test');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('@tests', __DIR__);

Codeception\Util\Autoload::addNamespace('tests\fixtures', '@tests/fixtures');
Codeception\Util\Autoload::addNamespace('tests\codeception', '@tests/codeception');
Codeception\Util\Autoload::addNamespace('tests\_support', '@tests/_support');
Codeception\Util\Autoload::addNamespace('tests\_support\traits', '@tests/_support/traits');

Codeception\Util\Autoload::addNamespace('app', ROOT);
