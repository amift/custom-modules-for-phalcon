<?php

return [
    'di' => [
        'localeHandler' => 'Common\Service\LocaleHandlerService',
        'flashMessages' => 'Common\View\Tag\FlashSessionOutputHelper',
        'gridPager' => 'Common\View\Tag\PagerHelper',
        'enableStatus' => 'Common\View\Tag\EnableStatusHelper',
        'dateChangedAt' => 'Common\View\Tag\DateChangedAtHelper',
        'uploader' => 'Common\Upload\Uploader',
    ],
];
