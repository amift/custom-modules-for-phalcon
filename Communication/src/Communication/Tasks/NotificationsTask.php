<?php

namespace Communication\Tasks;

use Communication\Entity\Notification;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use System\Entity\CronJob;

class NotificationsTask extends \Phalcon\Cli\Task
{

    /**
     * @var \Communication\Repository\NotificationRepository
     */
    protected $_notificationsRepo;

    /**
     * @var string
     */
    protected $_stackTrace = '';

    /**
     * Task for email notifications sending
     * 
     * @access public
     * @throws \Core\Exception\RuntimeException
     */
    public function sendEmailsAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        $cronAction = 'communication notifications sendEmails';
        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->startRunning();
            $this->getEntityManager()->flush($cronjob);
        }

        $this->_stackTrace = '';
        $success = true;
        $errorMsg = '';

        try {
            $this->_stackTrace.= sprintf('Started to send email notifications') . PHP_EOL;

            $limit  = 100;
            $sender = $this->getNotificationSender();
            $repo   = $this->getNotificationRepo();

            $total = $sent = $unsent = 0;

            $notifications = $repo->getScheduledEmailNotificationsQuery($limit, new \DateTime('now'))->iterate();
            foreach ($notifications as $row) {
                $notification = $row[0];
                /* @var $notification Notification */

                $this->_stackTrace.= sprintf('Processing notification nr. %d [id: %d]', ++$total, $notification->getId()) . PHP_EOL;

                try {
                    $sender->sendNotification($notification->getId());
                    ++$sent;
                } catch (\Exception $exc) {
                    ++$unsent;
                }
            }

            $this->_stackTrace.= sprintf('Total processed: %d', $total) . PHP_EOL;
            $this->_stackTrace.= sprintf('Total was sent: %d', $total) . PHP_EOL;
            $this->_stackTrace.= sprintf('Total was not sent: %d', $unsent) . PHP_EOL;

        } catch (ORMInvalidArgumentException $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        } catch (\Exception $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        }

        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->stopRunning($this->_stackTrace, $success, $errorMsg);
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * @return \Communication\Service\NotificationSenderService
     */
    protected function getNotificationSender()
    {
        return $this->notification_sender;
    }

    /**
     * Get EntityManager instance
     * 
     * @access protected
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository Repository class
     */
    protected function getEntityRepository($entityName)
    {
        return $this->getEntityManager()->getRepository($entityName);
    }

    /**
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