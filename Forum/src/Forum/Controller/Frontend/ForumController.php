<?php

namespace Forum\Controller\Frontend;

use Common\Controller\AbstractFrontendController;
use Common\Tool\StringTool;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Forum\Entity\ForumCategory;
use Forum\Entity\ForumReply;
use Forum\Entity\ForumReplyRate;
use Forum\Entity\ForumReportedReply;
use Forum\Entity\ForumTopic;
use Forum\Entity\ForumTopicRate;
use Forum\Forms\AddTopicMemberForm;
use Members\Entity\Member;
use Translations\Tool\Group;

class ForumController extends AbstractFrontendController
{

    /**
     * @var \Forum\Repository\ForumCategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * @var \Forum\Repository\ForumTopicRepository
     */
    protected $_topicsRepo;

    /**
     * @var \Forum\Repository\ForumTopicRateRepository
     */
    protected $_topicsRateRepo;

    /**
     * @var \Forum\Repository\ForumReplyRepository
     */
    protected $_repliesRepo;

    /**
     * @var \Forum\Repository\ForumReplyRateRepository
     */
    protected $_replyRateRepo;

    /**
     * @var \Members\Repository\MemberRepository
     */
    protected $_membersRepo;

    /**
     * Forum view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction($category1 = '', $category2 = '', $category3 = '')
    {
        // Check if category url params realy exists
        if ($category1 !== '') {
            if ($this->forumCategoriesService->isCategoriesParamsReal($category1, $category2, $category3) === false) {
                $this->dispatcher->forward([
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\Frontend',
                    'controller'    => 'error',
                    'action'        => 'notFound',
                ]);
                return false;
            }
        }

        // Load items
        $action = 'topics';
        if ($this->forumCategoriesService->hasSubcategories($category1, $category2, $category3)) {
            $action = 'categories';
        }

        $this->dispatcher->forward([
            'module'        => 'Forum',
            'namespace'     => 'Forum\Controller\Frontend',
            'controller'    => 'forum',
            'action'        => $action,
        ]);
    }

    /**
     * Forum categories list view.
     * 
     * @access public
     * @param string $category1
     * @param string $category2
     * @param string $category3
     * @return \Phalcon\Mvc\View
     */
    public function categoriesAction($category1 = '', $category2 = '', $category3 = '')
    {
        $contentClass = 'forum-content';
        $enableCoverAdd = false;
        $fullUrl = $this->getFullListViewUrl($category1, $category2, $category3);
        $categories = $this->forumCategoriesService->getListItems($category1, $category2, $category3);
        
        $listBaseUrl = rtrim($this->config->web_url . $this->url->get(['for' => 'forum_list']), '/');
        if ($category1 !== '' && $category2 !== '') {
            $listBaseUrl.= '/' . $category1;
            if ($category2 !== '' && $category3 !== '') {
                $listBaseUrl.= '/' . $category2;
            }
        }

        // Set up valid meta data
        list($meta, $finalCategory) = $this->getMetaAndFinalCategoryData($fullUrl, $category1, $category2, $category3);
        $breadcrumbUrls = $this->getBreadcrumbUrls($finalCategory);

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
        $this->view->setVars(compact('contentClass', 'enableCoverAdd', 'fullUrl', 'breadcrumbUrls', 'listBaseUrl', 'categories'));
    }

