<?php
/**
 * Application configuration for api tests
 */
require __DIR__ . '/../api/_bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../config/web.php'),
    require(__DIR__ . '/config.php')
);

return $config;
