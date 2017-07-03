<?php

namespace SinSquare\Doctrine\Tests\Resources\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="entity_one")
 * @UniqueEntity(fields={"route"}, message="Record with the given route is already defined.")
 */
class EntityOne
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @ORM\Column(name="route", type="text", nullable=false)
     * @Assert\NotNull()
     */
    protected $route;
    /**
     * @ORM\OneToMany(targetEntity="EntityTwo", mappedBy="entityOne")
     */
    protected $entityTwos;

    public function __construct()
    {
        $this->entityTwos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /* ##################################################### */
    /* ##------------------- GENERATED -------------------## */
    /* ##################################################### */

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set route.
     *
     * @param string $route
     *
     * @return EntityOne
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route.
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Add entityTwo.
     *
     * @param \SinSquare\Doctrine\Tests\Resources\Entity\EntityTwo $entityTwo
     *
     * @return EntityOne
     */
    public function addEntityTwo(\SinSquare\Doctrine\Tests\Resources\Entity\EntityTwo $entityTwo)
    {
        $this->entityTwos[] = $entityTwo;

        return $this;
    }

    /**
     * Remove entityTwo.
     *
     * @param \SinSquare\Doctrine\Tests\Resources\Entity\EntityTwo $entityTwo
     */
    public function removeEntityTwo(\SinSquare\Doctrine\Tests\Resources\Entity\EntityTwo $entityTwo)
    {
        $this->entityTwos->removeElement($entityTwo);
    }

    /**
     * Get entityTwos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntityTwos()
    {
        return $this->entityTwos;
    }
}
