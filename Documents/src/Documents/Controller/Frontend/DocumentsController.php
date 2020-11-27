<?php

namespace Documents\Controller\Frontend;

use Common\Controller\AbstractFrontendController;
use Documents\Entity\Document;

class DocumentsController extends AbstractFrontendController
{

    /**
     * @var \Documents\Repository\DocumentRepository
     */
    protected $_documentsRepo;

    /**
     * @toDo
     */
    public function indexAction()
    {
        $lvl1 = $this->dispatcher->getParam('lvl1');
        $lvl2 = $this->dispatcher->getParam('lvl2');
        $contentClass = 'text-page';

        $this->view->setVars(compact('contentClass', 'lvl1', 'lvl2'));
    }

    /**
     * Output document (page) by key
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function showByKeyAction()
    {
        $key        = $this->dispatcher->getParam('key');
        $cacheKey   = 'document-' . $key;
        $data       = $this->cache->get($cacheKey);
        $loadHowToBlock = $key === 'how-it-works' ? true : false;

        if ($data === null) {
            $contentClass = 'text-page';
            $originalDocument = $this->getDocumentRepo()->findObjectByKey($key);

            if (null === $originalDocument || $originalDocument->isDisabled()) {
                $this->dispatcher->forward([
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\Frontend',
                    'controller'    => 'error',
                    'action'        => 'notFound',
                ]);
                return false;
            }

            // Clean only needed values for output
            $document = $originalDocument->getArrayCopy();
            unset($document['parent'], $document['childrens']);

            // Create cache
            $data = compact('contentClass', 'document', 'loadHowToBlock');
            $this->cache->save($cacheKey, $data);
        }

        // Set up meta data
        if (isset($data['document']) && is_array($data['document'])) {
            $this->metaData
                ->setTitle($data['document']['seoTitle'])
                ->setDescription($data['document']['seoDescription'])
                ->setKeywords($data['document']['seoKeywords'])
                ->enableAddTitleSuffix()
                ->setLinkCanonical($this->config->web_url . '/' . $data['document']['slug']);
        }

        $this->view->setVars($data);
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
