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
 * @package Tmf\LogFilter\Filter
 */
class EndPointFilter implements LogFilterEventListener
{

    protected $endpointPattern = '';

    public function __construct($endpoints = [])
    {

        $this->endpointPattern = $this->buildEndpointPattern($endpoints);
    }

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

    public function onReport()
    {

    }

    public function buildEndpointPattern(array $endpoints = [])
    {
        return sprintf('/^%s/', implode('|', array_map(function ($endpoint) {
            return sprintf('(%s)', $endpoint);
        }, $endpoints)));
    }

    /**
     * @return string
     */
    public function getEndpointPattern()
    {
        return $this->endpointPattern;
    }


}