<?php
namespace Seckill\Impl;

use seckill\Inter\CacheQueueRepositoriesInterface;
use Redis;

class RedisQueueRepositories implements CacheQueueRepositoriesInterface
{
    private $handler;

    private $lkey;

    public function __construct($redis_config, $lkey)
    {
        $ip = $redis_config['IP'];
        $port = $redis_config['PORT'];
        $redis_connection = new Redis();
        $redis_connection->connect($ip, $port);
        $this->handler = $redis_connection;
        $this->lkey = $lkey;
    }

    public function getLen()
    {
        return $this->handler->lSize($this->lkey);
    }

    public function push($obj)
    {
        return $this->handler->lPush($this->lkey, $obj);
    }

    public function pop()
    {
        return $this->handler->lPop($this->lkey);
    }

    public function initQueue($queue)
    {
        $this->handler->del($this->lkey);
        foreach ($queue as $obj) {
            $this->push($obj);
        }
    }

    public function clearQueue()
    {
        $this->handler->del($this->lkey);
    }
}


?>