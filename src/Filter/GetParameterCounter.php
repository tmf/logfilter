<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter\Filter;

use Tmf\LogFilter\Event\LogFilterEventListener;
use Tmf\LogFilter\Event\LogEntriesEvent;

/**
 * Class GetParameterCounter
 *
 * @package Tmf\LogFilter\Filter
 */
class GetParameterCounter implements LogFilterEventListener
{
    private $getParameter;

    private $getParameterValueCounts = [];

    public function __construct($getParameter)
    {
        $this->getParameter = $getParameter;
    }

    public function onProcess(LogEntriesEvent $event)
    {
        foreach ($event->getLogEntries() as $logEntry) {
            $parametersString = parse_url($logEntry->getRequest(), PHP_URL_QUERY);
             parse_str($parametersString, $parameters);
            if (isset($parameters[$this->getParameter])) {
                $value = $parameters[$this->getParameter];
                if(!isset($this->getParameterValueCounts[$value])){
                    $this->getParameterValueCounts[$value] = 0;
                }
                $this->getParameterValueCounts[$value]++ ;
            }
        }
    }

    public function onReport()
    {
        asort($this->getParameterValueCounts);
        var_dump($this->getParameterValueCounts);
    }
}