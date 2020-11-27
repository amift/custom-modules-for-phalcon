<?php

namespace Translations\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Translations\Entity\Translation;
use Translations\Entity\TranslationValue;
use Translations\Forms\TranslationForm;
use Translations\Tool\Group;

class TranslationsController extends AbstractBackendController
{

    /**
     * @var \Translations\Repository\TranslationRepository
     */
    protected $_translationRepo;

    /**
     * Translations list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->translations;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $groups = Group::getLabels();//$this->getTranslationRepo()->getGroups();

        $qb = $this->getTranslationRepo()->createQueryBuilder('t')
                ->select('t, ti')
                ->join('t.values', 'ti')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('t.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('group')
            ->searchInFields('search', [ 
                't.key', 't.defaultValue', 'ti.value',
            ])
        ;

        $filters->apply($qb, 't');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters', 'groups'));
    }

    /**
     * Translation edit view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $translation = $this->getTranslationRepo()->findObjectById($id);

        if (null === $translation) {
            $this->flashSession->error(sprintf('Translation with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'translations_list']));
        }

        $form   = new TranslationForm($translation, ['edit' => true]);
        $action = $this->url->get(['for' => 'translations_edit', 'id' => $translation->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $translation);
                try {
                    // Save data
                    $this->getEntityManager()->persist($translation);
                    $translationValue = $translation->getTranslatedValue($this->localeHandler->getDefaultLocale(), true);
                    $translationValue->setValue($translation->getDefaultValue());
                    $this->getEntityManager()->persist($translationValue);
                    $this->getEntityManager()->flush();

                    // Back to list
                    $this->flashSession->success(sprintf('Translation "%s" info updated successfully!', $translation->getId()));
                    return $this->response->redirect($this->url->get(['for' => 'translations_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('translation', 'form', 'action', 'error'));
    }

    /**
     * Get Translation entity repository
     * 
     * @access public
     * @return \Translations\Repository\TranslationRepository
     */
    public function getTranslationRepo()
    {
        if ($this->_translationRepo === null || !$this->_translationRepo) {
            $this->_translationRepo = $this->getEntityRepository(Translation::class);
        }

        return $this->_translationRepo;
    }

}
