<?php

namespace App\Entity;

use App\Repository\AttributeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttributeRepository::class)
 */
class Attribute
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $swffexporttoff;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $swffexporttitle;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pos;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $displayinbasket;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cmiuuid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $unit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $variant_attribute_sort;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSwffexporttoff(): ?string
    {
        return $this->swffexporttoff;
    }

    public function setSwffexporttoff(?string $swffexporttoff): self
    {
        $this->swffexporttoff = $swffexporttoff;

        return $this;
    }

    public function getSwffexporttitle(): ?string
    {
        return $this->swffexporttitle;
    }

    public function setSwffexporttitle(?string $swffexporttitle): self
    {
        $this->swffexporttitle = $swffexporttitle;

        return $this;
    }

    public function getPos(): ?int
    {
        return $this->pos;
    }

    public function setPos(?int $pos): self
    {
        $this->pos = $pos;

        return $this;
    }

    public function getDisplayinbasket(): ?bool
    {
        return $this->displayinbasket;
    }

    public function setDisplayinbasket(?bool $displayinbasket): self
    {
        $this->displayinbasket = $displayinbasket;

        return $this;
    }

    public function getCmiuuid(): ?string
    {
        return $this->cmiuuid;
    }

    public function setCmiuuid(string $cmiuuid): self
    {
        $this->cmiuuid = $cmiuuid;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getVariantAttributeSort(): ?int
    {
        return $this->variant_attribute_sort;
    }

    public function setVariantAttributeSort(?int $variant_attribute_sort): self
    {
        $this->variant_attribute_sort = $variant_attribute_sort;

        return $this;
    }
}
