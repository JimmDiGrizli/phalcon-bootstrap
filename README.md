Bootstrap component for Phalcon [![Build Status](https://travis-ci.org/JimmDiGrizli/phalcon-bootstrap.png?branch=develop)](https://travis-ci.org/JimmDiGrizli/phalcon-bootstrap) [![Dependency Status](https://www.versioneye.com/user/projects/537c890314c1582e370008db/badge.svg)](https://www.versioneye.com/user/projects/537c890314c1582e370008db)
===============================

**This component is used as a basis for [Pherlin](https://travis-ci.org/JimmDiGrizli/pherlin). I recommend to use it instead of this bootstrap.**

Run application
---------------

To launch the application, you need to execute code:

```php
$app = new Bootstrap(new FactoryDefault());
echo $app->run();
```

Pass ```true``` into a method ```app()```, if you do not want to run the handler:

```php
$app = new Bootstrap(new FactoryDefault());
echo $app->run(true);
```

Configuration file
------------------

By default, the configuration file is here ```../app/config/config_%environment%.yml```. 
```%environment%``` - environment under which the application is running.

To change the configuration file, you must use the method ```setPathConfig()```:

```php
$app = new Bootstrap(new FactoryDefault());
$app->setPathConfig('config/config.%environment%.ini');
echo $app->run();
```

If you are on the same machine run two copies of the site, you need to specify a 
different name for the application cache:

```php
$app = new Bootstrap(new FactorDefault(), 'prod', 'FestApp');
// in another application:
$app = new Bootstrap(new FactorDefault(), 'prod', 'SecondApp');
```

Environment
-----------

By default, the environment is set to `` `dev ```. To change it, pass the second 
parameter name of the desired environment.

```php
$app = new Bootstrap(new FactoryDefault(), 'prod');
```

Сaching 
-------

Bootstrap allows you to cache the application configuration. When creating object 
of class ```Bootstrap```, there is check the presence of ```apc``` or ```apcu```.
If ```APC(u)``` is found, the configuration will be cached. To disable caching, you 
should report it:

```php
$app = new Bootstrap(new FactoryDefault(), 'prod');
$app->setCacheable(false);

// check
echo $app->isCacheable();
// print: false
```

Loader
------

If you need the autoloader (```Phalcon\Loader```), you can request it from the bootstrap:

```php
$app = new Bootstrap(new FactoryDefault(), 'prod');

/**
* @var $loader Phalcon\Loader
*/
$loader = $app->getLoader();
```
