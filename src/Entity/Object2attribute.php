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

    /**
     * @ORM\ManyToOne(targetEntity=Article::class, inversedBy="attribute")
     * @ORM\JoinColumn(nullable=false)
     */
    private $object;

    /**
     * @ORM\ManyToOne(targetEntity=Attribute::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $attr;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getObject(): ?Article
    {
        return $this->object;
    }

    public function setObject(?Article $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getAttr(): ?Attribute
    {
        return $this->attr;
    }

    public function setAttr(?Attribute $attr): self
    {
        $this->attr = $attr;

        return $this;
    }
}
