<?php

$config = require __DIR__ . '/loader.php';
return require __DIR__ . '/db/' . $config['yii2']['database']['driver'] . '.php';