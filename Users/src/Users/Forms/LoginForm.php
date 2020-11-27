<?php

namespace Users\Forms;

use Core\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\Identical;

class LoginForm extends Form
{

    public function initialize()
    {
        // Email
        $email = new Text('email', [
            'placeholder' => 'E-mail',
            'class' => 'form-control',
            'tabindex' => '1'
        ]);
        $email->setLabel('E-mail');
        $email->addValidators([
            new PresenceOf([
                'message' => 'The e-mail is required'
            ]),
            new Email([
                'message' => 'The e-mail is not valid'
            ])
        ]);
        $this->add($email);

        // Password
        $password = new Password('password', [
            'placeholder' => 'Password',
            'class' => 'form-control',
            'tabindex' => '2'
        ]);
        $password->setLabel('Password');
        $password->addValidator(new PresenceOf([
            'message' => 'The password is required'
        ]));
        $password->clear();
        $this->add($password);

        // Submit button
        $this->add(new Submit('go', [
            'class' => 'btn btn-primary active',
            'value' => 'Sign In',
            'role' => 'button',
            'tabindex' => '3'
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

}
