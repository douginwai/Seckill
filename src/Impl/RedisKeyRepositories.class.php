<?php
namespace seckill\Impl;

use seckill\Inter\CacheKeyRepositoriesInterface;
use Redis;

class RedisKeyRepositories implements CacheKeyRepositoriesInterface
{
    private $handler;

    public function __construct($redis_config)
    {
        $ip = $redis_config['IP'];
        $port = $redis_config['PORT'];
        $redis_connection = new Redis();
        $redis_connection->connect($ip, $port);
        $this->handler = $redis_connection;
    }

    public function get($key)
    {
        return $this->handler->get($key);
    }

    public function del($key)
    {
        return $this->handler->del($key);
    }

    public function set($key, $obj, $param = []) 
    {
        return $this->handler->set($key, $obj, $param);
    }

    public function exists($key)
    {
        return $this->handler->exists($key);
    }

    public function time()
    {
        return $this->handler->time();
    }
}


?>