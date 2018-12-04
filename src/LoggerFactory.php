<?php
/**
 * Created by PhpStorm.
 * User: fabian
 * Date: 04.12.18
 * Time: 06:53
 */

namespace Tiny;


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;

class LoggerFactory
{
    private static $logger = array();

    /**
     * @param string $name
     * @param int $level
     * @return Logger
     */
    public static function getLogger(string $name = 'default', $level = Logger::INFO): Logger
    {
        if (!key_exists($name, self::$logger)) {
            self::$logger[$name] = self::newLogger($name, $level);
        }

        return self::$logger[$name];
    }

    private static function newLogger($name, $level): Logger
    {
        $log = new Logger($name);

        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $handler = new StreamHandler(self::getLogFilePath($name), $level);
        $handler->setFormatter($formatter);

        $log->pushHandler($handler);

        return $log;
    }

    private static function getLogDir(): string
    {
        $request = Request::createFromGlobals();
        $documentRoot = $request->server->get('DOCUMENT_ROOT') ?: __DIR__;

        return $documentRoot . '/../var/logs';
    }

    /**
     * @param $name
     * @return string
     */
    private static function getLogFilePath($name): string
    {
        return sprintf('%s/%s.log', self::getLogDir(), $name);
    }
}