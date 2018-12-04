<?php
/**
 * Created by PhpStorm.
 * User: fabian
 * Date: 04.12.18
 * Time: 07:28
 */

namespace Tiny;


use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    public function testDefaultLogger()
    {
        $logger1 = LoggerFactory::getLogger();
        $logger2 = LoggerFactory::getLogger('default');

        $this->assertEquals($logger1, $logger2);
    }

    public function testSameLogger()
    {
        $logger1 = LoggerFactory::getLogger('foo', Logger::INFO);
        $logger2 = LoggerFactory::getLogger('foo', Logger::ERROR);

        $this->assertEquals($logger1, $logger2);
    }

    public function testDifferentLogger()
    {
        $logger1 = LoggerFactory::getLogger('foo');
        $logger2 = LoggerFactory::getLogger('bar');

        $this->assertNotEquals($logger1, $logger2);
    }
}
