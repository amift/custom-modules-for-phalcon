<?php

return [
    'di' => [
        'localeHandler' => 'Common\Service\LocaleHandlerService',
        'uploader' => 'Common\Upload\Uploader',
        'gridPagerAjax' => 'Common\View\Tag\Frontend\PagerAjaxHelper',
        'gridPager' => 'Common\View\Tag\Frontend\PagerHelper',
        'metaData' => 'Common\Service\MetaDataService',
    ],
];
