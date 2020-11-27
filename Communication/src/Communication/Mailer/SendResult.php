<?php

namespace Communication\Mailer;

use Common\Traits\ObjectSimpleCallRewrite;
use Common\Traits\ObjectSimpleHydrating;

class SendResult 
{

    use ObjectSimpleCallRewrite;
    use ObjectSimpleHydrating;

    /**
     * @var boolean
     */
    private $success;

    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var \DateTime
     */
    private $sentAt;

    /**
     * Class constructor
     * 
     * @access public
     * @param boolean $success
     * @param string $error
     * @param string $uniqueId
     */
    public function __construct($success = false, $error = '', $uniqueId = '') 
    {
        $this->success = $success;
        $this->error = $error;
        $this->uniqueId = $uniqueId;
        $this->sentAt = new \DateTime('now');
    }

    /**
     * Check if mail was sent
     * 
     * @access public
     * @return boolean
     */
    public function isSuccess() 
    {
        return $this->success === true ? true : false;
    }

}