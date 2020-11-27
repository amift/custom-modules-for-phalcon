<?php

namespace System\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use System\Entity\CronJob;
use System\Entity\CronJobLog;
use System\Forms\CronJobForm;
use System\Tool\CronJobState as State;

class CronjobsController extends AbstractBackendController
{

    /**
     * @var \System\Repository\CronJobRepository
     */
    protected $_cronjobsRepo;

    /**
     * @var \System\Repository\CronJobLogRepository
     */
    protected $_cronjobLogsRepo;

    /**
     * CronJobs list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->default;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getCronJobRepo()->createQueryBuilder('c')
                ->select('c')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('c.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state')
            ->addField('enabled')
            ->searchInFields('search', [ 
                'c.title', 'c.cronAction', 'c.cronExpr',
            ])
        ;

        $filters->apply($qb, 'c');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * CronJob edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $cronjob = $this->getCronJobRepo()->findObjectById($id);
        $tab = 'general';

        if (null === $cronjob) {
            $this->flashSession->error(sprintf('CronJob with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'cronjobs_list']));
        }

        $form   = new CronJobForm($cronjob, ['edit' => true]);
        $action = $this->url->get(['for' => 'cronjobs_edit', 'id' => $cronjob->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $cronjob);
                try {
                    // Save data
                    $this->getEntityManager()->persist($cronjob);
                    $this->getEntityManager()->flush();

                    // Back to list
                    $this->flashSession->success(sprintf('CronJob "%s" info updated successfully!', (string)$cronjob));
                    return $this->response->redirect($this->url->get(['for' => 'cronjobs_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('cronjob', 'form', 'action', 'error', 'tab'));
    }

    /**
     * CronJob logs list view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function logAction()
    {
        $id = $this->dispatcher->getParam('id');
        $cronjob = $this->getCronJobRepo()->findObjectById($id);
        $tab = 'log';

        if (null === $cronjob) {
            $this->flashSession->error(sprintf('CronJob with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'cronjobs_list']));
        }

        $perPage = $this->config->settings->page_size->default;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getCronJobLogRepo()->createQueryBuilder('c')
                ->select('c')
                ->where('c.cronjob = :cronjobId')
                ->setParameter('cronjobId', $cronjob->getId())
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('c.id', 'DESC');

        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('cronjob', 'tab', 'paginator', 'perPage', 'currentPage'));
    }

    /**
     * CronJob logs details view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function logViewAction()
    {
        $id = $this->dispatcher->getParam('id');
        $log = $this->getCronJobLogRepo()->find($id);
        $tab = 'log';

        if (null === $log) {
            $this->flashSession->error(sprintf('CronJobLog with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'cronjobs_list']));
        }

        $cronjob = $log->getCronjob();

        if (null === $cronjob) {
            $this->flashSession->error(sprintf('CronJob with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'cronjobs_list']));
        }

        $this->view->setVars(compact('cronjob', 'tab', 'log'));
    }

    /**
     * CronJob manual run action.
     * 
     * @access public
     * @return JsonContent
     */
    public function runAction()
    {
        $dateFormat = 'd/m/y H:i:s';
        $id         = $this->dispatcher->getParam('id');
        $cronjob    = $this->getCronJobRepo()->findObjectById($id);
        $response   = [
            'success' => false,
            'status' => State::getLabel(State::STATUS_ERROR),
            'statusId' => State::STATUS_ERROR,
            'started' => (new \DateTime('now'))->format($dateFormat),
            'finished' => (new \DateTime('now'))->format($dateFormat)
        ];

        // Execute cron job task
        if (is_object($cronjob)) {
            $result = shell_exec(sprintf(
                '%s %s %s', 
                $this->config->php_bin, 
                ROOT_PATH . DS . 'public' . DS . 'index.php', 
                $cronjob->getCronAction()
            ));

            $this->getEntityManager()->refresh($cronjob);

            $response['success']    = true;
            $response['status']     = State::getLabel($cronjob->getState());
            $response['statusId']   = $cronjob->getState();
            $response['started']    = (is_object($cronjob->getLaunchedAt())) ? $cronjob->getLaunchedAt()->format($dateFormat) : '-';
            $response['finished']   = (is_object($cronjob->getFinishedAt())) ? $cronjob->getFinishedAt()->format($dateFormat) : '-';
        }

        // Set up response data
        $this->response->setJsonContent($response);

        return $this->response->send();
    }

    /**
     * Get CronJob entity repository
     * 
     * @access public
     * @return \System\Repository\CronJobRepository
     */
    public function getCronJobRepo()
    {
        if ($this->_cronjobsRepo === null || !$this->_cronjobsRepo) {
            $this->_cronjobsRepo = $this->getEntityRepository(CronJob::class);
        }

        return $this->_cronjobsRepo;
    }

    /**
     * Get CronJobLog entity repository
     * 
     * @access public
     * @return \System\Repository\CronJobRepository
     */
    public function getCronJobLogRepo()
    {
        if ($this->_cronjobLogsRepo === null || !$this->_cronjobLogsRepo) {
            $this->_cronjobLogsRepo = $this->getEntityRepository(CronJobLog::class);
        }

        return $this->_cronjobLogsRepo;
    }

}