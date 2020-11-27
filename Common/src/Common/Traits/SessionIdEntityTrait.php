<?php

namespace Common\Traits;

trait SessionIdEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="session_id", type="string", length=50, nullable=true)
     */
    private $sessionId;

    /**
     * Set sessionId
     *
     * @param string $sessionId
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

}
