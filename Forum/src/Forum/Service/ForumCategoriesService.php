<?php

namespace Forum\Service;

use Core\Library\AbstractLibrary;
use Forum\Entity\ForumCategory;

class ForumCategoriesService extends AbstractLibrary
{

    /**
     * @var \Forum\Repository\ForumCategoryRepository
     */
    protected $_categoryRepo;

    /**
     * @var array
     */
    protected $_categortSlugItems;

    /**
     * @return array
     */
    public function getCategorySlugItems()
    {
        if ($this->_categortSlugItems !== null && is_array($this->_categortSlugItems)) {
            return $this->_categortSlugItems;
        }

        $items = $this->cache->get('forum-category-items');
            
        if ($items === null) {
            $items = [];

            $rows = $this->getForumCategoryRepo()->getEnabledCategoriesSlugList();
            foreach ($rows as $row) {
                switch ((int)$row['level']) {
                    case 1 :
                        $items[$row['slug']][''][''] = $row['id'];
                        break;
                    case 2 :
                        $items[$row['parent1Slug']][$row['slug']][''] = $row['id'];
                        break;
                    case 3 :
                        $items[$row['parent2Slug']][$row['parent1Slug']][$row['slug']] = $row['id'];
                        break;
                }
            }

            $this->cache->save('forum-category-items', $items);
        }

        $this->_categortSlugItems = $items;

        return $items;
    }

    public function isCategoriesParamsReal($cat1 = '', $cat2 = '', $cat3 = '')
    {
        $categories = $this->getCategorySlugItems();

        if (!isset($categories[$cat1][''][''])) {
            return false;
        }

        if (!isset($categories[$cat1][$cat2][''])) {
            return false;
        }

        if (!isset($categories[$cat1][$cat2][$cat3])) {
            return false;
        }

        return true;
    }

    public function hasSubcategories($cat1 = '', $cat2 = '', $cat3 = '')
    {
        $categories = $this->getCategorySlugItems();

        if ($cat3 !== '') {
            return is_array($categories[$cat1][$cat2][$cat3]) && count($categories[$cat1][$cat2][$cat3]) > 1 ? true : false;
        }

        if ($cat2 !== '') {
            return is_array($categories[$cat1][$cat2]) && count($categories[$cat1][$cat2]) > 1 ? true : false;
        }

        if ($cat1 !== '') {
            return is_array($categories[$cat1]) && count($categories[$cat1]) > 1 ? true : false;
        }

        return true;
    }

    public function getCategoryFilterCriteriaForPosts($cat1 = '', $cat2 = '', $cat3 = '')
    {
        $result = [
            'name' => null,
            'value' => null
        ];

        if ($cat1 == '' && $cat2 == '' && $cat3 == '') {
            return $result;
        }

        $categories = $this->getCategorySlugItems();

        if ($cat1 !== '') {
            $result = [
                'name' => 'categoryLvl1',
                'value' => $categories[$cat1]['']['']
            ];
        }

        if ($cat2 !== '') {
            $result = [
                'name' => 'categoryLvl2',
                'value' => $categories[$cat1][$cat2]['']
            ];
        }

        if ($cat3 !== '') {
            $result = [
                'name' => 'categoryLvl3',
                'value' => $categories[$cat1][$cat2][$cat3]
            ];
        }

        return $result;
    }

    /**
     * Get ForumCategory entity reposotory
     * 
     * @access public
     * @return \Forum\Repository\ForumCategoryRepository
     */
    public function getForumCategoryRepo()
    {
        if ($this->_categoryRepo === null || !$this->_categoryRepo) {
            $this->_categoryRepo = $this->getEntityRepository(ForumCategory::class);
        }

        return $this->_categoryRepo;
    }

    public function getCategoryBySlugFromLevel($slug = '', $parent = null, $level = 1)
    {
        return $this->getForumCategoryRepo()->findObjectBySlugAndParentFromLevel(
            $slug, (is_object($parent) ? $parent->getId() : 0), $level
        );
    }

    //--------------------------------------------------------------------------

