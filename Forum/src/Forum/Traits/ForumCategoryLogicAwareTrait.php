<?php

namespace Forum\Traits;

trait ForumCategoryLogicAwareTrait
{

    public function increaseTopicsCount()
    {
        $this->topicsCount = $this->topicsCount + 1;

        return $this;
    }

    public function decreaseTopicsCount()
    {
        if ($this->topicsCount > 0) {
            $this->topicsCount = $this->topicsCount - 1;
        }

        return $this;
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
