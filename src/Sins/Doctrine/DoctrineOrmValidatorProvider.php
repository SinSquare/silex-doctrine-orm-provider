<?php

namespace SinSquare\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Bridge\Doctrine\Validator\DoctrineInitializer;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;

class DoctrineOrmValidatorProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        if (!isset($app['validator'])) {
            throw new \LogicException(sprintf("You have to register the 'ValidatorServiceProvider' before registering 'DoctrineOrmValidatorProvider'."));
        }

        $app['doctrine.orm.validator.unique_validator'] = function ($app) {
            return new UniqueEntityValidator($app['doctrine.manager_registry']);
        };
        if (!isset($app['validator.validator_service_ids'])) {
            $app['validator.validator_service_ids'] = array();
        }
        $app['validator.validator_service_ids'] = array_merge(
            $app['validator.validator_service_ids'],
            array('doctrine.orm.validator.unique' => 'doctrine.orm.validator.unique_validator')
        );
        $app['validator.object_initializers'] = $app->extend('validator.object_initializers', function (array $objectInitializers) use ($app) {
            $objectInitializers[] = new DoctrineInitializer($app['doctrine.manager_registry']);

            return $objectInitializers;
        });

        $app['validator.loader.annotation'] = function ($app) {
            return new AnnotationLoader(new AnnotationReader());
        };

        $app['validator.loaders'] = function ($app) {
            $loaders = array();
            $annotationLoader = $app['validator.loader.annotation'];
            if (null !== $annotationLoader) {
                $loaders[] = $annotationLoader;
            }

            return $loaders;
        };

        $app['validator.mapping.class_metadata_factory'] = function ($app) {
            $loaders = $app['validator.loaders'];

            return new LazyLoadingMetadataFactory(
                new LoaderChain($loaders)
            );
        };
    }
}
