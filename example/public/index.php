<?php
require_once __DIR__ . '/../../vendor/autoload.php';

(new \Phalcon\Debug())->listen(true, true);

use GetSky\Phalcon\Bootstrap\Bootstrap;
use Phalcon\DI\FactoryDefault;

try {
    $app = new Bootstrap(new FactoryDefault());
    echo $app->run('false');
    echo '<pre>';
    $di = $app->getDI();
    var_dump($di->getService('route'));
    echo '<br>';
    var_dump($di->getService('request'));
    echo '<br>';
    var_dump($di->getService('response'));
    echo '<br>';
    echo '</pre>';
} catch (Phalcon\Exception $e) {
    echo $e->getMessage() . '<br />';
    echo $e->getTraceAsString();
} catch (PDOException $e) {
    echo $e->getMessage();
}