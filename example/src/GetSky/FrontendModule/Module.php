<?php
namespace GetSky\FrontendModule;

use Phalcon\Config\Adapter\Ini;
use Phalcon\Mvc\ModuleDefinitionInterface;
use GetSky\Phalcon\AutoloadServices\Registrant;

class Module implements ModuleDefinitionInterface
{

    /**
     * Registers an autoloader related to the module
     *
     */
    public function registerAutoloaders()
    {
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