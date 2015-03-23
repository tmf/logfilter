<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter\Filter;

use Tmf\LogFilter\Event\LogFilterEventListener,
    Tmf\LogFilter\Event\LogEntriesEvent;
use Tmf\LogFilter\LogEntry;

/**
 * Class RequestAbuseFilter
 *
 * @package Tmf\LogFilter\Filter
 */
class RequestAbuseFilter implements LogFilterEventListener
{
    /**
     * @var array|LogEntry[]
     */
    private $withheldLogEntries = [];

    /**
     * @var array|LogEntry[]
     */
    private $blockedLogEntries = [];

    private $window;
    private $maxHits;

    public function __construct($window, $maxHits)
    {
        $this->window = $window;
        $this->maxHits = $maxHits;
    }

    public function onProcess(LogEntriesEvent $event)
    {
        $window = $this->window;
        $latestTimestamp = 0;
        foreach ($event->getLogEntries() as $logEntry) {
            $blocked = false;
            $latestTimestamp = $logEntry->getTimestamp() > $latestTimestamp ? $logEntry->getTimestamp() : $latestTimestamp;
            foreach ($this->blockedLogEntries as $blockedLogEntry) {
                if ($blockedLogEntry->getHost() == $logEntry->getHost()) {
                    $blocked = true;
                    break;
                }
            }
            if (!$blocked) {
                $this->withheldLogEntries[] = $logEntry;
                $logEntries = array_filter($this->withheldLogEntries, function (LogEntry $withheld) use ($logEntry, $window) {
                    return $withheld->getHost() == $logEntry->getHost() && $withheld->getTimestamp() > ($logEntry->getTimestamp() - $window);
                });
                if (count($logEntries) > $this->maxHits) {
                    $this->blockedLogEntries[] = $logEntry;
                }
            }

        }
        $passOnLogEntries = [];
        $newWithheldLogEntries = [];
        if ($event->shouldFlush()) {
            $passOnLogEntries = $this->withheldLogEntries;
        } else {
            foreach ($this->withheldLogEntries as $logEntry) {
                if ($logEntry->getTimestamp() > ($latestTimestamp - $window)) {
                    $newWithheldLogEntries[] = $logEntry;
                } else {
                    $passOnLogEntries[] = $logEntry;
                }
            }
        }


        $this->withheldLogEntries = $newWithheldLogEntries;
        $event->setLogEntries($passOnLogEntries);


    }

    public function onReport()
    {

    }
}