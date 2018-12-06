<?php
/**
 * Created by PhpStorm.
 * User: fabian
 * Date: 06.12.18
 * Time: 07:45
 */

namespace Tiny;


use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class TestHandler extends AbstractHandler
{
    private $errorLogs = array();

    /**
     * @param int $level The minimum logging level at which this handler will be triggered
     */
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level, false);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if ($record['level'] < $this->level) {
            return false;
        }

        switch($record['level']) {
            case Logger::ERROR:
                $this->errorLogs[] = $record['message'];
                break;
        }

        return true;
    }

    /**
     * @return array
     */
    function getErrorLogs(): array
    {
        return $this->errorLogs;
    }
}