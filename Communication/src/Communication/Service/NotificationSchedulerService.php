<?php

namespace Communication\Service;

use Communication\Entity\Notification;
use Communication\Entity\Template;
use Communication\Tool\NotificationState as State;
use Communication\Tool\TemplateType as Type;
use Core\Library\AbstractLibrary;
use Members\Entity\Member;

class NotificationSchedulerService extends AbstractLibrary
{
    /**
     * @var \Communication\Repository\TemplateRepository
     */
    protected $_templatesRepo;

    /**
     * @var \Members\Repository\MemberRepository
     */
    protected $_membersRepo;

    /**
     * Schedule notification
     *
     * @access public
     * @param int $memberId
     * @param type $templateCode
     * @param array $params
     * @param array $attachments
     * @param null|DateTime $toSendAt
     * @return void
     * @throws \Core\Exception\RuntimeException
     */
    public function scheduleNotification($memberId = null, $templateCode = '', array $params = [], array $attachments = [], $toSendAt = null)
    {
        $templateType = 'email';

        $member = $this->getMemberRepo()->findObjectById($memberId);
        if ($member === null) {
            throw new \Core\Exception\RuntimeException('Member not found');
        }

        $template = $this->getTemplateRepo()->getTemplate($templateCode);
        /* @var $template Template */

        if ($template === null) {
            throw new \Core\Exception\RuntimeException(sprintf('Template with type "%s" and code "%s" not found!', $templateType, $templateCode));
        }

        if (! $template->isEnabled()) {
            return;
        }

        // Fill placeholders with data
        $placeholders = $this->getFilledPlaceholders($template->getPlaceholders(), $params);

        // Set up notification data
        $notification = new Notification();
        $notification->setType(Type::TYPE_EMAIL);
        $notification->setState(State::STATUS_NEW);
        if ( $toSendAt instanceof \DateTime ) {
            $notification->setToSendAt($toSendAt);
        }
        $notification->setMember($member);
        $notification->setReceiver($member->getEmail());
        $notification->setTemplate($template);
        $notification->setSubject($template->getSubject());
        $notification->setBody(strtr($template->getBody(), $placeholders));
        $notification->setFromEmail($template->getFromEmail());
        $notification->setFromName($template->getFromName());

        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush($notification);
    }

    /**
     * Filled placeholders with given values.
     *
     * @access protected
     * @param array $placeholders
     * @param array $params
     * @return array
     */
    protected function getFilledPlaceholders(array $placeholders = [], array $params = [])
    {
        $filled = [];

        foreach ($placeholders as $placeholder) {
            $key = sprintf('{{ %s }}', $placeholder);
            $filled[$key] = isset($params[$placeholder]) ? $params[$placeholder] : '';
        }

        return $filled;
    }

    /**
     * Get Template entity repository
     *
     * @access protected
     * @return \Communication\Repository\TemplateRepository
     */
    protected function getTemplateRepo()
    {
        if ($this->_templatesRepo === null || !$this->_templatesRepo) {
            $this->_templatesRepo = $this->getEntityRepository(Template::class);
        }

        return $this->_templatesRepo;
    }

    /**
     * Get Member entity repository
     *
     * @access protected
     * @return \Members\Repository\MemberRepository
     */
    protected function getMemberRepo()
    {
        if ($this->_membersRepo === null || !$this->_membersRepo) {
            $this->_membersRepo = $this->getEntityRepository(Member::class);
        }

        return $this->_membersRepo;
    }
}