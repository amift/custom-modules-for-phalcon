<?php

namespace Communication\Controller\Backend;

use Articles\Entity\Article;
use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Common\Tool\StringTool;
use Communication\Entity\Newsletter;
use Communication\Entity\NewsletterArticle;
use Communication\Forms\NewsletterForm;
use Communication\Tool\NewsletterContent;
use Doctrine\ORM\Tools\Pagination\Paginator;

class NewsletterController extends AbstractBackendController
{
    /**
     * @var \Communication\Repository\NewsletterRepository
     */
    protected $_newslettersRepo;

    /**
     * @var \Articles\Repository\ArticleRepository
     */
    protected $_articlesRepo;

    /**
     * Newsletters list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->newsletters;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $contentClass = 'notifications_page';

        $qb = $this->getNewsletterRepo()->createQueryBuilder('n')
                ->select('n')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('n.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('type')
            ->addField('state')
            ->searchInFields('search', [ 
                'n.title', 'n.fromName', 'n.fromEmail', 'n.subject', 'n.body'
            ])
        ;

        $filters->apply($qb, 'n');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('contentClass', 'paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * Newsletter edit view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $newsletter = $this->getNewsletterRepo()->findObjectById($id);
        $contentClass = 'notifications_page';

        if (null === $newsletter) {
            $this->flashSession->error(sprintf('Newsletter with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'newsletters_list']));
        }

        $savedArticles = $newsletter->getArticles();
        $form = new NewsletterForm($newsletter, ['edit' => true]);
        $action = $this->url->get(['for' => 'newsletters_edit', 'id' => $newsletter->getId()]);
        $error  = '';
        $new = false;
        
        if (!$newsletter->isEditable()) {
            $this->flashSession->warning("Newsletter not more allowed to edit");
        }

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $newsletter);
                try {
                    // newsletter articles
                    $submittedArticles = 0;
                    $validArticles = 0;
                    $ids = [];
                    if ($newsletter->isTypeWeekly()) {
                        $data = $this->request->getPost()['articles'];
                        foreach ($data as $x => $row) {
                            $submittedArticles++;
                            if ($row['id'] === '') { // add new
                                $article = $this->getArticleRepo()->findObjectById($row['article_id']);
                                $newsletterArticle = new NewsletterArticle();
                                $newsletterArticle->setNewsletter($newsletter);
                                $newsletterArticle->setOrdering((int)$row['ordering']);
                                if (is_object($article)) {
                                    $newsletterArticle->setArticle($article);
                                    $validArticles++;
                                }
                                $this->getEntityManager()->persist($newsletterArticle);
                            } else { // update existing
                                foreach ($savedArticles as $savedArticle) {
                                    if ((string)$savedArticle->getId() === (string)$row['id']) {
                                        $article = $this->getArticleRepo()->findObjectById($row['article_id']);
                                        $savedArticle->setOrdering((int)$row['ordering']);
                                        if (is_object($article)) {
                                            $savedArticle->setArticle($article);
                                            $validArticles++;
                                        }
                                        $ids[] = (string)$row['id'];
                                    }
                                }
                            }
                        }

                        if ($submittedArticles < 1) {
                            throw new \Exception('At least 1 article need to set up for weekly newsletter');
                        }
                        if ($validArticles < $submittedArticles) {
                            throw new \Exception('Seems not all articles selected correctly, please, try to submit again');
                        }
                    }

                    // Remove deleted
                    foreach ($savedArticles as $articleToDelete) {
                        if (!in_array((string)$articleToDelete->getId(), $ids)) {
                            $newsletter->removeArticle($articleToDelete);
                        }
                    }

                    if ($newsletter->isTypeCustom()) {
                        $newsletter->setBody(StringTool::createParagraphTags($newsletter->getBody(), false));
                    }

                    $d = \DateTime::createFromFormat('d/m/Y H:i:s', $newsletter->getToSendAt());
                    $newsletter->setToSendAt($d);

                    $newsletter->setReceiverCriterias((array)$this->request->getPost()['receiverCriterias']);

                    // Save data
                    $this->getEntityManager()->flush();

                    // Back to list
                    $this->flashSession->success(sprintf('Newsletter "%s" info updated successfully!', (string)$newsletter));
                    return $this->response->redirect($this->url->get(['for' => 'newsletters_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            } else {
                // newsletter articles
                $ids = [];
                if ($newsletter->isTypeWeekly()) {
                    $data = $this->request->getPost()['articles'];
                    foreach ($data as $x => $row) {
                        if ($row['id'] === '') { // add new
                            $article = $this->getArticleRepo()->findObjectById($row['article_id']);
                            $newsletterArticle = new NewsletterArticle();
                            $newsletterArticle->setNewsletter($newsletter);
                            $newsletterArticle->setOrdering((int)$row['ordering']);
                            if (is_object($article)) {
                                $newsletterArticle->setArticle($article);
                            }
                            $this->getEntityManager()->persist($newsletterArticle);
                        } else { // update existing
                            foreach ($savedArticles as $savedArticle) {
                                if ((string)$savedArticle->getId() === (string)$row['id']) {
                                    $article = $this->getArticleRepo()->findObjectById($row['article_id']);
                                    $savedArticle->setOrdering((int)$row['ordering']);
                                    if (is_object($article)) {
                                        $savedArticle->setArticle($article);
                                    }
                                    $ids[] = (string)$row['id'];
                                }
                            }
                        }
                    }
                }

                // Remove deleted
                foreach ($savedArticles as $articleToDelete) {
                    if (!in_array((string)$articleToDelete->getId(), $ids)) {
                        $newsletter->removeArticle($articleToDelete);
                    }
                }

                $newsletter->setReceiverCriterias((array)$this->request->getPost()['receiverCriterias']);
            }
        }

        $this->view->setVars(compact('contentClass', 'newsletter', 'form', 'action', 'error', 'new'));
    }

    /**
     * Newsletter add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $newsletter = new Newsletter();
        $newsletter->setFromName($this->config->communication->default->fromName);
        $newsletter->setFromEmail($this->config->communication->default->fromEmail);

        $contentClass = 'notifications_page';
        $form = new NewsletterForm($newsletter, ['add' => true]);
        $action = $this->url->get(['for' => 'newsletters_add']);
        $error  = '';
        $new = true;

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $newsletter);
                try {

                    $submittedArticles = 0;
                    $validArticles = 0;
                    if ($newsletter->isTypeWeekly()) {
                        $data = $this->request->getPost()['articles'];
                        foreach ($data as $x => $row) {
                            $submittedArticles++;
                            $article = $this->getArticleRepo()->findObjectById($row['article_id']);
                            $newsletterArticle = new NewsletterArticle();
                            $newsletterArticle->setOrdering((int)$row['ordering']);
                            if (is_object($article)) {
                                $newsletterArticle->setArticle($article);
                                $validArticles++;
                            }
                            $newsletter->addArticle($newsletterArticle);
                        }
                        if ($submittedArticles < 1) {
                            throw new \Exception('At least 1 article need to set up for weekly newsletter');
                        }
                        if ($validArticles < $submittedArticles) {
                            throw new \Exception('Seems not all articles selected correctly, please, try to submit again');
                        }
                    }

                    if ($newsletter->isTypeCustom()) {
                        $newsletter->setBody(StringTool::createParagraphTags($newsletter->getBody(), false));
                    }

                    $d = \DateTime::createFromFormat('d/m/Y H:i:s', $newsletter->getToSendAt());
                    $newsletter->setToSendAt($d);

                    $newsletter->setReceiverCriterias((array)$this->request->getPost()['receiverCriterias']);

                    // Save data
                    $this->getEntityManager()->persist($newsletter);
                    $this->getEntityManager()->flush();

                    // Back to list
                    $this->flashSession->success(sprintf('Newsletter "%s" created successfully!', (string)$newsletter));
                    return $this->response->redirect($this->url->get(['for' => 'newsletters_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            } else {
                if ($newsletter->isTypeWeekly()) {
                    $data = $this->request->getPost()['articles'];
                    foreach ($data as $x => $row) {
                        $article = $this->getArticleRepo()->findObjectById($row['article_id']);
                        $newsletterArticle = new NewsletterArticle();
                        $newsletterArticle->setOrdering((int)$row['ordering']);
                        if (is_object($article)) {
                            $newsletterArticle->setArticle($article);
                        }
                        $newsletter->addArticle($newsletterArticle);
                    }
                }
                $newsletter->setReceiverCriterias((array)$this->request->getPost()['receiverCriterias']);
            }
        }

        $this->view->setVars(compact('contentClass', 'newsletter', 'form', 'action', 'error', 'new'));
    }

    /**
     * @return mixed
     */
    public function searchForArticlesAjaxAction()
    {
        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => 0, 'results' => [], 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        $searchTerm = $this->request->getQuery('searchTerm', 'string', '');

        $dateFrom = new \DateTime('now');
        $dateFrom->modify('-1000 days');

        $qb = $this->getArticleRepo()->createQueryBuilder('a')
                ->select('a')
                ->where('DATE(a.publicationDate) >= :publicationDateFrom')
                ->setParameter('publicationDateFrom', $dateFrom->format('Y-m-d'));
        if ($searchTerm !== '') {
            $qb->andWhere('LOWER(CAST(a.title AS text)) LIKE :searchTerm');
            $qb->setParameter('searchTerm', '%' . mb_strtolower($searchTerm) . '%');
        }
        $qb->setMaxResults(10)->orderBy('a.id', 'DESC');

        $results = [];
        foreach ($qb->getQuery()->getResult() as $article) {
            $results[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'date' => $article->getPublicationDate()->format('d.m.Y H:i'),
                'category' => $article->getCategoryLvl1()->getTitle()
            ];
        }

        $this->response->setJsonContent(['success' => 1, 'results' => $results]);
        return $this->response->send();
    }

