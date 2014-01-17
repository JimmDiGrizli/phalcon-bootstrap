<?php
namespace GetSkyExample\FrontendModule\Providers;

use GetSky\Phalcon\AutoloadServices\Provider;
use Phalcon\Mvc\Dispatcher;

class DispatcherProvider implements Provider {

    /**
     * @return callable
     */
    public function getServices()
    {
        return function() {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('GetSkyExample\FrontendModule\Controllers');
            return $dispatcher;
        };
    }
} 