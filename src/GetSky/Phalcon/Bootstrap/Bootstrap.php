<?php
namespace GetSky\Phalcon\Bootstrap;

use GetSky\Phalcon\AutoloadServices\Registrant;
use Phalcon\Config\Adapter\Ini;
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
     * Default path of the application configuration file
     */
    const DEFAULT_CONFIG = 'Resources/config/config.ini';

    /**
     * Default application environment
     */
    const DEFAULT_ENVIRONMENT = 'dev';

    /**
     * Default application environment
     */
    const DEFAULT_CONFIG_NAME = 'options';

    /**
     * The path to the application configuration file
     * @var string|null
     */
    private $pathConfig;

    /**
     * The variable indicates the application environment
     * @var string|null
     */
    private $environment;

    /**
     * Application configuration
     * @var Config|null
     */
    private $config;

    /**
     * The application configuration
     * @var Config|null
     */
    private $options;

    /**
     * The configuration of services for the dependency injection
     * @var Config|null
     */
    private $services;

    /**
     * The loader of namespace
     * @var Loader|null
     */
    private $loader;

    /**
     * @param DI $di
     * @param null|string $environment
     */
    public function __construct(DI $di, $environment = null)
    {
        parent::__construct($di);
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
     * @throw PathNotFoundException
     */
    protected function boot()
    {
        $this->config = new Ini($this->getPathConfig());

        if ($this->environment === null) {
            $this->environment = $this->config->get(
                'environment',
                self::DEFAULT_ENVIRONMENT
            );
        }

        if (count($this->config->get('path')) === 0) {
            throw new PathNotFoundException('Not found paths in config file');
        }

        /**
         * @var Config[] $configs
         */
        $configs = [];
        foreach ($this->config->get('path') as $x => $paths) {
            foreach ($paths as $path) {
                $path = str_replace("{environment}", $this->environment, $path);
                if (is_readable($path)) {
                    $ini = new Ini ($path);
                    if (!isset($configs[$x])) {
                        $configs[$x] = $ini;
                    } else {
                        $configs[$x]->merge($ini);
                    }
                }
            }
            $this->$x = $configs[$x];
        }

    }

    /**
     * The method gives way to a configuration application
     * @return string
     */
    public function getPathConfig()
    {
        if ($this->pathConfig === null) {
            return $this::DEFAULT_CONFIG;
        } else {
            return $this->pathConfig;
        }
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

        if ($this->options !== null) {
            $modules = $this->options->get('modules', null);
        }

        if ($modules !== null) {
            $pathFile = $this->config->get('modules')->get('path');
            $module = $this->config->get('modules')->get('module');
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
        if ($this->loader === null) {
            $this->loader = new Loader();
        }

        $namespace = $this->config->get('app', null);

        if ($namespace !== null) {
            foreach ($this->config->get('app') as $namespace => $path) {
                $this->loader->registerNamespaces([$namespace => $path], true);
            }

            $this->loader->register();
        }
    }

    /**
     * @return null|Loader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Initializing services in dependency injection
     */
    protected function initServices()
    {
        $this->getDI()->setShared(
            'registrant',
            new Registrant($this->services)
        );

        $this->options->merge(
            new Config(
                [
                    'app-status' => [
                        'environment' => $this->environment,
                        'config' => $this->config
                    ]
                ]
            )
        );

        $configName = $this->config->get(
            'config-name',
            $this::DEFAULT_CONFIG_NAME
        );

        $this->getDI()->setShared(
            $configName,
            $this->options
        );
        $this->getDI()->get('registrant')->registration();
    }
} 