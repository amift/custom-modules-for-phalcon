<?php

namespace Articles\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Articles\Entity\Category;

class CategoryListener implements EventSubscriber
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
        if ( ! $args->getEntity() instanceOf Category ) {
            return;
        }

        $this->prePersistData($args);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ( ! $args->getEntity() instanceOf Category ) {
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
        /* @var $category Category */

        $repo = $args->getEntityManager()->getRepository(Category::class);
        $category->setOrdering($repo->getNextOrderingValue());

        $this->updateCategoryLevel($category);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    protected function preUpdateData(PreUpdateEventArgs $args)
    {
        $category = $args->getEntity();
        /* @var $category Category */

        if ($args->hasChangedField('parent') && $args->getOldValue('parent') != $args->getNewValue('parent')) {
            $this->updateCategoryLevel($category);
        }
    }

    protected function updateCategoryLevel(Category $category)
    {
        $level = 1;

        if (is_object($category->getParent())) {
            $level = (int)$category->getParent()->getLevel() + 1;
        }

        $category->setLevel($level);
    }

    protected function updateChildsLevels(Category $category)
    {
        //var_dump($category->getChildrens());
        //die('CategoryListener->updateChildsLevels');
    }

}

