<?php

namespace Common\Traits;

trait KeyEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="`key`", type="string", length=50, nullable=true)
     */
    private $key;

    /**
     * Set key
     *
     * @param string $key
     * @return $this
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

}
