<?php

namespace app\components;

use Redis;
use RPQ\Client;
use yii\base\BaseObject;
use Yii;

final class RPQComponent extends BaseObject
{
    /**
     * @var array $redis
     */
    public $redis = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
        'namespace' => 'rpq'
    ];

    /**
     * @var array $queues
     */
    public $queues;

    /**
     * @var RPQ\Client $client
     */
    private $client;

    /**
     * Returns the RPQ Client
     * @return RPQ\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns the queue object
     * @param string $name
     * @return array
     */
    public function getQueue($name = 'default')
    {
        return $this->getClient()->getQueue($name);
    }

    public function init()
    {
        parent::init();
        $redis = new Redis;
        $redis->connect($this->redis['host'], $this->redis['port']);
        $this->client = new Client($redis, $this->redis['namespace']);
    }
}