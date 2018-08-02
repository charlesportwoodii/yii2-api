<?php
/**
 * Application configuration for unit tests
 */
require __DIR__ . '/../unit/_bootstrap.php';
$consoleConfig = require(ROOT . '/console/config/config.php');
$config = yii\helpers\ArrayHelper::merge(
    require(ROOT . '/api/config/config.php'),
    require(__DIR__ . '/config.php')
);

return $config;
