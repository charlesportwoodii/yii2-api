<?php 

use \Symfony\Component\Yaml\Yaml;

$configFile = __DIR__ . '/../config.yml';

if (!file_exists($configFile)) {
    throw new Exception('Missing config/config.yml file.');
}

$config = Yaml::parse(file_get_contents($configFile));

return [
    'class'                 => 'yii\db\Connection',
    'dsn'                   => 'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['database'],
    'username'              => $config['database']['username'],
    'password'              => $config['database']['password'],
    'charset'               => 'utf8',
    'enableSchemaCache'     => !YII_DEBUG,
    'schemaCacheDuration'   => YII_DEBUG ? 0 : 86400,
    'schemaCache'           => 'cache'
];