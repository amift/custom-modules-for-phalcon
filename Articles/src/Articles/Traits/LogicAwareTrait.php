<?php

namespace Articles\Traits;

use Articles\Tool\State;
use Articles\Tool\Type;
use Common\VideoUrl\VideoUrlParser;
use Common\Tool\UrlTool;

trait LogicAwareTrait
{
    
    private $_sourceUrlMaxChars = 75;

    public function isAllowedToRate()
    {
        if ($this->isNew() || $this->isActive()) {
            return true;
        }

        return false;
    }

    public function isNew()
    {
        return (int)$this->state === State::STATE_NEW ? true : false;
    }

    public function isActive()
    {
        return (int)$this->state === State::STATE_ACTIVE ? true : false;
    }

    public function isInactive()
    {
        return (int)$this->state === State::STATE_INACTIVE ? true : false;
    }

    public function isBlocked()
    {
        return (int)$this->state === State::STATE_BLOCKED ? true : false;
    }

    public function getStateLabel()
    {
        $labels = State::getLabels();
        
        if (isset($labels[$this->state])) {
            return $labels[$this->state];
        }
        
        return $this->state;
    }

    public function getFrontendStateClass()
    {
        $classes = [
            State::STATE_NEW => 'pending',
            State::STATE_ACTIVE => 'published',
            State::STATE_INACTIVE => 'cancelled',
            State::STATE_BLOCKED => 'cancelled',
        ];
        
        if (isset($classes[$this->state])) {
            return $classes[$this->state];
        }
        
        return 'pending';
    }

    public function isNews()
    {
        return (int)$this->type === Type::TYPE_NEWS ? true : false;
    }

    public function isVideo()
    {
        return (int)$this->type === Type::TYPE_VIDEO ? true : false;
    }

    public function hasMember()
    {
        if (is_object($this->getMember())) {
            return true;
        }

        return false;
    }

    public function hasSourceUrl()
    {
        return $this->sourceUrl !== null && $this->sourceUrl !== '' ? true : false;
    }

    public function prettyTitle($value = '', $suffix = '...')
    {
        return strlen($value) > $this->_sourceUrlMaxChars ? substr($value, 0 , $this->_sourceUrlMaxChars) . $suffix : $value;
    }

    public function getFormattedSource()
    {
        if ($this->hasSourceUrl()) {
            $title = UrlTool::getOnlyDomainName($this->getSourceUrl());
            return sprintf(
                '<a href="%s" target="_blank" title="%s" class="original">%s</a>',
                $this->getSourceUrl(),
                $title,
                $title //$this->prettyTitle($this->getSourceUrl())
            );
        }

        return '';
    }

    public function hasImage()
    {
        return $this->image !== null && $this->image !== '' ? true : false;
    }

    public function hasMediaAuthorName()
    {
        return $this->mediaSourceName !== null && $this->mediaSourceName !== '' ? true : false;
    }

    public function hasMediaAuthorUrl()
    {
        return $this->mediaSourceUrl !== null && $this->mediaSourceUrl !== '' ? true : false;
    }

    public function getFormattedMediaAuthor()
    {
        if ($this->hasMediaAuthorUrl() && $this->hasMediaAuthorName()) {
            $title = UrlTool::getOnlyDomainName($this->getMediaSourceUrl());
            return sprintf(
                '<a href="%s" target="_blank" title="%s" class="original">%s</a>',
                $this->getMediaSourceUrl(),
                $this->getMediaSourceName(),
                $title //$this->prettyTitle($this->getMediaSourceUrl())
            );
        } elseif ($this->hasMediaAuthorUrl() && !$this->hasMediaAuthorName()) {
            $title = UrlTool::getOnlyDomainName($this->getMediaSourceUrl());
            return sprintf(
                '<a href="%s" target="_blank" title="%s" class="original">%s</a>',
                $this->getMediaSourceUrl(),
                $title,
                $title //$this->prettyTitle($this->getMediaSourceUrl())
            );
        } elseif (!$this->hasMediaAuthorUrl() && $this->hasMediaAuthorName()) {
            return sprintf('<span class="added">%s</span>', $this->getMediaSourceName());
        }

        return '';
    }

