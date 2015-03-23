<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2015 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\LogFilter\Console;

use Symfony\Component\Console\Application as BaseApplication,
    Symfony\Component\Console\Input\InputDefinition,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

use Tmf\LogFilter\Console\Command\FilterCommand;

/**
 * Class Application
 *
 * @package Tmf\LogFilter\Console
 */
class Application extends BaseApplication
{
    const VERSION = 0.1;
    const NAME    = 'logfilter';

    public function __construct()
    {
        parent::__construct(static::NAME, static::VERSION);
    }

    public function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputOption('--config', '-c', InputOption::VALUE_REQUIRED, 'Specify config file to use.'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this behat version.'),
        ));
    }


    protected function getCommandName(InputInterface $input)
    {
        if (!$input->hasParameterOption(array('--config', '-c')) && !$input->getFirstArgument()) {
            return 'list';
        }

        return 'logfilter';
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getParameterOption(array('--config', '-c'));

        if (!is_readable($configFile)) {
            $configFile = 'config.yml';
        }

        $container = $this->createContainer($configFile);
        $filterCommand = new FilterCommand($container);

        $this->add($filterCommand);

        return parent::doRun($input, $output);
    }

    protected function createContainer($configFile)
    {
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator(dirname($configFile)));
        $loader->load($configFile);

        $container->addCompilerPass(new RegisterListenersPass());
        $container->compile();

        return $container;
    }
}




