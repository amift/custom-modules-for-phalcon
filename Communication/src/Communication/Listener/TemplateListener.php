<?php

namespace Communication\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Communication\Entity\Template;

class TemplateListener implements EventSubscriber
{

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if (! $args->getEntity() instanceOf Template) {
            return;
        }

        $template = $args->getEntity();
        /* @var $template Template */

        $template->setCode(sprintf('manual_template_%s', $template->getId()));
    }

}
