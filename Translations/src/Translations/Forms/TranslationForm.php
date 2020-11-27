<?php

namespace Translations\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Translations\Entity\Translation;
use Translations\Tool\Group;

class TranslationForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param Translation $entity
     * @param array $options
     * @return void
     */
    public function initialize(Translation $entity = null, array $options = [])
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        $attributes = [
            'key' => [
                'class' => 'form-control', 'tabindex' => '1', 'placeholder' => 'Key',
            ],
            'group' => [
                'class' => 'selectpicker', 'tabindex' => '2',
            ]
        ];
        if (isset($options['edit']) && $options['edit'] === true) {
            $attributes['key']['disabled'] = 'disabled';
            $attributes['group']['disabled'] = 'disabled';
        }

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Key
        $firstName = new Text('key', $attributes['key']);
        $firstName->setLabel('Key');
        $firstName->addValidators([
            $validatorRequired,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Translation::class),
                'fields' => [ 'key' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Translation with given key already exists',
                ],
            ])
        ]);
        $this->add($firstName);

        // Group
        $group = new Select('group', Group::getLabels(), $attributes['group']);
        $group->setLabel('Group');
        $group->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined group given',
                'domain' => Group::getKeys()
            ])
        ]);
        $this->add($group);

        // Default value
        $defaultValue = new TextArea('defaultValue', [
            'class' => 'form-control',
            'tabindex' => '3',
            'style' => 'height: 100px;'
        ]);
        $defaultValue->setLabel('Translation');
        $this->add($defaultValue);

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

}
