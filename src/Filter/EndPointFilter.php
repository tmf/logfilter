<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter\Filter;

use Tmf\LogFilter\Event\LogEntriesEvent;
use Tmf\LogFilter\Event\LogFilterEventListener;
use Tmf\LogFilter\LogEntry;

/**
 * Class EndPointFilter
 *
 * This EventListener will filter out any log entries not matching the configured request endpoints.
 * The endpoint patterns are Regex patterns.
 *
 * @package Tmf\LogFilter\Filter
 */
class EndPointFilter implements LogFilterEventListener
{
    /**
     * @var string the combined endpoint regex pattern
     */
    protected $endpointPattern = '';

    /**
     * @param array $endpoints array of endpoint patterns (without /^/)
     */
    public function __construct($endpoints = [])
    {
        $this->endpointPattern = $this->buildEndpointPattern($endpoints);
    }

    /**
     * Incoming log entries from the filter chain
     *
     * @param LogEntriesEvent $event
     */
    public function onProcess(LogEntriesEvent $event)
    {
        $endpointPattern = $this->getEndpointPattern();

        $event->setLogEntries(
            array_filter($event->getLogEntries(), function (LogEntry $logEntry) use ($endpointPattern) {
                $matches = [];
                preg_match($endpointPattern, $logEntry->getRequest(), $matches);

                return count($matches) > 0;
            })
        );
    }

    /**
     * nothing to report...
     */
    public function onReport()
    {

    }

    /**
     * Helper function to build a combined endpoint regex pattern from the different endpoint sub-patterns.
     *
     * @param array $endpoints
     * @return string
     */
    public function buildEndpointPattern(array $endpoints = [])
    {
        return sprintf('/^%s/', implode('|', array_map(function ($endpoint) {
            return sprintf('(%s)', $endpoint);
        }, $endpoints)));
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getEndpointPattern()
    {
        return $this->endpointPattern;
    }
}