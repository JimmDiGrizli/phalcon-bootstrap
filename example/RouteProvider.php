<?php

use GetSky\Phalcon\Bootstrap\Provider;
use Phalcon\Mvc\Router;

class RouteProvider implements Provider
{

    /**
     * @return callable
     */
    public function getServices()
    {
        return function () {
            $router = new Router();
            return $router;
        };
    }
} 