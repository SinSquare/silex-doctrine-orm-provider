<?php

namespace SinSquare\Doctrine\Tests;

use Silex\Provider\ValidatorServiceProvider;
use SinSquare\Doctrine\DoctrineOrmValidatorProvider;
use SinSquare\Doctrine\Tests\Resources\Entity\EntityOne;

class ORMProviderValidatorTest extends BaseORMProviderTest
{
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

        $this->application->register(new ValidatorServiceProvider());
        $this->application->register(new DoctrineOrmValidatorProvider());
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->config);
    }

    public function testValidatorRegistration()
    {
        $validator = $this->application['validator'];
        $this->assertNotNull($validator);
    }

    public function testFieldValidation()
    {
        $entity = new EntityOne();

        $validator = $this->application['validator'];

        $errors = $validator->validate($entity);

        $this->assertEquals(1, count($errors));

        foreach ($errors as $error) {
            $this->assertInstanceOf(\Symfony\Component\Validator\Constraints\NotNull::class, $error->getConstraint());
        }
    }

    public function testUniqueValidation()
    {
        $em = $this->application['doctrine.orm.em'];
        $route = sha1(uniqid());
        $entity = new EntityOne();
        $entity->setRoute($route);
        $em->persist($entity);
        $em->flush();

        $em->clear();
        unset($entity);

        $entity = new EntityOne();
        $entity->setRoute($route);

        $validator = $this->application['validator'];
        $errors = $validator->validate($entity);

        $this->assertEquals(1, count($errors));

        foreach ($errors as $error) {
            $this->assertInstanceOf(\Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity::class, $error->getConstraint());
        }
    }
}
