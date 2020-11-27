<?php

namespace Polls\Controller\Backend;

use Articles\Entity\Category;
use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Common\Tool\StringTool;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Polls\Entity\Poll;
use Polls\Entity\PollOption;
use Polls\Forms\PollForm;

class PollsController extends AbstractBackendController
{

    /**
     * @var \Polls\Repository\PollRepository
     */
    protected $_pollsRepo;

    /**
     * @var \Articles\Repository\CategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * Polls list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->polls;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $categories = $this->getCategoriesOptionsFiltersList();

        $qb = $this->getPollRepo()->createQueryBuilder('p')
                ->select('p')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('p.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state')
            ->addField('startpage')
            ->addField('publicationDateFrom', Filters::TYPE_DATE, 'p.publicationDate', Filters::COMP_GTE)
            ->addField('publicationDateTo', Filters::TYPE_DATE, 'p.publicationDate', Filters::COMP_LTE)
            ->addField('categoryLvl1', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('p.categoryLvl1 = :categoryLvl1');
                    $qb->setParameter('categoryLvl1', $value);
                }
            })
            ->addField('categoryLvl2', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('p.categoryLvl2 = :categoryLvl2');
                    $qb->setParameter('categoryLvl2', $value);
                }
            })
            ->addField('categoryLvl3', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('p.categoryLvl3 = :categoryLvl3');
                    $qb->setParameter('categoryLvl3', $value);
                }
            })
            ->searchInFields('search', [ 
                'p.title', 'p.slug', 'p.content',
            ])
        ;

        $filters->apply($qb, 'a');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters', 'categories'));
    }

    /**
     * Poll info view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function viewAction()
    {
        $id = $this->dispatcher->getParam('id');
        $poll = $this->getPollRepo()->findObjectById($id);

        if (null === $poll) {
            $this->flashSession->error(sprintf('Poll with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'polls_list']));
        }

        $this->view->setVars(compact('poll'));
    }

    /**
     * Poll add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $poll = new Poll();
        $form = new PollForm($poll, ['edit' => true]);
        $action = $this->url->get(['for' => 'polls_add']);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $poll);

                // Poll options
                $data = $this->request->getPost()['options'];
                foreach ($data as $x => $row) {
                    $pollOption = new PollOption();
                    $pollOption->setTitle($row['title']);
                    $pollOption->setOrdering((int)$row['ordering']);
                    $pollOption->setPoll($poll);
                    $fileResult = $this->uploadPollOptionFile($pollOption, $x);
                    if ($fileResult['filesUploaded'] === true) {
                        if ($fileResult['success']) {
                            $pollOption->setImage($fileResult['fileInfo'][0]['filename']);
                            $pollOption->setImagePath($fileResult['fileInfo'][0]['path']);
                            $image = new \Phalcon\Image\Adapter\GD($pollOption->getImagePath());
                            if ($image->getWidth() != 168 || $image->getHeight() != 168) {
                                $image->resize(168, 168);
                                $imgSaved = $image->save();
                            }
                        }
                    }
                    $this->getEntityManager()->persist($pollOption);
                }

                if ((int)$poll->getCategoryLvl1() > 0) {
                    $category = $this->getCategoryRepo()->findObjectById($poll->getCategoryLvl1());
                    $poll->setCategoryLvl1($category);
                } else {
                    $poll->setCategoryLvl1(null);
                }

                if ((int)$poll->getCategoryLvl2() > 0) {
                    $category = $this->getCategoryRepo()->findObjectById($poll->getCategoryLvl2());
                    $poll->setCategoryLvl2($category);
                } else {
                    $poll->setCategoryLvl2(null);
                }

                if ((int)$poll->getCategoryLvl3() > 0) {
                    $category = $this->getCategoryRepo()->findObjectById($poll->getCategoryLvl3());
                    $poll->setCategoryLvl3($category);
                } else {
                    $poll->setCategoryLvl3(null);
                }

                $d = \DateTime::createFromFormat('d/m/Y H:i:s', $poll->getPublicationDate());
                $poll->setPublicationDate($d);

                $poll->setContent(StringTool::createParagraphTags($poll->getContent()));

                // Save data
                $this->getEntityManager()->persist($poll);
                $this->getEntityManager()->flush();

                // Back to list
                $this->flashSession->success(sprintf('Poll "%s" added successfully!', (string)$poll));
                return $this->response->redirect($this->url->get(['for' => 'polls_list']));
            }
        }

        $this->view->setVars(compact('poll', 'form', 'action', 'error'));
    }

    /**
     * Poll edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $poll = $this->getPollRepo()->findObjectById($id);

        if (null === $poll) {
            $this->flashSession->error(sprintf('Poll with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'polls_list']));
        }

        $savedOptions = $poll->getOptions();
        $form = new PollForm($poll, ['edit' => true]);
        $action = $this->url->get(['for' => 'polls_edit', 'id' => $poll->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $poll);

                // Poll options
                $ids = [];
                $data = $this->request->getPost()['options'];
                foreach ($data as $x => $row) {
                    if ($row['id'] === '') { // add new
                        $pollOption = new PollOption();
                        $pollOption->setTitle($row['title']);
                        $pollOption->setOrdering((int)$row['ordering']);
                        $pollOption->setPoll($poll);
                        $fileResult = $this->uploadPollOptionFile($pollOption, $x);
                        if ($fileResult['filesUploaded'] === true) {
                            if ($fileResult['success']) {
                                $pollOption->setImage($fileResult['fileInfo'][0]['filename']);
                                $pollOption->setImagePath($fileResult['fileInfo'][0]['path']);
                                $image = new \Phalcon\Image\Adapter\GD($pollOption->getImagePath());
                                if ($image->getWidth() != 168 || $image->getHeight() != 168) {
                                    $image->resize(168, 168);
                                    $imgSaved = $image->save();
                                }
                            }
                        }
                        $this->getEntityManager()->persist($pollOption);
                    } else { // update existing
                        foreach ($savedOptions as $savedOption) {
                            if ((string)$savedOption->getId() === (string)$row['id']) {
                                $savedOption->setTitle($row['title']);
                                $savedOption->setOrdering((int)$row['ordering']);
                                $fileResult = $this->uploadPollOptionFile($savedOption, $x);
                                if ($fileResult['filesUploaded'] === true) {
                                    if ($fileResult['success']) {
                                        $savedOption->setImage($fileResult['fileInfo'][0]['filename']);
                                        $savedOption->setImagePath($fileResult['fileInfo'][0]['path']);
                                        $image = new \Phalcon\Image\Adapter\GD($savedOption->getImagePath());
                                        if ($image->getWidth() != 168 || $image->getHeight() != 168) {
                                            $image->resize(168, 168);
                                            $imgSaved = $image->save();
                                        }
                                    }
                                }
                                $ids[] = (string)$row['id'];
                            }
                        }
                    }
                }

                // Remove deleted
                foreach ($savedOptions as $optionToDelete) {
                    if (!in_array((string)$optionToDelete->getId(), $ids)) {
                        $poll->decreaseVotesCountByValue($optionToDelete->getVotesCount());
                        $poll->removeOption($optionToDelete);
                    }
                }

                if ((int)$poll->getCategoryLvl1() > 0) {
                    $category = $this->getCategoryRepo()->findObjectById($poll->getCategoryLvl1());
                    $poll->setCategoryLvl1($category);
                } else {
                    $poll->setCategoryLvl1(null);
                }

                if ((int)$poll->getCategoryLvl2() > 0) {
                    $category = $this->getCategoryRepo()->findObjectById($poll->getCategoryLvl2());
                    $poll->setCategoryLvl2($category);
                } else {
                    $poll->setCategoryLvl2(null);
                }

                if ((int)$poll->getCategoryLvl3() > 0) {
                    $category = $this->getCategoryRepo()->findObjectById($poll->getCategoryLvl3());
                    $poll->setCategoryLvl3($category);
                } else {
                    $poll->setCategoryLvl3(null);
                }

                $d = \DateTime::createFromFormat('d/m/Y H:i:s', $poll->getPublicationDate());
                $poll->setPublicationDate($d);

                $poll->setContent(StringTool::createParagraphTags($poll->getContent()));

                // Save data
                $this->getEntityManager()->flush();

                // Back to list
                $this->flashSession->success(sprintf('Poll "%s" info updated successfully!', (string)$poll));
                return $this->response->redirect($this->url->get(['for' => 'polls_list']));
            }
        }

        $this->view->setVars(compact('poll', 'form', 'action', 'error'));
    }

    public function uploadPollOptionFile($pollOption, $x)
    {
        $result = [
            'success' => false,
            'fileInfo' => [],
            'error' => 'No image uploaded',
            'filesUploaded' => false
        ];

        $phalconFile = null;

        // Set valid name
        $overwriteFileName = time();
        if ($pollOption->getTitle() !== '') {
            $overwriteFileName = StringTool::createSlug($pollOption->getTitle().' '.time());
        }

        // Get needed file
        if ($this->request->hasFiles() !== false) {

            // Simple check if has realy selected files for upload
            $files = $this->request->getUploadedFiles();
            if (sizeof($files) > 0) {
                foreach ($files as $file) {
                    if ($file->isUploadedFile() && 'options.'.(string)$x.'.image' === $file->getKey()) {
                    //if ($file->getTempName() !== '' && 'options.'.(string)$x.'.image' === $file->getKey()) {
                        $phalconFile = $file;
                        $result['filesUploaded'] = true;
                        break;
                    }
                }
            }

            // Complate with validations
            if ($phalconFile !== null) {
                $this->uploader->setRules($this->getImageRules($overwriteFileName, $pollOption->getImageDirectory()));
                if ($this->uploader->isValidFile($phalconFile) === true) {
                    $fileInfo = $this->uploader->moveFile($phalconFile);
                    $result['success'] = true;
                    $result['fileInfo'] = $fileInfo;//$this->uploader->getInfo();
                    $result['error'] = '';
                } else {
                    $result['error'] = 'Image file is not valid';
                    foreach ($this->uploader->getErrors() as $value) {
                        $result['error'] = $value;
                        break;
                    }
                }
            }
        }

        /*// Remove all files uploaded when errors occuared
        if ($result['success'] === false) {
            $this->uploader->truncate();
        }*/

