<?php

use Symfony\Component\Yaml\Yaml;
use yii\helpers\ArrayHelper;

$configFile = __DIR__ . '/config.yml';
$defaultConfig = __DIR__ . '/config-default.yml';

if (!file_exists($configFile)) {
    throw new Exception('Missing config/config.yml file.');
}

$default = Yaml::parse(file_get_contents($defaultConfig));
$config = Yaml::parse(file_get_contents($configFile));

return ArrayHelper::merge($default, $config);