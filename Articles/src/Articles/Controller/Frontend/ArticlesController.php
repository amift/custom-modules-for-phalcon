<?php

namespace Articles\Controller\Frontend;

use Articles\Entity\Article;
use Articles\Entity\ArticlePoints;
use Articles\Entity\ArticleRate;
use Articles\Entity\Category;
use Articles\Entity\Comment;
use Articles\Entity\CommentRate;
use Articles\Entity\ReportedComment;
use Articles\Forms\AddTextualArticleMemberForm;
use Articles\Forms\AddVideoArticleMemberForm;
use Common\Controller\AbstractFrontendController;
use Common\Tool\StringTool;
use Common\VideoUrl\VideoUrlParser;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Members\Entity\Member;
use Translations\Tool\Group;

class ArticlesController extends AbstractFrontendController
{

    /**
     * @var \Articles\Repository\ArticleRepository
     */
    protected $_articleRepo;

    /**
     * @var \Articles\Repository\ArticleRateRepository
     */
    protected $_articleRateRepo;

    /**
     * @var \Articles\Repository\CategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * @var \Articles\Repository\CommentRepository
     */
    protected $_commentsRepo;

    /**
     * @var \Articles\Repository\CommentRateRepository
     */
    protected $_commentRateRepo;

    /**
     * @var \Articles\Repository\ReportedCommentRepository
     */
    protected $_reportedCommentRepo;

    /**
     * @var \Members\Repository\MemberRepository
     */
    protected $_membersRepo;

    /**
     * Articles list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $contentClass   = $this->config->settings->masonry ? 'pinterest-theme' : 'page-content';
        $category       = $this->dispatcher->getParam('category', 'string', '');
        $subcategory    = $this->dispatcher->getParam('subcategory', 'string', '');

        // Check if category url params realy exists
        if ($category !== '') {
            if ($this->categoriesService->isCategoriesParamsReal($category, $subcategory) === false) {
                $this->dispatcher->forward([
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\Frontend',
                    'controller'    => 'error',
                    'action'        => 'notFound',
                ]);
                return false;
            }
        }

        $enableCoverAdd = false;
        $fullUrl = $this->config->web_url;
        if ($category !== '') {
            //$enableCoverAdd = true;
            $fullUrl.= '/' . $category;
            if ($subcategory !== '') {
                //$enableCoverAdd = true;
                $fullUrl.= '/' . $subcategory;
            }
        }

        // Get articles data
        $params     = $this->categoriesService->getCategoryFilterCriteria($category, $subcategory);
        $articles   = $this->articlesService->getArticlesForOutput($params['name'], $params['value'], $fullUrl);
        $articles['fullUrl'] = $category !== '' ? $fullUrl : '';

        // Get meta data
        $meta = [
            'title' => '',
            'description' => '',
            'keywords' => '',
            'autoKeys' => [],
        ];
        $this->metaData->disableAddTitleSuffix();
        if ($fullUrl !== '') {
            if ($category !== '') {
                $ormCategory1 = $this->categoriesService->getCategoryBySlugFromLevel($category, null, 1);
                if (is_object($ormCategory1)) {
                    $meta['title'] = $ormCategory1->getSeoTitle() !== '' ? $ormCategory1->getSeoTitle() : $ormCategory1->getTitle();
                    $meta['description'] = $ormCategory1->getSeoDescription();
                    $meta['keywords'] = $ormCategory1->getSeoKeywords();
                    $meta['autoKeys'][] = $ormCategory1->getTitle();
                    if ($subcategory !== '') {
                        $ormCategory2 = $this->categoriesService->getCategoryBySlugFromLevel($subcategory, $ormCategory1, 2);
                        if (is_object($ormCategory2)) {
                            $meta['title'] = $ormCategory2->getSeoTitle() !== '' ? $ormCategory2->getSeoTitle() : ($ormCategory1->getTitle() . ', ' . $ormCategory2->getTitle());
                            $meta['description'] = $ormCategory2->getSeoDescription() !== '' ? $ormCategory2->getSeoDescription() : $meta['description'];
                            $meta['keywords'] = $ormCategory2->getSeoKeywords() !== '' ? $ormCategory2->getSeoKeywords() : $meta['keywords'];
                            $meta['autoKeys'][] = $ormCategory2->getTitle();
                        }
                    }
                }
            }
        }

        // Set up meta data
        if ($meta['title'] !== '') {
            $this->metaData->setTitle($meta['title']);
            $this->metaData->enableAddTitleSuffix();
        }
        if ($meta['description'] !== '') {
            $this->metaData->setDescription($meta['description']);
        }
        if ($meta['keywords'] !== '') {
            $this->metaData->setKeywords($meta['keywords']);
        } else {
            $this->metaData->createKeywordsFromText('', 4, true, (count($meta['autoKeys']) > 0 ? implode(', ', $meta['autoKeys']) : ''));
        }
        $this->metaData->setLinkCanonical($fullUrl);

        // Assign data for view
        $categoryParams = $params;
        $this->view->setVars(compact('contentClass', 'articles', 'enableCoverAdd', 'categoryParams', 'category', 'subcategory'));
    }

    /**
     * Article open view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function showAction()
    {
        $contentClass   = 'page-content';
        $category       = $this->dispatcher->getParam('category', 'string', '');
        $subcategory    = $this->dispatcher->getParam('subcategory', 'string', '');
        $articleSlug    = $this->dispatcher->getParam('slug', 'string', '');
        $articleId      = $this->dispatcher->getParam('id', 'string', '');
        $enableCoverAdd = true;

        // Check if category url params realy exists
        if ($this->categoriesService->isCategoriesParamsReal($category, $subcategory) === false) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }

        // Get article data
        $article = $this->articlesService->getArticleById($articleId);

        // Invalid slug and/or ID values combination in URL
        if ($article === null || $article->getSlug() !== $articleSlug || !$article->isAllowedToRate()) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }

        // Get articles data
        $params     = $this->categoriesService->getCategoryFilterCriteria($category, $subcategory);
        $articles   = $this->articlesService->getSideActualArticles($params['name'], $params['value'], [ $article->getId() ]);

        // If authorised member, check if allready rated
        $allreadyRated = true;
        if ($this->auth->isAuthorised()) {
            // If current user is article author then disable rating options otherwise check if realy has rated or not
            $member = $article->getMember();
            if (is_object($member) && $member->getId() === $this->auth->getAuthorisedUserId()) {
                $allreadyRated = true;
            } else {
                $allreadyRated = $this->getArticleRateRepo()->allreadyRated(
                    $article->getId(), $this->auth->getAuthorisedUserId()
                );
            }
        }

        /*// Set up meta data
        $reservedKeys = [];
        $reservedKeys[] = $article->getCategoryLvl1()->getTitle();
        if (is_object($article->getCategoryLvl2())) {
            $reservedKeys[] = $article->getCategoryLvl2()->getTitle();
        }
        if (is_object($article->getCategoryLvl3())) {
            $reservedKeys[] = $article->getCategoryLvl3()->getTitle();
        }
        $this->metaData
            ->setTitle($article->getTitle())
            ->setDescription($article->getSummary())
            ->createKeywordsFromText($article->getTitle() . ' ' . $article->getSummary() . ' ' . $article->getContent(), 4, true, (count($reservedKeys) > 0 ? implode(', ', $reservedKeys) : ''))
            ->enableAddTitleSuffix();*/
        
