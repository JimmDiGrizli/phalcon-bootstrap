<?php
namespace GetSky\Phalcon\AutoloadServices\Tests;

use GetSky\Phalcon\Bootstrap\Bootstrap;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use PHPUnit_Framework_TestCase;
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
        $this->assertInstanceOf(
            'Phalcon\Mvc\Application',
            $this->bootstrap
        );
    }

    public function testConstOfBootstrap()
    {
        $this->assertSame('Resources/config/config.ini', Bootstrap::DEFAULT_CONFIG);
        $this->assertSame('dev', Bootstrap::DEFAULT_ENVIRONMENT);
    }

    public function testChangingEnvironment()
    {
        $ref = new \ReflectionClass(self::TEST_CLASS);
        $object = $ref->newInstance(new FactoryDefault(),'prod');

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
        $ref = new \ReflectionClass(self::TEST_CLASS);

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);

        $environment = $ref->getProperty('environment');
        $environment->setAccessible(true);

        $object = $ref->newInstance(new FactoryDefault());
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');
        $method->invoke($object);
        $this->assertSame('tests', $environment->getValue($object));

        $object = $ref->newInstance(new FactoryDefault(),'prod');
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/config.ini');
        $method->invoke($object);
        $this->assertSame('prod', $environment->getValue($object));

        $object = $ref->newInstance(new FactoryDefault());
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/configNoEnv.ini');
        $method->invoke($object);
        $this->assertSame('dev', $environment->getValue($object));
    }

    /**
     * @expectedException \GetSky\Phalcon\Bootstrap\PathNotFoundException
     */
    public function testPathNotFoundException()
    {
        $ref = new \ReflectionClass(self::TEST_CLASS);

        $method = new ReflectionMethod(self::TEST_CLASS, 'boot');
        $method->setAccessible(true);

        $object = $ref->newInstance(new FactoryDefault());
        $object->setPathConfig('GetSky/Phalcon/Bootstrap/configException.ini');
        $method->invoke($object);
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