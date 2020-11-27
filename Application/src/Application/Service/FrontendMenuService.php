<?php

namespace Application\Service;

use Core\Library\AbstractLibrary;
use Articles\Entity\Category;

class FrontendMenuService extends AbstractLibrary
{

    /**
     * @var \Articles\Repository\CategoryRepository
     */
    protected $_categoryRepo;

    public function getMenuItems()
    {
        //$items = $this->cache->get('menu-items');
        $items = \Phalcon\Di::getDefault()->get('cache')->get('menu-items');
            
        if ($items === null) {
            $items = [];

            $rows = $this->getCategoryRepo()->getCategoriesList();
            foreach ($rows as $row) {
                if (isset($row['enabled']) && isset($row['showInMenu']) && $row['enabled'] === true && $row['showInMenu'] === true) {
                    if ($row['parent'] === null) {
                        if (isset($items[$row['id']])) {
                            $items[$row['id']]['title'] = $row['title'];
                            $items[$row['id']]['slug'] = $row['slug'];
                            $items[$row['id']]['image'] = $row['image'] === null ? '' : $row['image'];
                        } else {
                            $items[$row['id']] = [
                                'title' => $row['title'],
                                'slug' => $row['slug'],
                                'image' => $row['image'] === null ? '' : $row['image'],
                                //'url' => sprintf('/%s', $row['slug']),
                                'childs' => []
                            ];
                        }
                    } else {
                        if (isset($items[$row['parent']])) {
                            $items[$row['parent']]['childs'][$row['id']] = [
                                'title' => $row['title'],
                                'slug' => $row['slug'],
                                'image' => $row['image'] === null ? '' : $row['image'],
                                //'url' => sprintf('/%s/%s', $items[$row['parent']]['slug'], $row['slug']),
                            ];
                        }
                    }
                }
            }

            //$this->cache->save('menu-items', $items);
            \Phalcon\Di::getDefault()->get('cache')->save('menu-items', $items);
        }

        return $items;
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

}
