<?php

namespace Application\Controller\Backend;

use Articles\Entity\Article;
use Articles\Entity\Category;
use Articles\Entity\Comment;
use Common\Controller\AbstractBackendController;
use Members\Entity\Member;
use Members\Entity\Withdraws;
use Polls\Entity\Poll;

class ApplicationController extends AbstractBackendController
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
     * @var \Articles\Repository\CommentRepository
     */
    protected $_commentRepo;

    /**
     * @var \Members\Repository\MemberRepository
     */
    protected $_membersRepo;

    /**
     * @var \Members\Repository\WithdrawsRepository
     */
    protected $_withdrawsRepo;

    /**
     * @var \Polls\Repository\PollRepository
     */
    protected $_pollsRepo;

    public function indexAction()
    {
        $articlesCount = $this->getArticleRepo()->getTotalCount();
        $membersCount = $this->getMemberRepo()->getTotalCount();
        $categoriesCount = $this->getCategoryRepo()->getTotalCount();
        $pollsCount = $this->getPollRepo()->getTotalCount();
        $commentsCount = $this->getCommentRepo()->getTotalCount();
        $withdrawsCount = $this->getWithdrawsRepo()->getTotalCount();

        $this->view->setVars(compact('articlesCount', 'pollsCount', 'categoriesCount', 'membersCount', 'commentsCount', 'withdrawsCount'));
    }

    public function exampleAjaxAction()
    {
        if ($this->request->isAjax() == true) {
            // This is an Ajax response so it doesn't generate any kind of view
            $this->view->setRenderLevel(View::LEVEL_NO_RENDER);
        }

        // ...
    }

    public function exampleHttpAction()
    {
        // Shows only the view related to the action
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        // ...

        /*// Render 'views-dir/posts/show.phtml'
        echo $this->view->render('posts/show');

        // Render 'views-dir/index.phtml' passing variables
        echo $this->view->render('index', array('posts' => Posts::find()));

        // Render 'views-dir/posts/show.phtml' passing variables
        echo $this->view->render('posts/show', array('posts' => Posts::find()));*/
    }

    /*public function deleteAction()
    {
        $this->flashSession->error("too bad! the form had errors");
        $this->flashSession->success("yes!, everything went very smoothly");
        $this->flashSession->notice("notice 1. this a very important information");
        $this->flashSession->notice("notice 2. this a very important information");
        $this->flashSession->warning("best check yo self, you're not looking too good.");

        $this->view->setVar('message', 'Hello, World from backend UsersController edit action!');

        //$this->view->pick('users/view');
    }*/

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
     * Get Comment entity repository
     * 
     * @access public
     * @return \Articles\Repository\CommentRepository
     */
    public function getCommentRepo()
    {
        if ($this->_commentRepo === null || !$this->_commentRepo) {
            $this->_commentRepo = $this->getEntityRepository(Comment::class);
        }

        return $this->_commentRepo;
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

    /**
     * Get Withdraws entity repository
     * 
     * @access public
     * @return \Members\Repository\WithdrawsRepository
     */
    public function getWithdrawsRepo()
    {
        if ($this->_withdrawsRepo === null || !$this->_withdrawsRepo) {
            $this->_withdrawsRepo = $this->getEntityRepository(Withdraws::class);
        }

        return $this->_withdrawsRepo;
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

}
