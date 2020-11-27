<?php

namespace Statistics\Controller\Frontend;

use Common\Controller\AbstractFrontendController;
use Statistics\Entity\SportParserResult;
use Translations\Tool\Group;

class ResultsController extends AbstractFrontendController
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

    public function resultAction()
    {
        $key = $this->dispatcher->getParam('key', 'string', '');

        $parsedResult = $this->getParserResultRepo()->findObjectByKey($key);

        if (!is_object($parsedResult)) {
            $this->response->setJsonContent([
                'error' => 'Invalid tournament KEY'
            ]);
        } else {
            $this->response->setJsonContent([
                'updated' => $parsedResult->getParsedAt()->format('Y-m-d H:i:s'),
                'league' => $parsedResult->getKey(),
                'data' => $parsedResult->getParsedData()
            ]);
        }

        return $this->response->send();
    }

}