    /**
     * @return mixed
     */
    public function previewAjaxAction()
    {
        if ($this->request->isAjax() !== true || $this->request->isPost() !== true) {
            $this->response->setJsonContent(['success' => 0, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        try {
            $type         = (int)$this->request->getPost('type', 'int', 1);
            $subject      = $this->request->getPost('subject', 'string', '');
            $body         = StringTool::createParagraphTags($this->request->getPost('body', 'string', ''), false);
            $articleIds   = $this->request->getPost('articleIds', 'string', '');
            $newsletterId = (int)$this->dispatcher->getParam('id');
            if ($newsletterId > 0) {
                $newsletter = $this->getNewsletterRepo()->findObjectById($newsletterId);
                if (!is_object($newsletter)) {
                    throw new \Exception('Newsletter by ID value not found!');
                }
                $type = $newsletter->getType();
                $subject = $newsletter->getSubject();
                $body = $newsletter->getBody();
                $articleIds = $newsletter->getArtilesIdsAsString();
            }

            $html = '';
            if ($type === 1) {
                $html = $this->di->get('newsletter_service')->bodyCustom($body);
            }
            if ($type === 2) {
                $html = $this->di->get('newsletter_service')->bodyArticles($subject, $articleIds);
            }

            $this->response->setJsonContent(['success' => 1, 'html' => NewsletterContent::preview($html)]);
        } catch (\Exception $ex) {
            $this->response->setJsonContent(['success' => 0, 'errors' => $ex->getMessage()]);
        }

        return $this->response->send();
    }

    /**
     * Get Newsletter entity repository
     *
     * @access public
     * @return \Communication\Repository\NewsletterRepository
     */
    public function getNewsletterRepo()
    {
        if ($this->_newslettersRepo === null || !$this->_newslettersRepo) {
            $this->_newslettersRepo = $this->getEntityRepository(Newsletter::class);
        }

        return $this->_newslettersRepo;
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
}