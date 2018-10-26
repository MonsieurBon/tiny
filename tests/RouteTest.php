<?php
declare(strict_types=1);

namespace Tiny;


use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testGetters()
    {
        $route = new Route('a', 'b', 'c');

        $this->assertEquals('a', $route->getMethod());
        $this->assertEquals('b', $route->getRoute());
        $this->assertEquals('c', $route->getCallable());
    }
}