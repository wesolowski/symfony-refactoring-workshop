<?php

namespace App\Entity;

use App\Repository\Object2attributeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=Object2attributeRepository::class)
 */
class Object2attribute
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
    private $objectid;

    /**
     * @ORM\Column(type="integer")
     */
    private $attrid;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $alpha_variantmerkmal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $value_2;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAttrid(): ?int
    {
        return $this->attrid;
    }

    public function setAttrid(int $attrid): self
    {
        $this->attrid = $attrid;

        return $this;
    }

    public function getAlphaVariantmerkmal(): ?bool
    {
        return $this->alpha_variantmerkmal;
    }

    public function setAlphaVariantmerkmal(?bool $alpha_variantmerkmal): self
    {
        $this->alpha_variantmerkmal = $alpha_variantmerkmal;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue2(): ?string
    {
        return $this->value_2;
    }

    public function setValue2(?string $value_2): self
    {
        $this->value_2 = $value_2;

        return $this;
    }
}