    /**
     * Forum topics list view.
     * 
     * @access public
     * @param string $category1
     * @param string $category2
     * @param string $category3
     * @return \Phalcon\Mvc\View
     */
    public function topicsAction($category1 = '', $category2 = '', $category3 = '')
    {
        $contentClass = 'forum-content';
        $enableCoverAdd = false;
        $fullUrl = $this->getFullListViewUrl($category1, $category2, $category3);
        
        $listBaseUrl = rtrim($this->config->web_url . $this->url->get(['for' => 'forum_list']), '/');
        if ($category1 !== '' && $category2 !== '') {
            $listBaseUrl.= '/' . $category1;
            if ($category2 !== '' && $category3 !== '') {
                $listBaseUrl.= '/' . $category2;
            }
        }

        // Set up valid meta data
        list($meta, $finalCategory) = $this->getMetaAndFinalCategoryData($fullUrl, $category1, $category2, $category3);
        $breadcrumbUrls = $this->getBreadcrumbUrls($finalCategory);

        // Get topics
        $topics['currentPage'] = (int)$this->request->getQuery('page', 'int', 1);
        $topics['perPage'] = 20;
        $topics['paginator'] = $this->getForumTopicRepo()->getTopics(
            $finalCategory, $topics['currentPage'], $topics['perPage']
        );

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
        $this->view->setVars(compact('contentClass', 'enableCoverAdd', 'fullUrl', 'breadcrumbUrls', 'listBaseUrl', 'finalCategory', 'topics'));
    }

    /**
     * Topic open view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function showAction($repliesOrder = 'asc')
    {
        $contentClass   = 'page-content';
        $enableCoverAdd = false;
        $category1      = $this->dispatcher->getParam('category1', 'string', '');
        $category2      = $this->dispatcher->getParam('category2', 'string', '');
        $category3      = $this->dispatcher->getParam('category3', 'string', '');
        $topicSlug      = $this->dispatcher->getParam('slug', 'string', '');
        $topicId        = $this->dispatcher->getParam('id', 'string', '');
        $repliesOrder   = strtoupper((string)$repliesOrder);
        $repliesPage   = (int)$this->request->getQuery('page', 'int', 1);

        if ($repliesPage < 1 || !in_array($repliesOrder, ['ASC', 'DESC', 'RATED'])) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }

        // Check if category url params realy exists
        if ($category1 !== '') {
            if ($this->forumCategoriesService->isCategoriesParamsReal($category1, $category2, $category3) === false) {
                $this->dispatcher->forward([
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\Frontend',
                    'controller'    => 'error',
                    'action'        => 'notFound',
                ]);
                return false;
            }
        }

        // Get topic data
        $topic = $this->getForumTopicRepo()->findObjectById($topicId);

        // Invalid slug and/or ID values combination in URL
        if ($topic === null || $topic->getSlug() !== $topicSlug) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }

        // If authorised member, check if topic rated
        $allreadyRated = true;
        if ($this->auth->isAuthorised()) {
            // If current user is topic author then disable rating options otherwise check if realy has rated or not
            $member = $topic->getMember();
            if (is_object($member) && $member->getId() === $this->auth->getAuthorisedUserId()) {
                $allreadyRated = true;
            } else {
                $allreadyRated = $this->getForumTopicRateRepo()->allreadyRated(
                    $topic->getId(), $this->auth->getAuthorisedUserId()
                );
            }
        }

        // Set up valid meta data
        $fullUrl = $this->getFullListViewUrl($category1, $category2, $category3);
        $meta = [
            'title' => $topic->getTitle(),
            'description' => $this->metaData->getDescriptionFromText($topic->getContent(), ' ...'),
            'keywords' => '',
            'autoKeys' => [],
        ];
        $finalCategory = null;
        $this->metaData->disableAddTitleSuffix();
        if ($fullUrl !== '') {
            if ($category1 !== '') {
                $ormCategory1 = $this->forumCategoriesService->getCategoryBySlugFromLevel($category1, null, 1);
                if (is_object($ormCategory1)) {
                    //$meta['title'] = (string)$ormCategory1->getSeoTitle() !== '' ? $ormCategory1->getSeoTitle() : $ormCategory1->getTitle();
                    //$meta['description'] = $ormCategory1->getSeoDescription();
                    $meta['keywords'] = $ormCategory1->getSeoKeywords();
                    $meta['autoKeys'][] = $ormCategory1->getTitle();
                    $finalCategory = $ormCategory1;
                    if ($category2 !== '') {
                        $ormCategory2 = $this->forumCategoriesService->getCategoryBySlugFromLevel($category2, $ormCategory1, 2);
                        if (is_object($ormCategory2)) {
                            //$meta['title'] = (string)$ormCategory2->getSeoTitle() !== '' ? $ormCategory2->getSeoTitle() : ($ormCategory1->getTitle() . ', ' . $ormCategory2->getTitle());
                            //$meta['description'] = $ormCategory2->getSeoDescription() !== '' ? $ormCategory2->getSeoDescription() : $meta['description'];
                            $meta['keywords'] = $ormCategory2->getSeoKeywords() !== '' ? $ormCategory2->getSeoKeywords() : $meta['keywords'];
                            $meta['autoKeys'][] = $ormCategory2->getTitle();
                            $finalCategory = $ormCategory2;
                            if ($category3 !== '') {
                                $ormCategory3 = $this->forumCategoriesService->getCategoryBySlugFromLevel($category3, $ormCategory2, 3);
                                if (is_object($ormCategory3)) {
                                    //$meta['title'] = (string)$ormCategory3->getSeoTitle() !== '' ? $ormCategory3->getSeoTitle() : ($ormCategory1->getTitle() . ', ' . $ormCategory2->getTitle() . ', ' . $ormCategory3->getTitle());
                                    //$meta['description'] = $ormCategory3->getSeoDescription() !== '' ? $ormCategory3->getSeoDescription() : $meta['description'];
                                    $meta['keywords'] = $ormCategory3->getSeoKeywords() !== '' ? $ormCategory3->getSeoKeywords() : $meta['keywords'];
                                    $meta['autoKeys'][] = $ormCategory3->getTitle();
                                    $finalCategory = $ormCategory3;
                                }
                            }
                        }
                    }
                }
            }
        }
        $breadcrumbUrls = $this->getBreadcrumbUrls($finalCategory);
        $baseUrl = $fullUrl;
        $fullUrl = $fullUrl . '/' . $topic->getSlug() . ':' . $topic->getId();

        // Get replies
        $repliesRateRepo = $this->getForumReplyRateRepo();
        $replies = $this->getTopicReplies($topic->getId(), $repliesPage, $repliesOrder, 20);

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
        $this->metaData->setLinkCanonical($topic->getTopicUrl($baseUrl));

        // Check view
        $viewChecked = $this->reupdateViewsCount($topic);

        // Assign data for view
        $this->view->setVars(compact('contentClass', 'enableCoverAdd', 'topic', 'fullUrl', 'breadcrumbUrls', 'replies', 'repliesRateRepo'));
    }

    public function addTopicAction()
    {
        $contentClass = 'add-new';
        $topic = new ForumTopic();
        $form = new AddTopicMemberForm($topic, []);
        $action = $this->url->get(['for' => 'forum_add_topic']);
        $module = 'forum';

        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {
                $success = false;
                $errors  = [];

                if ($form->isValid($this->request->getPost())) {
                    $form->bind($this->request->getPost(), $topic);
                    try {

                        $finalCategory = null;

                        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
                        /* @var $member Member */

