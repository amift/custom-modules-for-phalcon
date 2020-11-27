<?php

namespace Users\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use Phalcon\Validation\Validator\StringLength;
use Users\Entity\User;
use Users\Tool\State;
use Users\Validator\PasswordStrengthValidator as PasswordStrength;

class UserAddForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    public function initialize()
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Value is required'
        ]);

        $validatorNamesLength = new StringLength([
            'max' => 50,
            'min' => 2,
            'messageMaximum' => "Max 50 chars allowed",
            'messageMinimum' => "At least 2 chars need"
        ]);

        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // First name
        $firstName = new Text('firstName', [
            'placeholder' => 'Name',
            'class' => 'form-control',
            'tabindex' => '1'
        ]);
        $firstName->setLabel('Name');
        $firstName->addValidators([
            $validatorRequired,
            $validatorNamesLength
        ]);
        $this->add($firstName);

        // Last name
        $lastName = new Text('lastName', [
            'placeholder' => 'Surname',
            'class' => 'form-control',
            'tabindex' => '2'
        ]);
        $lastName->setLabel('Surname');
        $lastName->addValidators([
            $validatorRequired,
            $validatorNamesLength
        ]);
        $this->add($lastName);

        // Email
        $email = new Text('email', [
            'placeholder' => 'E-mail',
            'class' => 'form-control',
            'tabindex' => '3'
        ]);
        $email->setLabel('E-mail');
        $email->addValidators([
            $validatorRequired,
            new Email([
                'message' => 'The e-mail is not valid'
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(User::class),
                'fields' => [ 'email' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'User with given email already exists',
                ],
            ])
        ]);
        $this->add($email);

        // Phone
        $phone = new Text('phone', [
            'placeholder' => 'Phone',
            'class' => 'form-control',
            'tabindex' => '4'
        ]);
        $phone->setLabel('Phone');
        $phone->addValidators([
            $validatorRequired,
            new RegexValidator([
                'pattern' => '/^[-\+ \d]{8,}$/',
                'message' => 'Phone number is not valid'
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(User::class),
                'fields' => [ 'phone' ],
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'User with given phone already exists',
                ],
            ])
        ]);
        $this->add($phone);
        
        // State
        $state = new Select('state', State::getLabels(), ['class' => 'selectpicker', 'tabindex' => '5']);
        $state->setLabel('Status');
        $state->addValidators([
            $validatorRequired,
            new InclusionIn([
                'message' => 'Undefined status given',
                'domain' => State::getStates()
            ])
        ]);
        $this->add($state);

        // Password
        $password = new Password('newPassword', [
            'placeholder' => 'Password',
            'class' => 'form-control',
            'tabindex' => '6'
        ]);
        $password->setLabel('Password');
        $password->addValidators([
            $validatorRequired,
            new PasswordStrength([
                'minLength' => 8,
                'messages' => [
                    PasswordStrength::LENGTH => "Password must be at least 8 characters in length",
                    PasswordStrength::UPPER  => "Password must contain at least one uppercase letter",
                    PasswordStrength::LOWER  => "Password must contain at least one lowercase letter",
                    PasswordStrength::DIGIT  => "Password must contain at least one digit character"
                ]
            ])
        ]);
        $this->add($password);

        // Password confirm
        $passwordConfirm = new Password('passwordConfirm', [
            'placeholder' => 'Confirm password',
            'class' => 'form-control',
            'tabindex' => '7'
        ]);
        $passwordConfirm->setLabel('Confirm password');
        $passwordConfirm->addValidators([
            new Confirmation([
                'message' => "Password doesn't match confirmation",
                'with' => 'newPassword'
            ])
        ]);
        $passwordConfirm->clear();
        $this->add($passwordConfirm);

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
