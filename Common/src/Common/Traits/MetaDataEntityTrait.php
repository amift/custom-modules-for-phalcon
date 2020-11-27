<?php

namespace Common\Traits;

trait MetaDataEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="seo_title", type="string", nullable=true)
     */
    private $seoTitle;

    /**
     * @var string
     * @ORM\Column(name="seo_description", type="string", nullable=true)
     */
    private $seoDescription;

    /**
     * @var string
     * @ORM\Column(name="seo_keywords", type="string", nullable=true)
     */
    private $seoKeywords;

    /**
     * Set seoTitle
     *
     * @param string $seoTitle
     * @return $this
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    /**
     * Get seoTitle
     *
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * Set seoDescription
     *
     * @param string $seoDescription
     * @return $this
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * Get seoDescription
     *
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * Set seoKeywords
     *
     * @param string $seoKeywords
     * @return $this
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;

        return $this;
    }

    /**
     * Get seoKeywords
     *
     * @return string
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

}
