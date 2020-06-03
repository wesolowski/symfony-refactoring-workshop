<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
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
    private $cmiuuid;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $parentid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sort;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hidden;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $template;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $asy_cattype;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $crosssellingtitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $crosssellingtitle_1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $crosssellingtitle_2;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $asy_setcategory;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cat_desc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shortdesc;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $longdesc;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCmiuuid(): ?string
    {
        return $this->cmiuuid;
    }

    public function setCmiuuid(?string $cmiuuid): self
    {
        $this->cmiuuid = $cmiuuid;

        return $this;
    }

    public function getParentid(): ?string
    {
        return $this->parentid;
    }

    public function setParentid(?string $parentid): self
    {
        $this->parentid = $parentid;

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

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(?bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getAsyCattype(): ?int
    {
        return $this->asy_cattype;
    }

    public function setAsyCattype(?int $asy_cattype): self
    {
        $this->asy_cattype = $asy_cattype;

        return $this;
    }

    public function getCrosssellingtitle(): ?string
    {
        return $this->crosssellingtitle;
    }

    public function setCrosssellingtitle(?string $crosssellingtitle): self
    {
        $this->crosssellingtitle = $crosssellingtitle;

        return $this;
    }

    public function getCrosssellingtitle1(): ?string
    {
        return $this->crosssellingtitle_1;
    }

    public function setCrosssellingtitle1(?string $crosssellingtitle_1): self
    {
        $this->crosssellingtitle_1 = $crosssellingtitle_1;

        return $this;
    }

    public function getCrosssellingtitle2(): ?string
    {
        return $this->crosssellingtitle_2;
    }

    public function setCrosssellingtitle2(?string $crosssellingtitle_2): self
    {
        $this->crosssellingtitle_2 = $crosssellingtitle_2;

        return $this;
    }

    public function getAsySetcategory(): ?bool
    {
        return $this->asy_setcategory;
    }

    public function setAsySetcategory(?bool $asy_setcategory): self
    {
        $this->asy_setcategory = $asy_setcategory;

        return $this;
    }

    public function getCatDesc(): ?string
    {
        return $this->cat_desc;
    }

    public function setCatDesc(?string $cat_desc): self
    {
        $this->cat_desc = $cat_desc;

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
}
