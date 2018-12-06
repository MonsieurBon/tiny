<?php


namespace Tiny;


class Route
{
    private $method;
    private $route;
    private $handler;

    public function __construct($method, $route, $handler)
    {
        $this->method = $method;
        $this->route = $route;
        $this->handler = $handler;
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
    public function getHandler()
    {
        return $this->handler;
    }
}