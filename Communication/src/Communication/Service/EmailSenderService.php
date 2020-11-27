<?php

namespace Communication\Service;

use Communication\Entity\Notification;
use Communication\Mailer\DetailedPhpMailer;
use Communication\Mailer\SendResult;
use Core\Library\AbstractLibrary;

class EmailSenderService extends AbstractLibrary
{

    /**
     * @var \Phalcon\Config
     */
    private $_config;

    /**
     * Send email with data from notification.
     * 
     * @access public
     * @param Notification $notification
     * @return SendResult
     */
    public function send(Notification $notification)
    {
        try {
            $mailer = $this->initMailer();

            // Add recipient data
            $mailer->addAddress($notification->getReceiver());
            $member = $notification->getMember();
            if (is_object($member)) {
                $mailer->addAddress($notification->getReceiver(), $member->getUsername());
            }

            // Add sender data
            $mailer->setFrom($notification->getFromEmail(), $notification->getFromName());

            // Set message subject line
            $mailer->Subject = $notification->getSubject();

            // Set message body
            $mailer->msgHTML($notification->getBody());

            // Set attachments if exists
            if (count($notification->getAttachments()) > 0) {
                foreach ($notification->getAttachments() as $filePath) {
                    $mailer->addAttachment($filePath);
                }
            }

            // Send message
            $success = $mailer->send();

            return new SendResult($success, $mailer->getErrorInfo(), $mailer->getUniqueId());

        } catch (\Exception $e) {
            return new SendResult(false, $e->getMessage(), $mailer->getUniqueId());
        }
    }

    /**
     * Set up mailer default settings
     * 
     * @access protected
     * @return DetailedPhpMailer
     * @throws \Core\Exception\RuntimeException
     */
    protected function initMailer()
    {
        $mailer = new DetailedPhpMailer();

        $config = $this->getConfig();
        switch ($config->mail->driver) {
            case 'smtp' :
                $mailer->isSMTP();
                $mailer->Host = $config->mail->host;
                $mailer->SMTPSecure = $config->mail->encryption;
                $mailer->Port = $config->mail->port;
                if (!empty($config->mail->username) && !empty($config->mail->username)) {
                    $mailer->SMTPAuth = true;
                    $mailer->Username = $config->mail->username;
                    $mailer->Password = $config->mail->password;
                }
                //$mailer->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead
                break;
            case 'sendmail' :
                $mailer->isSendmail();
                if (!empty($config->mail->sendmail)) {
                    $this->Sendmail = $config->mail->sendmail;
                }
                break;
            case 'mail' :
                $mailer->isMail();
                break;
            default:
                throw new \Core\Exception\RuntimeException('Uknown mail sending driver!');
                break;
        }

        // Set default sender data
        $mailer->setFrom($config->mail->from->email, $config->mail->from->name);

        // Set email format to HTML
        $mailer->isHTML(true);

        // Set unicode charset
        $mailer->CharSet = 'utf-8';

        // Set normal priority
        $mailer->Priority = 3;

        // set word wrap to the RFC2822 limit
        $mailer->WordWrap = 78;

        // Set XMailer header
        if (!empty($config->mail->xmailer)) {
            $mailer->XMailer = $config->mail->xmailer;
        }

        return $mailer;
    }

    /**
     * Get config
     * 
     * @access protected
     * @return \Phalcon\Config
     */
    protected function getConfig()
    {
        if ($this->_config === null || !$this->_config) {
            $this->_config = $this->di->get('config');
        }

        return $this->_config;
    }

}