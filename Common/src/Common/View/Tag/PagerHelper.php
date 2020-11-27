<?php

namespace Common\View\Tag;

use Common\Tool\Filters;
use Phalcon\Mvc\View\Simple as SimpleView;

class PagerHelper
{

    public function links($paginator, $filters = null, $currentPageNumber = 1, $numItemsPerPage = 20, $range = 7)
    {
        $view = new SimpleView();
        $view->setViewsDir(ROOT_PATH . str_replace('/', DS, '/module/Application/view/' . APP_TYPE . '/templates/'));

        return $view->render(
            'partials/pagination', 
            $this->calculatePagionationParameters(
                $filters, 
                is_object($paginator) ? $paginator->count() : count($paginator), 
                $currentPageNumber, 
                $numItemsPerPage, 
                $range
            )
        );
    }

    public function getUrlQuery($activeParameters = [], $pageParameter = [])
    {
        $query = array_merge($activeParameters, $pageParameter);

        return http_build_query($query);
    }

    protected function calculatePagionationParameters($filters = null, $totalCount = 0, $currentPageNumber = 1, $numItemsPerPage = 20, $range = 7)
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
            'parameterNames'    => [ 'currentPage' => 'page' ],
            'activeParameters'  => $this->getFilterParameters($filters),
            'totalCount'        => $totalCount,
            'pagesInRange'      => $pagesInRange,
            'currentPageNumber' => $currentPageNumber,
            'pageCount'         => $pageCount,
            'intervalStart'     => $intervalStart,
            'intervalEnd'       => $intervalEnd
        ];
    }

    protected function getFilterParameters($filters = null)
    {
        $filterArray = [];

        if ($filters instanceof Filters) {
            foreach ($filters as $key => $value) {
                $filterArray[$key] = $value;
            }
        } else {
            $filterArray = $filters;
        }

        return $filterArray;
    }

}
