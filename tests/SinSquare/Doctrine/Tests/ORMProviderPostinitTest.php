<?php

namespace SinSquare\Doctrine\Tests;

use Silex\Provider\ValidatorServiceProvider;
use SinSquare\Doctrine\DoctrineOrmValidatorProvider;
use SinSquare\Doctrine\Tests\Resources\Entity\EntityOne;

class ORMProviderPostinitTest extends BaseORMProviderTest
{
    public static $called;

    protected function setUp()
    {
        self::$called = false;

        $this->config = array(
            'doctrine.orm.options' => array(
                'auto_generate_proxy_classes' => true,
                'proxy_dir' => __DIR__.'/Resources/Proxy',
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
                                'path' => __DIR__.'/Resources/Entity',
                                'use_simple_annotation_reader' => false,
                            ),
                        ),
                    ),
                ),
            ),
            'dbs.options' => array(
                'db1' => array(
                    'driver' => 'pdo_sqlite',
                    'path' => __DIR__.'/Resources/db1.db',
                ),
            ),
        );
        $this->init($this->config);

        $this->application->register(new ValidatorServiceProvider());
        $this->application->register(new DoctrineOrmValidatorProvider());
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->config);
    }

    public function testPostinit()
    {
        $app = $this->application;
        $this->application['doctrine.orm.em_factory.postinit'] = $this->application->protect(function ($name, $options, $manager) use ($app) {
            self::$called = true;
            return $manager;
        });

        $em1 = $this->application['doctrine.orm.em'];

        $this->assertEquals(self::$called, true);
    }

    public function testNoPostinit()
    {
        $app = $this->application;
        $em1 = $this->application['doctrine.orm.em'];

        $this->assertEquals(self::$called, false);
    }
}
