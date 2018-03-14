<?php

namespace seckill;


class SecKill
{
    private $cache_key_repositories;

    private $stock_queue;

    private $stock_repositories;

    private $order_repositories;

    private $stop_period = 60;
    private $dbo_period = 3;
    private $lock_period = 5;

    private $key_kill_status = '';
    private $key_stop_time = '';
    private $key_dbo_time = '';
    private $key_initlock = '';

    private $queue_length = 100;

    public function __construct(
        $config,
        $cache_key_repositories,
        $stock_queue,
        $stock_repositories,
        $order_repositories
    ) {

        $this->stop_period = $config['STOP_PERIOD'];
        $this->dbo_period = $config['DBO_PERIOD'];
        $this->lock_period = $config['LOCK_PERIOD'];

        $this->key_kill_status = $config['CACHE_LIST_KEY'] . 'kill_status';
        $this->key_stop_time = $config['CACHE_LIST_KEY'] . 'stop_time';
        $this->key_dbo_time = $config['CACHE_LIST_KEY'] . 'dbo_time';
        $this->key_initlock = $config['CACHE_LIST_KEY'] . 'initlock';

        $this->queue_length = $config['QUEUE_LENGTH'];

        $this->cache_key_repositories = $cache_key_repositories;
        $this->stock_queue = $stock_queue;
        $this->stock_repositories = $stock_repositories;
        $this->order_repositories = $order_repositories;

    }


    public function getTime()
    {
        $time_arr = $this->cache_key_repositories->time();
        return $time_arr[0];
    }

    public function stopKill()
    {
        $time = $this->getTime();
        $this->cache_key_repositories->set($this->key_stop_time, $time);
        $this->cache_key_repositories->set($this->key_kill_status, 3);
    }

    public function startKill()
    {
        $this->cache_key_repositories->set($this->key_kill_status, 1);
    }

    public function setDboTime()
    {
        $time = $this->getTime();
        $this->cache_key_repositories->set($this->key_dbo_time, $time);
    }


    public function getDboTime()
    {
        $time = $this->cache_key_repositories->get($this->key_dbo_time);
        return $time;
    }


    public function getInitLock($lock_id)
    {
        return $this->cache_key_repositories->set($this->key_initlock, $lock_id, ['nx', 'ex' => $this->lock_period]);
    }

    public function releaseInitLock($lock_id)
    {
        if ($this->cache_key_repositories->get($this->key_initlock) == $lock_id) {
            $this->cache_key_repositories->del($this->key_initlock);
        }
    }


    public function initStockQueue()
    {
        // 距离上次操作数据库开始时间间隔太小，则不执行操作
        if ($this->getTime() - $this->getDboTime() < $this->dbo_period) {
            return 'DBO';
        }
        $lock_id = time() . chr(mt_rand(33, 126)) . chr(mt_rand(33, 126));

        if ($this->getInitLock($lock_id)) {
            $queue = $this->stock_repositories->getQueue($this->queue_length);

            if (count($queue) > 0) {
                $this->stock_queue->initQueue($queue);
                $this->releaseInitLock($lock_id);
                return 'INIT+' . $lock_id;
            } else {
                $this->stopKill();
                $this->releaseInitLock($lock_id);
                return 'NOSTOCK';
            }
        } else {
            $this->releaseInitLock($lock_id);
            return 'INITLOCK';
        }
    }


    public function stockQueueIsEmpty()
    {
        if ($this->stock_queue->isEmpty()) {
            return true;
        } else {
            return false;
        }
    }


    public function stockQueueLen()
    {
        return $this->stock_queue->getLen();
    }

    public function popStock()
    {
        $stock_id = $this->stock_queue->pop();
        return $stock_id;
    }

    public function getKillStatus()
    {
        $status = $this->cache_key_repositories->get($this->key_kill_status);
        return $status;
    }


    public function getStopTime()
    {
        $time = $this->cache_key_repositories->get($this->key_stop_time);
        return $time;
    }


    public function buy($stock_id)
    {
        $this->setDboTime();
        return $this->order_repositories->buy($stock_id);
    }

    public function doSecKill()
    {
        switch ($this->getKillStatus()) {
            case 1:
                if ($this->stockQueueIsEmpty() === true) {
                    // 从mysql拿取库存到redis
                    $initResult = $this->initStockQueue();
                    return 'INITING_STOCK-' . $initResult;
                } else {
                    $stock_id = $this->popStock();
                    if ($stock_id > 0) {
                        if ($this->buy($stock_id)) {
//                            return 'SUCCESS-' . $stock_id;
                            return true;
                        } else {
                            return 'BUY_FAIL-' . $stock_id;
                        }

                    } else {
                        return 'INVALID_STOCKID' . $stock_id;
                    }
                }
                break;
            case 2:
                return 'BLOCK_KILL';
                break;
            case 3:
                if (time() - $this->getStopTime() > $this->stop_period) {
                    $this->startKill();
                    return 'START_KILL';
                }
                return 'STOP_KILL';
                break;
            default:
                return 'UNKNOWN_FAIL';
                break;
        }
    }

}


?>