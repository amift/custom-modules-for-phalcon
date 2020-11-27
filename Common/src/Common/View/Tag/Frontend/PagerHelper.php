<?php

namespace Common\View\Tag\Frontend;

use Phalcon\Mvc\View\Simple as SimpleView;

class PagerHelper
{

    public function links($fullUrl, $paginator, $currentPageNumber = 1, $numItemsPerPage = 20, $range = 7, $template = 'partials/pagination')
    {
        $view = new SimpleView();
        $view->setViewsDir(ROOT_PATH . str_replace('/', DS, '/module/Application/view/' . APP_TYPE . '/templates/'));

        return $view->render(
            $template, 
            $this->calculatePagionationParameters(
                $fullUrl, 
                is_object($paginator) ? $paginator->count() : count($paginator), 
                $currentPageNumber, 
                $numItemsPerPage, 
                $range
            )
        );
    }

    protected function calculatePagionationParameters($fullUrl, $totalCount = 0, $currentPageNumber = 1, $numItemsPerPage = 20, $range = 7)
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
            'fullUrl'           => $fullUrl,
            'parameterNames'    => [ 'currentPage' => 'page' ],
            'totalCount'        => $totalCount,
            'pagesInRange'      => $pagesInRange,
            'currentPageNumber' => $currentPageNumber,
            'pageCount'         => $pageCount,
            'intervalStart'     => $intervalStart,
            'intervalEnd'       => $intervalEnd
        ];
    }

}
