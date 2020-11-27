<?php

namespace Users\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Users\Entity\User;

class UserListener implements EventSubscriber
{

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ( ! $args->getEntity() instanceOf User ) {
            return;
        }

        $this->preUpdateData($args);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    protected function preUpdateData(PreUpdateEventArgs $args)
    {
        $user = $args->getEntity();
        /* @var $user User */

        // If status changed setup also status change date
        if ($args->hasChangedField('state') && $args->getOldValue('state') != $args->getNewValue('state')) {
            $user->setStateChangedAt(new \DateTime('now'));
        }
    }

}
