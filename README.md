LogFilter: a extensible log processor
=====================================

Logfilter is PHP CLI application which consumes log entries from a web server's access log. 
Each log entry is then processed in a filter chain implemented with the Symfony EventDispatcher component, where you can add your own filter's with a container service definition (config.yml).
The idea is that log entries can be passed along the filter chain (or withheld). Each filter (an EventListener) can also influence the propagation of the logentries or dispatch new events (like banning a host).

Usage
-----
1. Configure the filter chain with config.yml: declare your EventListener's as Services, order by inverse with priority parameter in the tag.
    ```yaml`
    # ... event dispatcher, other services under the 'services' key
    
        tmf.logfilter.endpoint:
            class: Tmf\LogFilter\Filter\EndPointFilter
            arguments:
                endpoints:
                    - "\\/test"
            tags:
                - { priority: 10, name: kernel.event_listener, event: logfilter.process, method: onProcess }
        
        tmf.logfilter.requestabuse:
            class: Tmf\LogFilter\Filter\RequestAbuseFilter
            arguments:
                window: 5
                maxHits: 3
                eventDispatcher: @event_dispatcher
            tags:
                - { priority: 9, name: kernel.event_listener, event: logfilter.process, method: onProcess }
                - { name: kernel.event_listener, event: logfilter.report, method: onReport }
                - { name: kernel.event_listener, event: logfilter.ban_host, method: onBanHost }
        
        tmf.logfilter.getparameter:
            class: Tmf\LogFilter\Filter\GetParameterCounter
            arguments:
                getParameter: "asdf"
            tags:
                - { priority: 8, name: kernel.event_listener, event: logfilter.process, method: onProcess }
                - { name: kernel.event_listener, event: logfilter.report, method: onReport }
                - { name: kernel.event_listener, event: logfilter.ban_host, method: onBanHost }
        
    ```
2. Run the logfilter with
    (not yet implemented)
    
    ```bash
        ./bin/logfilter --config=config.yml access.log
    ```
    
Extend
------
You can tie in your own filters (like a UserAgent filter, or a Datacenter IP-Block filter). 
EventListeners will receive a LogEntriesEvent event, whenever a 'logfilter.process' or 'logfilter.ban_host' event is dispatched.
The LogEntriesEvent can have one or multiple LogEntry objects (multiple: when log entries are withheld)