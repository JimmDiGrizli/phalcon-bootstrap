<?php
namespace GetSky\Phalcon\Bootstrap;

use ___PHPSTORM_HELPERS\this;
use GetSky\Phalcon\AutoloadServices\Registrant;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Config;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{

    const DIR = __DIR__;

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
         * @var Config $options
         */
        $options = $dependencyInjector->get('options');

        $options->merge(
            new Config(
                [
                    'module-options' => new Ini(
                            $this::DIR . '/Resources/config/options.ini'
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
            new Ini($this::DIR . '/Resources/config/services.ini')
        );
        $registrant->registration();
    }
} 