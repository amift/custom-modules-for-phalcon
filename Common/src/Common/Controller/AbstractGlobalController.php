<?php

namespace Common\Controller;

use Doctrine\ORM\EntityManager;
use Phalcon\Mvc\Controller;

class AbstractGlobalController extends Controller
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Get EntityManager.
     * 
     * @access protected
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        if ($this->_em === null || !$this->_em) {
            $this->_em = $this->di->getEntityManager();

        }

        return $this->_em;
    }

    /**
     * Get repository for an entity class.
     * 
     * @access protected
     * @param string $entityName Name of the entity
     * @return \Doctrine\ORM\EntityRepository Repository class
     */
    protected function getEntityRepository($entityName)
    {
        return $this->getEntityManager()->getRepository($entityName);
    }

}