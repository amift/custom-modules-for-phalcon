<?php

namespace Polls\View\Helper;

use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\View\Simple as SimpleView;

class PollsOutputHelper extends Component
{

    /**
     * Get SimpleView object for view rendering.
     * 
     * @access protected
     * @return SimpleView
     */
    protected function getView()
    {
        $view = new SimpleView();
        $view->setViewsDir(ROOT_PATH . str_replace('/', DS, '/module/Polls/view/' . APP_TYPE . '/polls/'));

        return $view;
    }

    public function renderSideBarPoll($categoryFieldName = null, $categoryFieldValue = null)
    {
        $poll = $this->pollsService->getSidebarPoll($categoryFieldName, $categoryFieldValue);

        if ($poll === null) {
            return '';
        }

        return $this->getView()->render('sidebar', compact('poll'));
    }

    public function renderSideBarPollInlinePart($poll = null)
    {
        if ($poll === null) {
            return '';
        }

        $votedOption = $this->pollsService->getVotedOptionId(
            $poll->getId(), 
            $this->auth->getAuthorisedUserId()
        );

        $showResults = is_object($votedOption) ? true : false;

        return $this->getView()->render('sidebarInline', compact('poll', 'showResults', 'votedOption'));
    }

}
