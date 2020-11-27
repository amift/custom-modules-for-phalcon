<?php

namespace Common\Traits;

trait IpAddreesEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="ip_address", type="string", length=15, nullable=true, options={"default":null})
     */
    private $ipAddress;

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

}
