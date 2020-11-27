<?php

namespace Common\Traits;

use Common\Traits\IpAddreesEntityTrait;
use Common\Traits\SessionIdEntityTrait;
use Common\Traits\UserAgentEntityTrait;

trait LoginDataEntityTrait
{

    use IpAddreesEntityTrait;
    use SessionIdEntityTrait;
    use UserAgentEntityTrait;

    /**
     * @var \DateTime
     * @ORM\Column(name="`date`", type="datetime", nullable=true)
     */
    private $date;

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

}