        // Get meta data
        $meta = [
            'title' => $article->getTitle(),
            'description' => $article->getSummary(),
            'keywords' => '',
            'autoKeys' => []
        ];
        
        $cat1 = $article->getCategoryLvl1();
        if (is_object($cat1)) {
            //$meta['description'] = $cat1->getSeoDescription();
            $meta['keywords'] = $cat1->getSeoKeywords() !== '' ? $cat1->getSeoKeywords() : $meta['keywords'];
            if ($cat1->getTitle() !== '') {
                $meta['autoKeys'][] = $cat1->getTitle();
            }
        }
        $cat2 = $article->getCategoryLvl2();
        if (is_object($cat2)) {
            //$meta['description'] = $cat2->getSeoDescription() !== '' ? $cat2->getSeoDescription() : $meta['description'];
            $meta['keywords'] = $cat2->getSeoKeywords() !== '' ? $cat2->getSeoKeywords() : $meta['keywords'];
            if ($cat2->getTitle() !== '') {
                $meta['autoKeys'][] = $cat2->getTitle();
            }
        }
        $cat3 = $article->getCategoryLvl3();
        if (is_object($cat3)) {
            //$meta['description'] = $cat3->getSeoDescription() !== '' ? $cat3->getSeoDescription() : $meta['description'];
            $meta['keywords'] = $cat3->getSeoKeywords() !== '' ? $cat3->getSeoKeywords() : $meta['keywords'];
            if ($cat3->getTitle() !== '') {
                $meta['autoKeys'][] = $cat3->getTitle();
            }
        }

        // Set up meta data
        if ($meta['title'] !== '') {
            $this->metaData->setTitle($meta['title']);
            $this->metaData->enableAddTitleSuffix();
        }
        if ($meta['description'] !== '') {
            $this->metaData->setDescription($meta['description']);
        }
        $reservedKeywords = $meta['keywords'] !== '' ? ($meta['keywords']) : (count($meta['autoKeys']) > 0 ? implode(', ', $meta['autoKeys']) : '');
        $this->metaData->createKeywordsFromText($article->getTitle() . ' ' . $article->getSummary() . ' ' . $article->getContent(), 4, true, $reservedKeywords);
        $this->metaData->setLinkCanonical($this->config->web_url . $article->getFullUrl());
        if ($article->hasImage()) {
            $this->metaData->setShareImage($article->getImagePublicPath());
        }

