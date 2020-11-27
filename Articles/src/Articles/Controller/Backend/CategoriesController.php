<?php

namespace Articles\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Articles\Entity\Category;
use Articles\Forms\CategoryForm;

class CategoriesController extends AbstractBackendController
{

    /**
     * @var \Articles\Repository\CategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * Categories list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->categories;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $parents = $this->getParentsOptionsList();

        $qb = $this->getCategoryRepo()->createQueryBuilder('c')
                ->select('c, childrens')
                ->leftJoin('c.childrens', 'childrens')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('c.ordering', 'ASC');/*
                ->orderBy('c.parent', 'DESC')
                ->addOrderBy('c.ordering', 'ASC');*/

        $filters = new Filters($this->request);
        $filters
            ->addField('enabled')
            ->addField('parent', Filters::TYPE_CALLBACK, function($qb, $value){
                if ($value !== '') {
                    if ((string)$value === '0') {
                        $qb->andWhere('c.parent IS NULL');
                    } else {
                        $qb->andWhere('c.parent = :parent');
                        $qb->setParameter('parent', $value);
                    }
                }
            })
            ->searchInFields('search', [ 
                'c.title', 'c.slug',
            ])
        ;

        $filters->apply($qb, 'c');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters', 'parents'));
    }

    /**
     * Category edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $category = $this->getCategoryRepo()->findObjectById($id);

        if (null === $category) {
            $this->flashSession->error(sprintf('Category with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'article_categories_list']));
        }

        $form = new CategoryForm($category, ['edit' => true]);
        $action = $this->url->get(['for' => 'article_categories_edit', 'id' => $category->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {

                $form->bind($this->request->getPost(), $category);
                try {
                    if ((int)$category->getParent() > 0) {
                        $parent = $this->getCategoryRepo()->findObjectById($category->getParent());
                        $parent->addChildren($category);
                    } else {
                        $category->setParent(null);
                    }
                
                    $saveData = true;

                    $fileResult = $this->uploadFile($category);
                    if ($fileResult['filesUploaded'] === true) {
                        if ($fileResult['success']) {
                            $category->setImage($fileResult['fileInfo'][0]['filename']);
                            $category->setImagePath($fileResult['fileInfo'][0]['path']);
                            $image = new \Phalcon\Image\Adapter\GD($category->getImagePath());
                            if ($image->getWidth() != 24 || $image->getHeight() != 24) {
                                $image->resize(24, 24);
                                if (!$image->save()) {
                                    $saveData = false;
                                }
                            }
                        } else {
                            $saveData = false;
                            $error = $fileResult['error'];
                        }
                    }

                    if ($saveData === true) {
                        // Save data
                        $this->getEntityManager()->persist($category);
                        $this->getEntityManager()->flush();
                        // Back to list
                        $this->flashSession->success(sprintf('Category "%s" info updated successfully!', (string)$category));
                        return $this->response->redirect($this->url->get(['for' => 'article_categories_list']));
                    }

                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('category', 'form', 'action', 'error'));
    }

    /**
     * Category add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $category = new Category();
        $form = new CategoryForm($category, ['edit' => true]);
        $action = $this->url->get(['for' => 'article_categories_add']);
        $error  = '';

        if ($this->request->isPost()) {

            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $category);
                try {
                    if ((int)$category->getParent() > 0) {
                        $parent = $this->getCategoryRepo()->findObjectById($category->getParent());
                        $parent->addChildren($category);
                    } else {
                        $category->setParent(null);
                    }
                
                    $saveData = true;

                    $fileResult = $this->uploadFile($category);
                    if ($fileResult['filesUploaded'] === true) {
                        if ($fileResult['success']) {
                            $category->setImage($fileResult['fileInfo'][0]['filename']);
                            $category->setImagePath($fileResult['fileInfo'][0]['path']);
                            $image = new \Phalcon\Image\Adapter\GD($category->getImagePath());
                            if ($image->getWidth() != 24 || $image->getHeight() != 24) {
                                $image->resize(24, 24);
                                if (!$image->save()) {
                                    $saveData = false;
                                }
                            }
                        } else {
                            $saveData = false;
                            $error = $fileResult['error'];
                        }
                    }

                    if ($saveData === true) {
                        // Save data
                        $this->getEntityManager()->persist($category);
                        $this->getEntityManager()->flush();
                        // Back to list
                        $this->flashSession->success(sprintf('Category "%s" created successfully!', (string)$category));
                        return $this->response->redirect($this->url->get(['for' => 'article_categories_list']));
                    }

                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('category', 'form', 'action', 'error'));
    }

    
    protected function uploadFile($category = null)
    {
        $overwriteFileName = null;
        if ($category->getSlug() !== '') {
            $overwriteFileName = $category->getSlug();
        }

        $result = [
            'success' => false,
            'fileInfo' => [],
            'error' => 'No image uploaded',
            'filesUploaded' => false
        ];

        if ($this->request->hasFiles() !== false) {
            // Simple check if has realy selected files for upload
            $files = $this->request->getUploadedFiles();
            if (sizeof($files) > 0) {
                foreach ($files as $file) {
                    if ($file->getTempName() !== '') {
                        $result['filesUploaded'] = true;
                        break;
                    }
                }
            }
            // Complate with validations
            $this->uploader->setRules($this->getImageRules($overwriteFileName, $category->getImageDirectory()));
            if ($this->uploader->isValid() === true) {
                $this->uploader->move();
                $result['success'] = true;
                $result['fileInfo'] = $this->uploader->getInfo();
                $result['error'] = '';
            } else {
                $result['error'] = 'Image file is not valid';
                foreach ($this->uploader->getErrors() as $value) {
                    $result['error'] = $value;
                    break;
                }
            }
        }

        // Remove all files uploaded when errors occuared
        if ($result['success'] === false) {
            $this->uploader->truncate();
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getImageRules($overwriteFileName = null, $path = '')
    {
        return [
            //'directory' =>  ROOT_PATH . DS . 'public' . DS . 'media' . DS . 'articles',
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

    public function orderingAjaxAction()
    {
        $success = 0;

        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => $success, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        // Get direction
        $direction = $this->dispatcher->getParam('direction');
        if (!in_array($direction, ['up', 'down'])) {
            $this->response->setJsonContent(['success' => $success, 'errors' => sprintf('Invalid direction "%s" value given, allowed only: "up", "down"', $direction)]);
            return $this->response->send();
        }

        // Get ordering category
        $id = $this->dispatcher->getParam('id');
        $category = $this->getCategoryRepo()->findObjectById($id);
        if (null === $category) {
            $this->response->setJsonContent(['success' => $success, 'errors' => sprintf('Category with ID value "%s" not found', $id)]);
            return $this->response->send();
        }

        // Get another ordering category
        $alternateCategory = $this->getCategoryRepo()->getNextObjectForOrderingChange($category->getOrdering(), $category->getParent(), $direction);

        // Complete updates
        if (is_object($alternateCategory)) {
            $newOrderingValue = $alternateCategory->getOrdering();
            $alternateCategory->setOrdering($category->getOrdering());
            $category->setOrdering($newOrderingValue);
            $this->getEntityManager()->flush();
            
            $this->flashSession->success(sprintf('Categories "%s" and "%s" ordering changed successfully!', (string)$category, (string)$alternateCategory));
            $success = 1;
        }

        // Send response
        $this->response->setJsonContent(['success' => $success]);

        return $this->response->send();
    }

    public function loadChildsJsonAction()
    {
        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => 0, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        $parentId = $this->dispatcher->getParam('id');
        $list = $this->getCategoryRepo()->getParentChildsList($parentId);

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        $this->response->setJsonContent(['success' => 1, 'data' => $rows]);

        return $this->response->send();
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
    protected function getParentsOptionsList()
    {
        $rows = $this->getCategoryRepo()->getParentsList();

        $tmp = [];
        foreach ($rows as $row) {
            if ($row['parent'] === null) {
                $tmp[$row['id']] = [
                    'title' => $row['title'],
                    'id' => $row['id'],
                    'childs' => []
                ];
            } else {
                $tmp[$row['parent']]['childs'][$row['id']] = [
                    'title' => $row['title'],
                    'id' => $row['id'],
                    'slug' => $row['slug'],
                ];
            }
        }

        $rows = [
            '0' => 'Root'
        ];
        foreach ($tmp as $item) {
            $rows[$item['id']] = sprintf('- %s', $item['title']);
            if (count($item['childs']) > 0) {
                foreach ($item['childs'] as $childItem) {
                    $rows[$childItem['id']] = sprintf('-- %s', $childItem['title']);
                }
            }
        }

        return $rows;
    }

}
