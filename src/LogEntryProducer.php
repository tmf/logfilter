<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Tmf\LogFilter\Event\LogEntriesEvent,
    Tmf\LogFilter\Event\LogFilterEventListener;

use MVar\Apache2LogParser\AccessLogParser,
    MVar\Apache2LogParser\LogIterator;

/**
 * Class LogFileReader
 *
 * @package Tmf\LogFilter
 */
class LogEntryProducer
{

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function process($logfile)
    {
        $parser = new AccessLogParser(AccessLogParser::FORMAT_COMBINED);

        foreach (new LogIterator($logfile, $parser) as $line => $data) {
            $logEntry = new LogEntry(
                $data['remote_host'],
                strtotime($data['time']),
                $data['request']['path'],
                $data['request_headers']['User-Agent'],
                $data['request']['method']
            );
            $this->eventDispatcher->dispatch(LogFilterEventListener::PROCESS, new LogEntriesEvent([$logEntry]));
        }
        $this->eventDispatcher->dispatch(LogFilterEventListener::PROCESS, new LogEntriesEvent([], true));

        $this->eventDispatcher->dispatch(LogFilterEventListener::REPORT);
    }
}