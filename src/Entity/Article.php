<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $artnum;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $parentid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $asy_packaging;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $asy_min_order;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $asy_installation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shortdesc;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $longdesc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $unitname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $asy_deltext_standard_1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $asy_deltext_standard_schweiz;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $asy_deltext_standard_2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alphabytes_variantenmerkmale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $asy_deltext_standard;

    /**
     * @ORM\OneToMany(targetEntity=Object2attribute::class, mappedBy="object")
     */
    private $attribute;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $varname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $varselect;

    public function __construct()
    {
        $this->attribute = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArtnum(): ?string
    {
        return $this->artnum;
    }

    public function setArtnum(string $artnum): self
    {
        $this->artnum = $artnum;

        return $this;
    }

    public function getParentid(): ?int
    {
        return $this->parentid;
    }

    public function setParentid(?int $parentid): self
    {
        $this->parentid = $parentid;

        return $this;
    }

    public function getAsyPackaging(): ?int
    {
        return $this->asy_packaging;
    }

    public function setAsyPackaging(?int $asy_packaging): self
    {
        $this->asy_packaging = $asy_packaging;

        return $this;
    }

    public function getAsyMinOrder(): ?int
    {
        return $this->asy_min_order;
    }

    public function setAsyMinOrder(?int $asy_min_order): self
    {
        $this->asy_min_order = $asy_min_order;

        return $this;
    }

    public function getAsyInstallation(): ?string
    {
        return $this->asy_installation;
    }

    public function setAsyInstallation(?string $asy_installation): self
    {
        $this->asy_installation = $asy_installation;

        return $this;
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

    public function getShortdesc(): ?string
    {
        return $this->shortdesc;
    }

    public function setShortdesc(?string $shortdesc): self
    {
        $this->shortdesc = $shortdesc;

        return $this;
    }

    public function getLongdesc(): ?string
    {
        return $this->longdesc;
    }

    public function setLongdesc(?string $longdesc): self
    {
        $this->longdesc = $longdesc;

        return $this;
    }

    public function getUnitname(): ?string
    {
        return $this->unitname;
    }

    public function setUnitname(?string $unitname): self
    {
        $this->unitname = $unitname;

        return $this;
    }

    public function getAsyDeltextStandard1(): ?string
    {
        return $this->asy_deltext_standard_1;
    }

    public function setAsyDeltextStandard1(?string $asy_deltext_standard_1): self
    {
        $this->asy_deltext_standard_1 = $asy_deltext_standard_1;

        return $this;
    }

    public function getAsyDeltextStandardSchweiz(): ?string
    {
        return $this->asy_deltext_standard_schweiz;
    }

    public function setAsyDeltextStandardSchweiz(?string $asy_deltext_standard_schweiz): self
    {
        $this->asy_deltext_standard_schweiz = $asy_deltext_standard_schweiz;

        return $this;
    }

    public function getAsyDeltextStandard2(): ?string
    {
        return $this->asy_deltext_standard_2;
    }

    public function setAsyDeltextStandard2(?string $asy_deltext_standard_2): self
    {
        $this->asy_deltext_standard_2 = $asy_deltext_standard_2;

        return $this;
    }

    public function getAlphabytesVariantenmerkmale(): ?string
    {
        return $this->alphabytes_variantenmerkmale;
    }

    public function setAlphabytesVariantenmerkmale(?string $alphabytes_variantenmerkmale): self
    {
        $this->alphabytes_variantenmerkmale = $alphabytes_variantenmerkmale;

        return $this;
    }

    public function getAsyDeltextStandard(): ?string
    {
        return $this->asy_deltext_standard;
    }

    public function setAsyDeltextStandard(?string $asy_deltext_standard): self
    {
        $this->asy_deltext_standard = $asy_deltext_standard;

        return $this;
    }

    /**
     * @return Collection|Object2attribute[]
     */
    public function getAttribute(): Collection
    {
        return $this->attribute;
    }

    public function addAttribute(Object2attribute $attribute): self
    {
        if (!$this->attribute->contains($attribute)) {
            $this->attribute[] = $attribute;
            $attribute->setObject($this);
        }

        return $this;
    }

    public function removeAttribute(Object2attribute $attribute): self
    {
        if ($this->attribute->contains($attribute)) {
            $this->attribute->removeElement($attribute);
            // set the owning side to null (unless already changed)
            if ($attribute->getObject() === $this) {
                $attribute->setObject(null);
            }
        }

        return $this;
    }

    public function getVarname(): ?string
    {
        return $this->varname;
    }

    public function setVarname(?string $varname): self
    {
        $this->varname = $varname;

        return $this;
    }

    public function getVarselect(): ?string
    {
        return $this->varselect;
    }

    public function setVarselect(?string $varselect): self
    {
        $this->varselect = $varselect;

        return $this;
    }
}
