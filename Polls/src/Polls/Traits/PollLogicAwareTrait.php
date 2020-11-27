<?php

namespace Polls\Traits;

use Polls\Tool\State;

trait PollLogicAwareTrait
{

    public function isPending()
    {
        return (int)$this->state === State::STATE_PENDING ? true : false;
    }

    public function isActive()
    {
        return (int)$this->state === State::STATE_ACTIVE ? true : false;
    }

    public function isArchived()
    {
        return (int)$this->state === State::STATE_ARCHIVED ? true : false;
    }

    public function getStateLabel()
    {
        $labels = State::getLabels();
        
        if (isset($labels[$this->state])) {
            return $labels[$this->state];
        }
        
        return $this->state;
    }

    public function getDate()
    {
        if ($this->publicationDate === null) {
            return $this->createdAt;
        }

        return $this->publicationDate;
    }

    public function getListViewDate()
    {
        $date = $this->getDate();

        $now = new \DateTime('now');
        $now->setTime(10, 10, 10);

        $saved = clone $date;
        $saved->setTime(10, 10, 10);

        $days = (int)$now->diff($saved)->days;

        if ($days < 1) {
            $format = 'H:i';
        } elseif ($days < 356) {
            $format = 'd.m';
        } else {
            $format = 'd.m.Y';
        }

        return $date->format($format);
    }

    public function getOpenViewDate()
    {
        return $this->getDate()->format('d.m.Y H:i');
    }

    public function getUrl()
    {
        return sprintf('/%s:%s', $this->slug, $this->id);
    }

    public function getFullUrl()
    {
        $cat1Url = '/' . $this->getCategoryLvl1()->getSlug();
        $cat2Url = is_object($this->getCategoryLvl2()) ? '/' . $this->getCategoryLvl2()->getSlug() : '';
        
        return sprintf('%s%s%s%s', $cat1Url, $cat2Url, '/aptauja', $this->getUrl());
    }

    public function getFormattedCommentsCount()
    {
        return sprintf('(%s)', $this->getCommentsCount());
    }

    public function getFormattedVotesCount($textWhenMany = 'votes', $textWhenOne = 'vote', $textWhenNoVotes = 'Currently no votes')
    {
        $count = $this->getVotesCount();

        $text = '';
        if ((int)$count > 1) {
            $text = $textWhenMany;
        } elseif ((int)$count < 1) {
            $text = $textWhenNoVotes;
            $count = '';
        } else {
            $text = $textWhenOne;
        }

        return trim(sprintf('%s %s', $count, $text));
    }

    public function increaseCommentsCount()
    {
        $this->commentsCount = $this->commentsCount + 1;

        return $this;
    }

    public function decreaseCommentsCount()
    {
        if ($this->commentsCount > 0) {
            $this->commentsCount = $this->commentsCount - 1;
        }

        return $this;
    }

    public function increaseVotesCount()
    {
        $this->votesCount = $this->votesCount + 1;

        return $this;
    }

    public function decreaseVotesCount()
    {
        if ($this->votesCount > 0) {
            $this->votesCount = $this->votesCount - 1;
        }

        return $this;
    }

    public function decreaseVotesCountByValue($value = 0)
    {
        if ($this->votesCount > 0 && (int)$value > 0) {
            $this->votesCount = $this->votesCount - (int)$value;
        }

        return $this;
    }

}
