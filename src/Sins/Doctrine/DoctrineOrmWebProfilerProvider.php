<?php

namespace SinSquare\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Twig\DoctrineExtension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineOrmWebProfilerProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        if (isset($app['profiler'])) {
            $app['twig'] = $app->extend('twig', function (\Twig_Environment $twig) {
                $twig->addExtension(new DoctrineExtension());

                return $twig;
            });
            $app['twig.loader.filesystem'] = $app->extend('twig.loader.filesystem',
                function (\Twig_Loader_Filesystem $twigLoaderFilesystem) {
                    $twigLoaderFilesystem->addPath(__DIR__.'/../Resources/views/ORMWebProfiler', 'DoctrineWebProfilerProvider');

                    return $twigLoaderFilesystem;
                }
            );
            $app['data_collectors'] = $app->extend('data_collectors',
                function (array $collectors) use ($app) {
                    if (isset($app['doctrine.orm.em'])) {
                        $app['doctrine.orm.logger'] = $app->factory(function ($app) {
                            return new DbalLogger($app['monolog'], $app['stopwatch']);
                        });
                        $collectors['db'] = function ($app) {
                            $dataCollector = new DataCollector($app['doctrine.manager_registry']);
                            foreach ($app['doctrine.manager_registry']->getConnectionNames() as $name) {
                                $logger = $app['doctrine.orm.logger'];
                                $app['doctrine.manager_registry']->getConnection($name)->getConfiguration()->setSQLLogger($logger);
                                $dataCollector->addLogger($name, $logger);
                            }

                            return $dataCollector;
                        };
                    }

                    return $collectors;
                }
            );
            $app['data_collector.templates'] = $app->extend('data_collector.templates',
                function (array $dataCollectorTemplates) use ($app) {
                    if (isset($app['doctrine.orm.em'])) {
                        $dataCollectorTemplates[] = array('db', '@DoctrineWebProfilerProvider/db.html.twig');
                    }

                    return $dataCollectorTemplates;
                }
            );
        }
    }
}
