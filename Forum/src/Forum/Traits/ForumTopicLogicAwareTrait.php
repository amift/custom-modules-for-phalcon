<?php

namespace Forum\Traits;

use Forum\Tool\ForumTopicState;

trait ForumTopicLogicAwareTrait
{

    public function getTopicUrl($baseUrl = '')
    {
        return sprintf('%s/%s:%s/%s', $baseUrl, $this->slug, $this->id, 'asc');
    }

    public function getLastReplyListViewDate()
    {
        $date = $this->getLastReplyAt();

        if (!is_object($date)) {
            return null;
        }

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

    public function isAllowedToRate()
    {
        if ($this->isNew() || $this->isActive()) {
            return true;
        }

        return false;
    }

    public function isNew()
    {
        return (int)$this->state === ForumTopicState::STATE_NEW ? true : false;
    }

    public function isActive()
    {
        return (int)$this->state === ForumTopicState::STATE_ACTIVE ? true : false;
    }

    public function isInactive()
    {
        return (int)$this->state === ForumTopicState::STATE_INACTIVE ? true : false;
    }

    public function isBlocked()
    {
        return (int)$this->state === ForumTopicState::STATE_BLOCKED ? true : false;
    }

    public function getStateLabel()
    {
        $labels = ForumTopicState::getLabels();
        
        if (isset($labels[$this->state])) {
            return $labels[$this->state];
        }
        
        return $this->state;
    }

    public function getFrontendStateClass()
    {
        $classes = [
            ForumTopicState::STATE_NEW => 'pending',
            ForumTopicState::STATE_ACTIVE => 'published',
            ForumTopicState::STATE_INACTIVE => 'cancelled',
            ForumTopicState::STATE_BLOCKED => 'cancelled',
        ];
        
        if (isset($classes[$this->state])) {
            return $classes[$this->state];
        }
        
        return 'pending';
    }

    public function hasMember()
    {
        if (is_object($this->getMember())) {
            return true;
        }

        return false;
    }

    public function getDate()
    {
        return $this->createdAt;
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

    public function getProfileViewDate()
    {
        return $this->getDate()->format('d.m.Y');
    }

    public function getFormattedRating()
    {
        $rating = $this->getRateAvg();
        $css    = '';
        $prefix = '';

        if ($this->isPositiveRateAvg()) {
            $prefix = '+';
        } elseif ($this->isNegativeRateAvg()) {
            $css = ' negative';
        }

        return sprintf('<span class="score%s">%s%s</span>', $css, $prefix, $rating);
    }

    public function getFormattedCommentsCount()
    {
        return sprintf('(%s)', $this->getCommentsCount());
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

    public function increaseViewsCount()
    {
        $this->viewsCount = $this->viewsCount + 1;

        return $this;
    }

    public function decreaseViewsCount()
    {
        if ($this->viewsCount > 0) {
            $this->viewsCount = $this->viewsCount - 1;
        }

        return $this;
    }

}
