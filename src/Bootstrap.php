<?php
namespace GetSky\Phalcon\Bootstrap;

use GetSky\Phalcon\AutoloadServices\Registrant;
use GetSky\Phalcon\ConfigLoader\ConfigLoader;
use Phalcon\Config;
use Phalcon\DI;
use Phalcon\Loader;
use Phalcon\Mvc\Application;

/**
 * Class Bootstrap
 * @package GetSky\Phalcon\Bootstrap
 */
class Bootstrap extends Application
{

    /**
     * The path to the application configuration file
     * @var string
     */
    private $pathConfig = '../app/config/config_%environment%.ini';
    /**
     * The variable indicates the application environment
     * @var string
     */
    private $environment = 'dev';
    /**
     * Application configuration
     * @var Config|null
     */
    private $config;
    /**
     * The loader of namespace
     * @var Loader
     */
    private $loader;

    /**
     * @param DI $di
     * @param null|string $environment
     */
    public function __construct(DI $di, $environment = null)
    {
        parent::__construct($di);
        $this->loader = new Loader();
        if ($environment !== null) {
            $this->environment = $environment;
        }
    }

    /**
     * Running the application
     * @param bool $hide
     * @return string
     */
    public function run($hide = false)
    {
        $this->boot();
        $this->initModules();
        $this->initNamespace();
        $this->initServices();
        if ($hide === false) {
            return $this->handle()->getContent();
        } else {
            return 'true';
        }
    }

    /**
     * Loads the settings option and a list of services for the application
     */
    protected function boot()
    {
        $configLoader = new ConfigLoader($this->environment);
        $this->di->setShared('config-loader', $configLoader);
        $this->config = $configLoader->create($this->getPathConfig());
    }

    /**
     * The method gives way to a configuration application
     * @return string
     */
    public function getPathConfig()
    {
        return str_replace(
            "%environment%",
            $this->environment,
            $this->pathConfig
        );
    }

    /**
     * The method sets a new path configuration file
     * @param string $pathConfig
     */
    public function setPathConfig($pathConfig)
    {
        $this->pathConfig = $pathConfig;
    }

    /**
     * Initializing modules
     */
    protected function initModules()
    {
        $modules = null;

        if ($this->config !== null) {
            $modules = $this->config->get('modules', null);
        }

        if ($modules !== null) {
            $pathFile = $this->config->get('bootstrap')->get('path');
            $module = $this->config->get('bootstrap')->get('module');
            $arrayModules = [];

            foreach ($modules as $name => $namespace) {
                $path = $pathFile . str_replace('\\', '/', $namespace);
                $arrayModules[$name] = [
                    'className' => $namespace . '\\' . substr($module, 0, -4),
                    'path' => $path . '/' . $module
                ];
            }
            $this->registerModules($arrayModules);
        }
    }

    /**
     * Initializing namespace of application
     */
    protected function initNamespace()
    {
        $namespaces = $this->config->get('namespaces', null);

        if ($namespaces !== null) {
            foreach ($namespaces as $namespace => $path) {
                $this->loader->registerNamespaces([$namespace => $path], true);
            }

            $this->loader->register();
        }
    }

    /**
     * Initializing services in dependency injection
     */
    protected function initServices()
    {
        $dependencies = $this->config->get('dependencies', null);
        $this->getDI()->setShared(
            'registrant',
            new Registrant($dependencies)
        );

        $this->config->merge(new Config(['environment' => $this->environment]));
        $this->getDI()->setShared('config', $this->config);

        $this->getDI()->get('registrant')->registration();
    }

    /**
     * @return null|Loader
     */
    public function getLoader()
    {
        return $this->loader;
    }
}
