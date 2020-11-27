<?php

namespace Statistics\Tasks;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use PHPHtmlParser\Dom as DomParser;
use Statistics\Entity\SportParserResult;
use Statistics\Tool\ParserCurl;

class ParseTask extends \Phalcon\Cli\Task
{

    /**
     * @var \Statistics\Repository\SportParserResultRepository
     */
    protected $_parserResultRepo;

    /**
     * Get SportParserResult entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportSeasonRepository
     */
    protected function getParserResultRepo()
    {
        if ($this->_parserResultRepo === null || !$this->_parserResultRepo) {
            $this->_parserResultRepo = $this->getEntityRepository(SportParserResult::class);
        }

        return $this->_parserResultRepo;
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
     * @throws \Core\Exception\RuntimeException
     */
    public function latvianHockeyChampRegularAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://lhf.lv/lv/subtournament/220', $curlOpt);
        $contents = $dom->find('div.teams-tab div.scroll-x');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;

            $dateCurrent = new \DateTime('now');

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_latvianHockeyChampRegular_original.html';
            file_put_contents($file, $html);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('expanded-cell') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_latvianHockeyChampRegular_cleaned.html';
            file_put_contents($file, $html);

            // Get array data
            $table = [];
            $domTable = new \DOMDocument(null, 'UTF-8');
            $res = @$domTable->loadHTML('<?xml encoding="UTF-8">' . $html);
            if ($res) {
                $rowsCount = 0;
                $goalsColumnIndex = null;
                $neededIndexedColumns = [];
                foreach ($domTable->getElementsByTagName('tr') as $tr) {
                    $cells = [];
                    $columnIndex = 0;
                    foreach ($tr->childNodes as $td) {
                        $columnIndex++;
                        $value = trim((string)$td->nodeValue);
                        // headig row replace values
                        if ($rowsCount === 0) {
                            if ($value !== '') {
                                switch ($value) {
                                    case 'Vieta' : $value = 'place'; break;
                                    case 'Komanda' : $value = 'team_name'; break;
                                    case 'Spēles' : $value = 'matches'; break;
                                    case 'Punkti' : $value = 'score'; break;
                                    case 'Uzvaras (pamatl.)' : $value = 'win'; break;
                                    case 'Uzvaras (PL/PSM)' : $value = 'win_ot'; break;
                                    case 'Zaudējumi (pamatl.)' : $value = 'lose'; break;
                                    case 'Zaudējumi (PL/PSM)' : $value = 'lose_ot'; break;
                                    case 'Vārti' : $value = 'goals'; $goalsColumnIndex = $columnIndex; break;
                                    default : $value = ''; break;
                                }
                                $neededIndexedColumns[$columnIndex] = $value !== '' ? true : false;
                            }
                        }
                        // if column neded add value
                        if (isset($neededIndexedColumns[$columnIndex]) && $neededIndexedColumns[$columnIndex] === true) {
                            if ($goalsColumnIndex === $columnIndex) {
                                if ($rowsCount === 0) {
                                    $cells[$columnIndex] = $value;
                                    $cells[$columnIndex+1] = 'miss';
                                    $cells[$columnIndex+2] = 'different';
                                } else {
                                    list($goals, $miss) = explode(':', $value, 2);
                                    $different = (int)$goals < (int)$miss ? '-' . ((int)$miss - (int)$goals) : '+' . ((int)$goals - (int)$miss);
                                    $cells[$columnIndex] = $goals;
                                    $cells[$columnIndex+1] = $miss;
                                    $cells[$columnIndex+2] = $different;
                                }
                            } else {
                                $cells[$columnIndex] = $value !== '' ? $value : '-';
                            }
                        }
                    }
                    // If table cells really found
                    if (count($cells) > 0) {
                        $table[] = $cells;
                    }
                    $rowsCount++;
                }
                $table = array_map('array_values', $table);
            } else {
                throw new \Core\Exception\RuntimeException('Error: can not parse table');
            }

            // 
            $resultLeague = 'latvijas-virsligas-hokeja-cempionats';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function optibetHockeyLeague2017RegularAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curl = new ParserCurl();
        $html = $curl->get('https://lhf.lv/lv/subtournament/245');

        $dom = new DomParser;
        $dom->load($html);
        $contents = $dom->find('div.teams-tab div.scroll-x');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;

            $dateCurrent = new \DateTime('now');

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_ohl_2017_Regular_original.html';
            file_put_contents($file, $html);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('expanded-cell') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_ohl_2017_regular_cleaned.html';
            file_put_contents($file, $html);

            // Get array data
            $table = [];
            $domTable = new \DOMDocument(null, 'UTF-8');
            $res = @$domTable->loadHTML('<?xml encoding="UTF-8">' . $html);
            if ($res) {
                $rowsCount = 0;
                $goalsColumnIndex = null;
                $neededIndexedColumns = [];
                foreach ($domTable->getElementsByTagName('tr') as $tr) {
                    $cells = [];
                    $columnIndex = 0;
                    foreach ($tr->childNodes as $td) {
                        $columnIndex++;
                        $value = trim((string)$td->nodeValue);
                        // headig row replace values
                        if ($rowsCount === 0) {
                            if ($value !== '') {
                                switch ($value) {
                                    case 'Vieta' : $value = 'place'; break;
                                    case 'Komanda' : $value = 'team_name'; break;
                                    case 'Spēles' : $value = 'matches'; break;
                                    case 'Punkti' : $value = 'score'; break;
                                    case 'Uzvaras (pamatl.)' : $value = 'win'; break;
                                    case 'Uzvaras (PL/PSM)' : $value = 'win_ot'; break;
                                    case 'Zaudējumi (pamatl.)' : $value = 'lose'; break;
                                    case 'Zaudējumi (PL/PSM)' : $value = 'lose_ot'; break;
                                    case 'Vārti' : $value = 'goals'; $goalsColumnIndex = $columnIndex; break;
                                    default : $value = ''; break;
                                }
                                $neededIndexedColumns[$columnIndex] = $value !== '' ? true : false;
                            }
                        }
                        // if column neded add value
                        if (isset($neededIndexedColumns[$columnIndex]) && $neededIndexedColumns[$columnIndex] === true) {
                            if ($goalsColumnIndex === $columnIndex) {
                                if ($rowsCount === 0) {
                                    $cells[$columnIndex] = $value;
                                    $cells[$columnIndex+1] = 'miss';
                                    $cells[$columnIndex+2] = 'different';
                                } else {
                                    list($goals, $miss) = explode(':', $value, 2);
                                    $different = (int)$goals < (int)$miss ? '-' . ((int)$miss - (int)$goals) : '+' . ((int)$goals - (int)$miss);
                                    $cells[$columnIndex] = $goals;
                                    $cells[$columnIndex+1] = $miss;
                                    $cells[$columnIndex+2] = $different;
                                }
                            } else {
                                $cells[$columnIndex] = $value !== '' ? $value : '-';
                            }
                        }
                    }
                    // If table cells really found
                    if (count($cells) > 0) {
                        $table[] = $cells;
                    }
                    $rowsCount++;
                }
                $table = array_map('array_values', $table);
            } else {
                throw new \Core\Exception\RuntimeException('Error: can not parse table');
            }

            // 
            $resultLeague = 'ohl-2017-2018';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function latvianFirstLeague2017RegularAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curl = new ParserCurl();
        $html = $curl->get('https://lhf.lv/lv/subtournament/254');

        $dom = new DomParser;
        $dom->load($html);
        $contents = $dom->find('div.teams-tab div.scroll-x');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;

            $dateCurrent = new \DateTime('now');

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_lv_first_league_2017_Regular_original.html';
            file_put_contents($file, $html);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('expanded-cell') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_lv_first_league_2017_regular_cleaned.html';
            file_put_contents($file, $html);

            // Get array data
            $table = [];
            $domTable = new \DOMDocument(null, 'UTF-8');
            $res = @$domTable->loadHTML('<?xml encoding="UTF-8">' . $html);
            if ($res) {
                $rowsCount = 0;
                $goalsColumnIndex = null;
                $neededIndexedColumns = [];
                foreach ($domTable->getElementsByTagName('tr') as $tr) {
                    $cells = [];
                    $columnIndex = 0;
                    foreach ($tr->childNodes as $td) {
                        $columnIndex++;
                        $value = trim((string)$td->nodeValue);
                        // headig row replace values
                        if ($rowsCount === 0) {
                            if ($value !== '') {
                                switch ($value) {
                                    case 'Vieta' : $value = 'place'; break;
                                    case 'Komanda' : $value = 'team_name'; break;
                                    case 'Spēles' : $value = 'matches'; break;
                                    case 'Punkti' : $value = 'score'; break;
                                    case 'Uzvaras (pamatl.)' : $value = 'win'; break;
                                    case 'Uzvaras (PL/PSM)' : $value = 'win_ot'; break;
                                    case 'Zaudējumi (pamatl.)' : $value = 'lose'; break;
                                    case 'Zaudējumi (PL/PSM)' : $value = 'lose_ot'; break;
                                    case 'Vārti' : $value = 'goals'; $goalsColumnIndex = $columnIndex; break;
                                    default : $value = ''; break;
                                }
                                $neededIndexedColumns[$columnIndex] = $value !== '' ? true : false;
                            }
                        }
                        // if column neded add value
                        if (isset($neededIndexedColumns[$columnIndex]) && $neededIndexedColumns[$columnIndex] === true) {
                            if ($goalsColumnIndex === $columnIndex) {
                                if ($rowsCount === 0) {
                                    $cells[$columnIndex] = $value;
                                    $cells[$columnIndex+1] = 'miss';
                                    $cells[$columnIndex+2] = 'different';
                                } else {
                                    list($goals, $miss) = explode(':', $value, 2);
                                    $different = (int)$goals < (int)$miss ? '-' . ((int)$miss - (int)$goals) : '+' . ((int)$goals - (int)$miss);
                                    $cells[$columnIndex] = $goals;
                                    $cells[$columnIndex+1] = $miss;
                                    $cells[$columnIndex+2] = $different;
                                }
                            } else {
                                $cells[$columnIndex] = $value !== '' ? $value : '-';
                            }
                        }
                    }
                    // If table cells really found
                    if (count($cells) > 0) {
                        $table[] = $cells;
                    }
                    $rowsCount++;
                }
                $table = array_map('array_values', $table);
            } else {
                throw new \Core\Exception\RuntimeException('Error: can not parse table');
            }

            // 
            $resultLeague = 'latvian-1st-league-2017-2018';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function skeleton2016MansWorldCupAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://www.ibsf.org/en/standings/rankings?season=2016&ctype=5&session=WC', $curlOpt);
        
        $html = $dom->innerHtml;

        $dateCurrent = new \DateTime('now');

        // Write the contents back to the file
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_skeleton_2016MansWorldCup_original.html';
        file_put_contents($file, $html);
        
        // Find table content
        $contents = $dom->find('div.table-responsive');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;
            unset($dom);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hidden-xs') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_skeleton_2016MansWorldCup_cleaned.html';
            file_put_contents($file, $html);

            // 
            $table = [];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $cells = [];
                if ($rowsCount < 1) {
                    $cells = [
                        'place',
                        'team_name',
                        'team_country',
                        'score',
                    ];
                } else {
                    $rank = trim($tr->find('td.rank')[0]->innerHtml);
                    $img = $tr->find('td.athlete img')[0];
                    $country = is_object($img) ? trim($img->getAttribute('alt')) : null;
                    $athlete = trim(strip_tags(str_ireplace('(Junior)', '', $tr->find('td.athlete a')[0]->innerHtml)));
                    $points = trim($tr->find('td.points')[0]->innerHtml);
                    /*var_dump($rank);
                    echo PHP_EOL;
                    var_dump($athlete);
                    echo PHP_EOL;
                    var_dump($country);
                    echo PHP_EOL;
                    var_dump($points);
                    echo PHP_EOL.'------------------'.PHP_EOL;*/
                    $cells = [
                        $rank,
                        $athlete,
                        $country,
                        $points,
                    ];
                }
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }
            
