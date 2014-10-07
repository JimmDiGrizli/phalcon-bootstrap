<?php
namespace GetSky\Phalcon\Bootstrap\Tests;

use GetSky\Phalcon\Bootstrap\Bootstrap;
use GetSky\Phalcon\ConfigLoader\ConfigLoader;
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

    public function testChangingEnvironment()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);
        $object = $ref->newInstance(new FactoryDefault(), 'prod');

        $environment = $ref->getProperty('environment');
        $environment->setAccessible(true);

        $this->assertSame('prod', $environment->getValue($object));
    }

    public function testDefaultEnvironment()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);
        $object = $ref->newInstance(new FactoryDefault());

        $environment = $ref->getProperty('environment');
        $environment->setAccessible(true);

        $this->assertSame('dev', $environment->getValue($object));
    }

    public function testSetGetPathConfig()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);
        $object = $ref->newInstance(new FactoryDefault());

        $pathConfig = $ref->getProperty('pathConfig');
        $pathConfig->setAccessible(true);

        $test = "test.ini";
        $object->setPathConfig($test);
        $this->assertSame(
            $test,
            $object->getPathConfig()
        );
    }

    public function testBoot()
    {
        $ref = new ReflectionClass(self::TEST_CLASS);

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);

        $config = $ref->getProperty('config');
        $config->setAccessible(true);

        $object = $ref->newInstance(new FactoryDefault());
        $object->setCacheable(false);
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');
        $method->invoke($object);

        /**
         * @var $configLoader ConfigLoader
         */
        $configLoader = $object->getDI()->get('config-loader');
        $this->assertInstanceOf(
            'GetSky\Phalcon\ConfigLoader\ConfigLoader',
            $configLoader
        );

        $ini = $configLoader->create('GetSky/Phalcon/Bootstrap/config.ini');
        $ini->merge(new Config(['environment' => 'dev']));
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
                'FrontendModule' =>
                    [
                        'className' => 'GetSkyExample\FrontendModule\Module',
                        'path' => '../src/GetSkyExample/FrontendModule/Module.php'
                    ]
            ],
            $this->bootstrap->getModules()
        );

        $object = $ref->newInstance(new FactoryDefault(), 'dev');
        $object->setCacheable(false);
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
                'App\TestProviders' => './tests/app/TestProviders/',
                'App\Services' => './tests/app/Services/'
            ],
            $this->bootstrap->getLoader()->getNamespaces()
        );

        $object = $ref->newInstance(new FactoryDefault(), 'dev');
        $object->setCacheable(false);
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
        $object = $ref->newInstance($di, 'dev');
        $object->setCacheable(false);
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);
        $method->invoke($object);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initModules');
        $method->setAccessible(true);
        $method->invoke($object);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initNamespace');
        $method->setAccessible(true);
        $method->invoke($object);

        $method = new ReflectionMethod(self::TEST_CLASS, 'initServices');
        $method->setAccessible(true);
        $method->invoke($object);

        $registrant = $object->getDi()->get('registrant');
        $this->assertInstanceOf(
            'GetSky\Phalcon\AutoloadServices\Registrant',
            $registrant
        );

        /**
         * @var $config Config
         */
        $config = $object->getDi()->get('config');
        $this->assertInstanceOf('Phalcon\Config', $config);

        $this->assertSame(
            'dev',
            $config->get('environment')
        );
    }

    protected function setUp()
    {
        $this->bootstrap = new Bootstrap(new FactoryDefault());
        $this->bootstrap->setCacheable(false);
    }

    protected function tearDown()
    {
        $this->bootstrap = null;
    }
} 