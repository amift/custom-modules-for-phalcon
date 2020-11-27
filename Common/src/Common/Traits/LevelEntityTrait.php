<?php

namespace Common\Traits;

trait LevelEntityTrait
{

    /**
     * @var smallint
     * @ORM\Column(name="lvl", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $level = 0;

    /**
     * Set level
     * 
     * @param integer $level
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     * 
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

}
