<?php

namespace Translations\Entity;

use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Common\Traits\ObjectSimpleHydrating;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Translations\Entity\TranslationValue;

/**
 * @ORM\Entity(repositoryClass="Translations\Repository\TranslationRepository")
 * @ORM\Table(
 *      name="translations",
 *      indexes={
 *          @ORM\Index(name="translations_key_idx", columns={"key"}),
 *          @ORM\Index(name="translations_group_idx", columns={"group"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="translations_unique_idx", columns={"key", "group"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Translation 
{

    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;
    use ObjectSimpleHydrating;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="`key`", type="string", nullable=false)
     */
    private $key;

    /**
     * @var string
     * @ORM\Column(name="`group`", type="string", nullable=false)
     */
    private $group;

    /**
     * @var string
     * @ORM\Column(name="`default_value`", type="text", nullable=true)
     */
    private $defaultValue;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Translations\Entity\TranslationValue", mappedBy="translation", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"locale" = "ASC"})
     */
    private $values;

    /**
     * Class constructor
     */
    public function __construct($key, $group, $defaultValue)
    {
        $this->setKey($key);
        $this->setGroup($group);
        $this->setDefaultValue($defaultValue);
        $this->values = new ArrayCollection();
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
     * Set key
     *
     * @param string $key
     * @return Translation
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string 
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set group
     *
     * @param string $group
     * @return Translation
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return string 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set defaultValue
     *
     * @param string $defaultValue
     * @return Translation
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get defaultValue
     *
     * @return string 
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set values
     * 
     * @param $values
     * @return Translation
     */
    public function setValues($values) 
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Get values
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getValues() 
    {
        return $this->values;
    }

    /**
     * Add TranslationValue
     * 
     * @param \Translations\Entity\TranslationValue $value
     */
    public function addValue(TranslationValue $value)
    {
        if (! $this->values->contains($value)) {
            $this->values->add($value);
            $value->setTranslation($this);
        }
    }

    /**
     * Get translated value for specified locale.
     * 
     * @param string $locale
     * @param boolean $returnObject
     * @return string
     */
    public function getTranslatedValue($locale, $returnObject = false)
    {
        foreach ($this->values as $trans) {
            if ($trans->getLocale() === $locale) {
                return $returnObject ? $trans : $trans->getValue();
            }
        }

        return $returnObject ? new TranslationValue($locale) : $this->defaultValue;
    }

}
