<?php

return [
    'di' => [
        'newsletterStatus' => 'Communication\View\Helper\NewsletterStatusHelper',
        'notificationStatus' => 'Communication\View\Helper\NotificationStatusHelper',
        'template_listener' => 'Communication\Listener\TemplateListener',
        'email_sender' => 'Communication\Service\EmailSenderService',
        'notification_scheduler' => 'Communication\Service\NotificationSchedulerService',
        'notification_sender' => 'Communication\Service\NotificationSenderService',
        'newsletter_service' => 'Communication\Service\NewsletterService',
    ],
];
