<?php

$yaml = require __DIR__ . '/loader.php';


$logger = new \Monolog\Logger('application');

if ($yaml['log']['handler'] === "\\Monolog\\Handler\\GelfHandler") {
    $url = \parse_url($yaml['log']['connection_string']);
    $transport = new \Gelf\Transport\UdpTransport($url['host'], $url['port'], \Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN);
    $publisher = new \Gelf\Publisher();
    $publisher->addTransport($transport);
    $handler = new $yaml['log']['handler']($publisher, $yaml['log']['level']);
} else {
    $handler = new $yaml['log']['handler']($yaml['log']['connection_string'], $yaml['log']['level']);
}

if ($yaml['log']['formatter'] !== null) {
    $formatter = new $yaml['log']['formatter']('application');
    $handler->setFormatter($formatter);
}

$logger->pushHandler($handler, $yaml['log']['level']);

return [
    [
        'class' => 'app\components\log\PsrTarget',
        'logger' => $logger,
        'levels' => ['info', 'error', 'warning']
    ]
];