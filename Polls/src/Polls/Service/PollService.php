<?php

namespace Polls\Service;

use Core\Library\AbstractLibrary;
use Polls\Entity\Poll;
use Polls\Entity\PollVote;

class PollService extends AbstractLibrary
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
     * Get poll object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Poll
     */
    public function getById($id = null)
    {
        return $this->getPollRepository()->findObjectById($id);
    }

    /**
     * Get poll object by categories param for sidebar.
     * 
     * @access public
     * @param null|string $categoryFieldName
     * @param null|string $categoryFieldValue
     * @return null|Poll
     */
    public function getSidebarPoll($categoryFieldName = null, $categoryFieldValue = null)
    {
        if ($categoryFieldName === null) {
            return $this->getPollRepository()->findObjectStartpage();
        } else {
            switch ($categoryFieldName) {
                case 'categoryLvl1':
                    return $this->getPollRepository()->findObjectByCategory1Level($categoryFieldValue);
                    break;
                case 'categoryLvl2':
                    return $this->getPollRepository()->findObjectByCategory2Level($categoryFieldValue);
                    break;
                case 'categoryLvl3':
                    return $this->getPollRepository()->findObjectByCategory3Level($categoryFieldValue);
                    break;
                default:
                    return null;
                    break;
            }
        }
    }

    public function isAllreadyVoted($pollId = null, $memberId = null)
    {
        if ($pollId !== null && $memberId !== null) {
            return $this->getPollVoteRepository()->allreadyVoted($pollId, $memberId);
        }

        return false;
    }

    public function getVotedOptionId($pollId = null, $memberId = null)
    {
        if ($pollId !== null && $memberId !== null) {
            $vote = $this->getPollVoteRepository()->getMemberVotedPollOption($pollId, $memberId);
            if ($vote !== null) {
                return $vote->getPollOption();
            }
        }

        return null;
    }

}