                        if (is_object($member)) {
                            $topic->setMember($member);
                        } else {
                            throw new \Exception(sprintf('Authorised user not found'));
                        }

                        if ((int)$topic->getCategoryLvl1() > 0) {
                            $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl1());
                            $topic->setCategoryLvl1($category);
                            $finalCategory = $category;
                            if (is_object($category)) {
                                $category->increaseTopicsCount();
                            }
                        } else {
                            $topic->setCategoryLvl1(null);
                        }

                        if ((int)$topic->getCategoryLvl2() > 0) {
                            $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl2());
                            $topic->setCategoryLvl2($category);
                            $finalCategory = $category;
                            if (is_object($category)) {
                                $category->increaseTopicsCount();
                            }
                        } else {
                            $topic->setCategoryLvl2(null);
                        }

                        if ((int)$topic->getCategoryLvl3() > 0) {
                            $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl3());
                            $topic->setCategoryLvl3($category);
                            $finalCategory = $category;
                            if (is_object($category)) {
                                $category->increaseTopicsCount();
                            }
                        } else {
                            $topic->setCategoryLvl3(null);
                        }

                        if (is_object($finalCategory)) {
                            if ($finalCategory->hasChildrens()) {
                                throw new \Exception(sprintf('Final category is not selected'));
                            }
                        } else {
                            throw new \Exception(sprintf('Categories not choosed'));
                        }

                        $topic->setCreatedFromIp($this->request->getClientAddress());
                        $topic->setSlug(StringTool::createSlug($topic->getTitle()));
                        $topic->setContent(StringTool::createParagraphTags($topic->getContent()));

                        $this->getEntityManager()->persist($topic);
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

                $redirectTo = '';
                if ($success) {
                    $tmp = end($this->getBreadcrumbUrls($finalCategory));
                    if (is_array($tmp) && array_key_exists('url', $tmp)) {
                        $redirectTo = $tmp['url'];// . '/' . $topic->getSlug() . ':' . $topic->getId();
                    }
                }

                $this->response->setJsonContent([
                    'success' => $success, 
                    'errors' => $errors, 
                    'redirect' => $redirectTo
                ]);
                return $this->response->send();
            }
        }

        $this->view->setVars(compact('contentClass', 'form', 'action', 'module'));
    }

    public function editTopicAction()
    {
        // Get topic data
        $topicId = $this->dispatcher->getParam('id');
        $topic = $this->getForumTopicRepo()->findObjectById($topicId);
        if ($topic === null) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }
        
        // Check if can edit
        $allowEdit = false;
        if ($this->auth->isAuthorised() && $topic->hasMember() && $topic->isNew()) {
            $member = $topic->getMember();
            if (is_object($member) && $member->getId() === $this->auth->getAuthorisedUserId()) {
                $allowEdit = true;
            }
        }
        if ($allowEdit === false) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'restrictedAccess',
            ]);
            return false;
        }

        $contentClass = 'add-new';
        $form = new AddTopicMemberForm($topic, ['edit' => true]);
        $action = $this->url->get(['for' => 'forum_topic_edit', 'id' => $topic->getId()]);
        $module = 'forum';

        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {
                $success = false;
                $errors  = [];

                $finalCategory = null;

                if ($form->isValid($this->request->getPost())) {
                    $form->bind($this->request->getPost(), $topic);
                    try {

                        if ((int)$topic->getCategoryLvl1() > 0) {
                            $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl1());
                            $topic->setCategoryLvl1($category);
                            $finalCategory = $category;
                            if (is_object($category)) {
                                $category->increaseTopicsCount();
                            }
                        } else {
                            $topic->setCategoryLvl1(null);
                        }

                        if ((int)$topic->getCategoryLvl2() > 0) {
                            $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl2());
                            $topic->setCategoryLvl2($category);
                            $finalCategory = $category;
                            if (is_object($category)) {
                                $category->increaseTopicsCount();
                            }
                        } else {
                            $topic->setCategoryLvl2(null);
                        }

                        if ((int)$topic->getCategoryLvl3() > 0) {
                            $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl3());
                            $topic->setCategoryLvl3($category);
                            $finalCategory = $category;
                            if (is_object($category)) {
                                $category->increaseTopicsCount();
                            }
                        } else {
                            $topic->setCategoryLvl3(null);
                        }

                        if (is_object($finalCategory)) {
                            if ($finalCategory->hasChildrens()) {
                                throw new \Exception(sprintf('Final category is not selected'));
                            }
                        } else {
                            throw new \Exception(sprintf('Categories not choosed'));
                        }

                        $topic->setSlug(StringTool::createSlug($topic->getTitle()));
                        $topic->setContent(StringTool::createParagraphTags($topic->getContent()));

                        $this->getEntityManager()->persist($topic);
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

                $redirectTo = '';
                if ($success) {
                    $tmp = end($this->getBreadcrumbUrls($finalCategory));
                    if (is_array($tmp) && array_key_exists('url', $tmp)) {
                        //$redirectTo = $tmp['url'];// . '/' . $topic->getSlug() . ':' . $topic->getId();
                        $this->getEntityManager()->refresh($topic);
                        $redirectTo = $tmp['url'] . '/' . $topic->getSlug() . ':' . $topic->getId() . '/asc';
                    }
                }

                $this->response->setJsonContent([
                    'success' => $success, 
                    'errors' => $errors, 
                    'redirect' => $redirectTo
                ]);
                return $this->response->send();
            }
        }

        $this->view->setVars(compact('contentClass', 'form', 'action', 'module'));
    }

    /**
     * Get ForumCategory entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumCategoryRepository
     */
    public function getForumCategoryRepo()
    {
        if ($this->_categoriesRepo === null || !$this->_categoriesRepo) {
            $this->_categoriesRepo = $this->getEntityRepository(ForumCategory::class);
        }

        return $this->_categoriesRepo;
    }

    /**
     * Get ForumTopic entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumTopicRepository
     */
    public function getForumTopicRepo()
    {
        if ($this->_topicsRepo === null || !$this->_topicsRepo) {
            $this->_topicsRepo = $this->getEntityRepository(ForumTopic::class);
        }

        return $this->_topicsRepo;
    }

    /**
     * Get ForumTopicRate entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumTopicRateRepository
     */
    public function getForumTopicRateRepo()
    {
        if ($this->_topicsRateRepo === null || !$this->_topicsRateRepo) {
            $this->_topicsRateRepo = $this->getEntityRepository(ForumTopicRate::class);
        }

        return $this->_topicsRateRepo;
    }

    /**
     * Get ForumReply entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumReplyRepository
     */
    public function getForumReplyRepo()
    {
        if ($this->_repliesRepo === null || !$this->_repliesRepo) {
            $this->_repliesRepo = $this->getEntityRepository(ForumReply::class);
        }

        return $this->_repliesRepo;
    }

    /**
     * Get ForumReplyRate entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumReplyRateRepository
     */
    public function getForumReplyRateRepo()
    {
        if ($this->_replyRateRepo === null || !$this->_replyRateRepo) {
            $this->_replyRateRepo = $this->getEntityRepository(ForumReplyRate::class);
        }

        return $this->_replyRateRepo;
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

    public function loadChildsJsonAction()
    {
        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => 0, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        $parentId = $this->dispatcher->getParam('id');
        $list = $this->getForumCategoryRepo()->getParentChildsList($parentId);

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        $this->response->setJsonContent(['success' => 1, 'data' => $rows]);

        return $this->response->send();
    }

    public function getTopicPreviewDataAjaxAction()
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
            $content = StringTool::createParagraphTags($this->request->getPost('content'));
            $content = trim($content) !== '' ? '<div class="col-md-12">'.$content.'</div>' : '';
            $html = '
                        <div class="row overall">
                            <div class="col-md-12 title">
                                <h1>' . $title . '</h1>
                            </div>
                        </div>
                        <div class="row news-body">
                            '.$content.'
                        </div>';
            $this->response->setJsonContent(['success' => 1, 'html' => $html]);
        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    public function rateTopicAjaxAction()
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

        $topic = $this->getForumTopicRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $topic ForumTopic */

        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (!is_object($member) || !is_object($topic)) {
            $error = $this->translator->trans('error_forum_topic_and_or_member_not_found', 'Topic and/or member not found', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (is_object($topic->getMember()) && $topic->getMember()->getId() === $this->auth->getAuthorisedUserId()) {
            $error = $this->translator->trans('error_can_not_rate_own_forum_topics', 'Can not rate own topics', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if ($this->getForumTopicRateRepo()->allreadyRated($topic->getId(), $member->getId())) {
            $error = $this->translator->trans('error_allready_rated_forum_topic', 'Allready rated this topic', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        try {

            $rate = new ForumTopicRate();
            $rate->setTopic($topic);
            $rate->setMember($member);
            $rate->setRate($type === 'plus' ? 1 : -1);
            $rate->setIpAddress($this->request->getClientAddress());
            $rate->setSessionId($this->session->getId());
            $rate->setUserAgent($this->request->getUserAgent());

            $this->getEntityManager()->persist($rate);

            if ($rate->isPositiveRate()) {
                $topic->setRatePlus($topic->getRatePlus() + 1);
            } else {
                $topic->setRateMinus($topic->getRateMinus() + 1);
            }
            $topic->setRateAvg($topic->getRatePlus() - $topic->getRateMinus());

            $this->getEntityManager()->flush();

            $html = $this->translator->trans('lbl_news_rating', 'Reitings') . ' ' . $topic->getFormattedRating();

            $this->response->setJsonContent(['success' => 1, 'html' => $html]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error . ' -> ' . $exc->getMessage()]);
        }

        return $this->response->send();
    }

    protected function getMetaAndFinalCategoryData($fullUrl = '', $category1 = '', $category2 = '', $category3 = '')
    {
        $meta = [
            'title' => '',
            'description' => '',
            'keywords' => '',
            'autoKeys' => [],
        ];

        $finalCategory = null;

        $this->metaData->disableAddTitleSuffix();

        if ($fullUrl !== '') {
            if ($category1 !== '') {
                $ormCategory1 = $this->forumCategoriesService->getCategoryBySlugFromLevel($category1, null, 1);
                if (is_object($ormCategory1)) {
                    $meta['title'] = (string)$ormCategory1->getSeoTitle() !== '' ? $ormCategory1->getSeoTitle() : $ormCategory1->getTitle();
                    $meta['description'] = $ormCategory1->getSeoDescription();
                    $meta['keywords'] = $ormCategory1->getSeoKeywords();
                    $meta['autoKeys'][] = $ormCategory1->getTitle();
                    $finalCategory = $ormCategory1;
                    if ($category2 !== '') {
                        $ormCategory2 = $this->forumCategoriesService->getCategoryBySlugFromLevel($category2, $ormCategory1, 2);
                        if (is_object($ormCategory2)) {
                            $meta['title'] = (string)$ormCategory2->getSeoTitle() !== '' ? $ormCategory2->getSeoTitle() : ($ormCategory1->getTitle() . ', ' . $ormCategory2->getTitle());
                            $meta['description'] = $ormCategory2->getSeoDescription() !== '' ? $ormCategory2->getSeoDescription() : $meta['description'];
                            $meta['keywords'] = $ormCategory2->getSeoKeywords() !== '' ? $ormCategory2->getSeoKeywords() : $meta['keywords'];
                            $meta['autoKeys'][] = $ormCategory2->getTitle();
                            $finalCategory = $ormCategory2;
                            if ($category3 !== '') {
                                $ormCategory3 = $this->forumCategoriesService->getCategoryBySlugFromLevel($category3, $ormCategory2, 3);
                                if (is_object($ormCategory3)) {
                                    $meta['title'] = (string)$ormCategory3->getSeoTitle() !== '' ? $ormCategory3->getSeoTitle() : ($ormCategory1->getTitle() . ', ' . $ormCategory2->getTitle() . ', ' . $ormCategory3->getTitle());
                                    $meta['description'] = $ormCategory3->getSeoDescription() !== '' ? $ormCategory3->getSeoDescription() : $meta['description'];
                                    $meta['keywords'] = $ormCategory3->getSeoKeywords() !== '' ? $ormCategory3->getSeoKeywords() : $meta['keywords'];
                                    $meta['autoKeys'][] = $ormCategory3->getTitle();
                                    $finalCategory = $ormCategory3;
                                }
                            }
                        }
                    }
                }
            }
        }

        return [ $meta, $finalCategory ];
    }

    protected function getFullListViewUrl($category1 = '', $category2 = '', $category3 = '')
    {
        $fullUrl = rtrim($this->config->web_url . $this->url->get(['for' => 'forum_list']), '/');

        if ($category1 !== '') {
            $fullUrl.= '/' . $category1;
            if ($category2 !== '') {
                $fullUrl.= '/' . $category2;
                if ($category3 !== '') {
                    $fullUrl.= '/' . $category3;
                }
            }
        }

        return rtrim($fullUrl, '/');
    }

    protected function getBreadcrumbUrls($finalCategory = null)
    {
        $urls = [];

        $url = rtrim($this->config->web_url . $this->url->get(['for' => 'forum_list']), '/');        
        $urls[] = [
            'url' => $url,
            'title' => 'Forums'
        ];

        $tmp = [];
        if (is_object($finalCategory)) {
            $tmp[$finalCategory->getLevel()] = [
                'title' => $finalCategory->getTitle(),
                'slug' => $finalCategory->getSlug(),
            ];
            $parent = $finalCategory->getParent();
            if (is_object($parent)) {
                $tmp[$parent->getLevel()] = [
                    'title' => $parent->getTitle(),
                    'slug' => $parent->getSlug(),
                ];
                $parent = $parent->getParent();
                if (is_object($parent)) {
                    $tmp[$parent->getLevel()] = [
                        'title' => $parent->getTitle(),
                        'slug' => $parent->getSlug(),
                    ];
                    $parent = $parent->getParent();
                }
            }
        }

        ksort($tmp);
        foreach ($tmp as $category) {
            $url = $url . '/' . $category['slug'];
            $urls[] = [
                'url' => $url,
                'title' => $category['title']
            ];
        }

        return $urls;
    }

    protected function getTopicReplies($topicId, $page = 1, $ordering = 'ASC', $perPage = 20)
    {
        $query = $this->getForumReplyRepo()->getTopicCommentsQuery($topicId, $ordering, $page, $perPage);

        $data = [
            'currentPage' => $page,
            'ordering' => $ordering,
            'perPage' => $perPage,
            'paginator' => new Paginator($query, true)
        ];

        $query = null;
        
        return $data;
    }

    public function saveTopicReplyAjaxAction()
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

        $topic = $this->getForumTopicRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $topic ForumTopic */

        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (!is_object($member) || !is_object($topic)) {
            $error = $this->translator->trans('error_forum_topic_and_or_member_not_found', 'Topic and/or member not found', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if ($member->isBannedForumReplies()) {
            $error = $this->translator->trans('error_member_banned_for_replies_posting', 'You have no permissions to post replies', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $replyOnComment = null;
        $replyOnCommentId = $this->request->getPost('replyOnComment', 'int', 0);
        if ((int)$replyOnCommentId > 0) {
            $replyOnComment = $this->getForumReplyRepo()->findObjectById($replyOnCommentId);
            /* @var $replyOnComment Comment */
            if (!is_object($replyOnComment)) {
                $error = $this->translator->trans('error_reply_comment_not_found', 'Reply comment not found', Group::FORUM);
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
            $error = $this->translator->trans('error_comment_length', 'At least 5 chars and max 2000 chars allowed.', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
                return $this->response->send();
            }

            // Save
            $reply = new ForumReply();
            $reply->setTopic($topic);
            $reply->setMember($member);
            $reply->setContent($txtComment);
            $reply->setIpAddress($this->request->getClientAddress());
            $reply->setSessionId($this->session->getId());
            $reply->setUserAgent($this->request->getUserAgent());
            if (is_object($replyOnComment)) {
                $reply->setReplyOnComment($replyOnComment);
            }

            $this->getEntityManager()->persist($reply);

            $this->relatedUpdatesOnReplySave($topic, $reply);

            $this->response->setJsonContent(['success' => 1]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    protected function reupdateViewsCount($topic = null)
    {
        if (!is_object($topic)) {
            return false;
        }

        $session = $this->session->get('topic-view-'.$topic->getId());

        if ((string)$session !== (string)$this->session->getId()) {
            $topic->increaseViewsCount();

            $cat3 = $topic->getCategoryLvl3();
            if (is_object($cat3)) {
                $cat3->increaseViewsCount();
            }

            $cat2 = $topic->getCategoryLvl2();
            if (is_object($cat2)) {
                $cat2->increaseViewsCount();
            }

            $cat1 = $topic->getCategoryLvl1();
            if (is_object($cat1)) {
                $cat1->increaseViewsCount();
            }

            $this->getEntityManager()->flush();

            $this->session->set('topic-view-'.$topic->getId(), $this->session->getId());
        }

        return true;
    }

    protected function relatedUpdatesOnReplySave($topic, $reply)
    {
        if (!is_object($topic) || !is_object($reply)) {
            return false;
        }

        $member = $reply->getMember();

        $topic->increaseCommentsCount();
        if (is_object($member)) {
            $topic->setLastReplyBy($member);
            $topic->setLastReplyByUsername((string)$member);
        }
        $topic->setLastReplyAt($reply->getCreatedAt());
        $topic->setLastReply($reply->getContent());

        $cat3 = $topic->getCategoryLvl3();
        if (is_object($cat3)) {
            $cat3->increaseCommentsCount();
            $cat3->setLastReplyOnTopic($topic);
            if (is_object($member)) {
                $cat3->setLastReplyBy($member);
                $cat3->setLastReplyByUsername((string)$member);
            }
            $cat3->setLastReplyAt($reply->getCreatedAt());
            $cat3->setLastReply($reply->getContent());
        }

        $cat2 = $topic->getCategoryLvl2();
        if (is_object($cat2)) {
            $cat2->increaseCommentsCount();
            $cat2->setLastReplyOnTopic($topic);
            if (is_object($member)) {
                $cat2->setLastReplyBy($member);
                $cat2->setLastReplyByUsername((string)$member);
            }
            $cat2->setLastReplyAt($reply->getCreatedAt());
            $cat2->setLastReply($reply->getContent());
        }

        $cat1 = $topic->getCategoryLvl1();
        if (is_object($cat1)) {
            $cat1->increaseCommentsCount();
            $cat1->setLastReplyOnTopic($topic);
            if (is_object($member)) {
                $cat1->setLastReplyBy($member);
                $cat1->setLastReplyByUsername((string)$member);
            }
            $cat1->setLastReplyAt($reply->getCreatedAt());
            $cat1->setLastReply($reply->getContent());
        }

        $this->getEntityManager()->flush();
        
        return true;
    }

    public function rateTopicReplyAjaxAction()
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

        $reply = $this->getForumReplyRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $reply ForumReply */

        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (!is_object($member) || !is_object($reply)) {
            $error = $this->translator->trans('error_reply_and_or_member_not_found', 'Reply and/or member not found', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (is_object($reply->getMember()) && $reply->getMember()->getId() === $this->auth->getAuthorisedUserId()) {
            $error = $this->translator->trans('error_can_not_rate_own_replies', 'Can not rate own comments', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if ($this->getForumReplyRateRepo()->allreadyRated($reply->getId(), $member->getId())) {
            $error = $this->translator->trans('error_allready_rated_reply', 'Allready rated this comment', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        try {

            $rate = new ForumReplyRate();
            $rate->setReply($reply);
            $rate->setMember($member);
            $rate->setRate($type === 'plus' ? 1 : -1);
            $rate->setIpAddress($this->request->getClientAddress());
            $rate->setSessionId($this->session->getId());
            $rate->setUserAgent($this->request->getUserAgent());

            $this->getEntityManager()->persist($rate);

            if ($rate->isPositiveRate()) {
                $reply->setRatePlus($reply->getRatePlus() + 1);
            } else {
                $reply->setRateMinus($reply->getRateMinus() + 1);
            }
            $reply->setRateAvg($reply->getRatePlus() - $reply->getRateMinus());

            $this->getEntityManager()->flush();

            $html = $reply->getFormattedRating();

            $this->response->setJsonContent(['success' => 1, 'html' => $html]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    public function reportTopicReplyAjaxAction()
    {
        if ($this->request->isAjax() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $reply = $this->getForumReplyRepo()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $reply ForumReply */

        if (!is_object($reply)) {
            $error = $this->translator->trans('error_reply_not_found', 'Reply not found', Group::FORUM);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $member = null;
        if ($this->auth->isAuthorised()) {
            $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
            /* @var $member Member */
        }

        try {

            $reported = new ForumReportedReply();
            $reported->setReply($reply);
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

            $html = $this->translator->trans('reply_report_send', 'Nosūtīts', Group::FORUM);

            $this->response->setJsonContent(['success' => 1, 'html' => $html]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

}
