<?php

namespace SinSquare\Doctrine\Tests;

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use SinSquare\Cache\DoctrineCacheServiceProvider;
use SinSquare\Doctrine\DoctrineOrmServiceProvider;

abstract class BaseORMProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $application;

    public static function setUpBeforeClass()
    {
        $loader = require __DIR__.'/../../../../vendor/autoload.php';
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
    }

    protected function init($config)
    {
        $this->application = new Application($config);

        $this->application->register(new DoctrineServiceProvider());
        $this->application->register(new DoctrineCacheServiceProvider());
        $this->application->register(new DoctrineOrmServiceProvider());
    }

    protected function tearDown()
    {
        unset($this->application);
    }
}
