<?php
namespace seckill;

class Container
{
    public $bindings;

    public function bind($abstract, $concrete)
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function make($abstract, $parameters = [])
    {
        return call_user_func($this->bindings[$abstract], $parameters);
    }
}