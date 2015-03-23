<?php
/**
 * @autor Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */


namespace Tmf\LogFilter\Event;


interface LogFilterEventListener {
    public function onProcess(LogEntriesEvent $event);
    public function onReport();
}