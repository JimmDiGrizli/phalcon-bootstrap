<?php
namespace GetSky\Phalcon\AutoloadServices\Tests;

use GetSky\BackendModule\Module;
use GetSky\Phalcon\Bootstrap\Bootstrap;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Loader;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionMethod;

class BootstrapTest extends PHPUnit_Framework_TestCase
{
    const TEST_CLASS = 'GetSky\Phalcon\Bootstrap\Bootstrap';

    /**
     * @var Bootstrap
     */
    protected $bootstrap;

    public function testIsApplication()
    {
        $this->assertInstanceOf('Phalcon\Mvc\Application', $this->bootstrap);
    }

    public function testConstOfBootstrap()
    {
        $this->assertSame('Resources/config/config.ini', Bootstrap::DEFAULT_CONFIG);
        $this->assertSame('dev', Bootstrap::DEFAULT_ENVIRONMENT);
    }

    public function testChangingEnvironment()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);
        $object = $ref->newInstance(new FactoryDefault(), 'prod');

        $environment = $ref->getProperty('environment');
        $environment->setAccessible(true);

        $this->assertSame('prod', $environment->getValue($object));
    }

    public function testSetGetPathConfig()
    {
        $default = $this->bootstrap->getPathConfig();
        $this->assertSame(Bootstrap::DEFAULT_CONFIG, $default);

        $test = "test.ini";
        $this->bootstrap->setPathConfig($test);
        $this->assertSame($test, $this->bootstrap->getPathConfig());
    }

    public function testChangingEnvironmentInBootMethod()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);

        $environment = $ref->getProperty('environment');
        $environment->setAccessible(true);

        $object = $ref->newInstance(new FactoryDefault());
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');
        $method->invoke($object);
        $this->assertSame('tests', $environment->getValue($object));

        $object = $ref->newInstance(new FactoryDefault(), 'prod');
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');
        $method->invoke($object);
        $this->assertSame('prod', $environment->getValue($object));

        $object = $ref->newInstance(new FactoryDefault());
        $object->setPathConfig(
            'GetSky/Phalcon/Bootstrap/configNoEnvAndModules.ini'
        );
        $method->invoke($object);
        $this->assertSame('dev', $environment->getValue($object));
    }

    /**
     * @expectedException \GetSky\Phalcon\Bootstrap\PathNotFoundException
     */
    public function testPathNotFoundException()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);

        $object = $ref->newInstance(new FactoryDefault());
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/configException.ini');
        $method->invoke($object);
    }

    public function testBoot()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);

        $services = $ref->getProperty('services');
        $services->setAccessible(true);

        $options = $ref->getProperty('options');
        $options->setAccessible(true);

        $config = $ref->getProperty('config');
        $config->setAccessible(true);

        $object = $ref->newInstance(new FactoryDefault());
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');
        $method->invoke($object);

        $ini = new Config\Adapter\Ini('GetSky/Phalcon/Bootstrap/services.ini');
        $iniProd = new Config\Adapter\Ini(
            'GetSky/Phalcon/Bootstrap/environment/tests/config/services.ini'
        );
        $ini->merge($iniProd);
        $this->assertEquals($ini, $services->getValue($object));

        $ini = new Config\Adapter\Ini('GetSky/Phalcon/Bootstrap/options.ini');
        $iniProd = new Config\Adapter\Ini(
            'GetSky/Phalcon/Bootstrap/environment/tests/config/options.ini'
        );
        $ini->merge($iniProd);
        $this->assertEquals($ini, $options->getValue($object));

        $ini = new Config\Adapter\Ini('GetSky/Phalcon/Bootstrap/config.ini');
        $this->assertEquals($ini, $config->getValue($object));
    }

    public function testInitModules()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);

        $this->bootstrap->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);
        $method->invoke($this->bootstrap);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initModules');
        $method->setAccessible(true);
        $method->invoke($this->bootstrap);

        $this->assertEquals(
            [
                'frontend'=>
                    [
                        'className'=> 'GetSky\FrontendModule\Module',
                        'path' => '/src/GetSky/FrontendModule/Module.php'
                    ]
            ],
            $this->bootstrap->getModules()
        );

        $object = $ref->newInstance(new FactoryDefault(),'dev');
        $object->setPathConfig(
            'GetSky/Phalcon/Bootstrap/configNoEnvAndModules.ini'
        );
        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);
        $method->invoke($object);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initModules');
        $method->setAccessible(true);
        $method->invoke($object);

        $this->assertNull($object->getModules());
    }

    public function testInitNamespace()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);

        $this->bootstrap->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);
        $method->invoke($this->bootstrap);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initNamespace');
        $method->setAccessible(true);
        $method->invoke($this->bootstrap);

        $this->assertEquals(
            [
                'App\Providers' => './tests/app/Providers/',
                'App\Services' => './tests/app/Services/'
            ],
            $this->bootstrap->getLoader()->getNamespaces()
        );

        $object = $ref->newInstance(new FactoryDefault(), 'dev');
        $object->setPathConfig(
            'GetSky/Phalcon/Bootstrap/configNoEnvAndModules.ini'
        );
        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);
        $method->invoke($object);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initNamespace');
        $method->setAccessible(true);
        $method->invoke($object);

        $this->assertNull($object->getLoader()->getNamespaces());
    }

    public function testInitServices()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);

        $di = new FactoryDefault();
        $object = $ref->newInstance($di,'dev');
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);
        $method->invoke($object);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initModules');
        $method->setAccessible(true);
        $method->invoke($object);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initServices');
        $method->setAccessible(true);
        $method->invoke($object);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initNamespace');
        $method->setAccessible(true);
        $method->invoke($object);

        $this->assertInstanceOf('GetSky\Phalcon\Bootstrap\Bootstrap', $object);
    }

    protected function setUp()
    {
        $this->bootstrap = new Bootstrap(new FactoryDefault());
    }

    protected function tearDown()
    {
        $this->bootstrap = null;
    }
} 