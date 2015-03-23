<?php
/**
 * @autor Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */


namespace Tmf\LogFilter\Event;


interface LogFilterEventListener {
    /**
     * process event: process new log entries
     */
    const PROCESS = 'logfilter.process';

    /**
     * report event: report findings after all log entries have been processed
     */
    const REPORT  = 'logfilter.report';

    /**
     * ban hosts: ban any log entries with the same host as specified in the event data
     */
    const BAN_HOST  = 'logfilter.ban_host';

    /**
     * Analyze new log entries from the filter chain (produced by a log reader)
     *
     * @param LogEntriesEvent $event
     */
    public function onProcess(LogEntriesEvent $event);

    /**
     * Report any findings after the log entries have been processed
     */
    public function onReport();
}