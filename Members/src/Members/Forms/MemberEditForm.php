<?php

namespace Members\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Common\Forms\Element\Checkbox;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Members\Entity\Member;
use Members\Tool\Confirmed;
use Members\Tool\State;

class MemberEditForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param Member $entity
     * @param array $options
     * @return void
     */
    public function initialize(Member $entity = null, array $options = [])
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        $validatorNamesLength = new StringLength([
            'max' => 30,
            'min' => 2,
            'messageMaximum' => "Max 30 chars allowed",
            'messageMinimum' => "At least 2 chars need"
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Username
        $username = new Text('username', [ 'class' => 'form-control', 'tabindex' => '1', 'placeholder' => 'Username' ]);
        $username->setLabel('Username');
        $username->addValidators([
            $validatorRequired,
            $validatorNamesLength,
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Member::class),
                'fields' => [ 'username' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Member with given username already exists',
                ],
            ])
        ]);
        $this->add($username);

        // Email
        $email = new Text('email', [
            'placeholder' => 'E-mail',
            'class' => 'form-control', 
            'tabindex' => '2'
        ]);
        $email->setLabel('E-mail');
        $email->addValidators([
            $validatorRequired,
            new Email([
                'message' => 'E-mail address is invalid'
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Member::class),
                'fields' => [ 'email' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'Member with given email already exists',
                ],
            ])
        ]);
        $this->add($email);

        // Status
        $state = new Select('state', State::getLabels(), [ 'class' => 'selectpicker', 'tabindex' => '3' ]);
        $state->setLabel('Status');
        $state->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined group given',
                'domain' => State::getStates()
            ])
        ]);
        $this->add($state);

        // Confirmed
        $state = new Select('confirmed', Confirmed::getShortLabels(), [ 'class' => 'selectpicker', 'tabindex' => '4' ]);
        $state->setLabel('Confirmed');
        $state->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined group given',
                'domain' => Confirmed::getStates()
            ])
        ]);
        $this->add($state);
        
        // Banned for news posting
        //$bannedPosting = new Check('bannedPosting', [ 'value' => 1, 'tabindex' => '5' ]);
        $bannedPosting = new Checkbox('bannedPosting', [ 'tabindex' => '5' ]);
        $bannedPosting->setLabel('Disabled articles adding');
        //$bannedPosting->setDefault(0); // or `false` in case it's not filtered
        //$bannedPosting->addFilter('int'); // filtering to boolean value
        $this->add($bannedPosting);
        
        // Banned for news posting
        $bannedCommenting = new Checkbox('bannedCommenting', [ 'tabindex' => '6' ]);
        $bannedCommenting->setLabel('Disabled comments adding');
        $this->add($bannedCommenting);

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
