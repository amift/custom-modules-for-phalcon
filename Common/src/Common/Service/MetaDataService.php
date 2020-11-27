<?php

namespace Common\Service;

use Core\Library\AbstractLibrary;
use Common\Tool\KeywordsTool;

class MetaDataService extends AbstractLibrary
{

    /**
     * @var string
     */
    protected $_title = '';

    /**
     * @var string
     */
    protected $_description = '';

    /**
     * @var string
     */
    protected $_keywords = '';

    /**
     * @var boolean
     */
    protected $_addTitleSuffix = true;

    protected $_robots = [
        'all' => true,
        'index' => true,
        'follow' => true
    ];

    protected $_links = [
        'canonical' => '',
        'prev' => '',
        'next' => ''
    ];

    protected $_shareImage = '';

    /**
     * Gets values from config parameter 'meta_data'
     * 
     * @var array
     */
    protected $_default;

    /**
     * @var \Phalcon\Escaper
     */
    protected $_escaper;

    /**
     * On service init do values getting from config
     * 
     * @access public
     * @return void
     */
    public function init()
    {
        $this->_default = (array)$this->di->getConfig()->meta_data;
        $this->_escaper = new \Phalcon\Escaper();
    }

    /**
     * Enable title suffix
     * 
     * @access public
     * @return \Common\Service\MetaDataService
     */
    public function enableAddTitleSuffix()
    {
        $this->_addTitleSuffix = true;
        
        return $this;
    }

    /**
     * Disable title suffix
     * 
     * @access public
     * @return \Common\Service\MetaDataService
     */
    public function disableAddTitleSuffix()
    {
        $this->_addTitleSuffix = false;
        
        return $this;
    }

    /**
     * Check if title suffix is enabled or not
     * 
     * @access public
     * @return boolean
     */
    public function isEnabledAddTitleSuffix()
    {
        return $this->_addTitleSuffix === true;
    }

    /**
     * Set title
     * 
     * @access public
     * @param string $title
     * @return \Common\Service\MetaDataService
     */
    public function setTitle($title = '')
    {
        $this->_title = $title;
        
        return $this;
    }

    /**
     * Get title
     * 
     * @access public
     * @return string
     */
    public function getTitle()
    {
        if (trim($this->_title) === '') {
            $this->_title = $this->getDefaultConfigValue('title');
        }

        if ($this->isEnabledAddTitleSuffix()) {
            return $this->escape($this->_title . $this->getDefaultConfigValue('custom_title_suffix'));
        }

        return $this->escape($this->_title);
    }

    /**
     * Set description
     * 
     * @access public
     * @param string $description
     * @return \Common\Service\MetaDataService
     */
    public function setDescription($description = '')
    {
        $this->_description = $description;
        
        return $this;
    }

    /**
     * Get description
     * 
     * @access public
     * @return string
     */
    public function getDescription()
    {
        if (trim($this->_description) === '') {
            $this->_description = $this->getDefaultConfigValue('description');
        }

        return $this->escape($this->_description);
    }

    /**
     * Set keywords
     * 
     * @access public
     * @param string $keywords
     * @return \Common\Service\MetaDataService
     */
    public function setKeywords($keywords = '')
    {
        $this->_keywords = $keywords;
        
        return $this;
    }

    /**
     * Get keywords
     * 
     * @access public
     * @return string
     */
    public function getKeywords()
    {
        if (trim($this->_keywords) === '') {
            $this->_keywords = $this->getDefaultConfigValue('keywords');
        }

        return $this->escape($this->_keywords);
    }

    /**
     * Get valid site author meta data text
     * 
     * @access public
     * @return string
     */
    public function getAuthor()
    {
        return $this->escape($this->getDefaultConfigValue('author'));
    }

    /**
     * Get valid copyright meta data text
     * 
     * @access public
     * @return string
     */
    public function getCopyright()
    {
        $launchYear = $text = (int)$this->getDefaultConfigValue('year_launched');
        $currentYear = (int)date('Y');

        if ($launchYear < $currentYear) {
            $text = sprintf('%s - %s', $launchYear, $currentYear);
        }

        return $this->escape(sprintf('Copyright © %s, %s', $text, $this->getAuthor()));
    }

