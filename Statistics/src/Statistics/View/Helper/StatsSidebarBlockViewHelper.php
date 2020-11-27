<?php

namespace Statistics\View\Helper;

use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\View\Simple as SimpleView;

class StatsSidebarBlockViewHelper extends Component
{

    /**
     * Get SimpleView object for view rendering.
     * 
     * @access protected
     * @return SimpleView
     */
    protected function getView()
    {
        $view = new SimpleView();
        $view->setViewsDir(ROOT_PATH . str_replace('/', DS, '/module/Statistics/view/' . APP_TYPE . '/sidebar/'));

        return $view;
    }

    /**
     * Render sidebar stats table
     * 
     * @access public
     * @param null|string $categoryFieldName
     * @param null|string $categoryFieldValue
     * @return string
     */
    public function renderSideBarStatsTable($categoryFieldName = null, $categoryFieldValue = null)
    {
        $load = false;
        $sportTypeIds = [];
        $sportLeagueIds = [];
        $hideFilters = false;

        // If start page
        if ($categoryFieldName === null && $categoryFieldValue === null) {
            $load = true;
        }
        // Else means category choosed
        else {
            if ((int)$categoryFieldValue > 0) {
                switch ($categoryFieldName) {
                    case 'categoryLvl1' :
                        $sportTypeIds = $this->statisticsService->getSportTypeIdsByArticleCategory($categoryFieldValue);
                        if (count($sportTypeIds) > 0) {
                            $load = true;
                        }
                        break;
                    case 'categoryLvl2' :
                        $sportLeagueIds = $this->statisticsService->getSportLeagueIdsByArticleCategory($categoryFieldValue);
                        if (count($sportLeagueIds) > 0) {
                            $load = true;
                            $hideFilters = true;
                        }
                        break;
                }
            }
        }

        // Get HTML
        if ($load) {
            $data = $this->statisticsService->getSidebarData($sportTypeIds, $sportLeagueIds);
            if (count($data['filters']) > 0 && $data['selected_stats_ids'] !== '') {
                return $this->getView()->render('sidebar', compact('data', 'hideFilters'));
            }
        }

        return '';
    }

    public function getSidebarStatsTableHtml($sportLeagueGroup, $statsRows)
    {
        return $this->getView()->render('sidebar-league-group-table', compact('sportLeagueGroup', 'statsRows'));
    }

}
