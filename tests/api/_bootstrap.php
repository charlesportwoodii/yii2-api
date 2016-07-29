<?php

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(dirname(__DIR__)));
defined('YII_TEST_ENTRY_URL') or define('YII_TEST_ENTRY_URL', '/index.php');
defined('YII_TEST_ENTRY_FILE') or define('YII_TEST_ENTRY_FILE', YII_APP_BASE_PATH . '/web/index.php');

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;

$_SERVER['SERVER_NAME'] =  parse_url('http://localhost:8080', PHP_URL_HOST);
$_SERVER['SERVER_PORT'] =  parse_url('http://localhost:8080', PHP_URL_PORT) ?: '80';