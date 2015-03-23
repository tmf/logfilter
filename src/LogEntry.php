<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter;

/**
 * Class LogEntry
 *
 * @package Tmf\LogFilter
 */
class LogEntry
{
    protected $host;

    protected $timestamp;

    protected $request;

    protected $userAgent;

    protected $method;

    public function __construct($host, $timestamp, $request, $userAgent, $method = 'GET')
    {
        $this->host = $host;
        $this->timestamp = $timestamp;
        $this->request = $request;
        $this->userAgent = $userAgent;
        $this->method = $method;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }
}