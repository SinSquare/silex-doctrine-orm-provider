<?php

namespace SinSquare\Doctrine\Tests;

use SinSquare\Doctrine\Tests\Resources\Entity\EntityOne;
use SinSquare\Doctrine\Tests\Resources\Entity\EntityTwo;

class ORMProviderBasicTest extends BaseORMProviderTest
{
    public static function setUpBeforeClass()
    {
        $loader = require __DIR__.'/../../../../vendor/autoload.php';
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
    }

    protected function setUp()
    {
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
    }

    public function testEntityOp1()
    {
        $em = $this->application['doctrine.orm.em'];

        $route = sha1(uniqid());

        $entity = new EntityOne();
        $entity->setRoute($route);

        $em->persist($entity);
        $em->flush();

        $id = $entity->getId();

        $em->clear();
        unset($entity);

        $entity = $em
            ->getRepository(EntityOne::class)
            ->findOneById($id);

        $this->assertInstanceOf(EntityOne::class, $entity);

        $this->assertEquals($id, $entity->getId());
        $this->assertEquals($route, $entity->getRoute());
    }

    public function testEntityOp2()
    {
        $em = $this->application['doctrine.orm.em'];

        $route = sha1(uniqid());
        $name = sha1(uniqid());

        $entity = new EntityOne();
        $entity->setRoute($route);

        $entity2 = new EntityTwo();
        $entity2->setName($name);
        $entity2->setEntityOne($entity);

        $em->persist($entity);
        $em->persist($entity2);
        $em->flush();

        $id = $entity->getId();

        $em->clear();
        unset($entity);
        unset($entity2);

        $entity = $em
            ->getRepository(EntityOne::class)
            ->findOneById($id);

        $this->assertInstanceOf(EntityOne::class, $entity);

        $this->assertEquals($id, $entity->getId());
        $this->assertEquals($route, $entity->getRoute());

        $this->assertEquals(1, count($entity->getEntityTwos()));

        $entity2 = $entity->getEntityTwos()->first();

        $this->assertInstanceOf(EntityTwo::class, $entity2);
        $this->assertEquals($name, $entity2->getName());
    }
}