    /**
     * Get from config default meta data value by key
     * 
     * @access public
     * @param string $param
     * @return string
     * @throws \RuntimeException
     */
    public function getDefaultConfigValue($param = '')
    {
        if (!array_key_exists($param, $this->_default)) {
            throw new \RuntimeException(sprintf('Config parameter "%s" not found', $param));
        }
        
        return $this->_default[$param];
    }

    /**
     * Clean meta data value and trim double spaces
     * 
     * @access public
     * @param string $value
     * @return string
     */
    public function escape($value = '')
    {
        $value = str_replace(['»', '«', '"', "'", '„', '”'], ' ', $value);
        
        return preg_replace('/\s+/', ' ', $this->_escaper->escapeHtmlAttr($value));
    }

    public function createDescriptionFromText($text = '')
    {
        $this->_description = KeywordsTool::text(
            $text, 160, '', $this->_escaper
        );

        return $this;
    }

    public function getDescriptionFromText($text = '', $endChars = '')
    {
        return KeywordsTool::text(
            $text, 160, $endChars, $this->_escaper
        );
    }

    public function createKeywordsFromText($text = '', $maxKeys = 25, $addDefault = false, $customWords = '')
    {
        if ($customWords !== '') {
             $this->_keywords .= $customWords;
        }

        $autoWords = KeywordsTool::keywords(
            $text, $maxKeys, $this->_escaper, (array)$this->getDefaultConfigValue('keywords_banned_words')
        );
        if ($autoWords !== '') {
            $this->_keywords .= (trim($this->_keywords) !== '' ? ', ' : '') . $autoWords;
        }

        if ($addDefault === true) {
            $this->_keywords .= (trim($this->_keywords) !== '' ? ', ' : '') . $this->getDefaultConfigValue('keywords');
        }

        return $this;
    }

    protected function setRobotsByKey($key, $values = [])
    {
        if (isset($values[$key]) && is_bool($values[$key])) {
            $this->_robots[$key] = $values[$key];
        }
    }

    public function setRobots($values = [])
    {
        $this->setRobotsByKey('all', $values);
        $this->setRobotsByKey('index', $values);
        $this->setRobotsByKey('follow', $values);

        return $this;
    }

    public function getRobots()
    {
        return $this->_robots;
    }

    public function getRobotsAsString()
    {
        $items = [];
        if ($this->_robots['all'] === true) {
            $items[] = 'all';
        }
        $items[] = $this->_robots['index'] === true ? 'index' : 'noindex';
        $items[] = $this->_robots['follow'] === true ? 'follow' : 'nofollow';

        return implode(',', $items);
    }

    public function getGoogleSiteVerificationCodeTag()
    {
        $code = $this->getDefaultConfigValue('google_site_verification_code');
        if ($code !== '') {
            return sprintf('<meta name="google-site-verification" content="%s">', $code);
        }

        return '';
    }

    public function setLinkByKey($key, $value = '')
    {
        $this->_links[$key] = $value;
    }

    public function setLinksByKey($key, $values = [])
    {
        if (isset($values[$key])) {
            $this->_links[$key] = $values[$key];
        }
    }

    public function setLinkCanonical($value = '')
    {
        $this->setLinkByKey('canonical', $value);

        return $this;
    }

    public function setLinkPrev($value = '')
    {
        $this->setLinkByKey('prev', $value);

        return $this;
    }

    public function setLinkNext($value = '')
    {
        $this->setLinkByKey('next', $value);

        return $this;
    }

    public function setLinks($values = [])
    {
        $this->setLinkByKey('canonical', $values);
        $this->setLinkByKey('prev', $values);
        $this->setLinkByKey('next', $values);

        return $this;
    }

    public function getLinks()
    {
        return $this->_links;
    }

    public function getLinkByKey($key)
    {
        if (isset($this->_links[$key])) {
            return $this->_links[$key];
        }

        return '';
    }

    public function getCanonicalLinkTag()
    {
        $value = $this->getLinkByKey('canonical');
        if ($value !== '') {
            return sprintf('<link rel="canonical" href="%s">', $value);
        }

        return '';  
    }

