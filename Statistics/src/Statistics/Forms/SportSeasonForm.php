<?php

namespace Statistics\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Common\Tool\Enable;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportSeason;
use Statistics\Entity\SportType;

class SportSeasonForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param SportSeason $entity
     * @param array $options
     * @return void
     */
    public function initialize(SportSeason $entity = null, array $options = [])
    {
        $sportType = $entity->getSportType();
        if (is_object($sportType)) {
            $entity->setSportType($sportType->getId());
        }

        $sportLeague = $entity->getSportLeague();
        if (is_object($sportLeague)) {
            $entity->setSportLeague($sportLeague->getId());
        }

        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Type
        $typesOptions = $this->getTypesList();
        $keys = array_keys($typesOptions);
        if (isset($keys[0])) unset($keys[0]);
        $sportType = new Select('sportType', $typesOptions, [ 'class' => 'selectpicker', 'tabindex' => '1' ]);
        $sportType->setLabel('Type');
        $sportType->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => $keys,
            ])
        ]);
        $this->add($sportType);

        // League
        $leaguesOptions = $this->getLeaguesList($entity->getSportType());
        $keys = array_keys($leaguesOptions);
        if (isset($keys[0])) unset($keys[0]);
        $sportLeague = new Select('sportLeague', $leaguesOptions, [ 'class' => 'selectpicker', 'tabindex' => '2' ]);
        $sportLeague->setLabel('League');
        $sportLeague->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => $keys,
            ])
        ]);
        $this->add($sportLeague);

        // Title
        $title = new Text('title', [ 'class' => 'form-control', 'tabindex' => '3', 'placeholder' => 'Title' ]);
        $title->setLabel('Title');
        $title->addValidators([
            $validatorRequired,
            new StringLength([
                'max' => 250,
                'min' => 2,
                'messageMaximum' => "Max 250 chars allowed",
                'messageMinimum' => "At least 2 chars need"
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(SportSeason::class),
                'fields' => [ 'title', 'sportType', 'sportLeague' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Sport season with given title already exists',
                ],
            ])
        ]);
        $this->add($title);

        // Key
        $key = new Text('key', [ 'class' => 'form-control', 'tabindex' => '4', 'placeholder' => 'Key' ]);
        $key->setLabel('Key');
        $key->addValidators([
            $validatorRequired,
            new StringLength([
                'max' => 50,
                'min' => 2,
                'messageMaximum' => "Max 50 chars allowed",
                'messageMinimum' => "At least 2 chars need"
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(SportSeason::class),
                'fields' => [ 'key', 'sportType', 'sportLeague' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Sport season with given key already exists',
                ],
            ])
        ]);
        $this->add($key);

        // Actual
        $actual = new Select('actual', Enable::getShortLabels(), [ 'class' => 'selectpicker', 'tabindex' => '5' ]);
        $actual->setLabel('Actual');
        $actual->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => Enable::getStates()
            ])
        ]);
        $this->add($actual);

        // importApiUrl
        $importApiUrl = new Text('importApiUrl', [ 'class' => 'form-control', 'tabindex' => '6', 'placeholder' => 'URL' ]);
        $importApiUrl->setLabel('Import API URL');
        $importApiUrl->addValidators([
            new StringLength([
                'max' => 250,
                'messageMaximum' => "Max 250 chars allowed"
            ])
        ]);
        $this->add($importApiUrl);

        // CSRF
        $csrf = new Hidden('csrf');
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        $csrf->clear();
        $this->add($csrf);

        parent::initialize();
    }

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
     * @return array
     */
    protected function getTypesList()
    {
        $list = $this->getEntityManager()->getRepository(SportType::class)->getList();

        $rows = [
            '0' => '-'
        ];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        return $rows;
    }

    /**
     * @return array
     */
    protected function getLeaguesList($sportTypeId = null)
    {
        $list = $this->getEntityManager()->getRepository(SportLeague::class)->getList($sportTypeId);

        $rows = [
            '0' => '-'
        ];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        return $rows;
    }

}
