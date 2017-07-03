<?php

/*
console to generate entities
php tests/SinSquare/Doctrine/Tests/Resources/console.php orm:generate-entities tests
php tests/SinSquare/Doctrine/Tests/Resources/console.php orm:generate:proxies
php tests/SinSquare/Doctrine/Tests/Resources/console.php  orm:schema-tool:update --dump-sql
*/

set_time_limit(0);
$loader = require_once __DIR__.'/../../../../../vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use SinSquare\Cache\DoctrineCacheServiceProvider;
use SinSquare\Doctrine\DoctrineOrmServiceProvider;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputOption;

$app = new Application();

$app['doctrine.orm.options'] = array(
    'auto_generate_proxy_classes' => true,
    'proxy_dir' => __DIR__.'/Proxy',
    'proxy_namespace' => 'Proxies',

    'entity_managers' => array(
        'default' => array(
            'query_cache_driver' => array(
                'type' => 'array',
            ),
            'metadata_cache_driver' => array(
                'type' => 'array',
            ),
            'result_cache_driver' => array(
                'type' => 'array',
            ),
            'connection' => 'db1',
            'mappings' => array(
                array(
                    'type' => 'annotation',
                    'namespace' => 'SinSquare\\Doctrine\\Tests\\Resources\\Entity',
                    'alias' => 'TestBundle',
                    'path' => __DIR__.'/Entity',
                    'use_simple_annotation_reader' => false,
                ),
            ),
        ),
    ),
);

$app['dbs.options'] = array(
    'db1' => array(
        'driver' => 'pdo_sqlite',
        'path' => __DIR__.'/db1.db',
    ),
);

$app->register(new DoctrineServiceProvider());
$app->register(new DoctrineCacheServiceProvider());
$app->register(new DoctrineOrmServiceProvider());

$console = new ConsoleApplication();
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));

$em = $app['doctrine.orm.em'];

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
));

$console->setHelperSet($helperSet);
Doctrine\ORM\Tools\Console\ConsoleRunner::addCommands($console);

$console->run();
