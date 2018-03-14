<?php
namespace seckill\Inter;

interface StockLogicInterface
{
    function getQueue($count);

    function resetQueue();
}

?>