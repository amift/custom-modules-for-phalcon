<?php

namespace Statistics\Service;

use Core\Library\AbstractLibrary;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportLeagueGroup;
use Statistics\Entity\SportSeason;
use Statistics\Entity\SportStatsOverall;
use Statistics\Entity\SportType;
use Statistics\Tool\StatsField;
use Translations\Tool\Group;

class StatsService extends AbstractLibrary
{

    /**
     * @var \Statistics\Repository\SportLeagueRepository
     */
    protected $_leagueRepo;

    /**
     * @var \Statistics\Repository\SportLeagueGroupRepository
     */
    protected $_leagueGroupRepo;

    /**
     * @var \Statistics\Repository\SportSeasonRepository
     */
    protected $_seasonRepo;

    /**
     * @var \Statistics\Repository\SportStatsOverallRepository
     */
    protected $_statsOverallRepo;

    /**
     * @var \Statistics\Repository\SportTypeRepository
     */
    protected $_typeRepo;

    public function getDefaultStatsParam($sportTypeIds = [], $sportLeagueIds = [])
    {
        $statsParams = '';
        $seasons = $this->getSeasonRepo()->getAllActualQuery($sportTypeIds, $sportLeagueIds)->getResult();
        foreach ($seasons as $season) {
            /* @var $season SportSeason */

            $type = $season->getSportType();
            /* @var $type SportType */

            $league = $season->getSportLeague();
            /* @var $league SportLeague */

            $statsParams = sprintf('%s-%s-%s', $type->getId(), $league->getId(), $season->getId());//{typeId:[0-9]+}-{leagueId:[0-9]+}-{seasonId:[0-9]+}

            break;
        }

        return $statsParams;
    }

    /**
     * Get sidebar stats data
     * 
     * @access public
     * @param array $sportTypeIds
     * @param array $sportLeagueIds
     * @return array
     */
    public function getSidebarData($sportTypeIds = [], $sportLeagueIds = [])
    {
        $selected = true;

        $data = [
            'filters' => [],
            'selected_stats_ids' => ''
        ];

        $seasons = $this->getSeasonRepo()->getAllActualQuery($sportTypeIds, $sportLeagueIds)->getResult();
        foreach ($seasons as $season) {
            /* @var $season SportSeason */

            $type = $season->getSportType();
            /* @var $type SportType */

            $league = $season->getSportLeague();
            /* @var $league SportLeague */

            $statsParams = sprintf('%s-%s-%s', $type->getId(), $league->getId(), $season->getId());

            $typeData = [
                'id' => $type->getId(),
                'title' => $type->getTitle(),
                'key' => $type->getKey(),
                'selected' => $selected,
                'default_stats' => $statsParams,
                'leagues' => [],
            ];

            if (!isset($data['filters'][$type->getId()])) {
                $data['filters'][$type->getId()] = $typeData;
            }

            $data['filters'][$type->getId()]['leagues'][$league->getId()] = [
                'leagueId' => $league->getId(),
                'leagueTitle' => $league->getTitle(),
                'seasonId' => $season->getId(),
                'seasonTitle' => $season->getTitle(),
                'selected' => $selected,
                'default_stats' => $statsParams,
            ];

            if ($selected) {
                $data['selected_stats_ids'] = $statsParams;
            }

            $selected = false;
        }

        return $data;
    }

