<?php

/**
 * This configuration is shared both by Yii2 and RPQ, and as of such must be able to handle multiple configurations
 */

// RPQ doesn't define our 'ROOT' directory, so define it
defined('ROOT') or define('ROOT', __DIR__ . '/../..');

// If RPQ has not already defined this, use 'application'
defined('LOGGER_APP_NAME') or define('LOGGER_APP_NAME', 'application');

// Fetch our YAML configuration
$yaml = include ROOT . '/config/loader.php';

// Use Yii::getAlias if is available, otherwise define our runtime directory
$path = defined('Yii') ? Yii::getAlias('@runtime') : ROOT . '/runtime';

// Create a new logger instanced based upon LOGGER_APP_NAME
$logger = new \Monolog\Logger(LOGGER_APP_NAME);

// Create a file based StreamHandler to the default Yii2 log location
$handler = new \Monolog\Handler\StreamHandler($path . '/logs/application.log', $yaml['log']['level']);

// Use a customer LineFormatter so GROKing in Logstash is easier.
$formatter = new \Monolog\Formatter\LineFormatter("[%datetime%] %channel%.%level_name%: [%message%] [%context%] [%extra%]\n");
$handler->setFormatter($formatter);

// Push the handler
$logger->pushHandler($handler, $yaml['log']['level']);

// Return a shared instanced of \Monolog\Logger
return $logger;
