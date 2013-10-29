<?php
namespace GetSky\Phalcon\Bootstrap;

use GetSky\Phalcon\AutoloadServices\Registrant;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Config;
use Phalcon\DiInterface;
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
    const DEFAULT_CONFIG = '/Resources/config/options.ini';

    /**
     * Default application environment
     */
    const DEFAULT_ENVIRONMENT = 'dev';

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
     * @param DiInterface $di
     * @param null|string $environment
     */
    public function __construct(DiInterface $di, $environment = null)
    {
        parent::__construct($di);
        if ($environment !== null) {
            $this->$environment = $environment;
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
     * Running the application
     * @param bool $hide
     * @return string
     */
    public function run($hide = false)
    {
        $this->boot();
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
        $this->config = new Ini($this->getPathConfig());

        if ($this->environment === null) {
            $this->environment = $this->config->get(
                'environment',
                self::DEFAULT_ENVIRONMENT
            );
        }

        /**
         * @var Config[] $configs
         */
        $configs = [];
        foreach ($this->config->get('path') as $x => $paths) {
            foreach ($paths as $y => $path) {
                $path = str_replace("{environment}", $this->environment, $path);
                if (is_readable($path)) {
                    $ini = new Ini ($path);
                    if ($configs[$x] === null) {
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
     * Initializing services in dependency injection
     */
    protected function initServices()
    {
        $this->getDI()->setShared(
            'registrant',
            new Registrant($this->services)
        );
        $this->getDI()->setShared("options", $this->options);
        $this->getDI()->get('registrant')->registration();
    }
} 