<?php

namespace System\Controller\Backend;

use Common\Controller\AbstractBackendController;

class CacheController extends AbstractBackendController
{

    /**
     * Cached data list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $rows = [];

        $prefix = $this->config->backend_cache->prefix;
        $keyPrefix = '';//'document-';'menu-';'category-';
        $keys = $this->cache->queryKeys($prefix . $keyPrefix);
        foreach ($keys as $key) {
            $keyWoPrefix = str_replace($prefix, '', $key);
            $keyWoKeyPrefix = str_replace($prefix . $keyPrefix, '', $key);
            $rows[] = [
                'key' => $keyWoPrefix,
                'real_key' => $key,
                'data' => $this->cache->get($keyWoPrefix)
            ];
        }

        $this->view->setVars(compact('rows'));
    }

    /**
     * Delete cached data by KEY
     * 
     * @access public
     * @return void
     */
    public function deleteAction()
    {
        $key = $this->dispatcher->getParam('key');
        $this->cache->delete($key);

        return $this->response->redirect($this->url->get(['for' => 'cache_list']));
    }

}