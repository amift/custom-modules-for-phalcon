<?php

namespace Communication\Mailer;

use PHPMailer;

class DetailedPhpMailer extends PHPMailer
{

    /**
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->ErrorInfo;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueid;
    }

}