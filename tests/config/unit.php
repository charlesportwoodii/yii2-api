<?php
/**
 * Application configuration for unit tests
 */
require __DIR__ . '/../unit/_bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../config/web.php'),
    require(__DIR__ . '/config.php')
);

return $config;
