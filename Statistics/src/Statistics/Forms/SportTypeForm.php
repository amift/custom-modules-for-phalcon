<?php

namespace Statistics\Forms;

use Articles\Entity\Category;
use Common\Forms\Element\MuliSelect;
use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Statistics\Entity\SportType;
use Statistics\Tool\StatsField;

class SportTypeForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param SportType $entity
     * @param array $options
     * @return void
     */
    public function initialize(SportType $entity = null, array $options = [])
    {
        $articleCategoryLvl1 = $entity->getArticleCategoryLvl1();
        if (is_object($articleCategoryLvl1)) {
            $entity->setArticleCategoryLvl1($articleCategoryLvl1->getId());
        }

        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Title
        $title = new Text('title', [ 'class' => 'form-control', 'tabindex' => '1', 'placeholder' => 'Title' ]);
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
                'object_repository' => $this->getEntityManager()->getRepository(SportType::class),
                'fields' => [ 'title' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Sport type with given title already exists',
                ],
            ])
        ]);
        $this->add($title);

        // Key
        $key = new Text('key', [
            'placeholder' => 'Key',
            'class' => 'form-control', 
            'tabindex' => '2'
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
                'object_repository' => $this->getEntityManager()->getRepository(SportType::class),
                'fields' => [ 'key' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Sport type with given key already exists',
                ],
            ])
        ]);
        $this->add($key);

        // Score field
        $scoreField = new Select('scoreField', StatsField::getLabels(), [ 'class' => 'selectpicker', 'tabindex' => '3' ]);
        $scoreField->setLabel('Score field');
        $scoreField->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => StatsField::getFields()
            ])
        ]);
        $this->add($scoreField);

        // Arcticle Category Level 1
        $parant1Options = $this->getArticleCategoriesList(1);
        $keys = array_keys($parant1Options);
        if (isset($keys[0])) unset($keys[0]);
        $articleCategoryLvl1 = new Select('articleCategoryLvl1', $parant1Options, [ 'class' => 'selectpicker', 'tabindex' => '4' ]);
        $articleCategoryLvl1->setLabel('Article category LVL 1');
        $articleCategoryLvl1->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined value given',
                'domain' => $keys,
            ])
        ]);
        $this->add($articleCategoryLvl1);
        
        // Table fields
        if (is_string($entity->getTableFields())) {
            $entity->setTableFields([$entity->getTableFields()]);
        }
        $tableFields = new MuliSelect('tableFields', StatsField::getLabels(), [
            'class' => 'selectpicker', 'tabindex' => '5'
        ]);
        $tableFields->setLabel('Table fields');
        $tableFields->addValidators([
            $validatorRequired
        ]);
        $this->add($tableFields);

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
