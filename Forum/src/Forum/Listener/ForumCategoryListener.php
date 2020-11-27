<?php

namespace Forum\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Forum\Entity\ForumCategory;

class ForumCategoryListener implements EventSubscriber
{

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        if ( ! $args->getEntity() instanceOf ForumCategory ) {
            return;
        }

        $this->prePersistData($args);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ( ! $args->getEntity() instanceOf ForumCategory ) {
            return;
        }

        $this->preUpdateData($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    protected function prePersistData(LifecycleEventArgs $args)
    {
        $category = $args->getEntity();
        /* @var $category ForumCategory */

        $repo = $args->getEntityManager()->getRepository(ForumCategory::class);
        $category->setOrdering($repo->getNextOrderingValue());

        $this->updateCategoryLevel($category);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    protected function preUpdateData(PreUpdateEventArgs $args)
    {
        $category = $args->getEntity();
        /* @var $category ForumCategory */

        if ($args->hasChangedField('parent') && $args->getOldValue('parent') != $args->getNewValue('parent')) {
            $this->updateCategoryLevel($category);
        }
    }

    protected function updateCategoryLevel(ForumCategory $category)
    {
        $level = 1;

        if (is_object($category->getParent())) {
            $level = (int)$category->getParent()->getLevel() + 1;
        }

        $category->setLevel($level);
    }

    protected function updateChildsLevels(ForumCategory $category)
    {
        //var_dump($category->getChildrens());
        //die('CategoryListener->updateChildsLevels');
    }

}

