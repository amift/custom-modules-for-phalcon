<?php

namespace Translations\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Doctrine\ORM\Mapping as ORM;
use Translations\Entity\Translation;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="translations_values",
 *      indexes={
 *          @ORM\Index(name="translations_values_locale_idx", columns={"locale"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="translations_values_unique_idx", columns={"translation_id", "locale"}),
 *      }
 * )
 */
class TranslationValue 
{

    use ObjectSimpleHydrating;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Translation
     * @ORM\ManyToOne(targetEntity="Translations\Entity\Translation", inversedBy="values")
     * @ORM\JoinColumn(name="translation_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $translation;

    /**
     * @var string
     * @ORM\Column(name="`locale`", type="string", length=2, nullable=false)
     */
    private $locale;

    /**
     * @var string
     * @ORM\Column(name="`value`", type="text", nullable=true)
     */
    private $value;

    /**
     * Class constructor
     */
    public function __construct($locale, $value)
    {
        $this->locale = $locale;
        $this->value = $value;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set translation 
     *
     * @param Translation $translation
     * @return TranslationValue
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Get translation
     *
     * @return Translation
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return TranslationValue
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string 
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return TranslationValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

}
