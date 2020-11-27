<?php

namespace Polls\Controller\Frontend;

use Articles\Entity\Comment;
use Articles\Entity\CommentRate;
use Articles\Entity\ReportedComment;
use Common\Controller\AbstractFrontendController;
use Common\Tool\NumberTool;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Members\Entity\Member;
use Polls\Entity\Poll;
use Polls\Entity\PollVote;
use Translations\Tool\Group;

class PollsController extends AbstractFrontendController
{

    /**
     * @var \Polls\Repository\PollRepository
     */
    protected $_pollRepo;

    /**
     * @var \Polls\Repository\PollVoteRepository
     */
    protected $_pollVoteRepo;

    /**
     * @var \Members\Repository\MemberRepository
     */
    protected $_membersRepo;

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
     * Poll open view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function showAction()
    {
        $contentClass   = 'page-content';
        $category       = $this->dispatcher->getParam('category', 'string', '');
        $subcategory    = $this->dispatcher->getParam('subcategory', 'string', '');
        $pollSlug       = $this->dispatcher->getParam('slug', 'string', '');
        $pollId         = $this->dispatcher->getParam('id', 'string', '');
        $enableCoverAdd = false;

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

        // Get poll data
        $poll = $this->getPollRepository()->findObjectById($pollId);

        // Invalid slug and/or ID values combination in URL
        if ($poll === null || $poll->getSlug() !== $pollSlug) {
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
        $articles   = $this->articlesService->getSideActualArticles($params['name'], $params['value']);
        
        //----------------------------------------------------------------------
        
        $allreadyVoted = false;
        if ($this->auth->isAuthorised()) {
            $votedOption = $this->pollsService->getVotedOptionId(
                $poll->getId(), 
                $this->auth->getAuthorisedUserId()
            );
            $allreadyVoted = is_object($votedOption) ? true : false;
        }

        // Get meta data
        $meta = [
            'title' => $poll->getTitle(),
            'description' => strip_tags($poll->getContent()),
            'keywords' => '',
            'autoKeys' => []
        ];
        
        $cat1 = $poll->getCategoryLvl1();
        if (is_object($cat1)) {
            $meta['keywords'] = $cat1->getSeoKeywords() !== '' ? $cat1->getSeoKeywords() : $meta['keywords'];
            if ($cat1->getTitle() !== '') {
                $meta['autoKeys'][] = $cat1->getTitle();
            }
        }
        $cat2 = $poll->getCategoryLvl2();
        if (is_object($cat2)) {
            $meta['keywords'] = $cat2->getSeoKeywords() !== '' ? $cat2->getSeoKeywords() : $meta['keywords'];
            if ($cat2->getTitle() !== '') {
                $meta['autoKeys'][] = $cat2->getTitle();
            }
        }
        $cat3 = $poll->getCategoryLvl3();
        if (is_object($cat3)) {
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
        $this->metaData->createKeywordsFromText($poll->getTitle() . ' ' . $poll->getContent(), 4, true, $reservedKeywords);
        $this->metaData->setLinkCanonical($this->config->web_url . $poll->getFullUrl());

        // Assign data for view
        $categoryParams = $params;
        $this->view->setVars(compact('contentClass', 'poll', 'articles', 'enableCoverAdd', 'allreadyVoted', 'categoryParams'));
    }

    public function saveVoteAjaxAction()
    {
        if ($this->request->isAjax() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        if (!$this->auth->isAuthorised()) {
            $error = $this->translator->trans('error_need_to_authorise', 'Need to authorise', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error, 'showLoginForm' => 1]);
            return $this->response->send();
        }

        $poll = $this->getPollRepository()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $poll Article */

        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (!is_object($member) || !is_object($poll)) {
            $error = $this->translator->trans('error_poll_and_or_member_not_found', 'Poll and/or member not found', Group::POLLS);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $pollOptionId = (string)$this->dispatcher->getParam('option_id');
        $choosedOption = null;
        foreach ($poll->getOptions() as $option) {
            if ((string)$option->getId() === $pollOptionId) {
                $choosedOption = $option;
                break;
            }
        }

        if (!is_object($choosedOption)) {
            $error = $this->translator->trans('error_poll_option_not_found', 'Choosed poll option not found', Group::POLLS);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        /*$votedOption = $this->pollsService->getVotedOptionId(
            $poll->getId(), 
            $member->getId()
        );
        $allreadyVoted = is_object($votedOption) ? true : false;*/
        if ($this->getPollVoteRepository()->allreadyVoted($poll->getId(), $member->getId())) {
        //if ($allreadyVoted) {
            $error = $this->translator->trans('error_allready_voted_poll', 'Allready voted in this poll', Group::POLLS);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        try {

            // Save vote
            $vote = new PollVote();
            $vote->setPoll($poll);
            $vote->setPollOption($choosedOption);
            $vote->setMember($member);
            $vote->setIpAddress($this->request->getClientAddress());
            $vote->setSessionId($this->session->getId());
            $vote->setUserAgent($this->request->getUserAgent());
            $this->getEntityManager()->persist($vote);
            $poll->increaseVotesCount();
            $choosedOption->increaseVotesCount();

            // Recalculate percents
            foreach ($poll->getOptions() as $option) {
                $percent = NumberTool::percentageValue($option->getVotesCount(), $poll->getVotesCount(), true, true);
                $option->setVotesPercent($percent);
            }
            $this->getEntityManager()->flush();

            $this->getEntityManager()->refresh($poll);

            // Set response
            $html = $this->pollsRenderer->renderSideBarPollInlinePart($poll);
            $this->response->setJsonContent(['success' => 1, 'html' => $html]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    public function savePollCommentAjaxAction()
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

        $poll = $this->getPollRepository()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $poll Poll */

        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        if (!is_object($member) || !is_object($poll)) {
            $error = $this->translator->trans('error_poll_and_or_member_not_found', 'Poll and/or member not found', Group::POLLS);
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
            $comment->setPoll($poll);
            $comment->setMember($member);
            $comment->setContent($txtComment);
            $comment->setIpAddress($this->request->getClientAddress());
            $comment->setSessionId($this->session->getId());
            $comment->setUserAgent($this->request->getUserAgent());
            if (is_object($replyOnComment)) {
                $comment->setReplyOnComment($replyOnComment);
            }

            $this->getEntityManager()->persist($comment);

            $poll->increaseCommentsCount();

            $this->getEntityManager()->flush();

            $lastFilter = $this->session->get('last-poll-comments-filter');

            if (is_array($lastFilter) && array_key_exists('ordering', $lastFilter) && array_key_exists('page', $lastFilter)) {
                list($html, $filters, $paginator) = $this->getCommentsListHtmlSource($poll->getId(), $lastFilter['ordering'], $lastFilter['page']);
            } else {
                list($html, $filters, $paginator) = $this->getCommentsListHtmlSource($poll->getId());
            }

            $this->response->setJsonContent(['success' => 1, 'html' => $html, 'filters' => $filters, 'paginator' => $paginator]);

        } catch (\Exception $exc) {
            $error = $this->translator->trans('error_server', 'Server error! Please, try again later.', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
        }

        return $this->response->send();
    }

    public function loadPollCommentsAjaxAction()
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

        $poll = $this->getPollRepository()->findObjectById($this->dispatcher->getParam('id'));
        /* @var $poll Poll */

        if (!is_object($poll)) {
            $error = $this->translator->trans('error_poll_not_found', 'Poll not found', Group::POLLS);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $page = $this->dispatcher->getParam('page', 'int', 1);

        $this->session->set('last-poll-comments-filter', ['ordering' => $ordering, 'page' => $page]);

        list($html, $filters, $paginator) = $this->getCommentsListHtmlSource($poll->getId(), $ordering, $page);

        $this->response->setJsonContent(['success' => 1, 'html' => $html, 'filters' => $filters, 'paginator' => $paginator]);

        return $this->response->send();
    }

    protected function getCommentsListHtmlSource($pollId, $ordering = 'ASC', $page = 1, $perPage = 10)
    {
        $query = $this->getCommentRepo()->getPollCommentsQuery($pollId, $ordering, $page, $perPage);
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

        $urlSaveComment = $this->url->get(['for' => 'poll_save_comment', 'id' => $pollId]);

        $html = $htmlFilters = $htmlPaginator = '';

        $htmlFilters = '
                            <a' . ($ordering == 'ASC' ? ' class="active"' : '') . ' href="javascript:;" onclick="comments.load(\''. $this->url->get(['for' => 'poll_load_comments', 'id' => $pollId, 'order' => 'asc', 'page' => '1']) . '\');" title="'. $txtFirst . '">'. $txtFirst . '</a>
                            <a' . ($ordering == 'DESC' ? ' class="active"' : '') . ' href="javascript:;" onclick="comments.load(\''. $this->url->get(['for' => 'poll_load_comments', 'id' => $pollId, 'order' => 'desc', 'page' => '1']) . '\');" title="'. $txtLast . '">'. $txtLast . '</a>
                            <a' . ($ordering == 'RATED' ? ' class="active"' : '') . ' href="javascript:;" onclick="comments.load(\''. $this->url->get(['for' => 'poll_load_comments', 'id' => $pollId, 'order' => 'rated', 'page' => '1']) . '\');" title="'. $txtRated . '">'. $txtRated . '</a>';

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

        $htmlPaginator = $this->gridPagerAjax->links($pollId, 'poll_load_comments', $paginator, $page, $perPage, 7, strtolower($ordering));
        $paginator = null;

        return [$html, $htmlFilters, $htmlPaginator];
    }

    /**
     * Get Poll entity repository
     * 
     * @access public
     * @return \Polls\Repository\PollRepository
     */
    public function getPollRepository()
    {
        if ($this->_pollRepo === null || !$this->_pollRepo) {
            $this->_pollRepo = $this->getEntityRepository(Poll::class);
        }

        return $this->_pollRepo;
    }

    /**
     * Get PollVote entity repository
     * 
     * @access public
     * @return \Polls\Repository\PollVoteRepository
     */
    public function getPollVoteRepository()
    {
        if ($this->_pollVoteRepo === null || !$this->_pollVoteRepo) {
            $this->_pollVoteRepo = $this->getEntityRepository(PollVote::class);
        }

        return $this->_pollVoteRepo;
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

}
