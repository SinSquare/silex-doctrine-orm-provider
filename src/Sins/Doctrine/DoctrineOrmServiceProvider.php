<?php

namespace SinSquare\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\Driver;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineOrmServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['doctrine.orm.em_default_options'] = array(
            'query_cache_driver' => array(
                'type' => 'array',
            ),
            'metadata_cache_driver' => array(
                'type' => 'array',
            ),
            'result_cache_driver' => array(
                'type' => 'array',
            ),
            'hydration_cache_driver' => array(
                'type' => 'array',
            ),
            'connection' => 'default',
            'class_metadata_factory_name' => ClassMetadataFactory::class,
            'default_repository_class' => EntityRepository::class,
            'auto_mapping' => false,
            'hydrators' => array(
                // An array of hydrator names
            ),
            'mappings' => array(
                // An array of mappings, which may be a bundle name or something else
            ),
            'dql' => array(
                // a collection of string functions
                'string_functions' => array(),
                // a collection of numeric functions
                'numeric_functions' => array(),
                // a collection of datetime functions
                'datetime_functions' => array(),
            ),
            // Register SQL Filters in the entity manager
            'filters' => array(
                // An array of filters
            ),
        );

        $app['doctrine.orm.default_options'] = array(
            'default_entity_manager' => null,
            'auto_generate_proxy_classes' => false,
            'proxy_dir' => null,
            'proxy_namespace' => 'Proxies',
            'entity_managers' => array(),
        );

        $app['doctrine.orm.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['doctrine.orm.options'])) {
                $app['doctrine.orm.options'] = $app['doctrine.orm.default_options'];
            } else {
                $app['doctrine.orm.options'] = array_replace($app['doctrine.orm.default_options'], $app['doctrine.orm.options']);
            }

            $tmp = $app['doctrine.orm.options'];
            foreach ($tmp['entity_managers'] as $name => &$options) {
                $options = array_replace($app['doctrine.orm.em_default_options'], $options);

                if (empty($tmp['default_entity_manager'])) {
                    $tmp['default_entity_manager'] = $name;
                    $app['doctrine.orm.default'] = $name;
                }
            }
            $app['doctrine.orm.options'] = $tmp;
        });

        $app['doctrine.orm.em.factory'] = $app->protect(function ($options) {
        });

        $app['doctrine.orm.em.config.factory'] = $app->protect(function ($options) {
        });

        $app['doctrine.orm.cache.locator'] = $app->protect(function ($cacheName, $options) use ($app) {
            if (isset($options['name'])) {
                $name = $options['name'];
                if (isset($app['doctrine.cache.'.$name])) {
                    return $app['doctrine.cache.'.$name];
                }
                if (isset($app[$name])) {
                    return $app[$name];
                }
                throw new \LogicException(sprintf("There is no cache registered with the name '%s'."));
            }

            $cacheInstanceKey = 'doctrine.orm.cache.instances.'.$cacheName;

            if (isset($app[$cacheInstanceKey])) {
                return $app[$cacheInstanceKey];
            }

            $cache = $app['doctrine.cache.locator']($cacheName, $options);

            return $app[$cacheInstanceKey] = $cache;
        });

        $app['doctrine.orm.strategy.naming'] = function ($app) {
            return new DefaultNamingStrategy();
        };

        $app['doctrine.orm.strategy.quote'] = function ($app) {
            return new DefaultQuoteStrategy();
        };

        $app['doctrine.orm.entity_listener_resolver'] = function ($app) {
            return new DefaultEntityListenerResolver();
        };

        $app['doctrine.orm.repository_factory'] = function ($app) {
            return new DefaultRepositoryFactory();
        };

        $app['doctrine.manager_registry'] = function ($app) {
            return new ManagerRegistry($app);
        };

        //entity manager config
        $app['doctrine.orm.em.config'] = $app->protect(function ($name, $options) use ($app) {
            $config = new Configuration();

            //cache
            $config->setMetadataCacheImpl(
                $app['doctrine.orm.cache.locator']('metadata', $options['metadata_cache_driver'])
            );
            $config->setQueryCacheImpl(
                $app['doctrine.orm.cache.locator']('query', $options['query_cache_driver'])
            );
            $config->setResultCacheImpl(
                $app['doctrine.orm.cache.locator']('result', $options['result_cache_driver'])
            );
            $config->setHydrationCacheImpl(
                $app['doctrine.orm.cache.locator']('hydration', $options['hydration_cache_driver'])
            );

            //proxy
            if (isset($options['proxy_dir'])) {
                $proxyDir = $options['proxy_dir'];
            } else {
                $proxyDir = $app['doctrine.orm.options']['proxy_dir'];
            }
            $config->setProxyDir($proxyDir);

            if (isset($options['proxy_namespace'])) {
                $proxyNamespace = $options['proxy_namespace'];
            } else {
                $proxyNamespace = $app['doctrine.orm.options']['proxy_namespace'];
            }
            $config->setProxyNamespace($proxyNamespace);

            if (isset($options['auto_generate_proxy_classes'])) {
                $autoProxy = $options['auto_generate_proxy_classes'];
            } else {
                $autoProxy = $app['doctrine.orm.options']['auto_generate_proxy_classes'];
            }
            $config->setAutoGenerateProxyClasses($autoProxy);

            //custom hydrator
            $config->setCustomHydrationModes($options['hydrators']);

            //custom functions
            $config->setCustomStringFunctions($options['dql']['string_functions']);
            $config->setCustomNumericFunctions($options['dql']['numeric_functions']);
            $config->setCustomDatetimeFunctions($options['dql']['datetime_functions']);

            //filters
            if (is_array($options['filters']) && count($options['filters'])) {
                foreach ($options['filters'] as $filterName => $filterClass) {
                    $config->addFilter($filterName, $filterClass);
                }
            }

            $config->setClassMetadataFactoryName($options['class_metadata_factory_name']);
            $config->setDefaultRepositoryClassName($options['default_repository_class']);

            $config->setEntityListenerResolver($app['doctrine.orm.entity_listener_resolver']);
            $config->setRepositoryFactory($app['doctrine.orm.repository_factory']);

            $config->setNamingStrategy($app['doctrine.orm.strategy.naming']);
            $config->setQuoteStrategy($app['doctrine.orm.strategy.quote']);

            $chain = new MappingDriverChain();

            foreach ((array) $options['mappings'] as $entity) {
                if (!is_array($entity)) {
                    throw new \InvalidArgumentException(
                        "The 'doctrine.orm.em.options' option 'mappings' should be a nested array."
                    );
                }

                if (isset($entity['alias'])) {
                    $config->addEntityNamespace($entity['alias'], $entity['namespace']);
                }

                $driver = $app['doctrine.orm.mappingdriver.locator']($entity, $config);
                $chain->addDriver($driver, $entity['namespace']);
            }

            $config->setMetadataDriverImpl($chain);

            return $config;
        });

        //mapping driver
        $app['doctrine.orm.mappingdriver.locator'] = $app->protect(function ($options, $config) use ($app) {
            if (!isset($options['type'])) {
                throw new \InvalidArgumentException("Mapping 'type' must be set.");
            }

            $mappingName = 'doctrine.orm.mappingdriver._'.$options['type'];
            if (!isset($app[$mappingName])) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a recognized mapping driver', $options['type']));
            }

            return $app[$mappingName]($options, $config);
        });

        $app['doctrine.orm.mappingdriver._annotation'] = $app->protect(function ($options, Configuration $config) {
            $useSimpleAnnotationReader =
                isset($options['use_simple_annotation_reader'])
                ? (bool) $options['use_simple_annotation_reader']
                : true;

            return $config->newDefaultAnnotationDriver(array($options['path']), $useSimpleAnnotationReader);
        });

        $app['doctrine.orm.em_factory'] = $app->protect(function ($name, $options) use ($app) {
            $config = $app['doctrine.orm.em.config']($name, $options);

            $em = EntityManager::create(
                $app['dbs'][$options['connection']],
                $config,
                $app['dbs.event_manager'][$options['connection']]
            );

            return $app['doctrine.orm.em_factory.postinit']($name, $options, $em);

        });

        $app['doctrine.orm.em_factory.postinit'] = $app->protect(function ($name, $options, $manager) use ($app) {
            //to be able to attach traits, etc
            return $manager;
        });

        //initilazie config
        $app['doctrine.orm.options.initializer']();

        //default entity manager
        $app['doctrine.orm.em'] = function ($app) {
            $name = 'doctrine.orm.em.'.$app['doctrine.orm.options']['default_entity_manager'];

            return $app[$name];
        };

        //named entity managers
        foreach ($app['doctrine.orm.options']['entity_managers'] as $name => $options) {
            $app['doctrine.orm.em.'.$name] = function () use ($app, $name, $options) {
                return $app['doctrine.orm.em_factory']($name, $options);
            };
        }

        //ems container
        $app['doctrine.orm.ems'] = function ($app) {
            $ems = new Container();
            foreach ($app['doctrine.orm.options']['entity_managers'] as $name => $options) {
                $ems[$name] = function () use ($app, $name, $options) {
                    return $app['doctrine.orm.em_factory']($name, $options);
                };
            }

            return $ems;
        };
    }
}
