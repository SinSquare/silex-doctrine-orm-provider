<?php

namespace SinSquare\Doctrine\Tests;

use Doctrine\ORM\EntityManagerInterface;

class ORMProviderConfigTest extends BaseORMProviderTest
{
    private $config;

    protected function setUp()
    {
        $this->config = array(
            'doctrine.orm.options' => array(
                'auto_generate_proxy_classes' => false,
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
                    'other_em' => array(
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
                                'alias' => 'TestBundle2',
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
    }

    public function testDefaultEntityManagerNoDefault()
    {
        $this->init($this->config);

        $em1 = $this->application['doctrine.orm.em'];
        $em2 = $this->application['doctrine.orm.ems']['default'];
        $em3 = $this->application['doctrine.orm.ems']['other_em'];

        $this->assertInstanceOf(EntityManagerInterface::class, $em1);
        $this->assertInstanceOf(EntityManagerInterface::class, $em2);
        $this->assertInstanceOf(EntityManagerInterface::class, $em3);

        $this->assertEquals($em1, $em2);
        $this->assertNotEquals($em1, $em3);
        $this->assertNotEquals($em2, $em3);
    }

    public function testDefaultEntityManager()
    {
        $config = $this->config;
        $config['doctrine.orm.options']['default_entity_manager'] = 'other_em';

        $this->init($config);

        $em1 = $this->application['doctrine.orm.em'];
        $em2 = $this->application['doctrine.orm.ems']['default'];
        $em3 = $this->application['doctrine.orm.ems']['other_em'];

        $this->assertInstanceOf(EntityManagerInterface::class, $em1);
        $this->assertInstanceOf(EntityManagerInterface::class, $em2);
        $this->assertInstanceOf(EntityManagerInterface::class, $em3);

        $this->assertEquals($em1, $em3);
        $this->assertNotEquals($em1, $em2);
        $this->assertNotEquals($em3, $em2);
    }

    public function testCustomConfig1()
    {
        $config = $this->config;
        $config['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_1' => array(
                    'type' => 'void',
                ),
                'cache_2' => array(
                    'type' => 'void',
                ),
                'cache_3' => array(
                    'type' => 'void',
                ),
                'cache_4' => array(
                    'type' => 'void',
                ),
            ),
        );

        $config['doctrine.orm.options']['entity_managers']['default']['query_cache_driver'] = array('name' => 'cache_1');
        $config['doctrine.orm.options']['entity_managers']['default']['metadata_cache_driver'] = array('name' => 'cache_2');
        $config['doctrine.orm.options']['entity_managers']['default']['result_cache_driver'] = array('name' => 'cache_3');
        $config['doctrine.orm.options']['entity_managers']['default']['hydration_cache_driver'] = array('name' => 'cache_4');

        unset($config['doctrine.orm.options']['entity_managers']['other_em']);

        $this->init($config);

        $em = $this->application['doctrine.orm.em'];
        $config = $em->getConfiguration();

        $query = $config->getQueryCacheImpl();
        $meta = $config->getMetadataCacheImpl();
        $result = $config->getResultCacheImpl();
        $hydration = $config->getHydrationCacheImpl();

        $this->assertEquals($this->application['doctrine.cache.cache_1'], $query);
        $this->assertEquals($this->application['doctrine.cache.cache_2'], $meta);
        $this->assertEquals($this->application['doctrine.cache.cache_3'], $result);
        $this->assertEquals($this->application['doctrine.cache.cache_4'], $hydration);
    }

    public function testCustomConfig2()
    {
        $config = $this->config;
        $config['doctrine.cache.options'] = array(
            'aliases' => array(
                'cache_1' => 'query_cache_driver',
                'cache_2' => 'metadata_cache_driver',
                'cache_3' => 'result_cache_driver',
                'cache_4' => 'hydration_cache_driver',
            ),
            'providers' => array(
                'cache_1' => array(
                    'type' => 'void',
                ),
                'cache_2' => array(
                    'type' => 'void',
                ),
                'cache_3' => array(
                    'type' => 'void',
                ),
                'cache_4' => array(
                    'type' => 'void',
                ),
            ),
        );

        $config['doctrine.orm.options']['entity_managers']['default']['query_cache_driver'] = array('name' => 'query_cache_driver');
        $config['doctrine.orm.options']['entity_managers']['default']['metadata_cache_driver'] = array('name' => 'metadata_cache_driver');
        $config['doctrine.orm.options']['entity_managers']['default']['result_cache_driver'] = array('name' => 'result_cache_driver');
        $config['doctrine.orm.options']['entity_managers']['default']['hydration_cache_driver'] = array('name' => 'hydration_cache_driver');

        unset($config['doctrine.orm.options']['entity_managers']['other_em']);

        $this->init($config);

        $em = $this->application['doctrine.orm.em'];
        $config = $em->getConfiguration();

        $query = $config->getQueryCacheImpl();
        $meta = $config->getMetadataCacheImpl();
        $result = $config->getResultCacheImpl();
        $hydration = $config->getHydrationCacheImpl();

        $this->assertEquals($this->application['query_cache_driver'], $query);
        $this->assertEquals($this->application['metadata_cache_driver'], $meta);
        $this->assertEquals($this->application['result_cache_driver'], $result);
        $this->assertEquals($this->application['hydration_cache_driver'], $hydration);
    }

    public function testConncetion()
    {
        $config = $this->config;

        $config['doctrine.orm.options']['entity_managers']['other_em']['connection'] = 'db2';

        $config['dbs.options']['db2'] = array(
            'driver' => 'pdo_sqlite',
            'path' => __DIR__.'/Resources/db2.db',
        );

        $this->init($config);

        $em1 = $this->application['doctrine.orm.ems']['default'];
        $em2 = $this->application['doctrine.orm.ems']['other_em'];

        $conn1 = $em1->getConnection();
        $conn2 = $em2->getConnection();

        $this->assertNotEquals($conn1, $conn2);

        $this->assertEquals($this->application['dbs']['db1'], $conn1);
        $this->assertEquals($this->application['dbs']['db2'], $conn2);
    }
}
