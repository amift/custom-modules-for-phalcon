<?php

namespace Documents\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Documents\Entity\Document;
use Documents\Forms\DocumentForm;

class DocumentsController extends AbstractBackendController
{

    /**
     * @var \Documents\Repository\DocumentRepository
     */
    protected $_documentsRepo;

    /**
     * Documents list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->default;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getDocumentRepo()->createQueryBuilder('d')
                ->select('d')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('d.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('enabled')
            ->searchInFields('search', [
                'd.slug', 'd.key', 'd.title', 'd.content', 
                'd.seoTitle', 'd.seoDescription', 'd.seoKeywords',
            ])
        ;

        $filters->apply($qb, 'd');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * Document add view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $document = new Document();
        $form = new DocumentForm($document, ['edit' => true]);
        $action = $this->url->get(['for' => 'documents_add']);
        $error = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $document);
                try {
                    if ((int)$document->getParent() > 0) {
                        $parent = $this->getDocumentRepo()->findObjectById($document->getParent());
                        $parent->addChildren($document);
                    } else {
                        $document->setParent(null);
                    }

                    // Save data
                    $this->getEntityManager()->persist($document);
                    $this->getEntityManager()->flush();

                    // Back to list
                    $this->flashSession->success(sprintf('Document "%s" created successfully!', (string)$document));
                    return $this->response->redirect($this->url->get(['for' => 'documents_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('document', 'form', 'action', 'error'));
    }

    /**
     * Document edit view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $document = $this->getDocumentRepo()->findObjectById($id);

        if (null === $document) {
            $this->flashSession->error(sprintf('Document with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'documents_list']));
        }

        $form = new DocumentForm($document, ['edit' => true]);
        $action = $this->url->get(['for' => 'documents_edit', 'id' => $document->getId()]);
        $error = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $document);
                try {
                    if ((int)$document->getParent() > 0) {
                        $parent = $this->getDocumentRepo()->findObjectById($document->getParent());
                        $parent->addChildren($document);
                    } else {
                        $document->setParent(null);
                    }

                    // Save data
                    $this->getEntityManager()->persist($document);
                    $this->getEntityManager()->flush();

                    // Back to list
                    $this->flashSession->success(sprintf('Document "%s" info updated successfully!', (string)$document));
                    return $this->response->redirect($this->url->get(['for' => 'documents_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('category', 'form', 'action', 'error'));
    }

    /**
     * Document delete action.
     *
     * @access public
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->dispatcher->getParam('id');
        $document = $this->getDocumentRepo()->findObjectById($id);

        if (null === $document) {
            $this->flashSession->error(sprintf('Document with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'documents_list']));
        }

        // Remove data
        $this->getEntityManager()->remove($document);
        $this->getEntityManager()->flush();

        // Back to list
        $this->flashSession->success(sprintf('Document "%s" info deleted successfully!', (string)$document));
        return $this->response->redirect($this->url->get(['for' => 'documents_list']));
    }

    /**
     * Get Document entity repository
     * 
     * @access public
     * @return \Documents\Repository\DocumentRepository
     */
    public function getDocumentRepo()
    {
        if ($this->_documentsRepo === null || !$this->_documentsRepo) {
            $this->_documentsRepo = $this->getEntityRepository(Document::class);
        }

        return $this->_documentsRepo;
    }

}