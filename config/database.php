<?php

$config = require __DIR__ . '/loader.php';
return require __DIR__ . '/db/' . $config['database']['driver'] . '.php';