    public function checkStatsMainParamsAndSidebarTableHtml($params = '', $throwException = true)
    {
        list($typeId, $leagueId, $seasonId) = explode('-', $params, 3);

        $sportType          = $this->getTypeRepo()->findObjectById($typeId);
        $sportLeague        = $this->getLeagueRepo()->findObjectById($leagueId);
        $season             = $this->getSeasonRepo()->findObjectById($seasonId);

        if (
            !is_object($sportType) || !is_object($sportLeague) || !is_object($season)
        ) {
            if ($throwException) {
                throw new \Exception('error_stats_invalid_params');
            }
            return '';
        }

        $sportLeagueGroupIds = $this->getStatsActualLeagueGroupsIds($sportType, $sportLeague, $season);

        if (count($sportLeagueGroupIds) < 1) {
            if ($throwException) {
                throw new \Exception('error_stats_has_no_league_groups');
            }
            return '';
        }

        $scoreMethod = 'get' . ucfirst($sportType->getSoreField());

        $hasMatchesColumn = true;
        $htmlRows = '';
        foreach ($sportLeagueGroupIds as $sportLeagueGroupId) {
            $sportLeagueGroup = $this->getLeagueGroupRepo()->findObjectById($sportLeagueGroupId);
            if (is_object($sportLeagueGroup)) {
                $table = $this->getStatsOverallRepo()->getOverAllStatsTable(
                    $sportType->getId(),
                    $sportLeague->getId(),
                    $sportLeagueGroupId,
                    $season->getId()
                );
                $totalRows = count($table);
                if ($totalRows > 0) {
                    foreach ($table as $row) {
                        if ($row->getMatches() === null || (string)$row->getMatches() === '') {
                            $hasMatchesColumn = false;
                        }
                        break;
                    }
                    $htmlRows.= '<tr class="conference"><td colspan="'.($hasMatchesColumn ? '4' : '3').'">'.$sportLeagueGroup->getTitle().'</td></tr>';
                    $counter = 1;
                    foreach ($table as $row) {
                        $team = $row->getSportTeam();
                        /* @var $team SportTeam */

                        $rowClass = [];
                        if ($counter === 1) {
                            $rowClass[] = 'first';
                        } elseif ($counter === $totalRows) {
                            $rowClass[] = 'last';
                        }

                        // class="my-club"

                        if ($hasMatchesColumn) {
                            $htmlRows.= sprintf(
                                '<tr%s><td class="position">%s</td><td>%s</td><td class="position">%s</td><td class="points">%s</td></tr>', 
                                count($rowClass) > 0 ? ' class="' . implode(' ', $rowClass) . '"' : '',
                                $row->getPlace(), 
                                $team->getTitle() . ((string)$team->getCountry() !== '' ? ', ' . $team->getCountry() : ''), 
                                $row->getMatches(), 
                                $row->{$scoreMethod}()
                            );
                        } else {
                            $title = $team->getTitle();
                            if ((string)$team->getCountry() !== '') {
                                if (strlen((string)$team->getCountry()) === 2) {
                                    $title .= ' (' . strtoupper($team->getCountry()) . ')';
                                } else {
                                    $title .= ', ' . $team->getCountry();
                                }
                            }
                            $htmlRows.= sprintf(
                                '<tr%s><td class="position">%s</td><td>%s</td><td class="points">%s</td></tr>', 
                                count($rowClass) > 0 ? ' class="' . implode(' ', $rowClass) . '"' : '',
                                $row->getPlace(), 
                                $title, 
                                $row->{$scoreMethod}()
                            );
                        }

                        $counter++;
                    }
                }
            }
        }

        $category = $sportType->getArticleCategoryLvl1()->getSlug();
        $subcategory = null;
        if ($sportLeague->getArticleCategoryLvl2() !== null) {
            $subcategory = $sportLeague->getArticleCategoryLvl2()->getSlug();
        }
        //$urlFullStats = $this->config->web_url . $this->url->get(['for' => 'stats_full_by_article_subcategory', 'category' => $category, 'subcategory' => $subcategory]);
        $urlFullStats = $this->config->web_url . $this->url->get(['for' => 'stats_full_table', 'seasonkey' => $params]);
        $urlFullStatsLabel = $this->translator->trans('stats_link_full_table_label', 'Pilna tabula', Group::STATISTICS);


        $html = '
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="position"></th>';
        if ($hasMatchesColumn) {
            $html.= '
                            <th>'.$this->translator->trans('stats_field_team', 'Komanda', Group::STATISTICS).'</th>
                            <th class="position">'.$this->translator->trans('stats_field_matches_short', 'S', Group::STATISTICS).'</th>';
        } else {
            $html.= '
                            <th>'.$this->translator->trans('stats_field_athlete', 'Atlēts', Group::STATISTICS).'</th>';
        }
        $html.= '
                            <th class="points">'.$this->translator->trans('stats_field_score_short', 'P', Group::STATISTICS).'</th>
                        </tr>
                    </thead>
                    <tbody>';
        $html.= $htmlRows;
        $html.= '
                        <tr>
                            <td class="full-table" colspan="'.($hasMatchesColumn ? '4' : '3').'">
                                <a href="'.$urlFullStats.'" title="'.$urlFullStatsLabel.'">'.$urlFullStatsLabel.'</a>
                            </td>
                        </tr>
                    </tbody>
                </table>';

        return $html;
    }

