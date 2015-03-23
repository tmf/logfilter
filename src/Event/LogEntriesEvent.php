<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter\Event;

use Symfony\Component\EventDispatcher\Event;
use Tmf\LogFilter\LogEntry;

/**
 * Class LogEntriesEvent
 *
 * @package Tmf\LogFilter\Event
 */
class LogEntriesEvent extends Event
{
    protected $flush = false;

    /**
     * @var array|LogEntry[]
     */
    private $logEntries = [];

    /**
     * @param array|LogEntry[] $logEntries
     * @param bool $flush
     */
    public function __construct($logEntries = [], $flush = false)
    {
        $this->logEntries = $logEntries;
        $this->flush = $flush;
    }

    /**
     * @param array|LogEntry[] $logEntries
     */
    public function setLogEntries($logEntries)
    {
        $this->logEntries = $logEntries;
    }

    public function getLogEntries()
    {
        return $this->logEntries;
    }

    /**
     * @return boolean
     */
    public function shouldFlush()
    {
        return $this->flush;
    }

    /**
     * @param boolean $flush
     */
    public function setFlushFlag($flush)
    {
        $this->flush = $flush;
    }
}