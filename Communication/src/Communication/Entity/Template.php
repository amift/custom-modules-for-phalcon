<?php

namespace Communication\Entity;

use Common\Traits\ObjectSimpleHydrating;
use Common\Traits\MessageEntityTrait;
use Common\Traits\EnabledEntityTrait;
use Common\Traits\TitleEntityTrait;
use Common\Traits\DescriptionEntityTrait;
use Common\Traits\CreatedAtEntityTrait;
use Common\Traits\UpdatedAtEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Communication\Tool\TemplateType as Type;

/**
 * @ORM\Entity(repositoryClass="Communication\Repository\TemplateRepository")
 * @ORM\Table(
 *      name="communication_templates",
 *      indexes={
 *          @ORM\Index(name="communication_templates_enabled_idx", columns={"enabled"}),
 *          @ORM\Index(name="communication_templates_type_idx", columns={"type"}),
 *          @ORM\Index(name="communication_templates_module_idx", columns={"module"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="communication_templates_code_unique_idx", columns={"code"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Template 
{

    use ObjectSimpleHydrating;
    use EnabledEntityTrait;
    use TitleEntityTrait;
    use DescriptionEntityTrait;
    use MessageEntityTrait;
    use CreatedAtEntityTrait;
    use UpdatedAtEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var smallint
     * @ORM\Column(name="`type`", type="smallint", options={"unsigned":true})
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="`module`", type="string", length=50, nullable=false)
     */
    private $module;

    /**
     * @var string
     * @ORM\Column(name="`code`", type="string", length=150, nullable=true, options={"default":null})
     */
    private $code;

    /**
     * @ORM\Column(name="placeholders", type="json_array", nullable=true)
     */
    private $placeholders;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->placeholders = [];
    }

    /**
     * @return string
     */
    public function __toString() 
    {
        return sprintf(
            "[%s] %s", Type::getLabel($this->type), $this->title
        );
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
     * Set type
     *
     * @param smallint $type
     * @return Template
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return smallint 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set module
     *
     * @param string $module
     * @return Template
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return string 
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Template
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set placeholders
     *
     * @param array $placeholders
     * @return Template
     */
    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * Get placeholders
     *
     * @return array 
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

}
