<?php
namespace seckill\Inter;

interface CacheQueueRepositoriesInterface
{
    function __construct($redis_config, $lkey);

    function getLen();

    function push($obj);

    function pop();

    function initQueue($queue);

    function clearQueue();
}

?>