<?php


namespace Tiny;


class Route
{
    private $method;
    private $route;
    private $callable;

    public function __construct($method, $route, $callable)
    {
        $this->method = $method;
        $this->route = $route;
        $this->callable = $callable;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return \Closure
     */
    public function getCallable()
    {
        return $this->callable;
    }
}