    public function getServerPublicPath()
    {
        return str_replace('/', DS, sprintf('%s/public/', ROOT_PATH));
    }

    public function getImageDirectory()
    {
        $date = new \DateTime('now');

        $path = str_replace('/', DS, sprintf(
            '%smedia/articles/%s/%s/%s/original',
            $this->getServerPublicPath(), $date->format('Y'), $date->format('m'), $date->format('d')
        ));

        return $path;
    }

    public function getImagePublicPath($subfolder = 'original')
    {
        if ($this->imagePath !== '') {
            $orignalPublicPath = DS . str_replace($this->getServerPublicPath(), '', $this->imagePath);
            $orignalPublicPath = DS . str_replace('/var/www/clients/client1/web36/web/public/', '', $this->imagePath);
            $orignalPublicPath = str_replace(DS, '/', $orignalPublicPath);

            return str_replace('/original/', '/'.$subfolder.'/', $orignalPublicPath);
        }
        
        return '';
    }
    public function getImageServerPath()
    {
        if ($this->imagePath !== '') {
            $path = str_replace('/var/www/clients/client1/web36/web/public/', $this->getServerPublicPath(), $this->imagePath);

            return str_replace(DS, '/', $path );
        }
        
        return '';
    }

    public function getFormattedVideoSource()
    {
        return VideoUrlParser::getOutputSource($this->getVideo());
    }

    public function getIframeVideoSource()
    {
        return VideoUrlParser::getIframeSource(null, $this->getVideo());
    }

    public function getVideoSourceImagePath()
    {
        if ($this->isVideo()) {
            return VideoUrlParser::getListOutputImage($this->getVideo());
        }

        return '';
    }

    public function getDate()
    {
        if ($this->publicationDate === null) {
            return $this->createdAt;
        }

        return $this->publicationDate;
    }

    public function getListViewDate()
    {
        $date = $this->getDate();

        $now = new \DateTime('now');
        $now->setTime(10, 10, 10);

        $saved = clone $date;
        $saved->setTime(10, 10, 10);

        $days = (int)$now->diff($saved)->days;

        if ($days < 1) {
            $format = 'H:i';
        } elseif ($days < 356) {
            $format = 'd.m';
        } else {
            $format = 'd.m.Y';
        }

        return $date->format($format);
    }

    public function getOpenViewDate()
    {
        return $this->getDate()->format('d.m.Y H:i');
    }

    public function getProfileViewDate()
    {
        return $this->getDate()->format('d.m.Y');
    }

    public function getFormattedRating()
    {
        $rating = $this->getRateAvg();
        $css    = '';
        $prefix = '';

        if ($this->isPositiveRateAvg()) {
            $prefix = '+';
        } elseif ($this->isNegativeRateAvg()) {
            $css = ' negative';
        }

        return sprintf('<span class="score%s">%s%s</span>', $css, $prefix, $rating);
    }

    public function getFormattedCommentsCount()
    {
        return sprintf('(%s)', $this->getCommentsCount());
    }

    public function getUrl()
    {
        return sprintf('/%s:%s', $this->slug, $this->id);
    }

    public function getFullUrl()
    {
        $cat1Url = '/' . $this->getCategoryLvl1()->getSlug();
        $cat2Url = is_object($this->getCategoryLvl2()) ? '/' . $this->getCategoryLvl2()->getSlug() : '';
        
        return sprintf('%s%s%s', $cat1Url, $cat2Url, $this->getUrl());
    }

    public function setAsTextArticle()
    {
        $this->type = Type::TYPE_NEWS;
        
        return $this;
    }

    public function setAsVideoArticle()
    {
        $this->type = Type::TYPE_VIDEO;
        
        return $this;
    }

    public function increaseCommentsCount()
    {
        $this->commentsCount = $this->commentsCount + 1;

        return $this;
    }

    public function decreaseCommentsCount()
    {
        if ($this->commentsCount > 0) {
            $this->commentsCount = $this->commentsCount - 1;
        }

        return $this;
    }

}
