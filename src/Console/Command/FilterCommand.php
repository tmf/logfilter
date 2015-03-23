<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter\Console\Command;

use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tmf\LogFilter\LogEntryProducer;

/**
 * Class DefaultCommand
 *
 * @package Tmf\LogFilter\Command
 */
class FilterCommand extends Command
{

    protected $container;


    public function __construct(ContainerBuilder $container = null)
    {
        $this->container = $container;
        parent::__construct('logfilter');
    }

    protected function configure()
    {
        $this->setName('logfilter')
             ->setDescription('Filter a web server access log file according to the specified filters set up in the container configuration')
             ->setDefinition([
                 new InputArgument('logfile', InputArgument::REQUIRED)
             ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($this->container instanceof ContainerBuilder) {
            /**
             * @var LogEntryProducer $logEntryProducer
             */
            $logEntryProducer = $this->container->get('tmf.logfilter.reader');
            $logEntryProducer->process($input->getArgument('logfile'));
        }
    }


}