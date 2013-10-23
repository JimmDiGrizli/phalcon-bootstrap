<?php
namespace GetSky\Phalcon\Bootstrap;

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

    const DEFAULT_ENVIRONMENT = 'dev';
    const DEFAULT_CONFIG = '/Resources/config/options.ini';
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
    public function setConfigIni($path)
    {
        $this->config = new Ini($path);
    }

    /**
     * @param string $path
     */
    public function setServiceIni($path)
    {
        $this->services = new Ini($path);
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->services = $environment;
    }

    /**
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
        if ($this->config === null) {
            $this->setConfigIni(self::DEFAULT_CONFIG);
        }

        if ($this->environment === null) {
            $this->environment = $this->config->get(
                'environment',
                self::DEFAULT_ENVIRONMENT
            );
        }

        if ($this->services === null) {
            $this->setServiceIni(
                str_replace(
                    "{environment}",
                    $this->environment,
                    $this->config->get(
                        'path.services',
                        self::DEFAULT_SERVICES_PATH
                    )
                )
            );
        }
    }

    protected function initServices()
    {
        /**
         * @var Config $service
         */
        foreach ($this->services as $key => $service) {
            $class = $service->get('provider');
            $param = str_replace(
                "{environment}",
                $this->environment,
                $service->get('param')
            );
            $shared = $service->get('shared', false);

            $provider = new $class($param);

            if ($provider instanceof Provider) {
                $this->getDI()[$key] = $provider->getServices();
            } else {
                if ($shared === true) {
                    $this->getDI()->setShared($key, $provider);
                } else {
                    $this->getDI()[$key] = $provider;
                }
            }
        }
    }
} 