    public function getStatsActualLeagueGroupsIds($sportType, $sportLeague, $season)
    {
        $sportLeagueGroupIds = $this->getStatsOverallRepo()->getStatsActualLeagueGroupsIds($sportType, $sportLeague, $season);

        return $sportLeagueGroupIds;
    }

    /**
     * Get sport types IDS by choosed article category data
     * 
     * @access public
     * @param null|string $articleCategoryLvl1Id
     * @return array
     */
    public function getSportTypeIdsByArticleCategory($articleCategoryLvl1Id = null)
    {
        $ids = [];

        $rows = $this->getTypeRepo()->findAllByArticleCategoryId($articleCategoryLvl1Id);
        foreach ($rows as $row) {
            $ids[$row->getId()] = $row->getId();
        }

        return $ids;
    }

    /**
     * Get sport leagues IDS by choosed article category data
     * 
     * @access public
     * @param null|string $articleCategoryLvl2Id
     * @return array
     */
    public function getSportLeagueIdsByArticleCategory($articleCategoryLvl2Id = null)
    {
        $ids = [];

        $rows = $this->getLeagueRepo()->findAllByArticleCategoryId($articleCategoryLvl2Id);
        foreach ($rows as $row) {
            $ids[$row->getId()] = $row->getId();
        }

        return $ids;
    }

    /**
     * Get SportLeague entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportLeagueRepository
     */
    protected function getLeagueRepo()
    {
        if ($this->_leagueRepo === null || !$this->_leagueRepo) {
            $this->_leagueRepo = $this->getEntityRepository(SportLeague::class);
        }

        return $this->_leagueRepo;
    }

    /**
     * Get SportLeagueGroup entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportLeagueGroupRepository
     */
    protected function getLeagueGroupRepo()
    {
        if ($this->_leagueGroupRepo === null || !$this->_leagueGroupRepo) {
            $this->_leagueGroupRepo = $this->getEntityRepository(SportLeagueGroup::class);
        }

        return $this->_leagueGroupRepo;
    }

    /**
     * Get SportSeason entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportSeasonRepository
     */
    protected function getSeasonRepo()
    {
        if ($this->_seasonRepo === null || !$this->_seasonRepo) {
            $this->_seasonRepo = $this->getEntityRepository(SportSeason::class);
        }

        return $this->_seasonRepo;
    }

    /**
     * Get SportStatsOverall entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportStatsOverallRepository
     */
    protected function getStatsOverallRepo()
    {
        if ($this->_statsOverallRepo === null || !$this->_statsOverallRepo) {
            $this->_statsOverallRepo = $this->getEntityRepository(SportStatsOverall::class);
        }

        return $this->_statsOverallRepo;
    }

    /**
     * Get SportType entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportTypeRepository
     */
    protected function getTypeRepo()
    {
        if ($this->_typeRepo === null || !$this->_typeRepo) {
            $this->_typeRepo = $this->getEntityRepository(SportType::class);
        }

        return $this->_typeRepo;
    }

}
