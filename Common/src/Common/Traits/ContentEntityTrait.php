<?php

namespace Common\Traits;

trait ContentEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="`content`", type="text", nullable=true)
     */
    private $content;

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

}
