<?php

namespace Articles\View\Helper;

use Articles\Entity\Article;
use Articles\Tool\State;

class ArticleStatusHelper
{

    public function table(Article $article, $emptyValue = '-')
    {
        if ((int)$article->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($article);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s table-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    public function big(Article $article, $emptyValue = '-')
    {
        if ((int)$article->getState() === 0) {
            return $emptyValue;
        }

        list($title, $class) = $this->getData($article);

        $class = $class === 'label-default' ? 'label-neutral' : $class;

        $maskStatus = sprintf(
            '<div><span class="label %s big-label">%s</span></div>',
            $class, $title
        );

        return $maskStatus;
    }

    protected function getData(Article $article)
    {
        $status = $article->getState();
        $styles = State::getStyles();
        $labels = State::getLabels();

        if (! isset($styles[$status])) {
            throw new \RuntimeException(sprintf('Article status "%s" do not have style configuration data', $status));
        }
        $class = $styles[$status];

        if (! isset($labels[$status])) {
            throw new \RuntimeException(sprintf('Article status "%s" do not have label configuration data', $status));
        }
        $title = $labels[$status];

        return [$title, $class];
    }

}
