<?php

namespace Members\Forms;

use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\PresenceOf;
use Members\Entity\Member;
use Users\Validator\PasswordStrengthValidator as PasswordStrength;

class ChangePasswordForm extends Form
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
            'message' => 'Lauks ir obligāts'
        ]);

        // Old Password
        $oldPassword = new Password('old_password', [
            'placeholder' => 'Tekošā parole',
            'tabindex' => '1'
        ]);
        $oldPassword->setLabel('Tekošā parole');
        $oldPassword->addValidators([
            $validatorRequired
        ]);
        $oldPassword->clear();
        $this->add($oldPassword);

        // New Password
        $password = new Password('new_password', [
            'placeholder' => 'Jaunā parole',
            'tabindex' => '2'
        ]);
        $password->setLabel('Jaunā parole');
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
            'tabindex' => '3'
        ]);
        $passwordConfirm->setLabel('Paroles apstiprinājums');
        $passwordConfirm->addValidators([
            new Confirmation([
                'message' => "Jaunās paroles apstiprinājums nesakrīt",
                'with' => 'new_password'
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
