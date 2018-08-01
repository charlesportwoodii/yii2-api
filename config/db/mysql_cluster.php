<?php

if (!isset($yaml)) {
    $yaml = include ROOT . '/common/config/loader.php';
}

$dbConfig = include __DIR__ . '/mysql.php';

if (\is_array($yaml['database']['cluster_hosts'])) {
    $dbConfig['slaves'] = [];
    foreach ($yaml['database']['cluster_hosts'] as $host) {
        $dbConfig['slaves'][] = [
            'dsn' => 'mysql:host=' . $host . ';dbname=' . $dbConfig['database']['database']
        ];
    }
}

return $dbConfig;
