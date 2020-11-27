<?php

namespace Statistics\Tasks;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportLeagueGroup;
use Statistics\Entity\SportSeason;
use Statistics\Entity\SportStatsDaily;
use Statistics\Entity\SportStatsMonthly;
use Statistics\Entity\SportStatsOverall;
use Statistics\Entity\SportTeam;
use Statistics\Entity\SportType;
use System\Entity\CronJob;

class UpdateTask extends \Phalcon\Cli\Task
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
     * @var \Statistics\Repository\SportStatsDailyRepository
     */
    protected $_statsDailyRepo;

    /**
     * @var \Statistics\Repository\SportStatsMonthlyRepository
     */
    protected $_statsMonthlyRepo;

    /**
     * @var \Statistics\Repository\SportStatsOverallRepository
     */
    protected $_statsOverallRepo;

    /**
     * @var \Statistics\Repository\SportTeamRepository
     */
    protected $_teamRepo;

    /**
     * @var \Statistics\Repository\SportTypeRepository
     */
    protected $_typeRepo;

    /**
     * @var string
     */
    protected $_stackTrace = '';

    public function allActualSeasonsAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        $cronAction = 'statistics update allActualSeasons';
        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->startRunning();
            $this->getEntityManager()->flush($cronjob);
        }

        $this->_stackTrace = '';
        $success = true;
        $errorMsg = '';

        try {
            $this->_stackTrace.= sprintf('Started to update all active seasons') . PHP_EOL;

            $total = 0;

            $rows = $this->getSeasonRepo()->getAllActualQuery()->iterate();
            foreach ($rows as $row) {
                $season = $row[0];
                /* @var $season SportSeason */

                $apiUrl = trim((string)$season->getImportApiUrl());

                $this->_stackTrace.= sprintf('-----------------------') . PHP_EOL;
                $this->_stackTrace.= sprintf('Processing season nr. %d [id: %d, title: %s, apiUrl: %s]', ++$total, $season->getId(), (string)$season, $apiUrl) . PHP_EOL;
                if ($apiUrl !== '') {
                    $updated = $this->processSeasonUpdate($season, $apiUrl);
                    $this->getEntityManager()->clear();
                    $this->_stackTrace.= sprintf($updated ? 'Updated!' : 'Season stats was not updated!') . PHP_EOL;
                } else {
                    $this->_stackTrace.= sprintf('Api url is empty, so nothing to update') . PHP_EOL;
                }
            }

            $this->_stackTrace.= sprintf('-----------------------') . PHP_EOL;
            $this->_stackTrace.= sprintf('Total seasons processed: %d', $total) . PHP_EOL;
            $this->_stackTrace.= sprintf('Finished') . PHP_EOL;

        } catch (ORMInvalidArgumentException $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        } catch (\Exception $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        }

        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->stopRunning($this->_stackTrace, $success, $errorMsg);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Process season stats updating.
     * 
     * @access protected
     * @param SportSeason $season
     * @param string $apiUrl
     * @return boolean
     */
    protected function processSeasonUpdate($season, $apiUrl = '')
    {
        if ($apiUrl === '') {
            $apiUrl = trim((string)$season->getImportApiUrl());
        }

        $apiData = $this->standartCurlGet($apiUrl);
        $data = $this->parseApiData($apiData);

        if (!array_key_exists('stats', $data) || count($data['stats']) < 1) {
            $this->_stackTrace.= sprintf('Parsed stats data is empty!') . PHP_EOL;
            return false;
        }

        // Set import data actuality date
        if (is_object($data['last_update_at'])) {
            $season->setImportApiActualDate($data['last_update_at']);
        }

        // Get sport type and league from season
        $sportType   = $season->getSportType();
        $sportLeague = $season->getSportLeague();

        // Process stats
        foreach ($data['stats'] as $groupKey => $table) {

            // Get sport league group if not exists will create
            $sportLeagueGroup = $this->getSportLeagueGroupObject($sportLeague, $groupKey);
            //echo '-'.$sportLeagueGroup->getTitle() . PHP_EOL;

            // Save new data
            foreach ($table as $ordering => $row) {
                // Get sport team, if not exists will create
                $teamFlag = isset($row['flag']) ? trim((string)$row['flag']) : '';
                $teamCountry = isset($row['team_country']) && trim((string)$row['team_country']) !== '' ? trim((string)$row['team_country']) : null;
                $teamAdditionalInfo = isset($row['team_additional_info']) && trim((string)$row['team_additional_info']) !== '' ? trim((string)$row['team_additional_info']) : null;

                //
                $sportTeam = $this->getSportTeamObject($sportType, $sportLeague, trim((string)$row['team_name']), $teamFlag, $teamCountry, $teamAdditionalInfo);
                //echo '--'.$sportTeam->getTitle() . PHP_EOL;

                // Update overall
                $this->updateStatsOverAll($sportType, $sportLeague, $sportLeagueGroup, $season, $sportTeam, $ordering, $row);

                // @TODO Update daily

                // @TODO Update monthly
            }
        }

        $season->setLastImportFromApiAt(new \DateTime('now'));
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * Get data from api resource
     * 
     * @access protected
     * @param string $url
     * @return array
     */
    protected function standartCurlGet($url = '')
    {
        // Initialize a cURL session, set options and get response
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,/*
            CURLOPT_USERAGENT => 'AllSports.LV HTTP (Curl)',
            CURLOPT_CUSTOMREQUEST => 'GET',*/
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);
        $response = curl_exec($ch);

        // If no response check for error otherwise decode response
        if ($response === FALSE){
            $error = trim((string)curl_error($ch));
            $error = $error !== '' ? $error : 'Unknown error';
            $data = [
                'errors' => [
                    [ 'message' => 'CURL ERROR: ' . $error ]
                ]
            ];
        } elseif ($response === '') {
            $data = [
                'errors' => [
                    [ 'message' => 'NO DATA IN RESPONSE' ]
                ]
            ];
        } else {
            $data = json_decode($response, true);
        }

        return $data;
    }

    /**
     * Parse api response data to needed structure
     * 
     * @access protected
     * @param array $apiData
     * @param string $keyOverallGroup
     * @param string $apiDateFormat
     * @param string $apiSortKey
     * @return array
     */
    protected function parseApiData($apiData = [], $keyOverallGroup = 'overall', $apiDateFormat = 'Y-m-d H:i:s', $apiSortKey = 'place')
    {
        $result = [
            'league' => null,
            'last_update_at' => null,
            'stats' => []
        ];

        // Simple check for needed keys
        if (!array_key_exists('league', $apiData) || !array_key_exists('updated', $apiData) || !array_key_exists('data', $apiData)) {
            return $result;
        }

        // Set league key
        $result['league'] = $apiData['league'];

        // Set updated date and time object
        $result['last_update_at'] = new \DateTime('now');
        if ((string)$apiData['updated'] !== '') {
            $result['last_update_at'] = \DateTime::createFromFormat($apiDateFormat, (string)$apiData['updated']);
        }

        // Parse stats data to needed structure
        foreach ($apiData['data'] as $data) {
            if (array_key_exists('name', $data) && array_key_exists('table', $data)) {
                $groupName = trim((string)$data['name']) !== '' ? trim((string)$data['name']) : $keyOverallGroup;
                $parsedTable = [];
                foreach ($data['table'] as $k => $row) {
                    if ($k > 0) {
                        $newRow = [];
                        foreach ($data['table'][0] as $i => $n) {
                            $newRow[$n] = $row[$i];
                        }
                        $parsedTable[] = $newRow;
                    }
                }
                $result['stats'][$groupName] = $parsedTable;//$this->sortArrayByKeyValue($parsedTable, $apiSortKey);
            }
        }

        return $result;
    }

    /**
     * Sort array by key value
     * 
     * @access protected
     * @param array $data
     * @param string $sortKey
     * @param string $sortFlags
     * @return array
     */
    protected function sortArrayByKeyValue($data, $sortKey, $sortFlags = SORT_ASC)
    {
        if (empty($data) || empty($sortKey)) {
            return $data;
        }

        $ordered = array();
        foreach ($data as $key => $value) {
            $ordered[$value[$sortKey]] = $value;
        }

        ksort($ordered, $sortFlags);

        return array_values($ordered);
    }

    protected function getSportLeagueGroupObject($sportLeague, $groupKey = '')
    {
        $sportLeagueGroup = $sportLeague->getLeagueGroupByKey($groupKey);

        if (!is_object($sportLeagueGroup)) {
            $sportLeagueGroup = new SportLeagueGroup();
            $sportLeagueGroup->setKey($groupKey);
            $sportLeagueGroup->setTitle(ucfirst($groupKey));
            $sportLeagueGroup->setSportLeague($sportLeague);
            $this->getEntityManager()->persist($sportLeagueGroup);
        }

        return $sportLeagueGroup;
    }

    protected function getSportTeamObject($sportType, $sportLeague, $key = '', $flagCode = '', $teamCountry = null, $teamAdditionalInfo = null)
    {
        $sportTeam = $this->getTeamRepo()->findObjectByMainParams($sportType->getId(), $sportLeague->getId(), $key);

        if (!is_object($sportTeam)) {
            $sportTeam = new SportTeam();
            $sportTeam->setKey($key);
            $sportTeam->setTitle(ucfirst($key));
            $sportTeam->setFlagCode($flagCode);
            $sportTeam->setSportType($sportType);
            $sportTeam->setSportLeague($sportLeague);
            if ($teamCountry !== null) {
                $sportTeam->setCountry($teamCountry);
            }
            if ($teamAdditionalInfo !== null) {
                $sportTeam->setAdditionalInfo($teamAdditionalInfo);
            }
            $this->getEntityManager()->persist($sportTeam);
        } else {
            if ($teamCountry !== null) {
                if ($sportTeam->getCountry() === null) {
                    $sportTeam->setCountry($teamCountry);
                }
            }
            if ($teamAdditionalInfo !== null) {
                if ($sportTeam->getAdditionalInfo() === null) {
                    $sportTeam->setAdditionalInfo($teamAdditionalInfo);
                }
            }
        }

        return $sportTeam;
    }

    protected function updateStatsOverAll($sportType, $sportLeague, $sportLeagueGroup, $season, $sportTeam, $ordering, $row = [])
    {
        // Search for existing row
        $overAll = $this->getStatsOverallRepo()->findObjectByMainParams(
            $sportType->getId(), 
            $sportLeague->getId(), 
            $sportLeagueGroup->getId(), 
            $season->getId(), 
            $sportTeam->getId()
        );

        // Create new row
        if (!is_object($overAll)) {
            $overAll = new SportStatsOverall();
            $overAll->setSportType($sportType);
            $overAll->setSportLeague($sportLeague);
            $overAll->setSportLeagueGroup($sportLeagueGroup);
            $overAll->setSportSeason($season);
            $overAll->setSportTeam($sportTeam);
            $this->getEntityManager()->persist($overAll);
        }

        // Set actual stats data
        $newValues = [];
        foreach ($row as $key => $value) {
            $property = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $newValues[lcfirst($property)] = $value;
        }
        $overAll->exchangeArray($newValues);
        $overAll->setOrdering($ordering);
    }

    /**
     * Get EntityManager instance
     * 
     * @access protected
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Get repository for an entity class.
     * 
     * @access protected
     * @param string $entityName Name of the entity
     * @return mixed Repository class
     */
    protected function getEntityRepository($entityName)
    {
        return $this->getEntityManager()->getRepository($entityName);
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
     * Get SportStatsDaily entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportStatsDailyRepository
     */
    protected function getStatsDailyRepo()
    {
        if ($this->_statsDailyRepo === null || !$this->_statsDailyRepo) {
            $this->_statsDailyRepo = $this->getEntityRepository(SportStatsDaily::class);
        }

        return $this->_statsDailyRepo;
    }

    /**
     * Get SportStatsMonthly entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportStatsMonthlyRepository
     */
    protected function getStatsMonthlyRepo()
    {
        if ($this->_statsMonthlyRepo === null || !$this->_statsMonthlyRepo) {
            $this->_statsMonthlyRepo = $this->getEntityRepository(SportStatsMonthly::class);
        }

        return $this->_statsMonthlyRepo;
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
     * Get SportTeam entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportTeamRepository
     */
    protected function getTeamRepo()
    {
        if ($this->_teamRepo === null || !$this->_teamRepo) {
            $this->_teamRepo = $this->getEntityRepository(SportTeam::class);
        }

        return $this->_teamRepo;
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
