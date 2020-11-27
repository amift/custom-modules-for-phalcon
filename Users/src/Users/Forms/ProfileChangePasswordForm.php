<?php

namespace Users\Forms;

use Core\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\PresenceOf;
use Users\Entity\User;
use Users\Validator\PasswordStrengthValidator as PasswordStrength;

class ProfileChangePasswordForm extends Form
{

    /**
     * @var EntityManager 
     */
    protected $_em;

    /**
     * Form initialize
     * 
     * @access public
     * @param User $entity
     * @param array $options
     * @return void
     */
    public function initialize(User $entity = null, array $options = [])
    {
        // In edition the id is hidden
        $id = new Hidden('id');
        $this->add($id);

        // Old Password
        $oldPassword = new Password('oldPassword', [
            'placeholder' => 'Current password',
            'class' => 'form-control',
            'tabindex' => '1'
        ]);
        $oldPassword->setLabel('Current password');
        $oldPassword->addValidators([
            new PresenceOf([
                'message' => 'Value is required'
            ])
        ]);
        $oldPassword->clear();
        //$this->add($oldPassword);

        // New Password
        $password = new Password('newPassword', [
            'placeholder' => 'Password',
            'class' => 'form-control',
            'tabindex' => '2'
        ]);
        $password->setLabel('New password');
        $password->addValidators([
            new PresenceOf([
                'message' => 'Value is required'
            ]),
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
        $password->clear();
        $this->add($password);

        // Password confirm
        $passwordConfirm = new Password('passwordConfirm', [
            'placeholder' => 'Confirm password',
            'class' => 'form-control',
            'tabindex' => '3'
        ]);
        $passwordConfirm->setLabel('Verify new password');
        $passwordConfirm->addValidators([
            new Confirmation([
                'message' => "Password doesn't match confirmation",
                'with' => 'newPassword'
            ])
        ]);
        $passwordConfirm->clear();
        $this->add($passwordConfirm);

        // Submit button
        $this->add(new Submit('go', [
            'class' => 'btn btn-primary active',
            'value' => 'Change',
            'role' => 'button',
            'tabindex' => '4'
        ]));

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
