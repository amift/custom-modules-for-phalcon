<?php

namespace Common\View\Tag\Frontend;

use Phalcon\Mvc\View\Simple as SimpleView;

class PagerAjaxHelper
{

    public function links($objectId, $routeName, $paginator, $currentPageNumber = 1, $numItemsPerPage = 10, $range = 7, $ordering = null)
    {
        $view = new SimpleView();
        $view->setViewsDir(ROOT_PATH . str_replace('/', DS, '/module/Application/view/' . APP_TYPE . '/templates/'));

        $urlParams = [
            'for' => $routeName, 
            'id' => $objectId, 
            'page' => 1
        ];
        if ($ordering !== null) {
            $urlParams['order'] = $ordering;
        }

        return $view->render(
            'partials/paginationAjax', 
            $this->calculatePagionationParameters(
                $urlParams,
                $paginator->count(), 
                $currentPageNumber, 
                $numItemsPerPage, 
                $range
            )
        );
    }

    protected function calculatePagionationParameters($urlParams, $totalCount = 0, $currentPageNumber = 1, $numItemsPerPage = 20, $range = 7)
    {
        $pageCount = intval(ceil($totalCount / $numItemsPerPage));

        if ($range > $pageCount) {
            $range = $pageCount;
        }

        $delta = ceil($range / 2);

        if ($totalCount == 0) {
            $pagesInRange = [];
        } elseif ($currentPageNumber - $delta > $pageCount - $range) {
            $pagesInRange = range($pageCount - $range + 1, $pageCount);
        } else {
            if ($currentPageNumber - $delta < 0) {
                $delta = $currentPageNumber;
            }
            $offset = $currentPageNumber - $delta;
            $pagesInRange = range($offset + 1, $offset + $range);
        }

        $intervalStart = (($currentPageNumber * $numItemsPerPage) - $numItemsPerPage) + 1;
        if ($totalCount == 0) {
            $intervalStart = 0;
        }

        $intervalEnd = $totalCount;
        if ($currentPageNumber * $numItemsPerPage <= $totalCount) {
            $intervalEnd = $currentPageNumber * $numItemsPerPage;
        }

        return [
            'urlParams'         => $urlParams,
            'totalCount'        => $totalCount,
            'pagesInRange'      => $pagesInRange,
            'currentPageNumber' => $currentPageNumber,
            'pageCount'         => $pageCount,
            'intervalStart'     => $intervalStart,
            'intervalEnd'       => $intervalEnd
        ];
    }

}