        // Assign data for view
        $categoryParams = $params;
        $this->view->setVars(compact('contentClass', 'article', 'articles', 'allreadyRated', 'enableCoverAdd', 'categoryParams'));
    }

    public function addTextArticleAction()
    {
        $contentClass = 'add-new';
        $article = new Article();
        $form = new AddTextualArticleMemberForm($article, []);
        $action = $this->url->get(['for' => 'article_add_textual']);

        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {
                $success = false;
                $errors  = [];

                if ($form->isValid($this->request->getPost())) {
                    $form->bind($this->request->getPost(), $article);
                    try {

                        $article->setSlug(StringTool::createSlug($article->getTitle()));
                        $article->setContent(StringTool::createParagraphTags($article->getContent()));

                        $saveData = true;

                        $fileResult = $this->uploadFile($article);
                        if ($fileResult['filesUploaded'] === true) {
                            if ($fileResult['success']) {
                                $article->setImage($fileResult['fileInfo'][0]['filename']);
                                $article->setImagePath($fileResult['fileInfo'][0]['path']);
                            } else {
                                $saveData = false;
                                $errors['image'] = $fileResult['error'];
                            }
                        }

                        if ($saveData === true) {
                            $article = $this->setRealArticleData($article);

                            $this->getEntityManager()->persist($article);
                            $this->getEntityManager()->flush();

                            $points = new ArticlePoints();
                            $points->setArticle($article);
                            $this->getEntityManager()->persist($points);
                            $this->getEntityManager()->flush();

                            $success = true;
                        }

                    } catch (\Exception $exc) {
                        $errors['title'] = $exc->getMessage();
                    }
                } else {
                    foreach ($form->getElements() as $element) {
                        $name = $element->getName();
                        $messages = $form->getFieldErrorMessages($name);
                        $errors[$name] = $messages !== null ? $messages[0] : '';
                    }
                }

                $this->response->setJsonContent([
                    'success' => $success, 
                    'errors' => $errors, 
                    'redirect' => ($success ? $this->url->get(['for' => 'article_text_added']) : '')
                ]);
                return $this->response->send();
            }
        }

        $this->view->setVars(compact('contentClass', 'form', 'action'));
    }

    public function addVideoArticleAction()
    {
        $contentClass = 'add-new';
        $article = new Article();
        $form = new AddVideoArticleMemberForm($article, []);
        $action = $this->url->get(['for' => 'article_add_video']);

        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {
                $success = false;
                $errors  = [];

                if ($form->isValid($this->request->getPost())) {
                    $form->bind($this->request->getPost(), $article);
                    try {

                        $article = $this->setRealArticleData($article, true);
    
                        $this->getEntityManager()->persist($article);
                        $this->getEntityManager()->flush();

                        $points = new ArticlePoints();
                        $points->setArticle($article);
                        $this->getEntityManager()->persist($points);
                        $this->getEntityManager()->flush();

                        $success = true;

                    } catch (\Exception $exc) {
                        $errors['title'] = $exc->getMessage();
                    }
                } else {
                    foreach ($form->getElements() as $element) {
                        $name = $element->getName();
                        $messages = $form->getFieldErrorMessages($name);
                        $errors[$name] = $messages !== null ? $messages[0] : '';
                    }
                }

                $this->response->setJsonContent([
                    'success' => $success, 
                    'errors' => $errors, 
                    'redirect' => ($success ? $this->url->get(['for' => 'article_video_added']) : '')
                ]);
                return $this->response->send();
            }
        }

        $this->view->setVars(compact('contentClass', 'form', 'action'));
    }

    public function articleTextAddedAction()
    {
        $contentClass = 'my-profile';
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass'));
    }

    public function articleVideoAddedAction()
    {
        $contentClass = 'my-profile';
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass'));
    }

    public function loadChildsJsonAction()
    {
        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => 0, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        $parentId = $this->dispatcher->getParam('id');
        $list = $this->getCategoryRepo()->getParentChildsList($parentId, true);

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        $this->response->setJsonContent(['success' => 1, 'data' => $rows]);

        return $this->response->send();
    }

    /**
     * Get Article entity repository
     * 
     * @access public
     * @return \Articles\Repository\ArticleRepository
     */
    public function getArticleRepo()
    {
        if ($this->_articleRepo === null || !$this->_articleRepo) {
            $this->_articleRepo = $this->getEntityRepository(Article::class);
        }

        return $this->_articleRepo;
    }

    /**
     * Get ArticleRate entity repository
     * 
     * @access public
     * @return \Articles\Repository\ArticleRateRepository
     */
    public function getArticleRateRepo()
    {
        if ($this->_articleRateRepo === null || !$this->_articleRateRepo) {
            $this->_articleRateRepo = $this->getEntityRepository(ArticleRate::class);
        }

        return $this->_articleRateRepo;
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
     * Get Comment entity repository
     * 
     * @access public
     * @return \Articles\Repository\CommentRepository
     */
    public function getCommentRepo()
    {
        if ($this->_commentsRepo === null || !$this->_commentsRepo) {
            $this->_commentsRepo = $this->getEntityRepository(Comment::class);
        }

        return $this->_commentsRepo;
    }

    /**
     * Get CommentRate entity repository
     * 
     * @access public
     * @return \Articles\Repository\CommentRateRepository
     */
    public function getCommentRateRepo()
    {
        if ($this->_commentRateRepo === null || !$this->_commentRateRepo) {
            $this->_commentRateRepo = $this->getEntityRepository(CommentRate::class);
        }

        return $this->_commentRateRepo;
    }

    /**
     * Get ReportedComment entity repository
     * 
     * @access public
     * @return \Articles\Repository\ReportedCommentRepository
     */
    public function getReportedCommentRepo()
    {
        if ($this->_reportedCommentRepo === null || !$this->_reportedCommentRepo) {
            $this->_reportedCommentRepo = $this->getEntityRepository(ReportedComment::class);
        }

        return $this->_reportedCommentRepo;
    }

    /**
     * Get Member entity repository
     * 
     * @access public
     * @return \Members\Repository\MemberRepository
     */
    public function getMemberRepo()
    {
        if ($this->_membersRepo === null || !$this->_membersRepo) {
            $this->_membersRepo = $this->getEntityRepository(Member::class);
        }

        return $this->_membersRepo;
    }

    protected function setRealArticleData($article, $video = false)
    {
        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (is_object($member)) {
            $article->setMember($member);
        } else {
            throw new \Exception(sprintf('Authorised user not found'));
        }

        $article->setCreatedFromIp($this->request->getClientAddress());
        $article->setSlug(StringTool::createSlug($article->getTitle()));

        if ($video) {
            $article->setAsVideoArticle();
        } else {
            $article->setAsTextArticle();
        }

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

        return $article;
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

    public function rateArticleAjaxAction()
    {
        if ($this->request->isAjax() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $type = $this->dispatcher->getParam('type', 'string', '');
        if (!in_array($type, ['plus', 'minus'])) {
            $error = $this->translator->trans('error_invalid_rate_type', 'Invalid rate type', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (!$this->auth->isAuthorised()) {
            $error = $this->translator->trans('error_need_to_authorise', 'Need to authorise', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $article = $this->getArticleRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $article Article */

        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (!is_object($member) || !is_object($article)) {
            $error = $this->translator->trans('error_article_and_or_member_not_found', 'Article and/or member not found', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (is_object($article->getMember()) && $article->getMember()->getId() === $this->auth->getAuthorisedUserId()) {
            $error = $this->translator->trans('error_can_not_rate_own_articles', 'Can not rate own articles', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if ($this->getArticleRateRepo()->allreadyRated($article->getId(), $member->getId())) {
            $error = $this->translator->trans('error_allready_rated_article', 'Allready rated this article', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        try {

            $rate = new ArticleRate();
            $rate->setArticle($article);
            $rate->setMember($member);
            $rate->setRate($type === 'plus' ? 1 : -1);
            $rate->setIpAddress($this->request->getClientAddress());
            $rate->setSessionId($this->session->getId());
            $rate->setUserAgent($this->request->getUserAgent());

            $this->getEntityManager()->persist($rate);

            if ($rate->isPositiveRate()) {
                $article->setRatePlus($article->getRatePlus() + 1);
            } else {
                $article->setRateMinus($article->getRateMinus() + 1);
            }
            $article->setRateAvg($article->getRatePlus() - $article->getRateMinus());

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
            $this->recheckArticleRatingPoints($article->getId());

            $html = $this->translator->trans('lbl_news_rating', 'Reitings') . ' ' . $article->getFormattedRating();

            $this->response->setJsonContent(['success' => 1, 'html' => $html]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error . ' -> ' . $exc->getMessage()]);
        }

        return $this->response->send();
    }

    public function saveArticleCommentAjaxAction()
    {
        if ($this->request->isAjax() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (!$this->auth->isAuthorised()) {
            $error = $this->translator->trans('error_need_to_authorise', 'Need to authorise', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $article = $this->getArticleRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $article Article */

        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (!is_object($member) || !is_object($article)) {
            $error = $this->translator->trans('error_article_and_or_member_not_found', 'Article and/or member not found', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if ($member->isBannedCommenting()) {
            $error = $this->translator->trans('error_member_banned_for_comments_posting', 'You have no permissions to post comments', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $replyOnComment = null;
        $replyOnCommentId = $this->request->getPost('replyOnComment', 'int', 0);
        if ((int)$replyOnCommentId > 0) {
            $replyOnComment = $this->getCommentRepo()->findObjectById($replyOnCommentId);
            /* @var $replyOnComment Comment */
            if (!is_object($replyOnComment)) {
                $error = $this->translator->trans('error_reply_comment_not_found', 'Reply comment not found', Group::ARTICLES);
                $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
                return $this->response->send();
            }
        }

        try {

            // Get comment text and clean tags
            $allowedtags = ['<br>', '<b>', '<i>', '<u>', '<del>'];
            $txtComment = str_replace("\n", '<br>', $this->request->getPost('comment')); // new line to br
            $txtComment = strip_tags($txtComment, implode(', ', $allowedtags)); // leave only allowed tags
            $txtComment = preg_replace('/\s+/', ' ', $txtComment); // trim double spaces

            // Clean blacklisted words
            // @todo

            // Validate length
            if (mb_strlen($txtComment) < 5 || mb_strlen($txtComment) > 2000) {
            $error = $this->translator->trans('error_comment_length', 'At least 5 chars and max 2000 chars allowed.', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
                return $this->response->send();
            }

            // Save
            $comment = new Comment();
            $comment->setArticle($article);
            $comment->setMember($member);
            $comment->setContent($txtComment);
            $comment->setIpAddress($this->request->getClientAddress());
            $comment->setSessionId($this->session->getId());
            $comment->setUserAgent($this->request->getUserAgent());
            if (is_object($replyOnComment)) {
                $comment->setReplyOnComment($replyOnComment);
            }

            $this->getEntityManager()->persist($comment);

            $article->increaseCommentsCount();

            $this->getEntityManager()->flush();

            $lastFilter = $this->session->get('last-comments-filter');

            if (is_array($lastFilter) && array_key_exists('ordering', $lastFilter) && array_key_exists('page', $lastFilter)) {
                list($html, $filters, $paginator) = $this->getCommentsListHtmlSource($article->getId(), $lastFilter['ordering'], $lastFilter['page']);
            } else {
                list($html, $filters, $paginator) = $this->getCommentsListHtmlSource($article->getId());
            }

            $this->response->setJsonContent(['success' => 1, 'html' => $html, 'filters' => $filters, 'paginator' => $paginator]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    public function loadArticleCommentsAjaxAction()
    {
        if ($this->request->isAjax() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $ordering = strtoupper($this->dispatcher->getParam('order', 'string', 'ASC'));
        
        if (!in_array($ordering, ['ASC', 'DESC', 'RATED'])) {
            $error = $this->translator->trans('error_invalid_ordering_value', 'Invalid ordering value', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $article = $this->getArticleRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $article Article */

        if (!is_object($article)) {
            $error = $this->translator->trans('error_article_not_found', 'Article not found', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $page = $this->dispatcher->getParam('page', 'int', 1);

        $this->session->set('last-comments-filter', ['ordering' => $ordering, 'page' => $page]);

        list($html, $filters, $paginator) = $this->getCommentsListHtmlSource($article->getId(), $ordering, $page);

        $this->response->setJsonContent(['success' => 1, 'html' => $html, 'filters' => $filters, 'paginator' => $paginator]);

        return $this->response->send();
    }

    protected function getCommentsListHtmlSource($articleId, $ordering = 'ASC', $page = 1, $perPage = 10)
    {
        $query = $this->getCommentRepo()->getArticleCommentsQuery($articleId, $ordering, $page, $perPage);
        $paginator = new Paginator($query, true);
        $query = null;

        if (count($paginator) < 1) {
            $html = '
                        <div class="col-md-12 col-sm-12 col-xs-12 title">
                            <span class="dd">'. $this->translator->trans('comments_list_block_no_comments_info_text', 'Pašlaik vēl nav neviens komentārs pievienots. Tu vari pirmais izteikt savu viedokli ;)', Group::ARTICLES) . '</span>
                        </div>';

            return [$html, ''];
        }
        
        $txtPlus    = $this->translator->trans('btn_rating_plus_title', 'Plus', Group::ARTICLES);
        $txtMinus   = $this->translator->trans('btn_rating_minus_title', 'Mīnus', Group::ARTICLES);

        $authorised         = $this->auth->isAuthorised();
        $authorisedMemberId = $this->auth->getAuthorisedUserId();

        $txtLast  = $this->translator->trans('link_text_load_last_comments', 'Jaunākie', Group::ARTICLES);
        $txtFirst = $this->translator->trans('link_text_load_first_comments', 'Vecākie', Group::ARTICLES);
        $txtRated = $this->translator->trans('link_text_load_rated_comments', 'Labākie', Group::ARTICLES);
        $txtReport = $this->translator->trans('link_text_report_comment', 'Ziņot', Group::ARTICLES);
        $txtReply = $this->translator->trans('link_text_reply_comment', 'Atbildēt', Group::ARTICLES);
        $txtReplyInfoTextMask = $this->translator->trans('comment_reply_info_text_mask', 'Atbilde uz %skomentāru%s no %s', Group::ARTICLES);
        $txtReplyCommentBlocked = $this->translator->trans('comment_replied_is_blocked', 'Bloķēts komentārs!', Group::ARTICLES);

        $urlSaveComment = $this->url->get(['for' => 'article_save_comment', 'id' => $articleId]);

        $html = $htmlFilters = $htmlPaginator = '';

        $htmlFilters = '
                            <a' . ($ordering == 'ASC' ? ' class="active"' : '') . ' href="javascript:;" onclick="comments.load(\''. $this->url->get(['for' => 'article_load_comments', 'id' => $articleId, 'order' => 'asc', 'page' => '1']) . '\');" title="'. $txtFirst . '">'. $txtFirst . '</a>
                            <a' . ($ordering == 'DESC' ? ' class="active"' : '') . ' href="javascript:;" onclick="comments.load(\''. $this->url->get(['for' => 'article_load_comments', 'id' => $articleId, 'order' => 'desc', 'page' => '1']) . '\');" title="'. $txtLast . '">'. $txtLast . '</a>
                            <a' . ($ordering == 'RATED' ? ' class="active"' : '') . ' href="javascript:;" onclick="comments.load(\''. $this->url->get(['for' => 'article_load_comments', 'id' => $articleId, 'order' => 'rated', 'page' => '1']) . '\');" title="'. $txtRated . '">'. $txtRated . '</a>';

        $badWords = (array)$this->config->blacklisted_words;
        $replacementWords = '***';//[];
        
        foreach ($paginator as $comment) {
            $member = $comment->getMember();

            $memberName = is_object($member) ? ucfirst((string)$member) : 'Unknown';
            $memberLetter = substr($memberName, 0, 1);

            $commentId = $comment->getId();

            $allreadyRated = true;
            if ($authorised) {
                if (is_object($member) && $member->getId() === $authorisedMemberId) {
                    $allreadyRated = true;
                } else {
                    $allreadyRated = $this->getCommentRateRepo()->allreadyRated(
                        $comment->getId(), $authorisedMemberId
                    );
                }
            }
            
            $replyOnComment = $comment->getReplyOnComment();

            $row = '
                        <div class="row single-comment">
                            <div class="col-md-9 col-sm-9 col-xs-12 user">
                                <span class="avatar random2">' . $memberLetter . '</span>
                                <span class="date">' . $comment->getFormattedDate() . '</span>
                                <span class="username">
                                    ' . $memberName . '<span class="user-status"><i class="fa fa-certificate"></i><i class="fa fa-certificate"></i><i class="fa fa-certificate"></i><i class="fa fa-certificate"></i><i class="fa fa-certificate"></i></span>
                                </span>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-12 rate" id="comment'.$commentId.'RatingBlock">
                                <span class="rating">
                                    ' . $comment->getFormattedRating() . '
                                </span><span class="buttons"><a href="javascript:;" title="'.$txtPlus.'" class="rate plus" onclick="rating.saveCommentRate('.$commentId.', \''. $this->url->get(['for' => 'comment_save_rate', 'type' => 'plus', 'id' => $commentId]) . '\');">
                                    <i class="fa fa-plus"></i>
                                </a><a href="javascript:;" title="'.$txtMinus.'" class="rate minus" onclick="rating.saveCommentRate('.$commentId.', \''. $this->url->get(['for' => 'comment_save_rate', 'type' => 'minus', 'id' => $commentId]) . '\');">
                                    <i class="fa fa-minus"></i>
                                </a></span>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 comment">';
            if (is_object($replyOnComment)) {
                $replyOnCommentMember = $replyOnComment->getMember();
                $replyOnCommentMemberName = is_object($replyOnCommentMember) ? ucfirst((string)$replyOnCommentMember) : 'Unknown';
                $replyInfoText = sprintf($txtReplyInfoTextMask, '<a href="javascript:;" onclick="comments.showHideReply('.$commentId.');">', '</a>', '<span class="username">'.$replyOnCommentMemberName.'</span>');
                $row.= '
                                <span class="reply-on">'.$replyInfoText.'</span>
                                <p id="comm'.$commentId.'reply" class="commented hide">' . ($replyOnComment->isBlocked() ? $txtReplyCommentBlocked : str_ireplace($badWords, $replacementWords, strip_tags($replyOnComment->getContent(), '<br>'))) . '</p>';
            }
            $row.= '
                                <p>' . str_ireplace($badWords, $replacementWords, $comment->getContent()) . '</p>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-6 answer">';
            if ($authorised) {
                $row.= '
                                <a href="javascript:;" onclick="comments.loadReplyForm('.$commentId.', \''. $urlSaveComment . '\');" title="'.$txtReply.'">'.$txtReply.'</a>';
            }
            $row.= '
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-6 report" id="comment'.$commentId.'ReportBlock">
                                <a href="javascript:;" onclick="comments.report('.$commentId.', \''. $this->url->get(['for' => 'comment_save_reporting', 'id' => $commentId]) . '\');" title="'.$txtReport.'">'.$txtReport.'</a>
                            </div>';
            if ($authorised) {
                $row.= '
                            <div class="hided" id="comment'.$commentId.'ReplyBlock" style="display:none;">
                                <div class="col-md-9 col-sm-9 col-xs-12 textarea">
                                    <textarea></textarea>
                                </div>
                                <div class="col-md-3 col-sm-3 col-xs-12 submit">
                                    <a href="javascript:;" onclick="comments.saveReply('.$commentId.', \''. $urlSaveComment . '\');" title="Pievienot" class="publish">Pievienot</a>
                                </div>
                            </div>';
            }
            $row.= '
                        </div>';
            $html.= $row;
        }

        $htmlPaginator = $this->gridPagerAjax->links($articleId, 'article_load_comments', $paginator, $page, $perPage, 7, strtolower($ordering));
        $paginator = null;

        return [$html, $htmlFilters, $htmlPaginator];
    }

    public function rateCommentAjaxAction()
    {
        if ($this->request->isAjax() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $type = $this->dispatcher->getParam('type', 'string', '');
        if (!in_array($type, ['plus', 'minus'])) {
            $error = $this->translator->trans('error_invalid_rate_type', 'Invalid rate type', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (!$this->auth->isAuthorised()) {
            $error = $this->translator->trans('error_need_to_authorise', 'Need to authorise', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $comment = $this->getCommentRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $comment Comment */

        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (!is_object($member) || !is_object($comment)) {
            $error = $this->translator->trans('error_comment_and_or_member_not_found', 'Comment and/or member not found', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (is_object($comment->getMember()) && $comment->getMember()->getId() === $this->auth->getAuthorisedUserId()) {
            $error = $this->translator->trans('error_can_not_rate_own_comments', 'Can not rate own comments', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if ($this->getCommentRateRepo()->allreadyRated($comment->getId(), $member->getId())) {
            $error = $this->translator->trans('error_allready_rated_comment', 'Allready rated this comment', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        try {

            $rate = new CommentRate();
            $rate->setComment($comment);
            $rate->setMember($member);
            $rate->setRate($type === 'plus' ? 1 : -1);
            $rate->setIpAddress($this->request->getClientAddress());
            $rate->setSessionId($this->session->getId());
            $rate->setUserAgent($this->request->getUserAgent());

            $this->getEntityManager()->persist($rate);

            if ($rate->isPositiveRate()) {
                $comment->setRatePlus($comment->getRatePlus() + 1);
            } else {
                $comment->setRateMinus($comment->getRateMinus() + 1);
            }
            $comment->setRateAvg($comment->getRatePlus() - $comment->getRateMinus());

            $this->getEntityManager()->flush();

            $html = $comment->getFormattedRating();

            $this->response->setJsonContent(['success' => 1, 'html' => $html]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    public function reportCommentAjaxAction()
    {
        if ($this->request->isAjax() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $comment = $this->getCommentRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $comment Comment */

        if (!is_object($comment)) {
            $error = $this->translator->trans('error_comment_not_found', 'Comment not found', Group::ARTICLES);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $member = null;
        if ($this->auth->isAuthorised()) {
            $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
            /* @var $member Member */
        }

        /*if (is_object($member)) {
            if ($this->getReportedCommentRepo()->allreadyReportedByMember($comment->getId(), $member->getId())) {
                $error = $this->translator->trans('error_allready_reported_comment', 'Allready reported this comment', Group::ARTICLES);
                $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
                return $this->response->send();
            }
        } else {
            if ($this->getReportedCommentRepo()->allreadyReportedByGuest($comment->getId(), $this->request->getClientAddress())) {
                $error = $this->translator->trans('error_allready_reported_comment', 'Allready reported this comment', Group::ARTICLES);
                $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
                return $this->response->send();
            }
        }*/

        try {

            $reported = new ReportedComment();
            $reported->setComment($comment);
            if (is_object($member)) {
                $reported->setMember($member);
            }
            $reported->setIpAddress($this->request->getClientAddress());
            $reported->setSessionId($this->session->getId());
            $reported->setUserAgent($this->request->getUserAgent());
            //$reported->setReason();
            //$reported->setNote();

            $this->getEntityManager()->persist($reported);
            $this->getEntityManager()->flush();

            $html = $this->translator->trans('comment_report_send', 'Nosūtīts', Group::ARTICLES);

            $this->response->setJsonContent(['success' => 1, 'html' => $html]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    protected function recheckArticleRatingPoints($articleId)
    {
        $article = $this->getArticleRepo()->find($articleId);

        if (null === $article) {
            throw new \Exception(sprintf('Article with ID "%s" not found for points rechecking', $articleId));
        }

        // Check points config
        $config = $this->config->article_points;
        $propertiesNeeded = ['rate_level_1', 'rate_level_2', 'rate_level_3'];
        foreach ($propertiesNeeded as $property) {
            if (property_exists($config, $property) !== true) {
                throw new \Exception(sprintf('Points config parameter "%s" not found', $property));
            }
            $value = $config->$property;
            if (is_int($value) !== true) {
                throw new \Exception(sprintf('Points config parameter "%s" value "%s" is not valid', $property, $value));
            }
        }

        // Check rate avg levels values
        $configLevel = $this->config->rate_level_values;
        $ratesNeeded = ['rate_level_1', 'rate_level_2', 'rate_level_3'];
        foreach ($ratesNeeded as $property) {
            if (property_exists($configLevel, $property) !== true) {
                throw new \Exception(sprintf('Rate level config parameter "%s" not found', $property));
            }
            $value = $configLevel->$property;
            if (is_int($value) !== true) {
                throw new \Exception(sprintf('Rate level config parameter "%s" value "%s" is not valid', $property, $value));
            }
        }

        // Update points if need
        $hasPtsChanged = false;
        if ($article->isAllowedToRate()) {
            $date = new \DateTime('now');
            $avgRate = $article->getRateAvg();
            $points = $article->getPoints();
            $newPts = 0;
            // 1 lvl
            if ($points->getRating1LvlPtsAt() === null) {
                if ($avgRate >= $configLevel->rate_level_1) {
                    $newPts = $newPts + $config->rate_level_1;
                    $points->setRating1LvlPts($config->rate_level_1);
                    $points->setRating1LvlPtsAt($date);
                    $hasPtsChanged = true;
                }
            } else {
                // 2 lvl
                if ($points->getRating2LvlPtsAt() === null) {
                    if ($avgRate >= $configLevel->rate_level_2) {
                        $newPts = $newPts + $config->rate_level_2;
                        $points->setRating2LvlPts($config->rate_level_2);
                        $points->setRating2LvlPtsAt($date);
                        $hasPtsChanged = true;
                    }
                } else {
                    // 3 lvl
                    if ($points->getRating3LvlPtsAt() === null) {
                        if ($avgRate >= $configLevel->rate_level_3) {
                            $newPts = $newPts + $config->rate_level_3;
                            $points->setRating3LvlPts($config->rate_level_3);
                            $points->setRating3LvlPtsAt($date);
                            $hasPtsChanged = true;
                        }
                    }
                }
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

    public function getArticlePreviewDataAjaxAction()
    {
        if ($this->request->isAjax() !== true || $this->request->isPost() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (!$this->auth->isAuthorised()) {
            $error = $this->translator->trans('error_need_to_authorise', 'Need to authorise', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        try {
            $title = $this->request->getPost('title');
            $summary = $this->request->getPost('summary');
            $summary = trim($summary) !== '' ? '<span class="dd">'.$summary.'</span>' : '';
            $content = StringTool::createParagraphTags($this->request->getPost('content'));
            $content = trim($content) !== '' ? '<div class="col-md-12">'.$content.'</div>' : '';
            $video = $this->request->getPost('video');
            if (trim($video) !== '') {
                $video = VideoUrlParser::getIframeSource(null, $video);
                $video = trim($video) !== '' ? '<div class="col-md-12">'.$video.'</div>' : '';
            }
            $html = '
                        <div class="row overall">
                            <div class="col-md-12 title">
                                <h1>' . $title . '</h1>
                                ' . $summary . '
                            </div>
                        </div>
                        <div class="row news-body">
                            '.$video.'
                            '.$content.'
                        </div>';
            $this->response->setJsonContent(['success' => 1, 'html' => $html]);
        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    public function loadMoreAjaxAction()
    {
        if ($this->request->isAjax() !== true || $this->request->isPost() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        try {
            $page = $this->request->getPost('page', 'int', 1);
            if (!is_numeric($page)) {
                throw new \Exception('Invalid page requested');
            }
            $category = $this->request->getPost('category', 'string', '');
            $subcategory = $this->request->getPost('subcategory', 'string', '');

            // Check if category url params realy exists
            if ($category !== '') {
                if ($this->categoriesService->isCategoriesParamsReal($category, $subcategory) === false) {
                    throw new \Exception('Invalid category parameters');
                }
            }

            // Set full url
            $fullUrl = $this->config->web_url;
            if ($category !== '') {
                $fullUrl.= '/' . $category;
                if ($subcategory !== '') {
                    $fullUrl.= '/' . $subcategory;
                }
            }

            // Get articles data
            $perPage = 4;
            $params = $this->categoriesService->getCategoryFilterCriteria($category, $subcategory);
            $paginator = $this->getArticleRepo()->getCenterArticles(
                $params['name'], $params['value'], [], $page, $perPage
            );

            //
            $articleIds = [];
            $articlesHtml = $buttonHtml = '';
            if ($paginator !== null && count($paginator) > 0) :

                $e = new \Phalcon\Escaper();

                foreach ($paginator as $article) :
                /* @var $article Article */

                    $articleIds[] = $article->getId();
                    //$videoImage = $article->getVideoSourceImagePath();
                    //$isVideo = $videoImage !== '' ? tue : false;

                    $isVideo = $article->isVideo();

                    $articleRow = '
                    <li class="article">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12 col-sm-12 col-xs-12 pic">
                                    <span class="time">'. $article->getListViewDate() .'<span class="category">' . ($isVideo ? '<i class="fa fa-video-camera"></i>' : '') . $article->getCategoryLvl1()->getTitle() . '</span></span>';
                    if ($article->hasImage()) :
                        $articleRow.= '
                                    <a href="'. $article->getFullUrl() .'" title="'. $e->escapeHtmlAttr($article->getTitle()) .'" class="placeholder">
                                        <img src="'. $article->getImagePublicPath() .'" class="img-responsive" title="'. $e->escapeHtmlAttr($article->getTitle()) .'" border="0" />
                                    </a>';
                    elseif ($isVideo) :
                        $articleRow.= $article->getFormattedVideoSource();
                    else :
                        $articleRow.= '<br>';
                    endif;
                        $articleRow.= '
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12 article">
                                    <a href="'. $article->getFullUrl() .'" title="'. $e->escapeHtmlAttr($article->getTitle()).'" class="link">'. $article->getTitle().'</a>
                                    <span class="dd">'. $article->getSummary() .' <a href="'. $article->getFullUrl().'" class="comment">'. $article->getFormattedCommentsCount().'</a></span>
                                </div>
                            </div>
                        </div>
                    </li>';
                    $articlesHtml.= $articleRow;
                endforeach;

                if (trim($articlesHtml) !== '') {
                    $txtLoadMore = $this->translator->trans('articles-load-more', 'Ielādēt vēl', Group::ARTICLES);
                    $nextPage = $page+1;
                    $buttonHtml = '<a href="javascript:;" onclick="articles.loadMore(\'' . $this->url->get(['for' => 'articles_load_more']) . '\', \'' . (string)$category . '\', \'' . (string)$subcategory . '\', '.$nextPage.');" rel="nofollow" title="' . $txtLoadMore . '">' . $txtLoadMore . '</a>';
                }
            endif;

            $this->response->setJsonContent(['success' => 1, /*'articleIds' => $articleIds, */'page' => $page, 'html' => $articlesHtml, 'button' => $buttonHtml]);
        } catch (\Exception $exc) {
            $error = $exc->getMessage();//$this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }
}
