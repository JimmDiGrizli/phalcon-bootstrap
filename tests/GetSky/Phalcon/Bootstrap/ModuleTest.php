<?php
namespace GetSky\Phalcon\AutoloadServices\Tests;

use GetSky\Phalcon\Bootstrap\Module;
use PHPUnit_Framework_TestCase;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    const TEST_CLASS = 'GetSky\Phalcon\Bootstrap\Module';
    /**
     * @var Module
     */
    protected $module;

    public function testIsApplication()
    {
        $this->assertInstanceOf(
            'Phalcon\Mvc\ModuleDefinitionInterface',
            $this->module
        );
    }

    protected function setUp()
    {
        $this->module = new Module();
    }

    protected function tearDown()
    {
        $this->module = null;
    }
} 