    public function getRelatedPreviousLinkTag()
    {
        $value = $this->getLinkByKey('prev');
        if ($value !== '') {
            return sprintf('<link rel="prev" href="%s">', $value);
        }

        return '';  
    }

    public function getRelatedNextLinkTag()
    {
        $value = $this->getLinkByKey('next');
        if ($value !== '') {
            return sprintf('<link rel="next" href="%s">', $value);
        }

        return '';  
    }

    public function getFacebookMetaOgTags($siteUrl = '')
    {
        $lines = [
            sprintf('<meta property="og:type" content="article">'),
            sprintf('<meta property="og:site_name" content="%s">', $this->getAuthor()),
            sprintf('<meta property="og:title" content="%s">', $this->getTitle()),
            sprintf('<meta property="og:description" content="%s">', $this->getDescription()),
            sprintf('<meta property="og:image" content="%s%s">', $siteUrl, $this->getShareImage()),
            sprintf('<meta property="og:image:width" content="1200">'),
            sprintf('<meta property="og:image:height" content="630">'),
            sprintf('<meta property="og:image" content="%s%s">', $siteUrl, $this->getDefaultConfigValue('fb_page_logo')),
        ];

        try {
            $fanPageId = $this->getDefaultConfigValue('fb_fan_page_id');
        } catch (\Exception $exc) {
            $fanPageId = '';
        }

        if ($fanPageId !== '') {
            $lines[] = sprintf('<meta property="fb:page_id" content="%s" />', $fanPageId);
            $lines[] = sprintf('<meta property="fb:pages" content="%s" />', $fanPageId);
        }

        return implode('', $lines);
    }

    public function getTwitterMetaOgTags($siteUrl = '')
    {
        $lines = [
            sprintf('<meta name="twitter:card" content="summary_large_image">'),
            sprintf('<meta name="twitter:title" content="%s">', $this->getTitle()),
            sprintf('<meta name="twitter:description" content="%s">', $this->getDescription()),
            sprintf('<meta name="twitter:image" content="%s%s">', $siteUrl, $this->getShareImage()),
        ];

        $url = $this->getLinkByKey('canonical');
        if ($url === '') {
            $url = $siteUrl;
        }

        if ($url !== '') {
            $lines[] = sprintf('<meta name="twitter:url" content="%s">', $url);
        }

        try {
            $twitterAccount = $this->getDefaultConfigValue('twitter_account');
        } catch (\Exception $exc) {
            $twitterAccount = '';
        }

        if ($twitterAccount !== '') {
            $lines[] = sprintf('<meta name="twitter:site" content="%s">', $twitterAccount);
        }

        return implode('', $lines);
    }

    public function getCustomChareJs($siteUrl = '')
    {
        $url = $this->getLinkByKey('canonical');
        if ($url === '') {
            $url = $siteUrl;
        }

        return '
    <script type="text/javascript">
        /*<![CDATA[*/
            var shareUrl=encodeURIComponent(\''.$url.'\');
            var shareTitle=encodeURIComponent(\''.$this->getTitle().'\');
            var shareSummary=encodeURIComponent(\''.$this->getDescription().'\');
            var shareImg=encodeURIComponent(\''.$siteUrl.$this->getShareImage().'\');
        /*]]>*/
    </script>';
    }

    public function setShareImage($value = '')
    {
        $this->_shareImage = $value;
        
        return $this;
    }

    public function getShareImage()
    {
        if (trim($this->_shareImage) === '') {
            $this->_shareImage = $this->getDefaultConfigValue('default_share_image');
        }

        return $this->_shareImage;
    }

    public function getLikeMetaTitle($value = '')
    {
        if (trim($value) === '') {
            $value = $this->getDefaultConfigValue('title');
        }

        $suffix = $this->getDefaultConfigValue('custom_title_suffix');
        if ($suffix !== '') {
            return $this->escape($value . $suffix);
        }

        return $this->escape($value);
    }

    public function getLikeMetaDescription($value = '')
    {
        if (trim($value) === '') {
            $value = $this->getDefaultConfigValue('description');
        }

        return $this->escape($value);
    }

}
