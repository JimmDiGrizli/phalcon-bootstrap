<?php
namespace GetSky\Phalcon\Bootstrap;

use GetSky\Phalcon\AutoloadServices\Registrant;
use GetSky\Phalcon\ConfigLoader\ConfigLoader;
use Phalcon\Config;
use Phalcon\Config as BaseConfig;
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
    const CONFIG = '/Resources/config/config.yml';
    /**
     * Path to the services by default.
     */
    const SERVICES = '/Resources/config/services.yml';
    /**
     * Name of module.
     * In the custom class MUST OVERRIDE this constant.
     */
    const NAME = 'Module';
    /**
     * Use or not use the cache for module settings and configuration of services
     * @var bool
     */
    protected $cacheable = true;

    public function __construct()
    {
        if ($this->cacheable === true) {
            if (extension_loaded('apc') || extension_loaded('apcu')) {
                $this->cacheable = true;
            } else {
                $this->cacheable = false;
            }
        }
    }

    /**
     * Registers an autoloader related to the module
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function registerAutoloaders($dependencyInjector)
    {
        if ($this::DIR != null) {
            $namespace = substr(get_class($this), 0, strripos(get_class($this), '\\'));
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

        if ($settings->get('config', false) == false) {
            $settings->offsetSet(
                'config',
                $this->loadConfig($this::DIR . $this::CONFIG, $configLoader)
            );
        }

        if ($settings->get('global_services') == false) {
            /**
             * @var Registrant $registrant
             */
            $registrant = $dependencyInjector->get('registrant');
            $registrant->setServices(
                $this->loadConfig($this::DIR . $this::SERVICES, $configLoader)
            );
            $registrant->registration();
        }
    }

    private function loadConfig($path, ConfigLoader $configLoader)
    {
        $id = md5($this::DIR . $this::NAME . $path);
        $cache = null;

        if ($this->cacheable === true) {
            $cache = apc_fetch($id);
        }

        if ($cache === false || $cache === null) {
            $config = $configLoader->create($path);

            if ($cache === false) {
                apc_add($id, $config);
            }
        } else {
            $config = $cache;
        }

        return $config;
    }
}
