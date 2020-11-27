<?php

namespace Communication\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Communication\Entity\Template;
use Communication\Forms\TemplateForm;
use Communication\Tool\TemplateModule;
use Communication\Tool\TemplateType;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TemplatesController extends AbstractBackendController
{

    /**
     * @var \Communication\Repository\TemplateRepository
     */
    protected $_templatesRepo;

    /**
     * Templates list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->templates;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $contentClass = 'notifications_page';

        $qb = $this->getTemplateRepo()->createQueryBuilder('t')
                ->select('t')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('t.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('type')
            ->addField('module')
            ->addField('enabled')
            ->searchInFields('search', [ 
                't.title', 't.description', 't.code', 
                't.fromName', 't.fromEmail', 't.subject', 't.body'
            ])
        ;

        $filters->apply($qb, 't');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('contentClass', 'paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * Template edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $template = $this->getTemplateRepo()->findObjectById($id);
        $contentClass = 'notifications_page';

        if (null === $template) {
            $this->flashSession->error(sprintf('Template with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'notification_templates_list']));
        }

        $form = new TemplateForm($template, ['edit' => true]);
        $action = $this->url->get(['for' => 'notification_templates_edit', 'id' => $template->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $template);
                try {
                    // Save data
                    $this->getEntityManager()->persist($template);
                    $this->getEntityManager()->flush();

                    // Back to list
                    $this->flashSession->success(sprintf('Template "%s" info updated successfully!', (string)$template));
                    return $this->response->redirect($this->url->get(['for' => 'notification_templates_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('contentClass', 'template', 'form', 'action', 'error'));
    }

    /**
     * Template add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $template = new Template();
        $template->setModule(TemplateModule::MODULE_MANUAL);
        $template->setType(TemplateType::TYPE_EMAIL);
        $template->setPlaceholders((array)$this->config->communication->default->placeholders);
        $template->setFromName($this->config->communication->default->fromName);
        $template->setFromEmail($this->config->communication->default->fromEmail);

        $contentClass = 'notifications_page';
        $form = new TemplateForm($template, ['add' => true]);
        $action = $this->url->get(['for' => 'notification_templates_add']);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $template);
                try {
                    // Save data
                    $this->getEntityManager()->persist($template);
                    $this->getEntityManager()->flush();

                    // Back to list
                    $this->flashSession->success(sprintf('Template "%s" created successfully!', (string)$template));
                    return $this->response->redirect($this->url->get(['for' => 'notification_templates_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('contentClass', 'template', 'form', 'action', 'error'));
    }

    /**
     * Get Template entity repository
     * 
     * @access public
     * @return \Communication\Repository\TemplateRepository
     */
    public function getTemplateRepo()
    {
        if ($this->_templatesRepo === null || !$this->_templatesRepo) {
            $this->_templatesRepo = $this->getEntityRepository(Template::class);
        }

        return $this->_templatesRepo;
    }

}
