<?php

namespace SinSquare\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\ORM\ORMException;
use Pimple\Container;

class ManagerRegistry implements ManagerRegistryInterface
{
    protected $container;

    protected $connections;

    protected $defaultConnectionName;

    protected $managers;

    protected $defaultManagerName;

    protected $proxyInterfaceName;

    public function __construct($container, $proxyInterfaceName = 'Doctrine\ORM\Proxy\Proxy')
    {
        $this->container = $container;
        $this->proxyInterfaceName = $proxyInterfaceName;
    }

    public function getDefaultConnectionName()
    {
        $this->loadConnections();

        return $this->defaultConnectionName;
    }

    public function getConnection($name = null)
    {
        $this->loadConnections();

        $name = $this->validateName(
            $this->connections,
            $name,
            $this->getDefaultConnectionName())
        ;

        return $this->connections[$name];
    }

    public function getConnections()
    {
        $this->loadConnections();

        if ($this->connections instanceof Container) {
            $connections = array();
            foreach ($this->getConnectionNames() as $name) {
                $connections[$name] = $this->connections[$name];
            }
            $this->connections = $connections;
        }

        return $this->connections;
    }

    public function getConnectionNames()
    {
        $this->loadConnections();

        if ($this->connections instanceof Container) {
            return $this->connections->keys();
        } else {
            return array_keys($this->connections);
        }
    }

    protected function loadConnections()
    {
        if (is_null($this->connections)) {
            $this->connections = $this->container['dbs'];
            $this->defaultConnectionName = $this->container['dbs.default'];
        }
    }

    public function getDefaultManagerName()
    {
        $this->loadManagers();

        return $this->defaultManagerName;
    }

    public function getManager($name = null)
    {
        $this->loadManagers();
        $name = $this->validateManagerName($name);

        return $this->managers[$name];
    }

    protected function validateManagerName($name)
    {
        return $this->validateName(
            $this->managers,
            $name,
            $this->getDefaultManagerName())
        ;
    }

    protected function validateName($data, $name, $default)
    {
        if ($name === null) {
            $name = $default;
        }

        if (!isset($data[$name])) {
            throw new \InvalidArgumentException(sprintf('Element named "%s" does not exist.', $name));
        }

        return $name;
    }

    public function getManagers()
    {
        $this->loadManagers();

        if ($this->managers instanceof Container) {
            $managers = array();
            foreach ($this->getManagerNames() as $name) {
                $managers[$name] = $this->managers[$name];
            }
            $this->managers = $managers;
        }

        return $this->managers;
    }

    public function getManagerNames()
    {
        $this->loadManagers();

        if ($this->managers instanceof Container) {
            return $this->managers->keys();
        } else {
            return array_keys($this->managers);
        }
    }

    public function resetManager($name = null)
    {
        $this->loadManagers();
        $name = $this->validateManagerName($name);

        $this->managers[$name] = null;
    }

    protected function loadManagers()
    {
        if (is_null($this->managers)) {
            $this->managers = $this->container['doctrine.orm.ems'];
            $this->defaultManagerName = $this->container['doctrine.orm.default'];
        }
    }

    public function getAliasNamespace($alias)
    {
        foreach ($this->getManagerNames() as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
                // throw the exception only if no manager can solve it
            }
        }
        throw ORMException::unknownEntityNamespace($alias);
    }

    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObject);
    }

    public function getManagerForClass($class)
    {
        $proxyClass = new \ReflectionClass($class);
        if ($proxyClass->implementsInterface($this->proxyInterfaceName)) {
            $class = $proxyClass->getParentClass()->getName();
        }

        foreach ($this->getManagerNames() as $managerName) {
            if (!$this->getManager($managerName)->getMetadataFactory()->isTransient($class)) {
                return $this->getManager($managerName);
            }
        }
    }
}
