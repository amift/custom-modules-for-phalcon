<?php

namespace Communication\Tasks;

use Articles\Entity\Article;
use Common\Tool\PeriodTool;
use Communication\Entity\Notification;
use Communication\Entity\Newsletter;
use Communication\Entity\NewsletterArticle;
use Communication\Tool\NewsletterContent;
use Communication\Tool\NewsletterType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use System\Entity\CronJob;

class NewslettersTask extends \Phalcon\Cli\Task
{
    /**
     * @var \Communication\Repository\NewsletterRepository
     */
    protected $_newslettersRepo;

    /**
     * @var \Communication\Repository\NotificationRepository
     */
    private $_notificationsRepo;

    /**
     * @var \Articles\Repository\ArticleRepository
     */
    protected $_articlesRepo;

    /**
     * @var string
     */
    protected $_stackTrace = '';

    /**
     * Task for email queueing for sending from newsletter
     *
     * @access public
     * @throws \Core\Exception\RuntimeException
     */
    public function queueEmailsAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        $cronAction = 'communication newsletters queueEmails';
        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->startRunning();
            $this->getEntityManager()->flush($cronjob);
        }

        $this->_stackTrace = '';
        $success = true;
        $errorMsg = '';

        try {
            $this->_stackTrace.= sprintf('Started to queue newsletter email notifications') . PHP_EOL;

            $total       = 0;
            $repo        = $this->getNewsletterRepo();
            $newsletters = $repo->getQueuedNewslettersQuery(new \DateTime('now'), 1)->iterate();
            foreach ($newsletters as $row) {
                $newsletter = $row[0];
                /* @var $newsletter Newsletter */

//                $newsletter->startProcessing();
//                $this->getEntityManager()->flush($newsletter);

                // Create main notification body
                $htmlBody = '';
                try {
                    if ($newsletter->isTypeCustom()) {
                        $htmlBody = $this->getNewsletterService()->bodyCustom($newsletter->getBody());
                    } elseif ($newsletter->isTypeWeekly()) {
                        $htmlBody = $this->getNewsletterService()->bodyArticles($newsletter->getSubject(), $newsletter->getArtilesIdsAsString());
                    }
                } catch (\Exception $ex) {
//                    $newsletter->stopProcessing(false, 0, $ex->getMessage());
//                    $this->getEntityManager()->flush($newsletter);
                }

                // Get receivers
                $receivers = [];
                $receiversType = isset($newsletter->getReceiverCriterias()['type']) ? $newsletter->getReceiverCriterias()['type'] : '';
                if ($receiversType === 'members') {
                    $receivers = $this->getMemberService()->getNewsletterReceiversByCriterias(isset($newsletter->getReceiverCriterias()[$receiversType]) ? $newsletter->getReceiverCriterias()[$receiversType] : []);
                }
                echo '<pre>'.print_r($receivers,1).'</pre>';
                die();

                // Queue emails
                if ($htmlBody !== '') {
                    $html = NewsletterContent::full($htmlBody);
                    

                    $notificationsCount = 0;
                    
                    $unsubscribeUrl = 'Lga32SG24sJ412';
                    $placeholders = [ '{{ tpl_footer }}' => NewsletterContent::getFooter($unsubscribeUrl)];
                    $memberEmailBody = strtr($html, $placeholders);
                    //echo PHP_EOL . $memberEmailBody;
                    //die();
                }

                $this->getEntityManager()->refresh($newsletter);
                if ($notificationsCount > 0) {
                    $newsletter->stopProcessing(true, $notificationsCount);
                } else {
                    $newsletter->stopProcessing(false, 0, 'No messages queued by newsletter criteria');
                }
                $this->getEntityManager()->flush($newsletter);

                ++$total;
            }

            $this->_stackTrace.= sprintf('%d newsletters processed', $total) . PHP_EOL;

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

        echo $this->_stackTrace;
    }
    
    /**
     * @return \Communication\Service\NewsletterService
     */
    protected function getNewsletterService()
    {
        return $this->newsletter_service;
    }
    
    /**
     * @return \Member\Service\MemberService
     */
    protected function getMemberService()
    {
        return $this->memberService;
    }

    /**
     * Get Newsletter entity repository
     *
     * @access public
     * @return \Communication\Repository\NewsletterRepository
     */
    public function getNewsletterRepo()
    {
        if ($this->_newslettersRepo === null || !$this->_newslettersRepo) {
            $this->_newslettersRepo = $this->getEntityRepository(Newsletter::class);
        }

        return $this->_newslettersRepo;
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

    /**
     * Get Article entity repository
     *
     * @access public
     * @return \Articles\Repository\ArticleRepository
     */
    public function getArticleRepo()
    {
        if ($this->_articlesRepo === null || !$this->_articlesRepo) {
            $this->_articlesRepo = $this->getEntityRepository(Article::class);
        }

        return $this->_articlesRepo;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository Repository class
     */
    protected function getEntityRepository($entityName)
    {
        return $this->getEntityManager()->getRepository($entityName);
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
}