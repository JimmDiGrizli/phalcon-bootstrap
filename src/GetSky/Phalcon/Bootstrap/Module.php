<?php
namespace GetSky\Phalcon\Bootstrap;


use GetSky\Phalcon\AutoloadServices\Registrant;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;

abstract class Module implements ModuleDefinitionInterface
{

    protected $dir;

    /**
     * Registers an autoloader related to the module
     *
     */

    public function registerAutoloaders()
    {
        if ($this->dir != null) {
            $namespace = substr(
                get_class($this),
                0,
                strripos(get_class($this), '\\')
            );
            print $namespace;
            $loader = new Loader();

            $loader->registerNamespaces(array($namespace => $this->dir . '/'));

            $loader->register();

        }
    }

    /**
     * Registers an autoloader related to the module
     *
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function registerServices($dependencyInjector)
    {
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