<?php

namespace Members\Forms;

use Common\Doctrine\Validator\UniqueObjectValidator as UniqueObject;
use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Users\Validator\PasswordStrengthValidator as PasswordStrength;
use Members\Entity\Member;

class RegisterForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @return void
     */
    public function initialize()
    {
        $validatorRequired = new PresenceOf([
            'message' => 'Lauks ir obligāts'
        ]);

        // Username
        $username = new Text('username', [
            'placeholder' => 'Lietotāja vārds',
            'tabindex' => '1'
        ]);
        $username->setLabel('Lietotāja vārds');
        $username->addValidators([
            $validatorRequired,
            new StringLength([
                'max' => 30,
                'min' => 2,
                'messageMaximum' => "Max 30 simboli atļauti",
                'messageMinimum' => "Vismaz 2 simboli jāievada"
            ])
        ]);
        $this->add($username);

        // Email
        $email = new Text('email', [
            'placeholder' => 'E-pasts',
            'tabindex' => '2'
        ]);
        $email->setLabel('E-pasts');
        $email->addValidators([
            $validatorRequired,
            new Email([
                'message' => 'E-pasta adrese nav korekta'
            ]),
            new UniqueObject([
                'object_manager' => $this->getEntityManager(),
                'object_repository' => $this->getEntityManager()->getRepository(Member::class),
                'fields' => [ 'email' ],
                'simulate_identifier' => true, // Use this when no ID fields in form given
                'messages' => [
                    UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'E-pasts jau ir aizņemts, lūdzu, izvēlies citu',
                ],
            ])
        ]);
        $this->add($email);

        // Password
        $password = new Password('password', [
            'placeholder' => 'Parole',
            'tabindex' => '3'
        ]);
        $password->setLabel('Parole');
        $password->addValidators([
            $validatorRequired,
            new PasswordStrength([
                'minLength' => 8,
                'messages' => [
                    PasswordStrength::LENGTH => "Jāsatur vismaz 8 simbolus",
                    PasswordStrength::UPPER  => "Jāsatur vismaz 1 lielais burts",
                    PasswordStrength::LOWER  => "Jāsatur vismaz 1 mazais burts",
                    PasswordStrength::DIGIT  => "Jāsatur vismaz 1 cipars"
                ]
            ])
        ]);
        $password->clear();
        $this->add($password);

        // Password confirm
        $passwordConfirm = new Password('password_confirm', [
            'placeholder' => 'Paroles apstiprinājums',
            'tabindex' => '4'
        ]);
        $passwordConfirm->setLabel('Paroles apstiprinājums');
        $passwordConfirm->addValidators([
            new Confirmation([
                'message' => "Paroles apstiprinājums nesakrīt",
                'with' => 'password'
            ])
        ]);
        $passwordConfirm->clear();
        $this->add($passwordConfirm);

        /*// CSRF
        $csrf = new Hidden('csrf');
        $csrf->addValidator(new Identical([
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF validation failed'
        ]));
        $csrf->clear();
        $this->add($csrf);*/

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
