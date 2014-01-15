<?php
error_reporting(E_ALL);
require_once __DIR__ . '/../../vendor/autoload.php';

(new \Phalcon\Debug())->listen(true, true);

use GetSky\Phalcon\Bootstrap\Bootstrap;
use Phalcon\DI\FactoryDefault;

try {

    $app = new Bootstrap(new FactoryDefault());
    echo $app->run();
    echo '<pre>';
    $di = $app->getDI();

    print_r(
        'environment: ' .
        $di->get('options')->get('app-status')->get('environment') .
        '<br>'
    );

    var_dump($di->getService('router'));
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