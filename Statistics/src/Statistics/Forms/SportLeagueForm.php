<?php

namespace Statistics\Forms;

use Articles\Entity\Category;
use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportType;

class SportLeagueForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param SportLeague $entity
     * @param array $options
     * @return void
     */
    public function initialize(SportLeague $entity = null, array $options = [])
    {
        $sportTypeObject = $entity->getSportType();
        if (is_object($sportTypeObject)) {
            $entity->setSportType($sportTypeObject->getId());
        }

        $articleCategoryLvl2 = $entity->getArticleCategoryLvl2();
        if (is_object($articleCategoryLvl2)) {
            $entity->setArticleCategoryLvl2($articleCategoryLvl2->getId());
        }

        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Type
        $parant1Options = $this->getTypesList();
        $keys = array_keys($parant1Options);
        if (isset($keys[0])) unset($keys[0]);
        $sportType = new Select('sportType', $parant1Options, [ 'class' => 'selectpicker', 'tabindex' => '1' ]);
        $sportType->setLabel('Type');
        $sportType->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => $keys,
            ])
        ]);
        $this->add($sportType);

        // Title
        $title = new Text('title', [ 'class' => 'form-control', 'tabindex' => '2', 'placeholder' => 'Title' ]);
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
                'object_repository' => $this->getEntityManager()->getRepository(SportLeague::class),
                'fields' => [ 'title', 'sportType' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Sport league with given title already exists',
                ],
            ])
        ]);
        $this->add($title);

        // Key
        $key = new Text('key', [
            'placeholder' => 'Key',
            'class' => 'form-control', 
            'tabindex' => '3'
        ]);
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
                'object_repository' => $this->getEntityManager()->getRepository(SportLeague::class),
                'fields' => [ 'key', 'sportType' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Sport league with given key already exists',
                ],
            ])
        ]);
        $this->add($key);

        // Arcticle Category Level 2
        $parant2Options = $parant2OptionsToSelect = $this->getArticleCategoriesList(2);
        if (is_object($sportTypeObject)) {
            $articleCategoryLvl1Object = $sportTypeObject->getArticleCategoryLvl1();
            if (is_object($articleCategoryLvl1Object)) {
                if ((int)$articleCategoryLvl1Object->getId() > 0) {
                    $parant2OptionsToSelect = $this->getArticleCategoriesList(2, $articleCategoryLvl1Object->getId());
                }
            }
        }
        $articleCategoryLvl2 = new Select('articleCategoryLvl2', $parant2OptionsToSelect, [ 'class' => 'selectpicker', 'tabindex' => '4' ]);
        $articleCategoryLvl2->setLabel('Article category LVL 2');
        $articleCategoryLvl2->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => array_keys($parant2Options),
            ])
        ]);
        $this->add($articleCategoryLvl2);

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
    protected function getArticleCategoriesList($level = 1, $parentId = null)
    {
        $list = $this->getEntityManager()->getRepository(Category::class)->getParentsListFromLevel($level, $parentId);

        $rows = [
            '0' => '-'
        ];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        return $rows;
    }

}
