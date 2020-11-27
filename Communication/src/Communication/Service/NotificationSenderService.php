<?php

namespace Communication\Service;

use Communication\Entity\Notification;
use Communication\Mailer\SendResult;
use Communication\Service\EmailSenderService;
use Communication\Tool\NotificationState as State;
use Core\Library\AbstractLibrary;

class NotificationSenderService extends AbstractLibrary
{

    /**
     * @var EmailSenderService
     */
    private $_emailSender;

    /**
     * @var \Communication\Repository\NotificationRepository
     */
    private $_notificationsRepo;

    /**
     * Send notification.
     * 
     * @access public
     * @param int $id
     * @throws \Core\Exception\InvalidArgumentException
     * @throws \Core\Exception\RuntimeException
     */
    public function sendNotification($id)
    {
        $notification = $this->getNotificationRepo()->findObjectById($id);

        if (null === $notification) {
            throw new \Core\Exception\InvalidArgumentException(sprintf('Notification with ID value "%s" not found', $id));
        }

        if ($notification->isStateSent()) {
            throw new \Core\Exception\RuntimeException(sprintf('Notification is already sent.' ));
        }

        if (! $notification->isKnownType()) {
            throw new \Core\Exception\RuntimeException(sprintf('Unknown notification type "%s".', $notification->getType() ));
        }

        $result = null;

        if ($notification->isTypeEmail()) {
            $result = $this->sendEmail($notification);
            /* @var $result SendResult */
        }

        if ($result === null) {
            throw new \Core\Exception\RuntimeException(sprintf('Notification can not be sent, since it has no sender service.' ));
        }

        // Get default or existing sending details
        $sendingDetails = $notification->getSendingDetails();

        // Recheck previously sending details
        if (array_key_exists('UniqueId', $sendingDetails)) {
            $sendingDetails['PreviousUniqueId'][] = $sendingDetails['UniqueId'];
        }
        if (array_key_exists('Attempts', $sendingDetails)) {
            $sendingDetails['Attempts'] = (int)$sendingDetails['Attemps']+1;
        }

        // Set up new message unique Id value
        $sendingDetails['UniqueId'] = $result->getUniqueId();

        // Set up datails when success otherwise as error
        if ($result->isSuccess()) {
            $notification->setState(State::STATUS_SENT);
            $notification->setSentAt($result->getSentAt());
        } else {
            $notification->setState(State::STATUS_ERROR);
            $notification->setSendingError($result->getError());
            if (!array_key_exists('Attempts', $sendingDetails)) {
                $sendingDetails['Attempts'] = 1;
            }
        }
        $notification->setSendingDetails($sendingDetails);

        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush($notification);
    }

    /**
     * Sent email from notification
     * 
     * @access protected
     * @param Notification $notification
     * @return SendResult
     */
    protected function sendEmail(Notification $notification)
    {
        $result = $this->getEmailSender()->send($notification);
        /* @var $result SendResult */

        return $result;
    }

    /**
     * Get EmailSenderService
     * 
     * @access protected
     * @return EmailSenderService
     */
    protected function getEmailSender()
    {
        if ($this->_emailSender === null || !$this->_emailSender) {
            $this->_emailSender = $this->di->get('email_sender');
        }

        return $this->_emailSender;
    }

    /**
     * Get Notification entity repository
     * 
     * @access protected
     * @return \Communication\Repository\NotificationRepository
     */
    protected function getNotificationRepo()
    {
        if ($this->_notificationsRepo === null || !$this->_notificationsRepo) {
            $this->_notificationsRepo = $this->getEntityRepository(Notification::class);
        }

        return $this->_notificationsRepo;
    }

}
