<?php
namespace GetSky\Phalcon\AutoloadServices\Tests;

use GetSky\Phalcon\AutoloadServices\Registrant;
use GetSky\Phalcon\Bootstrap\Module;
use GetSky\Phalcon\ConfigLoader\ConfigLoader;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Loader;
use PHPUnit_Framework_TestCase;

/**
 * Class ModuleTest
 * @package GetSky\Phalcon\AutoloadServices\Tests
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{
    const TEST_CLASS = 'GetSky\Phalcon\Bootstrap\Module';
    /**
     * @var Module
     */
    protected $module;

    /**
     * @var FactoryDefault
     */
    protected $factory;

    public function testIsApplication()
    {
        $this->assertInstanceOf(
            'Phalcon\Mvc\ModuleDefinitionInterface',
            $this->module
        );
    }

    public function testRegisterAutoloaders()
    {
        $loader = $this->factory->get('loader');
        $this->module->registerAutoloaders($this->factory);

        $this->assertArrayHasKey('GetSkyExample\FrontendModule', $loader->getNamespaces());
    }

    public function testRegisterServices()
    {
        $this->module->registerServices($this->factory);
        $this->assertArrayHasKey(
            'volt',
            $this->factory
                ->get('config')
                ->get('modules')
                ->get('FrontendModule')
                ->get('config')
        );
        $this->assertInstanceOf(
            'Phalcon\Mvc\View',
            $this->factory->get('view')
        );
    }

    protected function setUp()
    {
        $this->module = new \GetSkyExample\FrontendModule\Module();
        $this->factory = new FactoryDefault();
        $configLoader = new ConfigLoader();
        $config = $configLoader->create('/tests/GetSky/Phalcon/bootstrap/config.ini');
        $registrant = new Registrant($config->get('dependencies'));
        $loader = new Loader();

        $this->factory->set('loader', $loader);
        $this->factory->set('registrant', $registrant);
        $this->factory->set('config-loader', $configLoader);
        $this->factory->set('config', $config);
    }

    protected function tearDown()
    {
        $this->module = null;
        $this->factory = null;
    }
} 