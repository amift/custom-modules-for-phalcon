<?php

namespace Articles\Service;

use Core\Library\AbstractLibrary;
use Articles\Entity\Category;

class CategoriesService extends AbstractLibrary
{

    /**
     * @var \Articles\Repository\CategoryRepository
     */
    protected $_categoryRepo;

    /**
     * @var array
     */
    protected $_categortSlugItems;

    public function getCategorySlugItems()
    {
        if ($this->_categortSlugItems !== null && is_array($this->_categortSlugItems)) {
            return $this->_categortSlugItems;
        }

        $items = $this->cache->get('category-items');
            
        if ($items === null) {
            $items = [];

            $rows = $this->getCategoryRepo()->getEnabledCategoriesSlugList();
            foreach ($rows as $row) {
                switch ((int)$row['level']) {
                    case 1 :
                        //$items[$row['slug']][''][''] = $row['id'];
                        $items[$row['slug']][''] = $row['id'];
                        break;
                    case 2 :
                        //$items[$row['parent1Slug']][$row['slug']][''] = $row['id'];
                        $items[$row['parent1Slug']][$row['slug']] = $row['id'];
                        break;
                    /*case 3 :
                        $items[$row['parent2Slug']][$row['parent1Slug']][$row['slug']] = $row['id'];
                        break;*/
                }
            }

            $this->cache->save('category-items', $items);
        }

        $this->_categortSlugItems = $items;

        return $items;
    }

    public function isCategoriesParamsReal($cat1 = '', $cat2 = '')//, $cat3 = '')
    {
        $categories = $this->getCategorySlugItems();

        //if (!isset($categories[$cat1][''][''])) {
        if (!isset($categories[$cat1][''])) {
            return false;
        }

        //if (!isset($categories[$cat1][$cat2][''])) {
        if (!isset($categories[$cat1][$cat2])) {
            return false;
        }

        /*if (!isset($categories[$cat1][$cat2][$cat3])) {
            return false;
        }*/

        return true;
    }

    public function getCategoryFilterCriteria($cat1 = '', $cat2 = '')//, $cat3 = '')
    {
        $result = [
            'name' => null,
            'value' => null
        ];

        if ($cat1 == '' && $cat2 == '') {
            return $result;
        }

        $categories = $this->getCategorySlugItems();

        if ($cat1 !== '') {
            $result = [
                'name' => 'categoryLvl1',
                'value' => $categories[$cat1]['']
            ];
        }

        if ($cat2 !== '') {
            $result = [
                'name' => 'categoryLvl2',
                'value' => $categories[$cat1][$cat2]
            ];
        }

        return $result;
    }

    /**
     * Get Category entity reposotory
     * 
     * @access public
     * @return \Articles\Repository\CategoryRepository
     */
    public function getCategoryRepo()
    {
        if ($this->_categoryRepo === null || !$this->_categoryRepo) {
            $this->_categoryRepo = $this->getEntityRepository(Category::class);
        }

        return $this->_categoryRepo;
    }

    public function getCategoryBySlugFromLevel($slug = '', $parent = null, $level = 1)
    {
        return $this->getCategoryRepo()->findObjectBySlugAndParentFromLevel(
            $slug, (is_object($parent) ? $parent->getId() : 0), $level
        );
    }

}


