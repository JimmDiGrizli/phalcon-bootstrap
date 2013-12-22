<?php
namespace GetSky\Phalcon\AutoloadServices\Tests;

use GetSky\Phalcon\Bootstrap\Bootstrap;
use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use PHPUnit_Framework_TestCase;

class BootstrapTest extends PHPUnit_Framework_TestCase
{

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

    public function testSetGetPathConfig()
    {
        $default = $this->bootstrap->getPathConfig();
        $this->assertSame(Bootstrap::DEFAULT_CONFIG, $default);

        $test = "test.ini";
        $this->bootstrap->setPathConfig($test);
        $this->assertSame($test, $this->bootstrap->getPathConfig());
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