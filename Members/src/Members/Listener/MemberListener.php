<?php

namespace Members\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Members\Entity\Member;

class MemberListener implements EventSubscriber
{

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            //Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    /*public function prePersist(LifecycleEventArgs $args)
    {
        if ( ! $args->getEntity() instanceOf Member ) {
            return;
        }
    }*/

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if ( ! $args->getEntity() instanceOf Member ) {
            return;
        }

        $member = $args->getEntity();
        /* @var $member Member */

        // Create unique random confirmation code
        $code = $member->getId() . preg_replace('/[^a-zA-Z0-9]/', '', base64_encode(openssl_random_pseudo_bytes(24)));
        $member->setConfirmCode(substr($code, 0, 50));
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ( ! $args->getEntity() instanceOf Member ) {
            return;
        }

        $this->preUpdateData($args);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    protected function preUpdateData(PreUpdateEventArgs $args)
    {
        $member = $args->getEntity();
        /* @var $member Member */

        // If status changed setup also status change date
        if ($args->hasChangedField('state') && $args->getOldValue('state') != $args->getNewValue('state')) {
            $member->setStateChangedAt(new \DateTime('now'));
        }

        // If confirmed changed setup also confirmed change date
        if ($args->hasChangedField('confirmed') && $args->getOldValue('confirmed') != $args->getNewValue('confirmed')) {
            $member->setConfirmedAt(new \DateTime('now'));
        }
    }

}
