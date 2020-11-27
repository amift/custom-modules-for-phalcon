<?php

namespace Common\Traits;

trait UserAgentEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="user_agent", type="string", nullable=true)
     */
    private $userAgent;

    /**
     * Set userAgent
     *
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

}
