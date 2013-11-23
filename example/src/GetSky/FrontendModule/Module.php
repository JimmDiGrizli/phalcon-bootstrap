<?php
namespace GetSky\FrontendModule;

use GetSky\Phalcon\AutoloadServices\Registrant;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{

    /**
     * Registers an autoloader related to the module
     *
     */
    public function registerAutoloaders()
    {
        $loader = new Loader();

        $loader->registerNamespaces(
            array(
                'GetSky\FrontendModule\Controllers' => __DIR__ . '/Controllers/',
                'GetSky\FrontendModule\Models' => __DIR__ . '/Models/',
                'GetSky\FrontendModule\Providers' => __DIR__ . '/Providers/'
            )
        );

        $loader->register();
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
        $registrant->setServices(new Ini('/Resources/config/services.ini'));
        $registrant->registration();
    }
}