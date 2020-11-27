<?php

namespace Articles\Service;

use Core\Library\AbstractLibrary;
use Articles\Entity\Article;
use Articles\Tool\State;
use Articles\Tool\Type;

class ArticlesService extends AbstractLibrary
{

    /**
     * @var \Articles\Repository\ArticleRepository
     */
    protected $_articleRepo;

    /**
     * Get Article entity reposotory
     * 
     * @access public
     * @return \Articles\Repository\ArticleRepository
     */
    public function getArticleRepo()
    {
        if ($this->_articleRepo === null || !$this->_articleRepo) {
            $this->_articleRepo = $this->getEntityRepository(Article::class);
        }

        return $this->_articleRepo;
    }

    public function getArticlesForOutput($categoryFieldName = null, $categoryFieldValue = null, $fullUrl = '')
    { 
        $result = [
            'promo' => null,//$this->getArticleRepo()->getPromoArticle($categoryFieldName, $categoryFieldValue),
            'side' => [
                'news' => null,
                'video' => null,
            ],
            'center' => null,
            'masonry' => $this->config->settings->masonry,
        ];
        
        if ($result['masonry'] === true) {

            $result['center']['currentPage'] = (int)$this->request->getQuery('page', 'int', 1);
            $result['center']['perPage'] = 24;
            $result['center']['paginator'] = $this->getArticleRepo()->getCenterArticles(
                $categoryFieldName, $categoryFieldValue, [], $result['center']['currentPage'], $result['center']['perPage']
            );

        } else {

            $excludeIds = [];
            if (is_object($result['promo'])) {
                $excludeIds[] = $result['promo']->getId();
            }

            $result['side']['video'] = $this->getArticleRepo()->getActualVideoArticles($categoryFieldName, $categoryFieldValue, $excludeIds);
            foreach ($result['side']['video'] as $article) {
                $excludeIds[] = $article->getId();
            }

            $result['side']['news'] = $this->getArticleRepo()->getActualNewsArticles($categoryFieldName, $categoryFieldValue, $excludeIds);
            foreach ($result['side']['news'] as $article) {
                $excludeIds[] = $article->getId();
            }

            $result['center']['currentPage'] = (int)$this->request->getQuery('page', 'int', 1);
            $result['center']['perPage'] = 10;
            $result['center']['paginator'] = $this->getArticleRepo()->getCenterArticles(
                $categoryFieldName, $categoryFieldValue, $excludeIds, 
                $result['center']['currentPage'], $result['center']['perPage']
            );

        }

        return $result;
    }

    public function getSideActualArticles($categoryFieldName = null, $categoryFieldValue = null, $excludeIds = [])
    {
        return $this->getArticleRepo()->getActualNewsArticles($categoryFieldName, $categoryFieldValue, $excludeIds);
    }

    public function getArticleById($id = null)
    {
        return $this->getArticleRepo()->findObjectById($id);
    }

}



