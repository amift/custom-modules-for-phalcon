<?php

namespace Communication\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Communication\Entity\Notification;
use Members\Entity\Member;

class NotificationsController extends AbstractBackendController
{

    /**
     * @var \Communication\Repository\NotificationRepository
     */
    protected $_notificationsRepo;

    /**
     * Notifications list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->notifications;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $contentClass = 'notifications_page';

        $qb = $this->getNotificationRepo()->createQueryBuilder('n')
                ->select('n')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('n.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state')
            ->searchInFields('search', [ 
                'n.receiver', 'n.fromName', 'n.fromEmail', 'n.subject', 'n.body'
            ])
        ;

        $filters->apply($qb, 'n');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('contentClass', 'paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * Notification details view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function viewAction()
    {
        $id = $this->dispatcher->getParam('id');
        $notification = $this->getNotificationRepo()->findObjectById($id);
        $contentClass = 'notifications_page';

        if (null === $notification) {
            $this->flashSession->error(sprintf('Notification with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'notifications_list']));
        }

        $this->view->setVars(compact('contentClass', 'notification'));
    }

    /**
     * Get Notification entity repository
     * 
     * @access public
     * @return \Communication\Repository\NotificationRepository
     */
    public function getNotificationRepo()
    {
        if ($this->_notificationsRepo === null || !$this->_notificationsRepo) {
            $this->_notificationsRepo = $this->getEntityRepository(Notification::class);
        }

        return $this->_notificationsRepo;
    }

    public function sendMailTestAction()
    {
        try {
            $this->notification_scheduler->scheduleNotification(
                1, 'email.member_registration', [
                    'username' => 'TestUserName',
                    //'tmp_password' => 'QWERTY124',
                    //'url_login' => 'http://www.allsports.lv/pieslegties',
                    'url_activation' => 'http://www.allsports.lv/konta-aktivizesana',
                ]
            );
            
            die('notification scheduled successfully');
        } catch (\Exception $exc) {
            var_dump($exc->getMessage());
            die('error');
        }

        /*try {
            $this->notification_sender->sendNotification(1);
            die('notification sent successfully');
        } catch (\Exception $exc) {
            var_dump($exc->getMessage());
            die('error');
        }*/
    }

}
