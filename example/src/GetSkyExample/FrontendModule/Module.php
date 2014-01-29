<?php
namespace GetSkyExample\FrontendModule;

use GetSky\Phalcon\Bootstrap\Module as ModuleBootstrap;
use Phalcon\Loader;

class Module extends ModuleBootstrap
{
    const DIR = __DIR__;
    const CONFIG = '/Resources/config/options.ini';
    const SERVICES = '/Resources/config/services.ini';
}