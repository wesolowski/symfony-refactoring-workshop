<?php

namespace App\Entity;

use App\Repository\Object2categoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=Object2categoryRepository::class)
 */
class Object2category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $catnid;

    /**
     * @ORM\Column(type="integer")
     */
    private $objectid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCatnid(): ?int
    {
        return $this->catnid;
    }

    public function setCatnid(int $catnid): self
    {
        $this->catnid = $catnid;

        return $this;
    }

    public function getObjectid(): ?int
    {
        return $this->objectid;
    }

    public function setObjectid(int $objectid): self
    {
        $this->objectid = $objectid;

        return $this;
    }
}
