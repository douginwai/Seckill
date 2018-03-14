<?php
namespace Seckill\Impl;

use seckill\Inter\StockQueueInterface;

class StockQueueImpl implements StockQueueInterface
{
    private $Repositories;

    public function __construct($Repositories)
    {
        $this->Repositories = $Repositories;
    }

    public function isEmpty()
    {
        $len = $this->Repositories->getLen();
        if ($len > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getLen()
    {
        return $this->Repositories->getLen();
    }

    public function push($obj)
    {
        return $this->Repositories->push($obj);
    }

    public function pop()
    {
        return $this->Repositories->pop();
    }

    public function initQueue($queue)
    {
        return $this->Repositories->initQueue($queue);
    }
}


?>