    public function getListItems($cat1 = '', $cat2 = '', $cat3 = '')
    {
        if ($cat3 !== '') {
            $parentSlug = $cat3;
            $preParentSlug = $cat2;
            $deep = 1;
        } elseif ($cat2 !== '') {
            $parentSlug = $cat2;
            $preParentSlug = $cat1;
            $deep = 3;
        } elseif ($cat1 !== '') {
            $parentSlug = $cat1;
            $preParentSlug = null;
            $deep = 3;
        } else {
            $parentSlug = null;
            $preParentSlug = null;
            $deep = 3;
        }

        $items = [];

        $rows = $this->getForumCategoryRepo()->getCategoriesListBySlug($parentSlug, $preParentSlug);
        foreach ($rows as $row) {
            $reply = [
                //'topicSlug' => $row['topicSlug'],
                //'topicTitle' => $row['topicTitle'],
                //'topicId' => $row['topicId'],
                'topicUrl' => '/' . $row['slug'] . '/' . $row['topicSlug'] . ':' . $row['topicId'] . '/asc',
                'lastReplyByUsername' => $row['lastReplyByUsername'],
                'lastReplyAt' => $row['lastReplyAt'],
                'lastReply' => $row['lastReply'],
            ];

            $items[$row['id']] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'commentsCount' => $row['commentsCount'],
                'viewsCount' => $row['viewsCount'],
                'topicsCount' => $row['topicsCount'],
                'description' => $row['content'] === null ? '' : $row['content'],
                'childs' => [],
                'lastReply' => [
                    $row['topicId'] => $reply
                ],
            ];

            //
            if ($deep > 1) {
                $rows2 = $this->getForumCategoryRepo()->getCategoriesListByParentId($row['id']);
                foreach ($rows2 as $row2) {
                    $reply = [
                        //'topicSlug' => $row2['topicSlug'],
                        //'topicTitle' => $row2['topicTitle'],
                        //'topicId' => $row2['topicId'],
                        'topicUrl' => '/' . $row['slug'] . '/' . $row2['slug'] . '/' . $row2['topicSlug'] . ':' . $row2['topicId'] . '/asc',
                        'lastReplyByUsername' => $row2['lastReplyByUsername'],
                        'lastReplyAt' => $row2['lastReplyAt'],
                        'lastReply' => $row2['lastReply'],
                    ];
                    
                    $items[$row['id']]['childs'][$row2['id']] = [
                        'id' => $row2['id'],
                        'title' => $row2['title'],
                        'slug' => $row2['slug'],
                        'commentsCount' => $row2['commentsCount'],
                        'topicsCount' => $row2['topicsCount'],
                        'viewsCount' => $row2['viewsCount'],
                        'description' => $row2['content'] === null ? '' : $row2['content'],
                        'childs' => [],
                        'lastReply' => [
                            $row2['topicId'] => $reply,
                        ],
                    ];
                    
                    if (isset($items[$row['id']]['lastReply'][$row2['topicId']])) {
                        $items[$row['id']]['lastReply'][$row2['topicId']] = $reply;
                    }
                    //
                    if ($deep > 2) {
                        $rows3 = $this->getForumCategoryRepo()->getCategoriesListByParentId($row2['id']);
                        foreach ($rows3 as $row3) {
                            $reply = [
                                //'topicSlug' => $row3['topicSlug'],
                                //'topicTitle' => $row3['topicTitle'],
                                //'topicId' => $row3['topicId'],
                                'topicUrl' => '/' . $row['slug'] . '/' . $row2['slug'] . '/' . $row3['slug'] . '/' . $row3['topicSlug'] . ':' . $row3['topicId'] . '/asc',
                                'lastReplyByUsername' => $row3['lastReplyByUsername'],
                                'lastReplyAt' => $row3['lastReplyAt'],
                                'lastReply' => $row3['lastReply'],
                            ];

                            $items[$row['id']]['childs'][$row2['id']]['childs'][$row3['id']] = [
                                'id' => $row3['id'],
                                'title' => $row3['title'],
                                'slug' => $row3['slug'],
                                'commentsCount' => $row3['commentsCount'],
                                'topicsCount' => $row3['topicsCount'],
                                'viewsCount' => $row3['viewsCount'],
                                'description' => $row3['content'] === null ? '' : $row3['content'],
                                'childs' => [],
                                'lastReply' => [
                                    $row3['topicId'] => $reply,
                                ],
                            ];

                            if (isset($items[$row['id']]['lastReply'][$row3['topicId']])) {
                                $items[$row['id']]['lastReply'][$row3['topicId']] = $reply;
                            }

                            if (isset($items[$row['id']]['childs'][$row2['id']]['lastReply'][$row3['topicId']])) {
                                $items[$row['id']]['childs'][$row2['id']]['lastReply'][$row3['topicId']] = $reply;
                            }
                        }
                    }
                }
            }
        }

        return $items;
    }

}
