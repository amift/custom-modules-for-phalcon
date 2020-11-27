<?php

namespace Articles\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Common\Tool\StringTool;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Articles\Entity\Article;
use Articles\Entity\ArticlePoints;
use Articles\Entity\Category;
use Articles\Forms\ArticleForm;

class ArticlesController extends AbstractBackendController
{

    /**
     * @var \Articles\Repository\ArticleRepository
     */
    protected $_articlesRepo;

    /**
     * @var \Articles\Repository\CategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * Articles list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->articles;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $categories = $this->getCategoriesOptionsFiltersList();

        $qb = $this->getArticleRepo()->createQueryBuilder('a')
                ->select('a')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('a.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state')
            ->addField('type')
            ->addField('promo')
            ->addField('actual')
            ->addField('startpage')
            ->addField('publicationDateFrom', Filters::TYPE_DATE, 'a.publicationDate', Filters::COMP_GTE)
            ->addField('publicationDateTo', Filters::TYPE_DATE, 'a.publicationDate', Filters::COMP_LTE)
            ->addField('member')
            ->addField('categoryLvl1', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('a.categoryLvl1 = :categoryLvl1');
                    $qb->setParameter('categoryLvl1', $value);
                }
            })
            ->addField('categoryLvl2', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('a.categoryLvl2 = :categoryLvl2');
                    $qb->setParameter('categoryLvl2', $value);
                }
            })
            ->addField('categoryLvl3', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('a.categoryLvl3 = :categoryLvl3');
                    $qb->setParameter('categoryLvl3', $value);
                }
            })
            ->searchInFields('search', [ 
                'a.title', 'a.slug', 'a.content', 'a.summary', 'a.sourceUrl', 
                'a.mediaSourceUrl', 'a.mediaSourceName', 'a.createdFromIp'
            ])
        ;

        $filters->apply($qb, 'a');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters', 'categories'));
    }

    /**
     * Article add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $article = new Article();
        $form = new ArticleForm($article, ['edit' => true]);
        $action = $this->url->get(['for' => 'articles_add']);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $article);

                $saveData = true;

                // Validate image file if need
                if ($article->isNews()) {
                    $fileResult = $this->uploadFile($article);
                    if ($fileResult['filesUploaded'] === true) {
                        if ($fileResult['success']) {
                            $article->setImage($fileResult['fileInfo'][0]['filename']);
                            $article->setImagePath($fileResult['fileInfo'][0]['path']);
                        } else {
                            $saveData = false;
                            $error = $fileResult['error'];
                        }
                    }
                }

                if ($saveData === true) {

                    if ((int)$article->getCategoryLvl1() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($article->getCategoryLvl1());
                        $article->setCategoryLvl1($category);
                    } else {
                        $article->setCategoryLvl1(null);
                    }

                    if ((int)$article->getCategoryLvl2() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($article->getCategoryLvl2());
                        $article->setCategoryLvl2($category);
                    } else {
                        $article->setCategoryLvl2(null);
                    }

                    if ((int)$article->getCategoryLvl3() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($article->getCategoryLvl3());
                        $article->setCategoryLvl3($category);
                    } else {
                        $article->setCategoryLvl3(null);
                    }

                    $d = \DateTime::createFromFormat('d/m/Y H:i:s', $article->getPublicationDate());
                    $article->setPublicationDate($d);

                    $article->setContent(StringTool::createParagraphTags($article->getContent()));

                    // Save data
                    $this->getEntityManager()->persist($article);
                    $this->getEntityManager()->flush();

                    // Set default points values
                    $points = new ArticlePoints();
                    $points->setArticle($article);
                    $this->getEntityManager()->persist($points);
                    $this->getEntityManager()->flush();

                    // Reckeck points
                    $this->getEntityManager()->refresh($article);
                    $this->recheckArticlePublicationPoints($article->getId());

                    // Back to list
                    $this->flashSession->success(sprintf('Article "%s" added successfully!', (string)$article));
                    return $this->response->redirect($this->url->get(['for' => 'articles_list']));
                }
            }
        }

        $this->view->setVars(compact('article', 'form', 'action', 'error'));
    }

    /**
     * Article edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $article = $this->getArticleRepo()->findObjectById($id);

        if (null === $article) {
            $this->flashSession->error(sprintf('Article with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'articles_list']));
        }

        $form = new ArticleForm($article, ['edit' => true]);
        $action = $this->url->get(['for' => 'articles_edit', 'id' => $article->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $article);

                $saveData = true;

                // Validate image file if need
                if ($article->isNews()) {
                    $fileResult = $this->uploadFile($article);
                    if ($fileResult['filesUploaded'] === true) {
                        if ($fileResult['success']) {
                            $article->setImage($fileResult['fileInfo'][0]['filename']);
                            $article->setImagePath($fileResult['fileInfo'][0]['path']);
                        } else {
                            $saveData = false;
                            $error = $fileResult['error'];
                        }
                    }
                }

                if ($saveData === true) {

                    if ((int)$article->getCategoryLvl1() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($article->getCategoryLvl1());
                        $article->setCategoryLvl1($category);
                    } else {
                        $article->setCategoryLvl1(null);
                    }

                    if ((int)$article->getCategoryLvl2() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($article->getCategoryLvl2());
                        $article->setCategoryLvl2($category);
                    } else {
                        $article->setCategoryLvl2(null);
                    }

                    if ((int)$article->getCategoryLvl3() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($article->getCategoryLvl3());
                        $article->setCategoryLvl3($category);
                    } else {
                        $article->setCategoryLvl3(null);
                    }

                    $d = \DateTime::createFromFormat('d/m/Y H:i:s', $article->getPublicationDate());
                    $article->setPublicationDate($d);

                    $article->setContent(StringTool::createParagraphTags($article->getContent()));

                    // Save data
                    $this->getEntityManager()->persist($article);
                    $this->getEntityManager()->flush();

                    // Set default points values if not exists
                    if (!is_object($article->getPoints())) {
                        $points = new ArticlePoints();
                        $points->setArticle($article);
                        $this->getEntityManager()->persist($points);
                        $this->getEntityManager()->flush();
                    }

                    // Reckeck points
                    $this->getEntityManager()->refresh($article);
                    $this->recheckArticlePublicationPoints($article->getId());

                    // Back to list
                    $this->flashSession->success(sprintf('Article "%s" info updated successfully!', (string)$article));
                    return $this->response->redirect($this->url->get(['for' => 'articles_list']));
                }
            }
        }

        $this->view->setVars(compact('article', 'form', 'action', 'error'));
    }

    protected function recheckArticlePublicationPoints($articleId)
    {
        $article = $this->getArticleRepo()->find($articleId);

        if (null === $article) {
            throw new \Exception(sprintf('Article with ID "%s" not found for points rechecking', $articleId));
        }

        // Check points config
        $config = $this->config->article_points;
        $propertiesNeeded = ['published', 'startpage', 'promo'];
        foreach ($propertiesNeeded as $property) {
            if (property_exists($config, $property) !== true) {
                throw new \Exception(sprintf('Points config parameter "%s" not found', $property));
            }
            $value = $config->$property;
            if (is_int($value) !== true) {
                throw new \Exception(sprintf('Points config parameter "%s" value "%s" is not valid', $property, $value));
            }
        }

        // Update points if need
        $hasPtsChanged = false;
        if ($article->isActive()) {
            $date = new \DateTime('now');
            $points = $article->getPoints();
            $newPts = 0;
            // publication
            if ($points->getPublicationPtsAt() === null) {
                $newPts = $newPts + $config->published;
                $points->setPublicationPts($config->published);
                $points->setPublicationPtsAt($date);
                $hasPtsChanged = true;
            }
            // startpage
            if ($article->isStartpage() && $points->getStartpagePtsAt() === null) {
                $newPts = $newPts + $config->startpage;
                $points->setStartpagePts($config->startpage);
                $points->setStartpagePtsAt($date);
                $hasPtsChanged = true;
            }
            // promo
            if ($article->isPromo() && $points->getPromoPtsAt() === null) {
                $newPts = $newPts + $config->promo;
                $points->setPromoPts($config->promo);
                $points->setPromoPtsAt($date);
                $hasPtsChanged = true;
            }
            // save points
            if ($hasPtsChanged === true) {
                
                // Update member totals
                $member = $article->getMember();
                if (is_object($member)) {
                    $memberPoints = $member->getTotalPointsData();
                    $currentEarnedPts = $memberPoints->getTotalEarned();
                    $memberPoints->setTotalEarned($currentEarnedPts + $newPts);
                    $memberPoints->recalculateActual();
                }

                // Save all to DB
                $this->getEntityManager()->flush();
            }
        }
    }

    protected function uploadFile($article = null)
    {
        $overwriteFileName = null;
        if ($article->getSlug() !== '') {
            $overwriteFileName = $article->getSlug();
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
            $this->uploader->setRules($this->getImageRules($overwriteFileName, $article->getImageDirectory()));
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
            'maxsize' => 5242880, // 5 MB
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
