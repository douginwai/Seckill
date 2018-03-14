<?php
namespace seckill\Inter;

interface CacheKeyRepositoriesInterface
{
    function __construct($redis_config);

    function get($key);

    function del($key);

    function set($key, $obj);

    function exists($key);

    function time();

}

?>