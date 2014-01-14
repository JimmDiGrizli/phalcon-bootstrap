<?php
namespace GetSky\Phalcon\Bootstrap;

use GetSky\Phalcon\AutoloadServices\Registrant;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Config;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{

    protected $dir;

    /**
     * Registers an autoloader related to the module
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function registerAutoloaders($dependencyInjector)
    {
        if ($this->dir != null) {
            $namespace = substr(
                get_class($this),
                0,
                strripos(get_class($this), '\\')
            );

            $loader = new Loader();

            $loader->registerNamespaces([$namespace => $this->dir . '/']);

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
                            $this->dir . '/Resources/config/options.ini'
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
            new Ini($this->dir . '/Resources/config/services.ini')
        );
        $registrant->registration();
    }
} 