<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */


namespace Tmf\LogFilter;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface LogFileReaderInterface
{
    public function read($logfile);

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher);
}