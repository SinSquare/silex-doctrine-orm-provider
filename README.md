Doctrine ORM provider for Silex 2.x framework
=======

[![Build Status](https://travis-ci.org/SinSquare/silex-doctrine-orm-provider.svg?branch=master)](https://travis-ci.org/SinSquare/silex-doctrine-orm-provider)

Installation
============

With composer :

``` json
{
    "require": {
        "sinsquare/silex-doctrine-orm-provider": "1.*"
    }
}
```
Registering the providers
====

- If you only need the ORM without validation and web profiler

```php
use Silex\Provider\DoctrineServiceProvider;
use SinSquare\Cache\DoctrineCacheServiceProvider;
use SinSquare\Doctrine\DoctrineOrmServiceProvider;

...

$application['doctrine.orm.options'] = array(<config>);

$application->register(new DoctrineServiceProvider());
$application->register(new DoctrineCacheServiceProvider());
$application->register(new DoctrineOrmServiceProvider());
```

- If you only need the ORM validation (UniqueEntity)
```php
//Register all the providers for the ORM

use Silex\Provider\ValidatorServiceProvider;
use SinSquare\Doctrine\DoctrineOrmValidatorProvider;

...

$application->register(new ValidatorServiceProvider());
$application->register(new DoctrineOrmValidatorProvider());
```

- If you only need the the ORM web profiler
```php
//Register all the providers for the ORM

use SinSquare\Doctrine\DoctrineOrmWebProfilerProvider;

...

$application->register(new DoctrineOrmWebProfilerProvider());
```

Configuration
====

The configuration of the ORM must be set in $application['doctrine.orm.options'], and it must exist before registering the provider.

A basic configuration scheme:
```php
$application['doctrine.orm.options'] = array(
    'default_entity_manager' => 'default',
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
);
```

The configuration scheme is similar to the one used in Smyfony [(read more here)](https://symfony.com/doc/current/reference/configuration/doctrine.html).

- default_entity_manager: Name of the default entity manager. If not set the first one will be the default.

- connection: Name of the DBAL connection to use. Read more at the DoctrineServiceProvider help [here](https://silex.sensiolabs.org/doc/2.0/providers/doctrine.html).

- mapping: Currently only the annotation type is supported by default, but you can extend the functionality. Look for $app['doctrine.orm.mappingdriver.locator'] in the DoctrineOrmServiceProvider.

- cache: The project uses SinSquare/silex-doctrine-cache-provider for cacheing, which is a wrapper for Doctrine Cache.

* Using anonym cache:
```php
'query_cache_driver' => array(
    'type' => 'array',
),
```
You can change the cache type, for more info check the Doctrine Cache component.
*Using named cache:
```php
$application['doctrine.cache.options'] = array(
    'providers' => array(
        'cache_1' => array(
            'type' => 'void',
        )
    ),
);

$application['doctrine.orm.options'] = array(
	...
	'result_cache_driver' => array(
        'name' => 'cache_1',
    ),
    ...
);
```

You can create new types of caches, please read how to [here](https://github.com/SinSquare/silex-doctrine-cache-provider);

Retrieving the EntityManager
=====

* The default entity manager:
```php
$em = $application['doctrine.orm.em'];
```
* A named entity manager:
```php
$em = $application['doctrine.orm.ems']['named_em'];
//OR
$em = $application['doctrine.orm.em.named_em'];
```
