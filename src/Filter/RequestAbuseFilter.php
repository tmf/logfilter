<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter\Filter;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Tmf\LogFilter\Event\LogFilterEventListener,
    Tmf\LogFilter\Event\LogEntriesEvent,
    Tmf\LogFilter\LogEntry;

/**
 * Class RequestAbuseFilter
 *
 * This EventListener will analyze incoming LogEntry objects and hold them in a temporary field as long as they're
 * relevant for the configured time window. If there are more requests within that window than the allowed maxHits
 * parameter a new logfilter.ban_host event is dispatched and future log entries with the same host are filtered out
 * (from the filter chain)
 *
 * Log entries which are no longer relevant in the current time window are passed along the filter chain.
 *
 * @package Tmf\LogFilter\Filter
 */
class RequestAbuseFilter implements LogFilterEventListener
{
    /**
     * @var array|LogEntry[] temporary storage for log entries in the time window
     */
    private $withheldLogEntries = [];

    /**
     * @var array|LogEntry[] remember blocked hosts
     */
    private $blockedLogEntries = [];

    /**
     * @var int time window to analyze a maximum number of request hits in seconds
     */
    private $window;

    /**
     * @var int the maximum number of hits allowed in a certain time window
     */
    private $maxHits;

    /**
     * @var EventDispatcherInterface the event dispatcher service for dispatching new events (like logfilter.ban_host)
     */
    private $eventDispatcher;

    /**
     * @param int                      $window
     * @param int                      $maxHits
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct($window, $maxHits, EventDispatcherInterface $eventDispatcher)
    {
        $this->window = $window;
        $this->maxHits = $maxHits;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Incoming log entries from the filter chain
     *
     * @param LogEntriesEvent $event object containing the log entries
     */
    public function onProcess(LogEntriesEvent $event)
    {
        $window = $this->window;
        $latestTimestamp = 0;
        // loop through the new log entries (normally just one)
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
                    $this->blockHost($logEntry);

                }
            }

        }
        // decide which log entries get passed along the filter chain, and which will be withheld
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

    /**
     * Helper function to block a host: dispatch a new 'logfilter.ban_host' event,
     * to which we should also be subscribed.
     *
     * @param LogEntry $logEntry
     */
    protected function blockHost(LogEntry $logEntry)
    {
        $event = new LogEntriesEvent([$logEntry]);
        $this->eventDispatcher->dispatch(LogFilterEventListener::BAN_HOST, $event);
    }

    /**
     * Incoming log entries where we should block the host
     *
     * @param LogEntriesEvent $event
     */
    public function onBanHost(LogEntriesEvent $event)
    {
        foreach ($event->getLogEntries() as $banned) {
            $this->blockedLogEntries[] = $banned;
            $this->withheldLogEntries = array_filter($this->withheldLogEntries, function (LogEntry $logEntry) use ($banned) {
                return $logEntry->getHost() !== $banned->getHost();
            });
        }
    }

    /**
     * nothing to report so far...
     */
    public function onReport()
    {

    }
}