            /*// Get array data
            $table = [];
            $domTable = new \DOMDocument(null, 'UTF-8');
            $res = @$domTable->loadHTML('<?xml encoding="UTF-8">' . $html);
            if ($res) {
                $rowsCount = 0;
                $neededIndexedColumns = [];
                foreach ($domTable->getElementsByTagName('tr') as $tr) {
                    $cells = [];
                    $columnIndex = 0;
                    foreach ($tr->childNodes as $td) {
                        $columnIndex++;
                        $value = trim((string)$td->nodeValue);
                        // headig row replace values
                        if ($rowsCount === 0) {
                            if ($value !== '') {
                                switch ($value) {
                                    case 'Rank' : $value = 'place'; break;
                                    case 'Athlete' : $value = 'team_name'; break;
                                    //case 'Spēles' : $value = 'matches'; break;
                                    case 'Points' : $value = 'score'; break;
                                    default : $value = ''; break;
                                }
                                $neededIndexedColumns[$columnIndex] = $value !== '' ? true : false;
                            }
                        }
                        // if column neded add value
                        if (isset($neededIndexedColumns[$columnIndex]) && $neededIndexedColumns[$columnIndex] === true) {
                            $cells[$columnIndex] = $value !== '' ? $value : '-';
                        }
                    }
                    // If table cells really found
                    if (count($cells) > 0) {
                        $table[] = $cells;
                    }
                    $rowsCount++;
                }
                $table = array_map('array_values', $table);
            } else {
                throw new \Core\Exception\RuntimeException('Error: can not parse table');
            }*/

