<?php
/**
 * Application configuration for unit tests
 */
require __DIR__ . '/../api/_bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../config/web.php'),
    []
);

return $config;
