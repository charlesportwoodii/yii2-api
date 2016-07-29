<?php 

use \Symfony\Component\Yaml\Yaml;

$configFile = __DIR__ . '/config.yml';

if (!file_exists($configFile)) {
    throw new Exception('Missing config/config.yml file.');
}

$config = Yaml::parse(file_get_contents($configFile));

return require __DIR__ . '/db/' . $config['database']['driver'] . '.php';