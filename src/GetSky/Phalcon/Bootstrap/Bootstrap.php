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
    const DEFAULT_CONFIG = '/Resources/config/options.ini';
    const DEFAULT_ENVIRONMENT = 'dev';
    const DEFAULT_SERVICES_PATH = '/app/{environment}/config/services.ini';

    /**
     * @var Config|null
     */
    private $config;
    /**
     * @var Config|null
     */
    private $services;
    /**
     * @var string|null
     */
    private $environment;

    /**
     * @param DiInterface $di
     * @param null|string $environment
     */
    public function __construct(DiInterface $di, $environment = null)
    {
        parent::__construct($di);
        if ($environment !== null) {
            $this->setEnvironment($environment);
        }
    }

    /**
     * @param string $path
     */
    public function setConfig($path)
    {
        $this->config = new Ini($path);
    }

    /**
     * @return Config|null
     */
    public function getConfig()
    {
        return $this->config;
    }


    /**
     * @param string $path
     */
    public function setService($path)
    {
        $this->services = new Ini($path);
    }

    /**
     * @return Config|null
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return Config|null
     */
    public function getEnvironment()
    {
        return $this->environment;
    }


    /**
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

    protected function boot()
    {
        if ($this->getConfig() === null) {
            $this->setConfig(self::DEFAULT_CONFIG);
        }

        if ($this->getEnvironment() === null) {
            $this->setEnvironment(
                $this->getConfig()->get(
                    'environment',
                    self::DEFAULT_ENVIRONMENT
                )
            );
        }

        if ($this->getServices() === null) {
            $this->setService(
                str_replace(
                    "{environment}",
                    $this->getEnvironment(),
                    $this->getConfig()->get(
                        'path.services',
                        self::DEFAULT_SERVICES_PATH
                    )
                )
            );
        }
    }

    protected function initServices()
    {
        $this->getDI()->setShared(
            'registrant',
            new Registrant($this->services)
        );
        $this->getDI()->get('registrant')->registration();
    }
} 