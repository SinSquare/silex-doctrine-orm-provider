<?php

namespace SinSquare\Doctrine\Tests\Resources\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="entity_two")
 */
class EntityTwo
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    protected $name;
    /**
     * @ORM\ManyToOne(targetEntity="EntityOne", inversedBy="entityTwos")
     * @ORM\JoinColumn(name="entityone_id", referencedColumnName="id")
     */
    protected $entityOne;

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
     * Set name.
     *
     * @param string $name
     *
     * @return EntityTwo
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set entityOne.
     *
     * @param \SinSquare\Doctrine\Tests\Resources\Entity\EntityOne $entityOne
     *
     * @return EntityTwo
     */
    public function setEntityOne(\SinSquare\Doctrine\Tests\Resources\Entity\EntityOne $entityOne = null)
    {
        $this->entityOne = $entityOne;

        return $this;
    }

    /**
     * Get entityOne.
     *
     * @return \SinSquare\Doctrine\Tests\Resources\Entity\EntityOne
     */
    public function getEntityOne()
    {
        return $this->entityOne;
    }
}
