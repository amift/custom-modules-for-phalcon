<?php

namespace Common\Traits;

trait CreatedFromIpEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="created_from_ip", type="string", length=15, nullable=true, options={"default":null})
     */
    private $createdFromIp = '';

    /**
     * Set createdFromIp
     *
     * @param string $createdFromIp
     * @return $this
     */
    public function setCreatedFromIp($createdFromIp)
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    /**
     * Get createdFromIp
     *
     * @return string
     */
    public function getCreatedFromIp()
    {
        return $this->createdFromIp;
    }

}
