<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter\Filter;

use Tmf\LogFilter\Event\LogFilterEventListener,
    Tmf\LogFilter\Event\LogEntriesEvent,
    Tmf\LogFilter\LogEntry;

/**
 * Class GetParameterCounter
 *
 * Count the number of hits on a certain GET request parameter.
 * Log entries containing the GET parameter are remembered, and a final count is done on reporting.
 * (log entries could be removed, after a logfilter.ban_host event is dispatched)
 *
 * @package Tmf\LogFilter\Filter
 */
class GetParameterCounter implements LogFilterEventListener
{
    /**
     * @var string the GET parameter in question
     */
    private $getParameter;

    /**
     * @var array|LogEntry[] storage for log entries with that get parameter
     */
    private $logEntriesWithGetParameter = [];

    /**
     * @param int $getParameter
     */
    public function __construct($getParameter)
    {
        $this->getParameter = $getParameter;
    }

    /**
     * Incoming log entries from the filter chain
     *
     * @param LogEntriesEvent $event
     */
    public function onProcess(LogEntriesEvent $event)
    {
        foreach ($event->getLogEntries() as $logEntry) {
            if (false !== $this->extractGetParameter($logEntry)) {
                $this->logEntriesWithGetParameter[] = $logEntry;
            }
        }
    }

    /**
     * Helper to extract the GET value
     *
     * @param LogEntry $logEntry
     * @return bool|string false if the GET parameter is not present, the value of the GET parameter otherwise
     */
    protected function extractGetParameter(LogEntry $logEntry)
    {
        $parametersString = parse_url($logEntry->getRequest(), PHP_URL_QUERY);
        parse_str($parametersString, $parameters);
        if (isset($parameters[$this->getParameter])) {
            return $parameters[$this->getParameter];
        }

        return false;
    }

    /**
     * Filter out LogEntries with the same host as the banned hosts from the event.
     *
     * @param LogEntriesEvent $event
     */
    public function onBanHost(LogEntriesEvent $event)
    {
        foreach ($event->getLogEntries() as $banned) {
            $this->logEntriesWithGetParameter = array_filter($this->logEntriesWithGetParameter, function (LogEntry $logEntry) use ($banned) {
                return $logEntry->getHost() !== $banned->getHost();
            });
        }
    }

    /**
     * report the number of hits on a GET parameter value
     */
    public function onReport()
    {

        $getParameterHitCount = [];
        foreach ($this->logEntriesWithGetParameter as $logEntry) {
            $value = $this->extractGetParameter($logEntry);
            if (!isset($getParameterHitCount[$value])) {
                $getParameterHitCount[$value] = 0;
            }
            $getParameterHitCount[$value]++;
        }
        ksort($getParameterHitCount);
        print_r($getParameterHitCount);
    }
}