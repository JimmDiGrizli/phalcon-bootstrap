<?php
namespace GetSky\Phalcon\Bootstrap;

use GetSky\Phalcon\AutoloadServices\Registrant;
use GetSky\Phalcon\ConfigLoader\ConfigLoader;
use Phalcon\Config as BaseConfig;
use Phalcon\Config;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{

    const DIR = __DIR__;
    const CONFIG = '/Resources/config/options.ini';
    const SERVICES = '/Resources/config/services.ini';

    /**
     * Registers an autoloader related to the module
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function registerAutoloaders($dependencyInjector)
    {
        if ($this::DIR != null) {
            $namespace = substr(
                get_class($this),
                0,
                strripos(get_class($this), '\\')
            );

            $loader = new Loader();

            $loader->registerNamespaces([$namespace => $this::DIR . '/']);

            $loader->register();

        }
    }

    /**
     * Registers an autoloader related to the module
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function registerServices($dependencyInjector)
    {
        /**
         * @var $options Config
         */
        $options = $dependencyInjector->get('options');

        /**
         * @var $configLoader ConfigLoader
         */
        $configLoader = $dependencyInjector->get('config-loader');

        $options->merge(
            new BaseConfig(
                [
                    'module-options' => $configLoader->create(
                            $this::DIR . $this::CONFIG
                        )
                ]
            )
        );
        $dependencyInjector->setShared('options', $options);

        /**
         * @var Registrant $registrant
         */
        $registrant = $dependencyInjector->get('registrant');
        $registrant->setServices(
            $configLoader->create($this::DIR . $this::SERVICES)
        );
        $registrant->registration();
    }
} 