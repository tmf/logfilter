<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tmf\LogFilter\Event\LogEntriesEvent,
    Tmf\LogFilter\Event\LogFilterEventListener;

/**
 * Class LogFileReader
 *
 * @package Tmf\LogFilter
 */
class LogFileReader implements LogFileReaderInterface
{

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function read($logfile)
    {
        $logEntries = [
            new LogEntry('1.1.1.1', 10, '/test?asdf=88', ''),
            new LogEntry('2.2.2.2', 11, '/test?asdf=99', ''),
            new LogEntry('1.1.1.1', 20, '/test?asdf=77', ''),
            new LogEntry('3.3.3.3', 20, '/test?asdf=99', ''),
            new LogEntry('1.1.1.1', 21, '/test?asdf=66', ''),
            new LogEntry('1.1.1.1', 22, '/test?asdf=99', ''),
            new LogEntry('4.4.4.4', 22, '/test?asdf=99', ''),
            //new LogEntry('1.1.1.1', 23, '/test?asdf=44', ''),
            new LogEntry('5.5.5.5', 30, '/qwer?asdf=99', ''),
            new LogEntry('5.5.5.5', 40, '/test?asdf=99', ''),
        ];

        foreach ($logEntries as $logEntry) {
            $this->eventDispatcher->dispatch(LogFilterEventListener::PROCESS, new LogEntriesEvent([$logEntry]));
        }
        $this->eventDispatcher->dispatch(LogFilterEventListener::PROCESS, new LogEntriesEvent([], true));

        $this->eventDispatcher->dispatch(LogFilterEventListener::REPORT);
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}