            $resultLeague = 'ibsf-2016-skeleton-mans-worlds-cup';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];
            //var_dump($table);

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function skeleton2016WomensWorldCupAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://www.ibsf.org/en/standings/rankings?season=2016&ctype=6&session=WC', $curlOpt);
        
        $html = $dom->innerHtml;

        $dateCurrent = new \DateTime('now');

        // Write the contents back to the file
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_skeleton_2016WomensWorldCup_original.html';
        file_put_contents($file, $html);
        
        // Find table content
        $contents = $dom->find('div.table-responsive');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;
            unset($dom);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hidden-xs') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_skeleton_2016WomensWorldCup_cleaned.html';
            file_put_contents($file, $html);

            // 
            $table = [];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $cells = [];
                if ($rowsCount < 1) {
                    $cells = [
                        'place',
                        'team_name',
                        'team_country',
                        'score',
                    ];
                } else {
                    $rank = trim($tr->find('td.rank')[0]->innerHtml);
                    $img = $tr->find('td.athlete img')[0];
                    $country = is_object($img) ? trim($img->getAttribute('alt')) : null;
                    $athlete = trim(strip_tags(str_ireplace('(Junior)', '', $tr->find('td.athlete a')[0]->innerHtml)));
                    $points = trim($tr->find('td.points')[0]->innerHtml);
                    $cells = [
                        $rank,
                        $athlete,
                        $country,
                        $points,
                    ];
                }
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }

            //
            $resultLeague = 'ibsf-2016-skeleton-womens-worlds-cup';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];
            //var_dump($table);

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function bobsleigh4Man2016WorldCupAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://www.ibsf.org/en/standings/rankings?season=2016&ctype=2&session=WC', $curlOpt);
        
        $html = $dom->innerHtml;

        $dateCurrent = new \DateTime('now');

        // Write the contents back to the file
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_bobsleigh_2016_4ManWorldCup_original.html';
        file_put_contents($file, $html);
        
        // Find table content
        $contents = $dom->find('div.table-responsive');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;
            unset($dom);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hidden-xs') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_bobsleigh_2016_4ManWorldCup_cleaned.html';
            file_put_contents($file, $html);

            // 
            $table = [];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $cells = [];
                if ($rowsCount < 1) {
                    $cells = [
                        'place',
                        'team_name',
                        'team_country',
                        'score',
                    ];
                } else {
                    $rank = trim($tr->find('td.rank')[0]->innerHtml);
                    $img = $tr->find('td.athlete img')[0];
                    $country = is_object($img) ? trim($img->getAttribute('alt')) : null;
                    $athlete = trim(strip_tags(str_ireplace('(Junior)', '', $tr->find('td.athlete a')[0]->innerHtml)));
                    $points = trim($tr->find('td.points')[0]->innerHtml);
                    $cells = [
                        $rank,
                        $athlete,
                        $country,
                        $points,
                    ];
                }
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }

            $resultLeague = 'ibsf-2016-bobsleigh-4-man-worlds-cup';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];
            //var_dump($table);

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function bobsleigh2Man2016WorldCupAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://www.ibsf.org/en/standings/rankings?season=2016&ctype=1&session=WC', $curlOpt);
        
        $html = $dom->innerHtml;

        $dateCurrent = new \DateTime('now');

        // Write the contents back to the file
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_bobsleigh_2016_2ManWorldCup_original.html';
        file_put_contents($file, $html);
        
        // Find table content
        $contents = $dom->find('div.table-responsive');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;
            unset($dom);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hidden-xs') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_bobsleigh_2016_2ManWorldCup_cleaned.html';
            file_put_contents($file, $html);

            // 
            $table = [];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $cells = [];
                if ($rowsCount < 1) {
                    $cells = [
                        'place',
                        'team_name',
                        'team_country',
                        'score',
                    ];
                } else {
                    $rank = trim($tr->find('td.rank')[0]->innerHtml);
                    $img = $tr->find('td.athlete img')[0];
                    $country = is_object($img) ? trim($img->getAttribute('alt')) : null;
                    $athlete = trim(strip_tags(str_ireplace('(Junior)', '', $tr->find('td.athlete a')[0]->innerHtml)));
                    $points = trim($tr->find('td.points')[0]->innerHtml);
                    $cells = [
                        $rank,
                        $athlete,
                        $country,
                        $points,
                    ];
                }
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }

            $resultLeague = 'ibsf-2016-bobsleigh-2-man-worlds-cup';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];
            //var_dump($table);

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function skeleton2017MansWorldCupAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://www.ibsf.org/en/rankings/rankings?season=2017&ctype=5&session=WC', $curlOpt);
        
        $html = $dom->innerHtml;

        $dateCurrent = new \DateTime('now');

        // Write the contents back to the file
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_skeleton_2017MansWorldCup_original.html';
        file_put_contents($file, $html);
        
        // Find table content
        $contents = $dom->find('div.table-responsive');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;
            unset($dom);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hidden-xs') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_skeleton_2017MansWorldCup_cleaned.html';
            file_put_contents($file, $html);

            // 
            $table = [];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $cells = [];
                if ($rowsCount < 1) {
                    $cells = [
                        'place',
                        'team_name',
                        'team_country',
                        'score',
                    ];
                } else {
                    $rank = trim($tr->find('td.rank')[0]->innerHtml);
                    $img = $tr->find('td.athlete img')[0];
                    $country = is_object($img) ? trim($img->getAttribute('alt')) : null;
                    $athlete = trim(strip_tags(str_ireplace('(Junior)', '', $tr->find('td.athlete a')[0]->innerHtml)));
                    $points = trim($tr->find('td.points')[0]->innerHtml);
                    /*var_dump($rank);
                    echo PHP_EOL;
                    var_dump($athlete);
                    echo PHP_EOL;
                    var_dump($country);
                    echo PHP_EOL;
                    var_dump($points);
                    echo PHP_EOL.'------------------'.PHP_EOL;*/
                    $cells = [
                        $rank,
                        $athlete,
                        $country,
                        $points,
                    ];
                }
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }
            
            /*// Get array data
            $table = [];
            $domTable = new \DOMDocument(null, 'UTF-8');
            $res = @$domTable->loadHTML('<?xml encoding="UTF-8">' . $html);
            if ($res) {
                $rowsCount = 0;
                $neededIndexedColumns = [];
                foreach ($domTable->getElementsByTagName('tr') as $tr) {
                    $cells = [];
                    $columnIndex = 0;
                    foreach ($tr->childNodes as $td) {
                        $columnIndex++;
                        $value = trim((string)$td->nodeValue);
                        // headig row replace values
                        if ($rowsCount === 0) {
                            if ($value !== '') {
                                switch ($value) {
                                    case 'Rank' : $value = 'place'; break;
                                    case 'Athlete' : $value = 'team_name'; break;
                                    //case 'Spēles' : $value = 'matches'; break;
                                    case 'Points' : $value = 'score'; break;
                                    default : $value = ''; break;
                                }
                                $neededIndexedColumns[$columnIndex] = $value !== '' ? true : false;
                            }
                        }
                        // if column neded add value
                        if (isset($neededIndexedColumns[$columnIndex]) && $neededIndexedColumns[$columnIndex] === true) {
                            $cells[$columnIndex] = $value !== '' ? $value : '-';
                        }
                    }
                    // If table cells really found
                    if (count($cells) > 0) {
                        $table[] = $cells;
                    }
                    $rowsCount++;
                }
                $table = array_map('array_values', $table);
            } else {
                throw new \Core\Exception\RuntimeException('Error: can not parse table');
            }*/

            $resultLeague = 'ibsf-2017-skeleton-mans-worlds-cup';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];
            //var_dump($table);

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function skeleton2017WomensWorldCupAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://www.ibsf.org/en/rankings/rankings?season=2017&ctype=6&session=WC', $curlOpt);
        
        $html = $dom->innerHtml;

        $dateCurrent = new \DateTime('now');

        // Write the contents back to the file
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_skeleton_2017WomensWorldCup_original.html';
        file_put_contents($file, $html);
        
        // Find table content
        $contents = $dom->find('div.table-responsive');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;
            unset($dom);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hidden-xs') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_skeleton_2017WomensWorldCup_cleaned.html';
            file_put_contents($file, $html);

            // 
            $table = [];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $cells = [];
                if ($rowsCount < 1) {
                    $cells = [
                        'place',
                        'team_name',
                        'team_country',
                        'score',
                    ];
                } else {
                    $rank = trim($tr->find('td.rank')[0]->innerHtml);
                    $img = $tr->find('td.athlete img')[0];
                    $country = is_object($img) ? trim($img->getAttribute('alt')) : null;
                    $athlete = trim(strip_tags(str_ireplace('(Junior)', '', $tr->find('td.athlete a')[0]->innerHtml)));
                    $points = trim($tr->find('td.points')[0]->innerHtml);
                    $cells = [
                        $rank,
                        $athlete,
                        $country,
                        $points,
                    ];
                }
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }

            //
            $resultLeague = 'ibsf-2017-skeleton-womens-worlds-cup';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];
            //var_dump($table);

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function bobsleigh4Man2017WorldCupAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://www.ibsf.org/en/rankings/rankings?season=2017&ctype=2&session=WC', $curlOpt);
        
        $html = $dom->innerHtml;

        $dateCurrent = new \DateTime('now');

        // Write the contents back to the file
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_bobsleigh_2017_4ManWorldCup_original.html';
        file_put_contents($file, $html);
        
        // Find table content
        $contents = $dom->find('div.table-responsive');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;
            unset($dom);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hidden-xs') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_bobsleigh_2017_4ManWorldCup_cleaned.html';
            file_put_contents($file, $html);

            // 
            $table = [];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $cells = [];
                if ($rowsCount < 1) {
                    $cells = [
                        'place',
                        'team_name',
                        'team_country',
                        'score',
                    ];
                } else {
                    $rank = trim($tr->find('td.rank')[0]->innerHtml);
                    $img = $tr->find('td.athlete img')[0];
                    $country = is_object($img) ? trim($img->getAttribute('alt')) : null;
                    $athlete = trim(strip_tags(str_ireplace('(Junior)', '', $tr->find('td.athlete a')[0]->innerHtml)));
                    $points = trim($tr->find('td.points')[0]->innerHtml);
                    $cells = [
                        $rank,
                        $athlete,
                        $country,
                        $points,
                    ];
                }
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }

            $resultLeague = 'ibsf-2017-bobsleigh-4-man-worlds-cup';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];
            //var_dump($table);

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function bobsleigh2Man2017WorldCupAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://www.ibsf.org/en/rankings/rankings?season=2017&ctype=1&session=WC', $curlOpt);
        
        $html = $dom->innerHtml;

        $dateCurrent = new \DateTime('now');

        // Write the contents back to the file
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_bobsleigh_2017_2ManWorldCup_original.html';
        file_put_contents($file, $html);
        
        // Find table content
        $contents = $dom->find('div.table-responsive');
        if (isset($contents[0])) {
            $html = $contents[0]->innerHtml;
            unset($dom);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hidden-xs') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'winter_sports' . DS . $dateCurrent->format('Y-m-d') . '_bobsleigh_2017_2ManWorldCup_cleaned.html';
            file_put_contents($file, $html);

            // 
            $table = [];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $cells = [];
                if ($rowsCount < 1) {
                    $cells = [
                        'place',
                        'team_name',
                        'team_country',
                        'score',
                    ];
                } else {
                    $rank = trim($tr->find('td.rank')[0]->innerHtml);
                    $img = $tr->find('td.athlete img')[0];
                    $country = is_object($img) ? trim($img->getAttribute('alt')) : null;
                    $athlete = trim(strip_tags(str_ireplace('(Junior)', '', $tr->find('td.athlete a')[0]->innerHtml)));
                    $points = trim($tr->find('td.points')[0]->innerHtml);
                    $cells = [
                        $rank,
                        $athlete,
                        $country,
                        $points,
                    ];
                }
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }

            $resultLeague = 'ibsf-2017-bobsleigh-2-man-worlds-cup';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];
            //var_dump($table);

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function iihf2016PreliminaryRoundAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
        ];

        $dom = new DomParser;
        $dom->loadFromUrl('http://s.widgets.iihf.hockey/HYDRA/2016-WM/widget_en_2016_wm_standings.html', $curlOpt);
        $html = str_ireplace('jsonCallback({"widgetdata":[{"id":"widget_en_2016_wm_standings","content":"', '', $dom->innerHtml);
        $html = str_ireplace('"}]});', '', $html);
        //var_dump($html);

        // Get array data
        $table = [];
        $domTable = new \DOMDocument(null, 'UTF-8');
        $res = @$domTable->loadHTML('<?xml encoding="UTF-8">' . $html);
        if ($res) {
            $rowsCount = 0;
            $goalsColumnIndex = null;
            $neededIndexedColumns = [];
            foreach ($domTable->getElementsByTagName('tr') as $tr) {
                $cells = [];
                $columnIndex = 0;
                foreach ($tr->childNodes as $td) {
                    $columnIndex++;
                    $value = trim((string)$td->nodeValue);
                    echo $value . '; ';
                    /*// headig row replace values
                    if ($rowsCount === 0) {
                        if ($value !== '') {
                            switch ($value) {
                                case 'Vieta' : $value = 'place'; break;
                                case 'Komanda' : $value = 'team_name'; break;
                                case 'Spēles' : $value = 'matches'; break;
                                case 'Punkti' : $value = 'score'; break;
                                case 'Uzvaras (pamatl.)' : $value = 'win'; break;
                                case 'Uzvaras (PL/PSM)' : $value = 'win_ot'; break;
                                case 'Zaudējumi (pamatl.)' : $value = 'lose'; break;
                                case 'Zaudējumi (PL/PSM)' : $value = 'lose_ot'; break;
                                case 'Vārti' : $value = 'goals'; $goalsColumnIndex = $columnIndex; break;
                                default : $value = ''; break;
                            }
                            $neededIndexedColumns[$columnIndex] = $value !== '' ? true : false;
                        }
                    }
                    // if column neded add value
                    if (isset($neededIndexedColumns[$columnIndex]) && $neededIndexedColumns[$columnIndex] === true) {
                        if ($goalsColumnIndex === $columnIndex) {
                            if ($rowsCount === 0) {
                                $cells[$columnIndex] = $value;
                                $cells[$columnIndex+1] = 'miss';
                                $cells[$columnIndex+2] = 'different';
                            } else {
                                list($goals, $miss) = explode(':', $value, 2);
                                $different = (int)$goals < (int)$miss ? '-' . ((int)$miss - (int)$goals) : '+' . ((int)$goals - (int)$miss);
                                $cells[$columnIndex] = $goals;
                                $cells[$columnIndex+1] = $miss;
                                $cells[$columnIndex+2] = $different;
                            }
                        } else {
                            $cells[$columnIndex] = $value !== '' ? $value : '-';
                        }
                    }*/
                }
                echo PHP_EOL;
                // If table cells really found
                if (count($cells) > 0) {
                    $table[] = $cells;
                }
                $rowsCount++;
            }
            $table = array_map('array_values', $table);
        } else {
            throw new \Core\Exception\RuntimeException('Error: can not parse table');
        }
            
        echo 'Parse IIHF 2016 Preliminary Round';
    }

    public function iihf2017PreliminaryRoundAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');
        
        echo 'Parse IIHF 2017 Preliminary Round';
    }

    public function fia2017RallyCrossSuperCarWrxAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $dataFields = [
            'catID' => '1',
            'standingsQty' => '100',
            'showTitle' => 'false',
            'showTableHeaders' => 'true',
            'fullstandings' => 'true'
        ];

        $curlOpt = [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => http_build_query($dataFields, '', '&')
        ];

        $url = 'http://www.fiaworldrallycross.com/_inc/standings.php';

        $curl = new ParserCurl($curlOpt);
        $html = $curl->get($url);

        if ($html !== '') {
            $dateCurrent = new \DateTime('now');

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'automoto' . DS . $dateCurrent->format('Y-m-d') . '_rally_cross_fia_2017_SuperCarWrx_original.html';
            file_put_contents($file, $html);

            // 
            $html = file_get_contents($file);
            $html = str_ireplace('<tr >', '<tr>', $html);
            $html = str_ireplace('<td><span class="driver">', '<td class="driver">', $html);
            $html = str_ireplace('</span> ', ' | ', $html);
            //var_dump($html);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hide-for-medium-down') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'automoto' . DS . $dateCurrent->format('Y-m-d') . '_rally_cross_fia_2017_SuperCarWrx_cleaned.html';
            file_put_contents($file, $html);

            //
            $table = [];
            $table[] = [
                'place',
                'team_name',
                'team_additional_info',
                'team_country',
                'score',
            ];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html, [
                'whitespaceTextNode' => true,
                'strict'             => false,
                'cleanupInput'       => false,
                'removeScripts'      => true,
                'removeStyles'       => true,
                'preserveLineBreaks' => true,
            ]);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $place = trim($tr->find('td.position')[0]->innerHtml);
                $img = $tr->find('td.hide-for-small-only img')[0];
                $country = is_object($img) ? trim($img->getAttribute('src')) : null;
                if (strlen((string)$country) > 0) {
                    $country = str_ireplace('/_img/_flags/', '', $country);
                    $country = str_ireplace('.png', '', $country);
                }
                $driver = trim(is_object($tr->find('td.driver a')[0]) ? $tr->find('td.driver a')[0]->innerHtml : $tr->find('td.driver')[0]->innerHtml);
                $points = trim($tr->find('td.points')[0]->innerHtml);
                list($driver, $car) = explode('|', $driver);/*
                var_dump($place);
                echo PHP_EOL;
                var_dump(trim($driver));
                echo PHP_EOL;
                var_dump(trim($car));
                echo PHP_EOL;
                var_dump($country);
                echo PHP_EOL;
                var_dump($points);
                echo PHP_EOL.'------------------'.PHP_EOL;*/
                $table[] = [
                    trim($place),
                    trim($driver),
                    trim($car),
                    $country,
                    trim($points),
                ];
                $rowsCount++;
            }

            //var_dump($table);die();

            $resultLeague = 'fia-2017-rally-cross-supercar-wrx';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function fia2017RallyCrossSuper1600Action()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $dataFields = [
            'catID' => '2',
            'standingsQty' => '100',
            'showTitle' => 'false',
            'showTableHeaders' => 'true',
            'fullstandings' => 'true'
        ];

        $curlOpt = [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS  => 20,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => http_build_query($dataFields, '', '&')
        ];

        $url = 'http://www.fiaworldrallycross.com/_inc/standings.php';

        $curl = new ParserCurl($curlOpt);
        $html = $curl->get($url);

        if ($html !== '') {
            $dateCurrent = new \DateTime('now');

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'automoto' . DS . $dateCurrent->format('Y-m-d') . '_rally_cross_fia_2017_Super1600_original.html';
            file_put_contents($file, $html);

            // 
            $html = file_get_contents($file);
            $html = str_ireplace('<tr >', '<tr>', $html);
            $html = str_ireplace('<td><span class="driver">', '<td class="driver">', $html);
            $html = str_ireplace('</span> ', ' | ', $html);
            //var_dump($html);

            // Remove unneeded data from stats table html source
            $dom2 = new DomParser;
            $dom2->load($html);
            foreach ($dom2->getElementsByClass('hide-for-medium-down') as $elem) {
                $elem->delete();
                unset($elem);
            }
            $html = (string) $dom2;
            unset($dom2);

            // Write the contents back to the file
            $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'automoto' . DS . $dateCurrent->format('Y-m-d') . '_rally_cross_fia_2017_Super1600_cleaned.html';
            file_put_contents($file, $html);

            //
            $table = [];
            $table[] = [
                'place',
                'team_name',
                'team_additional_info',
                'team_country',
                'score',
            ];
            $rowsCount = 0;
            $dom3 = new DomParser;
            $dom3->load($html, [
                'whitespaceTextNode' => true,
                'strict'             => false,
                'cleanupInput'       => false,
                'removeScripts'      => true,
                'removeStyles'       => true,
                'preserveLineBreaks' => true,
            ]);
            foreach ($dom3->getElementsByTag('tr') as $tr) {
                $place = trim($tr->find('td.position')[0]->innerHtml);
                $img = $tr->find('td.hide-for-small-only img')[0];
                $country = is_object($img) ? trim($img->getAttribute('src')) : null;
                if (strlen((string)$country) > 0) {
                    $country = str_ireplace('/_img/_flags/', '', $country);
                    $country = str_ireplace('.png', '', $country);
                }
                $driver = trim(is_object($tr->find('td.driver a')[0]) ? $tr->find('td.driver a')[0]->innerHtml : $tr->find('td.driver')[0]->innerHtml);
                $points = trim($tr->find('td.points')[0]->innerHtml);
                list($driver, $car) = explode('|', $driver);/*
                var_dump($place);
                echo PHP_EOL;
                var_dump(trim($driver));
                echo PHP_EOL;
                var_dump(trim($car));
                echo PHP_EOL;
                var_dump($country);
                echo PHP_EOL;
                var_dump($points);
                echo PHP_EOL.'------------------'.PHP_EOL;*/
                $table[] = [
                    trim($place),
                    trim($driver),
                    trim($car),
                    $country,
                    trim($points),
                ];
                $rowsCount++;
            }

            //var_dump($table);

            $resultLeague = 'fia-2017-rally-cross-super1600';
            $resultData = [
                [
                    'name' => 'overall',
                    'table' => $table,
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    public function khl2017Action()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $html = '';

        // Get html source
        $dateCurrent = new \DateTime('now');
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_' . $dateCurrent->format('H') . '_khl_2017-2018_cleaned.html';
        if (!file_exists($file)) {
            $curl = new ParserCurl();
            $html = $curl->get('https://www.sports.ru/stat/tags/tournament/table/4084863.html?page_type=table&no_controls=0');
            if ($html !== '') {
                // Remove unneeded data from html source
                $dom2 = new DomParser;
                $dom2->load($html, ['removeScripts' => true ]);
                $cssClassesToRemove = ['overBox', 'description'];
                foreach ($cssClassesToRemove as $cssClassToRemove) {
                    foreach ($dom2->getElementsByClass($cssClassToRemove) as $elem) {
                        $elem->delete();
                        unset($elem);
                    }
                }
                $html = (string) $dom2;
                // Write to file
                $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_' . $dateCurrent->format('H') . '_khl_2017-2018_cleaned.html';
                file_put_contents($file, $html);
            }
        } else {
            $html = file_get_contents($file);
        }
        
        if (trim($html) !== '') {
            
            $domAll = new DomParser;
            $domAll->load(trim($html), ['removeScripts' => true ]);

            $data = [ 'west' => [],'east' => [] ];

            $counter = 0;
            foreach ($domAll->getElementsByTag('table') as $table) {
                if ($counter <= 1) {
                    $tblKey = $counter === 1 ? 'east' : 'west';
                    $domTable = new DomParser;
                    $domTable->load(trim($table->find('tbody')->innerHtml), ['removeScripts' => true ]);

                    //echo trim($table->find('tbody')->innerHtml) . PHP_EOL;die();

                    $data[$tblKey] = [];
                    $data[$tblKey][] = [ "place", "team_name", "matches", "win", "win_ot", "lose", "lose_ot", "score", "goals", "miss", "different", "flag" ];
                    foreach ($domTable->getElementsByTag('tr') as $tr) {
                        $domRow = new DomParser;
                        $domRow->load(trim($tr->innerHtml), ['removeScripts' => true ]);

                        $rowData = [];
                        $flag = '';
                        foreach ($domRow->getElementsByTag('td') as $k => $td) {
                            if ($k === 0) {
                                $flag = '';
                            }
                            if ($k === 1) {
                                $i = $td->find('div.hide-field i')[1];
                                $flag = is_object($i) ? trim($i->getAttribute('alt')) : '';
                                $a = $tr->find('div.hide-field a.name')[0];
                                $team = is_object($a) ? trim($a->innerHtml) : '';
                                if ($team === '') {
                                    $team = is_object($a) ? trim($a->getAttribute('href')) : '';
                                    if (strlen((string)$team) > 0) {
                                        $team = str_ireplace('https://www.sports.ru/', '', $team);
                                        $team = str_ireplace('/', '', $team);
                                    }
                                }
                                $rowData[$k] = trim($this->getKhlTranslation($team));
                            } else {
                                if ($k === 0) {
                                    $rowData[$k] = trim(str_ireplace('*', '', strip_tags((string)$td->innerHtml)));
                                } else {
                                    $rowData[$k] = trim(strip_tags((string)$td->innerHtml));
                                }
                            }
                            if ($k === 10) {
                                $rowData[11] = $this->getKhlTranslation($flag);
                            }
                        }
                        $data[$tblKey][] = $rowData;
                        //var_dump($rowData); echo PHP_EOL.'--------------------------------'.PHP_EOL;
                    }
                } else {
                    break;
                }
                $counter++;
            }
            //var_dump($data); echo PHP_EOL.'--------------------------------'.PHP_EOL;
            
            
            $resultLeague = 'khl-2017-2018';
            $resultData = [
                [
                    'name' => 'west',
                    'table' => $data['west'],
                ],
                [
                    'name' => 'east',
                    'table' => $data['east'],
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    protected function getKhlTranslation($value = '')
    {
        if ($value === '') {
            return $value;
        }

        $value = str_ireplace(['Россия'], ['ru'], $value);
        $value = str_ireplace(['Финляндия'], ['fi'], $value);
        $value = str_ireplace(['Латвия'], ['lv'], $value);
        $value = str_ireplace(['Беларусь'], ['by'], $value);
        $value = str_ireplace(['Словакия'], ['sk'], $value);
        $value = str_ireplace(['Казахстан'], ['kz'], $value);
        $value = str_ireplace(['Китай'], ['ch'], $value);
        //$value = str_ireplace(['Horvatija'], ['hr'], $value);

        $value = str_ireplace(['ЦСКА'], ['CSKA'], $value);
        $value = str_ireplace(['Слован'], ['Slovan Bratislava'], $value);
        $value = str_ireplace(['Торпедо'], ['Torpedo'], $value);
        $value = str_ireplace(['СКА'], ['SKA'], $value);
        $value = str_ireplace(['Йокерит'], ['Jokerit'], $value);
        $value = str_ireplace(['Сочи'], ['HC Sochi'], $value);
        $value = str_ireplace(['Витязь'], ['Vityaz'], $value);
        $value = str_ireplace(['Локомотив'], ['Lokomotiv'], $value);
        $value = str_ireplace(['Динамо Минск'], ['Dinamo Minsk'], $value);
        $value = str_ireplace(['Динамо Рига'], ['Dinamo Riga'], $value);
        $value = str_ireplace(['Динамо Москва'], ['Dynamo Moscow'], $value);
        $value = str_ireplace(['Северсталь'], ['Severstal'], $value);
        $value = str_ireplace(['Спартак'], ['HC Spartak'], $value);
        $value = str_ireplace(['Авангард'], ['Avangard'], $value);
        $value = str_ireplace(['Ак Барс'], ['Ak Bars'], $value);
        $value = str_ireplace(['Барыс'], ['Barys'], $value);
        $value = str_ireplace(['Куньлунь'], ['Kunlun'], $value);
        $value = str_ireplace(['Металлург Мг'], ['Metallurg Magnitogorsk'], $value);
        $value = str_ireplace(['Салават Юлаев'], ['Salavat Yulaev'], $value);
        $value = str_ireplace(['Адмирал'], ['Admiral'], $value);
        $value = str_ireplace(['Трактор'], ['Traktor'], $value);
        $value = str_ireplace(['Нефтехимик'], ['Neftekhimik'], $value);
        $value = str_ireplace(['Сибирь'], ['Sibir'], $value);
        $value = str_ireplace(['Автомобилист'], ['Avtomobilist'], $value);
        $value = str_ireplace(['Амур'], ['Amur'], $value);
        $value = str_ireplace(['Лада'], ['Lada'], $value);
        $value = str_ireplace(['Югра'], ['Ugra'], $value);

        return $value;
    }

    public function eurobasket2017action()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $html = '';

        // Get html source
        $dateCurrent = new \DateTime('now');
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'basketball' . DS . $dateCurrent->format('Y-m-d') . '_' . $dateCurrent->format('H') . '_eurobasket_2017.html';
        if (!file_exists($file)) {
            $curl = new ParserCurl();
            $html = $curl->get('https://www.sports.ru/stat/tags/tournament/table/161038840.html?page_type=table&no_controls=0');
            if ($html !== '') {
                $html = str_replace('<tbody>', '', $html);
                $html = str_replace('</tbody>', '', $html);

                // Write to file
                $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'basketball' . DS . $dateCurrent->format('Y-m-d') . '_' . $dateCurrent->format('H') . '_eurobasket_2017.html';
                file_put_contents($file, $html);
            }            
        } else {
            $html = file_get_contents($file);
        }


        // Parse html
        if (trim($html) !== '') {
            
            $domAll = new DomParser;
            $domAll->load(trim($html), ['removeScripts' => true ]);

            $data = [ 'group_a' => [], 'group_b' => [], 'group_c' => [], 'group_d' => [] ];

            $counter = 0;
            foreach ($domAll->getElementsByTag('table') as $table) {
                if ($counter <= 3) {
                    $tblKey = 'group_a';
                    if ($counter === 1) {
                        $tblKey = 'group_b';
                    }
                    if ($counter === 2) {
                        $tblKey = 'group_c';
                    }
                    if ($counter === 3) {
                        $tblKey = 'group_d';
                    }

                    $domTable = new DomParser;
                    $domTable->load(trim($table->innerHtml), ['removeScripts' => true ]);

                    $data[$tblKey] = [];
                    $data[$tblKey][] = [ "place", "team_name", "matches", "win", "lose", "win_prc", "goals_avg", "miss_avg", "different", "home", "away" ];//, "flag" ];
                    foreach ($domTable->getElementsByTag('tr') as $x => $tr) {
                        if ($x < 6) {
                            $domRow = new DomParser;
                            $domRow->load(trim($tr->innerHtml));
                            $rowData = [];
                            foreach ($domRow->getElementsByTag('td') as $k => $td) {
                                if ($k === 1) {
                                    $a = $tr->find('div.hide-field a.name')[0];
                                    $team = is_object($a) ? trim($a->innerHtml) : '';
                                    $rowData[$k] = trim($this->getEurobasketTranslation($team));
                                } else {
                                    if ($k === 0) {
                                        $rowData[$k] = trim(str_ireplace('*', '', strip_tags((string)$td->innerHtml)));
                                    } else {
                                        $rowData[$k] = trim(strip_tags((string)$td->innerHtml));
                                    }
                                }
                            }
                            $data[$tblKey][] = $rowData;
                        }
                    }
                } else {
                    break;
                }
                $counter++;
            }
            //var_dump($data); echo PHP_EOL.'--------------------------------'.PHP_EOL;
            
            $resultLeague = 'eurobasket-2017';
            $resultData = [
                [
                    'name' => 'group_a',
                    'table' => $data['group_a'],
                ],
                [
                    'name' => 'group_b',
                    'table' => $data['group_b'],
                ],
                [
                    'name' => 'group_c',
                    'table' => $data['group_c'],
                ],
                [
                    'name' => 'group_d',
                    'table' => $data['group_d'],
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
            
        }
    }

    protected function getEurobasketTranslation($value = '')
    {
        if ($value === '') {
            return $value;
        }
        $value = str_ireplace(['Греция'], ['Greece'], $value);
        $value = str_ireplace(['Исландия'], ['Iceland'], $value);
        $value = str_ireplace(['Польша'], ['Poland'], $value);
        $value = str_ireplace(['Словения'], ['Slovenia'], $value);
        $value = str_ireplace(['Финляндия'], ['Finland'], $value);
        $value = str_ireplace(['Франция'], ['France'], $value);

        $value = str_ireplace(['Германия'], ['Germany'], $value);
        $value = str_ireplace(['Грузия'], ['Georgia'], $value);
        $value = str_ireplace(['Израиль'], ['Israel'], $value);
        $value = str_ireplace(['Италия'], ['Italy'], $value);
        $value = str_ireplace(['Литва'], ['Lithuania'], $value);
        $value = str_ireplace(['Украина'], ['Ukraine'], $value);

        $value = str_ireplace(['Венгрия'], ['Hungary'], $value);
        $value = str_ireplace(['Испания'], ['Spain'], $value);
        $value = str_ireplace(['Румыния'], ['Romania'], $value);
        $value = str_ireplace(['Хорватия'], ['Croatia'], $value);
        $value = str_ireplace(['Черногория'], ['Montenegro'], $value);
        $value = str_ireplace(['Чехия'], ['CZE'], $value);

        $value = str_ireplace(['Бельгия'], ['Belgium'], $value);
        $value = str_ireplace(['Великобритания'], ['Great Britain'], $value);
        $value = str_ireplace(['Латвия'], ['Latvia'], $value);
        $value = str_ireplace(['Россия'], ['Russia'], $value);
        $value = str_ireplace(['Сербия'], ['Serbia'], $value);
        $value = str_ireplace(['Турция'], ['Turkey'], $value);

        return $value;
    }

    public function nhl2017Action()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $html = '';

        // Get html source
        $dateCurrent = new \DateTime('now');
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_' . $dateCurrent->format('H') . '_nhl_2017-2018_cleaned.html';
        if (!file_exists($file)) {
            $curl = new ParserCurl();
            $html = $curl->get('https://www.sports.ru/stat/tags/tournament/table/1694525.html?page_type=table&no_controls=0');
            if ($html !== '') {
                // Remove unneeded data from html source
                $dom2 = new DomParser;
                $dom2->load($html, ['removeScripts' => true ]);
                $cssClassesToRemove = ['overBox', 'description'];
                foreach ($cssClassesToRemove as $cssClassToRemove) {
                    foreach ($dom2->getElementsByClass($cssClassToRemove) as $elem) {
                        $elem->delete();
                        unset($elem);
                    }
                }
                $html = (string) $dom2;
                // Write to file
                $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'hockey' . DS . $dateCurrent->format('Y-m-d') . '_' . $dateCurrent->format('H') . '_nhl_2017-2018_cleaned.html';
                file_put_contents($file, $html);
            }
        } else {
            $html = file_get_contents($file);
        }
        
        if (trim($html) !== '') {
            
            $domAll = new DomParser;
            $domAll->load(trim($html), ['removeScripts' => true ]);

            $data = [ 'west' => [], 'east' => [] ];

            $counter = 0;
            foreach ($domAll->getElementsByTag('table') as $table) {
                if ($counter <= 1) {
                    $tblKey = $counter === 1 ? 'west' : 'east';
                    $domTable = new DomParser;
                    $domTable->load(trim($table->find('tbody')->innerHtml), ['removeScripts' => true ]);

                    //echo trim($table->find('tbody')->innerHtml) . PHP_EOL;die();

                    $data[$tblKey] = [];
                    $data[$tblKey][] = [ "place", "team_name", "matches", "win", "win_ot", "lose", "lose_ot", "score", "goals", "miss", "different", "flag" ];
                    foreach ($domTable->getElementsByTag('tr') as $tr) {
                        $domRow = new DomParser;
                        $domRow->load(trim($tr->innerHtml), ['removeScripts' => true ]);

                        $rowData = [];
                        $flag = '';
                        foreach ($domRow->getElementsByTag('td') as $k => $td) {
                            if ($k === 0) {
                                $flag = '';
                            }
                            if ($k === 1) {
                                $i = $td->find('div.hide-field i')[1];
                                $flag = is_object($i) ? trim($i->getAttribute('alt')) : '';
                                $a = $tr->find('div.hide-field a.name')[0];
                                $team = is_object($a) ? trim($a->innerHtml) : '';
                                if ($team === '') {
                                    $team = is_object($a) ? trim($a->getAttribute('href')) : '';
                                    if (strlen((string)$team) > 0) {
                                        $team = str_ireplace('https://www.sports.ru/', '', $team);
                                        $team = str_ireplace('/', '', $team);
                                    }
                                }
                                $rowData[$k] = trim($this->getNhlTranslation($team));
                            } else {
                                if ($k === 0) {
                                    $rowData[$k] = trim(str_ireplace('*', '', strip_tags((string)$td->innerHtml)));
                                } else {
                                    $rowData[$k] = trim(strip_tags((string)$td->innerHtml));
                                }
                            }
                            if ($k === 10) {
                                $rowData[11] = $this->getNhlTranslation($flag);
                            }
                        }
                        $data[$tblKey][] = $rowData;
                        //var_dump($rowData); echo PHP_EOL.'--------------------------------'.PHP_EOL;
                    }
                } else {
                    break;
                }
                $counter++;
            }
            //var_dump($data); echo PHP_EOL.'--------------------------------'.PHP_EOL;
            
            
            $resultLeague = 'nhl-2017-2018';
            $resultData = [
                [
                    'name' => 'west',
                    'table' => $data['west'],
                ],
                [
                    'name' => 'east',
                    'table' => $data['east'],
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
        }
    }

    protected function getNhlTranslation($value = '')
    {
        if ($value === '') {
            return $value;
        }

        $value = str_ireplace(['Канада'], ['ca'], $value);
        $value = str_ireplace(['США'], ['us'], $value);

        $value = str_ireplace(['Сент-Луис'], ['St.Louis Blues'], $value);
        $value = str_ireplace(['Вегас'], ['Vegas Golden Knights'], $value);
        $value = str_ireplace(['Чикаго'], ['Chicago Blackhawks'], $value);
        $value = str_ireplace(['Лос-Анджелес'], ['Los Angeles Kings'], $value);
        $value = str_ireplace(['Колорадо'], ['Colorado Avalanche'], $value);
        $value = str_ireplace(['Калгари'], ['Calgary Flames'], $value);
        $value = str_ireplace(['Ванкувер'], ['Vancouver Canucks'], $value);
        $value = str_ireplace(['Анахайм'], ['Anaheim Ducks'], $value);
        $value = str_ireplace(['Даллас'], ['Dallas Stars'], $value);
        $value = str_ireplace(['Эдмонтон'], ['Edmonton Oilers'], $value);
        $value = str_ireplace(['Нэшвилл'], ['Nashville Predators'], $value);
        $value = str_ireplace(['Виннипег'], ['Winnipeg Jets'], $value);
        $value = str_ireplace(['Миннесота'], ['Minnesota Wild'], $value);
        $value = str_ireplace(['Аризона'], ['Arizona Coyotes'], $value);
        $value = str_ireplace(['Сан-Хосе'], ['San Jose Sharks'], $value);

        $value = str_ireplace(['Торонто'], ['Toronto Maple Leafs'], $value);
        $value = str_ireplace(['Вашингтон'], ['Washington Capitals'], $value);
        $value = str_ireplace(['Нью-Джерси'], ['New Jersey Devils'], $value);
        $value = str_ireplace(['Тампа-Бэй'], ['Tampa Bay Lightning'], $value);
        $value = str_ireplace(['Коламбус'], ['Columbus Blue Jackets'], $value);
        $value = str_ireplace(['Детройт'], ['Detroit Red Wings'], $value);
        $value = str_ireplace(['Оттава'], ['Ottawa Senators'], $value);
        $value = str_ireplace(['Филадельфия'], ['Philadelphia Flyers'], $value);
        $value = str_ireplace(['Каролина'], ['Carolina Hurricanes'], $value);
        $value = str_ireplace(['Айлендерс'], ['New York Islanders'], $value);
        $value = str_ireplace(['Питтсбург'], ['Pittsburgh Penguins'], $value);
        $value = str_ireplace(['Флорида'], ['Florida Panthers'], $value);
        $value = str_ireplace(['Бостон'], ['Boston Bruins'], $value);
        $value = str_ireplace(['Рейнджерс'], ['New York Rangers'], $value);
        $value = str_ireplace(['Монреаль'], ['Montreal Canadiens'], $value);
        $value = str_ireplace(['Баффало'], ['Buffalo Sabres'], $value);

        return $value;
    }

    protected function nba2017Action()
    {
        
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');

        $html = '';

        /*// Get html source
        $dateCurrent = new \DateTime('now');
        $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'basketball' . DS . $dateCurrent->format('Y-m-d') . '_' . $dateCurrent->format('H') . '_eurobasket_2017.html';
        if (!file_exists($file)) {
            $curl = new ParserCurl();
            $html = $curl->get('https://www.sports.ru/stat/tags/tournament/table/161038840.html?page_type=table&no_controls=0');
            if ($html !== '') {
                $html = str_replace('<tbody>', '', $html);
                $html = str_replace('</tbody>', '', $html);

                // Write to file
                $file = ROOT_PATH . DS . 'data' . DS . 'statistics_parser' . DS . 'basketball' . DS . $dateCurrent->format('Y-m-d') . '_' . $dateCurrent->format('H') . '_eurobasket_2017.html';
                file_put_contents($file, $html);
            }            
        } else {
            $html = file_get_contents($file);
        }


        // Parse html
        if (trim($html) !== '') {
            
            $domAll = new DomParser;
            $domAll->load(trim($html), ['removeScripts' => true ]);

            $data = [ 'group_a' => [], 'group_b' => [], 'group_c' => [], 'group_d' => [] ];

            $counter = 0;
            foreach ($domAll->getElementsByTag('table') as $table) {
                if ($counter <= 3) {
                    $tblKey = 'group_a';
                    if ($counter === 1) {
                        $tblKey = 'group_b';
                    }
                    if ($counter === 2) {
                        $tblKey = 'group_c';
                    }
                    if ($counter === 3) {
                        $tblKey = 'group_d';
                    }

                    $domTable = new DomParser;
                    $domTable->load(trim($table->innerHtml), ['removeScripts' => true ]);

                    $data[$tblKey] = [];
                    $data[$tblKey][] = [ "place", "team_name", "matches", "win", "lose", "win_prc", "goals_avg", "miss_avg", "different", "home", "away" ];//, "flag" ];
                    foreach ($domTable->getElementsByTag('tr') as $x => $tr) {
                        if ($x < 6) {
                            $domRow = new DomParser;
                            $domRow->load(trim($tr->innerHtml));
                            $rowData = [];
                            foreach ($domRow->getElementsByTag('td') as $k => $td) {
                                if ($k === 1) {
                                    $a = $tr->find('div.hide-field a.name')[0];
                                    $team = is_object($a) ? trim($a->innerHtml) : '';
                                    $rowData[$k] = trim($this->getEurobasketTranslation($team));
                                } else {
                                    if ($k === 0) {
                                        $rowData[$k] = trim(str_ireplace('*', '', strip_tags((string)$td->innerHtml)));
                                    } else {
                                        $rowData[$k] = trim(strip_tags((string)$td->innerHtml));
                                    }
                                }
                            }
                            $data[$tblKey][] = $rowData;
                        }
                    }
                } else {
                    break;
                }
                $counter++;
            }
            //var_dump($data); echo PHP_EOL.'--------------------------------'.PHP_EOL;
            
            $resultLeague = 'nba-2017';
            $resultData = [
                [
                    'name' => 'group_a',
                    'table' => $data['group_a'],
                ],
                [
                    'name' => 'group_b',
                    'table' => $data['group_b'],
                ],
                [
                    'name' => 'group_c',
                    'table' => $data['group_c'],
                ],
                [
                    'name' => 'group_d',
                    'table' => $data['group_d'],
                ],
            ];

            // Save to DB
            $parsedResult = $this->getParserResultRepo()->findObjectByKey($resultLeague);
            if (!is_object($parsedResult)) {
                $parsedResult = new SportParserResult();
                $this->getEntityManager()->persist($parsedResult);
            }
            $parsedResult->setKey($resultLeague);
            $parsedResult->setParsedAt($dateCurrent);
            $parsedResult->setParsedData($resultData);
            $this->getEntityManager()->flush();
            
        }*/
    }

    /*public function fia2017WrcDriversAction()
    {
        //http://www.wrc.com/live-ticker/daten/2017/matrix_driver.html

        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');
    }

    public function fia2017Wrc2DriversAction()
    {
        //http://www.wrc.com/live-ticker/daten/2017/matrix_driver.wrc-2.html

        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');
    }

    public function fia2017Wrc3DriversAction()
    {
        //http://www.wrc.com/live-ticker/daten/2017/matrix_driver.wrc-3.html

        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');
    }

    public function fia2017JwrcDriversAction()
    {
        //http://www.wrc.com/live-ticker/daten/2017/matrix_driver.jwrc.html

        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        ini_set('memory_limit', '512M');
    }*/

}
