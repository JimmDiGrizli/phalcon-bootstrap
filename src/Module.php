<?php
namespace GetSky\Phalcon\Bootstrap;

use GetSky\Phalcon\AutoloadServices\Registrant;
use GetSky\Phalcon\ConfigLoader\ConfigLoader;
use Phalcon\Config as BaseConfig;
use Phalcon\Config;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;

/**
 * Parent class for all custom modules
 *
 * Class Module
 * @package GetSky\Phalcon\Bootstrap
 */
class Module implements ModuleDefinitionInterface
{

    /**
     * Hack to fix the folder custom module.
     * In the custom class MUST OVERRIDE this constant.
     */
    const DIR = __DIR__;
    /**
     * Path to the default settings.
     */
    const CONFIG = '/Resources/config/config.ini';
    /**
     * Path to the services by default.
     */
    const SERVICES = '/Resources/config/services.ini';
    /**
     * Name of module.
     * In the custom class MUST OVERRIDE this constant.
     */
    const NAME = 'Module';

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

            $loader = $dependencyInjector->get('loader');
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
         * @var $settings Config
         */
        $options = $dependencyInjector->get('config');
        $settings = $options->get('modules')->get($this::NAME);
        /**
         * @var $configLoader ConfigLoader
         */
        $configLoader = $dependencyInjector->get('config-loader');

        if ($settings->get('config') == false) {

            $options->merge(
                new BaseConfig(
                    [
                        'module-options' => [
                            $this::NAME => $configLoader->create(
                                $this::DIR . $this::CONFIG
                            )
                        ]
                    ]
                )
            );

            $dependencyInjector->setShared('config', $options);
        }

        if ($settings->get('services') == false) {
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
}
