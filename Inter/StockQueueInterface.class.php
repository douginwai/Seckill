<?php
namespace seckill\Inter;

interface StockQueueInterface
{
    function __construct($Repositories);

    function isEmpty();

    function getLen();

    function push($obj);

    function pop();

    function initQueue($queue);

}


?>