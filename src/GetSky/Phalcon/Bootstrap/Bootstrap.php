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

    /**
     * @var Config|null
     */
    private $config;

    /**
     * @var Config|null
     */
    private $envServices;

    /**
     * @var Config|null
     */
    private $mainServices;

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
     * @param string $config
     */
    public function setConfig($config)
    {
        $this->config = new Ini($config);
    }

    /**
     * @return Config|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $mainServices
     */
    public function setMainServices($mainServices)
    {
        try {
            $this->mainServices = new Ini ($mainServices);
        } catch (\Exception $e) {
        }
    }

    /**
     * @return null|\Phalcon\Config
     */
    public function getMainServices()
    {
        return $this->mainServices;
    }

    /**
     * @param string $envServices
     */
    public function setEnvServices($envServices)
    {
        $this->envServices = new Ini($envServices);
    }

    /**
     * @return Config|null
     */
    public function getEnvServices()
    {
        return $this->envServices;
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

        if ($this->getMainServices() === null) {
            $this->setMainServices(
                $this->getConfig()->get(
                    'path.main.services',
                    null
                )

            );
        }

        if ($this->getEnvServices() === null) {
            $this->setEnvServices(
                str_replace(
                    "{environment}",
                    $this->getEnvironment(),
                    $this->getConfig()->get(
                        'path.services',
                        null
                    )
                )
            );
        }

        if ($this->mainServices !== null && $this->envServices !== null) {
            $this->mainServices->merge($this->envServices);
        } elseif ($this->envServices !== null) {
            $this->mainServices = $this->envServices;
        }
    }

    protected function initServices()
    {
        $this->getDI()->setShared(
            'registrant',
            new Registrant($this->mainServices)
        );
        $this->getDI()->get('registrant')->registration();
    }
} 