        return $result;
    }

    /**
     * @return array
     */
    protected function getImageRules($overwriteFileName = null, $path = '')
    {
        return [
            'dynamic' => $path,
            'maxsize' => 1048576, // 1 MB
            'mimes' =>  [
                'image/jpeg', 'image/png',
            ],
            'extensions' =>  [
                'jpeg', 'jpg', 'png',
            ],
            'sanitize' => true,
            'customFileName' => $overwriteFileName
        ];
    }

    /**
     * Get Poll entity repository
     * 
     * @access public
     * @return \Polls\Repository\PollRepository
     */
    public function getPollRepo()
    {
        if ($this->_pollsRepo === null || !$this->_pollsRepo) {
            $this->_pollsRepo = $this->getEntityRepository(Poll::class);
        }

        return $this->_pollsRepo;
    }

    /**
     * Get Category entity repository
     * 
     * @access public
     * @return \Articles\Repository\CategoryRepository
     */
    public function getCategoryRepo()
    {
        if ($this->_categoriesRepo === null || !$this->_categoriesRepo) {
            $this->_categoriesRepo = $this->getEntityRepository(Category::class);
        }

        return $this->_categoriesRepo;
    }

    /**
     * @return array
     */
    protected function getCategoriesOptionsFiltersList()
    {
        return [
            'categoryLvl1' => $this->getCategoriesOptionsListByLevelAndParent(1),
            'categoryLvl2' => $this->getCategoriesOptionsListByLevelAndParent(2, $this->request->getQuery('categoryLvl1', null, null)),
            'categoryLvl3' => $this->getCategoriesOptionsListByLevelAndParent(3, $this->request->getQuery('categoryLvl2', null, null))
        ];
    }

    /**
     * @return array
     */
    protected function getCategoriesOptionsListByLevelAndParent($level = 1, $parentId = null)
    {
        $list = $this->getCategoryRepo()->getParentsListFromLevel($level, $parentId);

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        return $rows;
    }

}
