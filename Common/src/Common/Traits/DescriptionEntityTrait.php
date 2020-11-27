<?php

namespace Common\Traits;

trait DescriptionEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="`description`", type="string", nullable=true)
     */
    private $description;

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

}
