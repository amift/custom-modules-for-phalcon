<?php

namespace Common\Traits;

trait MessageEntityTrait
{

    /**
     * @var string
     * @ORM\Column(name="msg_from_name", type="string", nullable=true)
     */
    private $fromName;

    /**
     * @var string
     * @ORM\Column(name="msg_from_email", type="string", nullable=true)
     */
    private $fromEmail;

    /**
     * @var string
     * @ORM\Column(name="msg_subject", type="string", nullable=true)
     */
    private $subject;

    /**
     * @var string
     * @ORM\Column(name="msg_body", type="text", nullable=false)
     */
    private $body;

    /**
     * Set fromName
     *
     * @param string $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Get fromName
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set fromEmail
     *
     * @param string $fromEmail
     * @return $this
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * Get fromEmail
     *
     * @return string 
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

}
